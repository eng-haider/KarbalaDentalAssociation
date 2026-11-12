<?php

namespace App\Filament\Resources\EventRegistrations\Actions;

use App\Models\OsifBroadcast;
use App\Support\OsifInvitation;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

/**
 * Sends one WhatsApp template with fixed wording (an announcement or a
 * reminder, «msg» by default) to everyone registered for OSIF, once per
 * number. The dialog sends it number by number in the browser
 * (resources/js/osif-broadcast.js), so it can stop and carry on later.
 */
class SendBroadcastAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendBroadcast';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('إرسال رسالة للجميع')
            ->icon(Heroicon::OutlinedMegaphone)
            ->color(fn (): string => OsifInvitation::canSendByApi(auth()->user()) ? 'primary' : 'gray')
            // As «إرسال الدعوات للجميع»: administrators see it, and its dialog says what is missing.
            ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
            ->modalHeading('إرسال رسالة واتساب لكل المسجّلين')
            ->modalDescription('رسالة واحدة (قالب واتساب معتمد) تصل إلى كل المسجّلين في ملتقى OSIF، مرة واحدة لكل رقم.')
            ->modalWidth(Width::TwoExtraLarge)
            ->modalContent(function () {
                if ($reasons = OsifInvitation::apiUnavailableReasons(auth()->user())) {
                    return view('filament.osif.whatsapp-api-unavailable', ['reasons' => $reasons]);
                }

                try {
                    $template = OsifBroadcast::approvedTemplate();
                    $problem = null;
                } catch (RuntimeException $exception) {
                    $template = null;
                    $problem = $exception->getMessage();
                }

                $registrations = OsifBroadcast::registrations();
                $withNumber = $registrations->filter(fn ($registration): bool => (bool) OsifBroadcast::number($registration));
                $total = $withNumber->unique(fn ($registration): string => OsifBroadcast::number($registration))->count();
                $latest = OsifBroadcast::query()->withCount([
                    'messages',
                    'messages as sent_count' => fn ($query) => $query->whereNotNull('sent_at'),
                ])->latest('id')->first();
                $unfinished = $latest && $latest->sent_count < $latest->messages_count ? $latest : null;

                return view('filament.osif.broadcast-modal', [
                    'templateName' => $template['name'] ?? OsifBroadcast::templateName(),
                    'body' => $template ? OsifBroadcast::bodyText($template) : null,
                    'needsImage' => $template && OsifBroadcast::needsImage($template),
                    'problem' => $problem,
                    'total' => $total,
                    'noNumber' => $registrations->count() - $withNumber->count(),
                    'duplicates' => $withNumber->count() - $total,
                    'latest' => $latest,
                    'resume' => $unfinished ? [
                        'queue' => $unfinished->pending()->orderBy('id')->get()->map->queueItem()->all(),
                        'sent' => $unfinished->sent_count,
                        'total' => $unfinished->messages_count,
                    ] : null,
                    'storeUrl' => route('osif.broadcasts.store', absolute: false),
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('إغلاق');
    }
}
