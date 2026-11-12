<?php

namespace App\Models;

use App\Support\AdScientist;
use App\Support\OsifInvitation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * A WhatsApp template with fixed wording (an announcement or a reminder, such
 * as «msg») sent through Ad Scientist to everyone registered for OSIF, once
 * per number. Its image is uploaded to Ad Scientist once and used for every
 * message. The dashboard's dialog sends the messages one by one
 * (resources/js/osif-broadcast.js), so a run can stop and carry on later.
 */
class OsifBroadcast extends Model
{
    protected $fillable = [
        'template',
        'image_path',
        'media_id',
        'user_id',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(OsifBroadcastMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function templateName(): string
    {
        return (string) config('services.adscientist.broadcast_template');
    }

    /**
     * The template as Ad Scientist holds it, once it is one this button can
     * send: approved, with no {{1}} to fill in, and an image or text header.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public static function approvedTemplate(?string $name = null): array
    {
        $name ??= static::templateName();
        $template = AdScientist::template($name, 'ar');

        if (! $template) {
            throw new RuntimeException("لا يوجد قالب باسم {$name} باللغة العربية في Ad Scientist.");
        }

        if ($template['status'] !== 'APPROVED') {
            throw new RuntimeException("القالب {$name} لم تتم الموافقة عليه بعد ({$template['status']}).");
        }

        foreach ($template['components'] ?? [] as $part) {
            if (preg_match('/\{\{\s*\w+\s*\}\}/', (string) ($part['text'] ?? ''))) {
                throw new RuntimeException("القالب {$name} فيه متغيرات مثل {{1}}، وهذا الزر يرسل القوالب ذات النص الثابت فقط.");
            }

            if ($part['type'] === 'HEADER' && ! in_array($part['format'] ?? 'TEXT', ['TEXT', 'IMAGE'], true)) {
                throw new RuntimeException("رأس القالب {$name} من نوع {$part['format']}، وهذا الزر يدعم الصورة أو النص فقط.");
            }
        }

        return $template;
    }

    /** @param  array<string, mixed>  $template */
    public static function needsImage(array $template): bool
    {
        return (collect($template['components'] ?? [])->firstWhere('type', 'HEADER')['format'] ?? null) === 'IMAGE';
    }

    /** @param  array<string, mixed>  $template */
    public static function bodyText(array $template): ?string
    {
        return collect($template['components'] ?? [])->firstWhere('type', 'BODY')['text'] ?? null;
    }

    /**
     * Everyone registered for OSIF who has a WhatsApp number, oldest first;
     * two registrations with the same number share one message.
     *
     * @return Collection<string, EventRegistration> Keyed by the number, as WhatsApp::number() writes it.
     */
    public static function recipients(): Collection
    {
        return static::registrations()
            ->filter(fn (EventRegistration $registration): bool => (bool) static::number($registration))
            ->unique(fn (EventRegistration $registration): string => static::number($registration))
            ->keyBy(fn (EventRegistration $registration): string => static::number($registration));
    }

    /** The registration's WhatsApp number, unless it is too short to be one (a typo such as «123»). */
    public static function number(EventRegistration $registration): ?string
    {
        $number = $registration->whatsappNumber();

        return $number && strlen($number) >= 10 ? $number : null;
    }

    /** @return Collection<int, EventRegistration> */
    public static function registrations(): Collection
    {
        return EventRegistration::query()
            ->whereHas('event', fn ($event) => $event->where('slug', Event::OSIF_SLUG))
            ->oldest('id')
            ->get();
    }

    /**
     * Uploads the image to Ad Scientist and lists every number to send to.
     *
     * @throws RuntimeException when the template or the image cannot be used.
     */
    public static function start(User $user, ?UploadedFile $image): self
    {
        $template = static::approvedTemplate();
        $path = $mediaId = null;

        if (static::needsImage($template)) {
            if (! $image) {
                throw new RuntimeException('اختر الصورة التي تظهر أعلى الرسالة.');
            }

            $path = $image->store('osif-broadcasts', 'public');

            try {
                $mediaId = AdScientist::uploadMedia($image->get(), 'osif-'.now()->format('Ymd-His').'.'.$image->extension(), $image->getMimeType());
            } catch (Throwable $exception) {
                Storage::disk('public')->delete($path);

                throw $exception;
            }
        }

        return DB::transaction(function () use ($user, $template, $path, $mediaId): self {
            $broadcast = static::create([
                'template' => $template['name'],
                'image_path' => $path,
                'media_id' => $mediaId,
                'user_id' => $user->getKey(),
            ]);

            static::recipients()
                ->map(fn (EventRegistration $registration, string $number): array => [
                    'osif_broadcast_id' => $broadcast->getKey(),
                    'event_registration_id' => $registration->getKey(),
                    'phone' => $number,
                    'name' => OsifInvitation::guestName($registration),
                ])
                ->values()
                ->chunk(500)
                ->each(fn (Collection $rows) => OsifBroadcastMessage::insert($rows->all()));

            return $broadcast;
        });
    }

    /** The latest broadcast while some of its numbers are still waiting; null once it has reached them all. */
    public static function unfinished(): ?self
    {
        $latest = static::query()->latest('id')->first();

        return $latest?->pending()->exists() ? $latest : null;
    }

    public function pending(): HasMany
    {
        return $this->messages()->whereNull('sent_at');
    }

    /**
     * Sends the message to one number, unless it was sent already or another
     * tab is sending it right now.
     *
     * @return bool False when it was not this call's to send.
     *
     * @throws RuntimeException when Ad Scientist refuses it; the number stays waiting.
     */
    public function send(OsifBroadcastMessage $message): bool
    {
        // A claim left by a tab closed mid-send is released after a couple of minutes.
        $claimed = OsifBroadcastMessage::query()
            ->whereKey($message->getKey())
            ->whereNull('sent_at')
            ->where(fn ($query) => $query->whereNull('claimed_at')->orWhere('claimed_at', '<', now()->subMinutes(2)))
            ->update(['claimed_at' => now()]);

        if (! $claimed) {
            return false;
        }

        try {
            AdScientist::sendTemplate($message->phone, $this->template, $this->components());
        } catch (Throwable $exception) {
            // Released for the next run (the claim was written by the query above, not this model).
            OsifBroadcastMessage::query()->whereKey($message->getKey())
                ->update(['claimed_at' => null, 'error' => mb_substr($exception->getMessage(), 0, 500)]);

            throw $exception instanceof RuntimeException ? $exception : new RuntimeException($exception->getMessage(), 0, $exception);
        }

        $message->forceFill(['sent_at' => now(), 'error' => null])->save();

        return true;
    }

    /** @return array<int, array<string, mixed>> The image header and any quick-reply buttons. */
    private function components(): array
    {
        $parts = collect(static::approvedTemplate($this->template)['components'] ?? []);
        $components = [];

        if (static::needsImage(['components' => $parts->all()])) {
            if (blank($this->media_id)) {
                throw new RuntimeException('لا توجد صورة لهذه الرسالة؛ ابدأ إرسالاً جديداً مع الصورة.');
            }

            // As the invitations send their card: Ad Scientist takes a media id, not an image link.
            $components[] = ['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['media_id' => $this->media_id]]]];
        }

        foreach ($parts->firstWhere('type', 'BUTTONS')['buttons'] ?? [] as $index => $button) {
            if ($button['type'] === 'QUICK_REPLY') {
                $components[] = ['type' => 'button', 'sub_type' => 'quick_reply', 'index' => $index, 'parameters' => [['type' => 'payload', 'payload' => 'BTN_QUICK_REPLY']]];
            }
        }

        return $components;
    }
}
