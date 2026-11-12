<?php

namespace Tests\Feature;

use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\OsifBroadcast;
use App\Models\OsifBroadcastMessage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OsifBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config([
            'services.adscientist.key' => 'test-key',
            'services.adscientist.broadcast_template' => 'msg',
            'services.adscientist.phone_number_id' => '737599209436750',
        ]);
    }

    /** What the fake Ad Scientist says about the «msg» template; a test may change it (then forget the cached list). */
    private string $templateStatus = 'APPROVED';

    private string $templateBody = 'بانتظاركم يوم الجمعة';

    private function fakeAdScientist(?string $status = null, ?string $body = null): void
    {
        $this->templateStatus = $status ?? $this->templateStatus;
        $this->templateBody = $body ?? $this->templateBody;

        Http::fake([
            'ai.adscientist.iq/v1/templates' => fn () => Http::response(['success' => true, 'data' => [[
                'name' => 'msg',
                'language' => 'ar',
                'status' => $this->templateStatus,
                'components' => [
                    ['type' => 'HEADER', 'format' => 'IMAGE'],
                    ['type' => 'BODY', 'text' => $this->templateBody],
                ],
            ]]]),
            'ai.adscientist.iq/v1/media/upload' => Http::response(['success' => true, 'data' => ['id' => 'media-123']]),
            'ai.adscientist.iq/*' => Http::response(['success' => true]),
        ]);
    }

    private function registration(array $attributes = []): EventRegistration
    {
        $event = Event::firstOrCreate(['slug' => Event::OSIF_SLUG], [
            'title' => 'ملتقى كربلاء لجراحة الفم وزراعة الأسنان',
            'starts_at' => now()->addDay()->setTime(9, 0),
            'registration_open' => true,
        ]);

        return $event->registrations()->create([
            'name' => 'حيدر عبد عون',
            'phone' => '07801234567',
            ...$attributes,
        ]);
    }

    private function sender(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_WHATSAPP_ADMIN]);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_message_goes_to_every_registered_number_once(): void
    {
        $this->fakeAdScientist();
        $first = $this->registration();
        $this->registration(['name' => 'تسجيل مكرر', 'phone' => '+964 780 123 4567']);
        $vip = $this->registration(['name' => 'ضيف VIP', 'phone' => '07700000001', 'is_vip' => true]);
        $this->registration(['name' => 'بلا رقم', 'phone' => '123']);
        // Another event's registrations are not OSIF's to message.
        Event::create(['title' => 'ندوة', 'starts_at' => now()->addWeek()])
            ->registrations()->create(['name' => 'ضيف ندوة', 'phone' => '07711111111']);
        $this->sender();

        $queue = $this->post('/osif/broadcasts', ['image' => UploadedFile::fake()->image('poster.jpg', 1080, 1080)], ['Accept' => 'application/json'])
            ->assertOk()
            ->json('queue');

        $this->assertCount(2, $queue);
        $broadcast = OsifBroadcast::sole();
        $this->assertSame('media-123', $broadcast->media_id);
        Storage::disk('public')->assertExists($broadcast->image_path);
        $this->assertSame(['9647801234567', '9647700000001'], $broadcast->messages()->orderBy('id')->pluck('phone')->all());
        $this->assertSame($first->getKey(), $broadcast->messages()->orderBy('id')->first()->event_registration_id);

        foreach ($queue as $item) {
            $this->postJson($item['url'])->assertOk()->assertJson(['sent' => true]);
        }

        // Pressed again (a second tab, a retry): never sent twice.
        $this->postJson($queue[0]['url'])->assertStatus(409);

        $sends = Http::recorded(fn (Request $request): bool => str_ends_with($request->url(), '/messages/send-template'))->values();
        $this->assertCount(2, $sends);
        $this->assertSame([
            'phoneNumberId' => '737599209436750',
            'phoneNumber' => '9647700000001',
            'templateName' => 'msg',
            'languageCode' => 'ar',
            'components' => [['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['media_id' => 'media-123']]]]],
        ], $sends[1][0]->data());

        $this->assertSame(0, $broadcast->pending()->count());
        $this->assertNull(OsifBroadcast::unfinished());
        $this->assertNotNull($vip->refresh());
    }

    public function test_a_refused_message_stays_waiting_for_the_next_run(): void
    {
        $this->registration();
        $this->sender();

        Http::fake([
            'ai.adscientist.iq/v1/templates' => Http::response(['success' => true, 'data' => [[
                'name' => 'msg', 'language' => 'ar', 'status' => 'APPROVED',
                'components' => [['type' => 'HEADER', 'format' => 'IMAGE'], ['type' => 'BODY', 'text' => 'نص']],
            ]]]),
            'ai.adscientist.iq/v1/media/upload' => Http::response(['success' => true, 'data' => ['id' => 'media-123']]),
            'ai.adscientist.iq/v1/messages/send-template' => Http::sequence()
                ->push(['success' => false, 'error' => ['message' => 'Rate limit']], 429)
                ->push(['success' => true]),
        ]);

        $url = $this->post('/osif/broadcasts', ['image' => UploadedFile::fake()->image('poster.png')], ['Accept' => 'application/json'])
            ->json('queue.0.url');

        $this->postJson($url)->assertStatus(422)->assertJson(['message' => 'Rate limit']);

        $message = OsifBroadcastMessage::sole();
        $this->assertNull($message->sent_at);
        $this->assertNull($message->claimed_at);
        $this->assertSame('Rate limit', $message->error);
        $this->assertTrue(OsifBroadcast::unfinished()->is($message->broadcast));

        $this->postJson($url)->assertOk();
        $this->assertNotNull($message->refresh()->sent_at);
    }

    public function test_the_image_is_required_and_the_template_must_be_approved(): void
    {
        $this->registration();
        $this->sender();

        $this->fakeAdScientist();
        $this->postJson('/osif/broadcasts')->assertStatus(422)->assertJson(['message' => 'اختر الصورة التي تظهر أعلى الرسالة.']);

        $this->post('/osif/broadcasts', ['image' => UploadedFile::fake()->create('poster.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson(['message' => 'الصورة يجب أن تكون JPG أو PNG.']);

        cache()->forget('adscientist.templates');
        $this->templateStatus = 'PENDING';
        $this->post('/osif/broadcasts', ['image' => UploadedFile::fake()->image('poster.jpg')], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJson(['message' => 'القالب msg لم تتم الموافقة عليه بعد (PENDING).']);

        cache()->forget('adscientist.templates');
        $this->templateStatus = 'APPROVED';
        $this->templateBody = 'مرحباً د. {{1}}';
        $this->post('/osif/broadcasts', ['image' => UploadedFile::fake()->image('poster.jpg')], ['Accept' => 'application/json'])
            ->assertStatus(422);

        $this->assertSame(0, OsifBroadcast::count());
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/messages/send-template'));
    }

    public function test_only_the_whatsapp_sending_role_may_send(): void
    {
        $this->fakeAdScientist();
        $this->registration();

        foreach ([User::ROLE_ADMIN, User::ROLE_OSIF_COORDINATOR, User::ROLE_OSIF_SCANNER] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'osif_hall' => 1]));

            $this->post('/osif/broadcasts', ['image' => UploadedFile::fake()->image('poster.jpg')], ['Accept' => 'application/json'])
                ->assertForbidden();
        }

        $this->assertSame(0, OsifBroadcast::count());
        Http::assertNothingSent();
    }

    public function test_the_dialog_shows_the_message_and_how_many_it_reaches(): void
    {
        $this->withoutVite();
        $this->fakeAdScientist(body: 'بانتظاركم لنصنع معاً نجاحاً مميزاً');
        $this->registration();
        $this->registration(['name' => 'ثاني', 'phone' => '07700000002']);
        $this->sender();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ListEventRegistrations::class)
            ->mountAction('sendBroadcast')
            ->assertMountedActionModalSee('بانتظاركم لنصنع معاً نجاحاً مميزاً')
            ->assertMountedActionModalSee('ستُرسل إلى 2 رقم واتساب')
            ->assertMountedActionModalSee('اختيار الصورة');

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/messages/send-template'));
    }
}
