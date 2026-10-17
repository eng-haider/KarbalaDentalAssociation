<?php

namespace Tests\Feature;

use App\Filament\Pages\OsifRegistrationSettings;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Resources\EventRegistrations\Pages\CreateEventRegistration;
use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Filament\Widgets\OsifProfessionalTitleCountsWidget;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Support\OsifInvitation;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OsifRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function osifEvent(array $attributes = []): Event
    {
        return Event::create([
            'title' => 'ملتقى كربلاء لجراحة الفم وزراعة الأسنان',
            'slug' => Event::OSIF_SLUG,
            'location' => 'جامعة السبطين للعلوم الطبية',
            'starts_at' => now()->addMonth()->setTime(9, 0),
            'registration_open' => true,
            ...$attributes,
        ]);
    }

    private function actingAsCoordinator(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_OSIF_COORDINATOR]);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('osif'));

        return $user;
    }

    public function test_the_form_offers_the_titles_managed_from_the_dashboard(): void
    {
        $this->osifEvent(['professional_titles' => ['طبيب أسنان استشاري']]);

        $this->get(route('osif.register'))
            ->assertOk()
            ->assertSee('طبيب أسنان استشاري', false)
            ->assertDontSee('طبيب أسنان اختصاص', false);
    }

    public function test_the_title_must_be_one_of_the_choices_and_the_clinic_question_is_required(): void
    {
        $event = $this->osifEvent();

        $this->post(route('events.register', $event), [
            'name' => 'علي حسين محمد',
            'phone' => '07801234567',
            'professional_title' => 'طبيب',
        ])->assertSessionHasErrors(['professional_title', 'owns_clinic']);

        $this->assertSame(0, EventRegistration::count());
    }

    public function test_email_is_stored_while_removed_fields_are_not(): void
    {
        $event = $this->osifEvent();

        $this->post(route('events.register', $event), [
            'name' => 'علي حسين محمد',
            'phone' => '07801234567',
            'professional_title' => 'طبيب أسنان اختصاص',
            'owns_clinic' => '0',
            'workplace' => 'عيادة خاصة',
            'city' => 'بغداد',
            'email' => 'ali@example.com',
            'attendance_type' => 'workshop',
        ])->assertSessionHasNoErrors();

        $registration = EventRegistration::sole();
        $this->assertFalse($registration->owns_clinic);
        $this->assertNull($registration->workplace);
        $this->assertNull($registration->city);
        $this->assertSame('ali@example.com', $registration->email);
        $this->assertNull($registration->attendance_type);
    }

    public function test_phone_numbers_become_wa_me_numbers(): void
    {
        $number = fn (string $phone) => (new EventRegistration(['phone' => $phone]))->whatsappNumber();

        $this->assertSame('9647801234567', $number('07801234567'));
        $this->assertSame('9647801234567', $number('0780 123 4567'));
        $this->assertSame('9647801234567', $number('+964 780 123 4567'));
        $this->assertSame('9647801234567', $number('+964 0780 123 4567'));
        $this->assertSame('9647801234567', $number('00964 7801234567'));
        $this->assertSame('9647801234567', $number('7801234567'));
    }

    public function test_coordinators_can_edit_the_professional_titles(): void
    {
        $event = $this->osifEvent();
        $this->actingAsCoordinator();

        Livewire::test(OsifRegistrationSettings::class)
            ->assertSet('data.registration_open', true)
            ->fillForm([
                'professional_titles' => [
                    ['title' => 'طبيب أسنان اختصاص'],
                    ['title' => ' طبيب أسنان استشاري '],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(
            ['طبيب أسنان اختصاص', 'طبيب أسنان استشاري'],
            $event->fresh()->professionalTitleOptions(),
        );
    }

    public function test_the_dashboard_counts_osif_registrations_for_each_professional_title(): void
    {
        $event = $this->osifEvent([
            'professional_titles' => [
                'طبيب أسنان اختصاص',
                'طبيب أسنان مقيم',
                'طبيب أسنان تدرج',
            ],
        ]);

        foreach ([
            ['حيدر علي حسن', '07801234561', 'طبيب أسنان اختصاص'],
            ['مهدي عامر قاسم', '07801234562', 'طبيب أسنان اختصاص'],
            ['زينب محمد كريم', '07801234563', 'طبيب أسنان مقيم'],
        ] as [$name, $phone, $title]) {
            $event->registrations()->create([
                'name' => $name,
                'phone' => $phone,
                'professional_title' => $title,
            ]);
        }

        Event::create(['title' => 'ندوة علمية', 'starts_at' => now()->addWeek()])
            ->registrations()
            ->create([
                'name' => 'مسجّل في فعالية أخرى',
                'phone' => '07801234564',
                'professional_title' => 'طبيب أسنان اختصاص',
            ]);

        $this->actingAsCoordinator();

        Livewire::test(OsifProfessionalTitleCountsWidget::class)
            ->assertSee('المسجّلون حسب الصفة المهنية أو الأكاديمية')
            ->assertSee('طبيب أسنان اختصاص')
            ->assertSee('طبيب أسنان مقيم')
            ->assertSee('طبيب أسنان تدرج')
            ->assertSee('2')
            ->assertSee('1')
            ->assertSee('0');
    }

    public function test_admins_still_see_every_event_and_the_settings_page_in_the_admin_panel(): void
    {
        $this->osifEvent()->registrations()->create([
            'name' => 'زينب علي حسن',
            'phone' => '07801234567',
            'professional_title' => 'طبيب أسنان مقيم',
            'owns_clinic' => false,
        ]);
        Event::create(['title' => 'ندوة علمية', 'starts_at' => now()->addWeek()])->registrations()->create([
            'name' => 'مصطفى كريم جاسم',
            'phone' => '07711112222',
            'membership_number' => 'KRB-7',
        ]);
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));

        $this->get(EventRegistrationResource::getUrl('index', panel: 'admin'))
            ->assertOk()
            ->assertSee('زينب علي حسن', false)
            ->assertSee('مصطفى كريم جاسم', false)
            ->assertSee('KRB-7', false);

        $this->get(OsifRegistrationSettings::getUrl(panel: 'admin'))
            ->assertOk()
            ->assertSee('خيارات «الصفة المهنية أو الأكاديمية»', false);
    }

    public function test_the_dashboard_lists_registrations_and_records_sent_invitations(): void
    {
        $event = $this->osifEvent();
        $registration = $event->registrations()->create([
            'name' => 'زينب علي حسن',
            'phone' => '07801234567',
            'whatsapp_phone' => '07801234567',
            'professional_title' => 'طبيب أسنان مقيم',
            'owns_clinic' => true,
            'whatsapp_consent' => true,
        ]);
        $this->actingAsCoordinator();

        $this->get(EventRegistrationResource::getUrl('index', panel: 'osif'))
            ->assertOk()
            ->assertSee('زينب علي حسن', false)
            ->assertSee('بانتظار الدعوة', false);

        // WhatsApp receives the invitation page link and renders its card preview.
        $payload = OsifInvitation::payload($registration);
        $link = rawurlencode($registration->invitationShareUrl());
        $this->assertStringContainsString($link, $payload['chatUrl']);
        $this->assertStringContainsString('تشمل الدعوة وجبة غداء', $payload['message']);
        $this->assertStringContainsString('يرجى إبراز رمز QR لاستلام وجبة الغداء', $payload['message']);
        $this->assertStringContainsString('يرجى إبراز الدعوة للدخول من البوابة الرئيسية', OsifInvitation::message($registration, false));

        Livewire::test(ListEventRegistrations::class)
            ->assertCanSeeTableRecords([$registration])
            ->mountAction(TestAction::make('sendInvitation')->table($registration))
            ->assertMountedActionModalSeeHtml('https://wa.me/9647801234567?text=')
            ->assertMountedActionModalSeeHtml($payload['chatUrl'])
            ->assertMountedActionModalSeeHtml(route('osif.invitation', $registration->fresh()->invitation_token))
            ->assertMountedActionModalSeeHtml('x-on:click.once="$wire.callMountedAction()"')
            ->assertMountedActionModalSee('إرسال رابط الدعوة')
            ->assertMountedActionModalSee('OSIF-'.str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT))
            ->callMountedAction()
            ->assertHasNoErrors();

        $registration->refresh();
        $this->assertNotNull($registration->invitation_sent_at);
        $this->assertTrue($registration->is_handled);

        $this->get(EventRegistrationResource::getUrl('index', panel: 'osif'))
            ->assertOk()
            ->assertSee('تم إرسال الدعوة — تمت المعالجة', false);
    }

    private function osifRegistration(): EventRegistration
    {
        return $this->osifEvent()->registrations()->create([
            'name' => 'حيدر عبد عون كاظم',
            'phone' => '07801234567',
            'whatsapp_phone' => '07801234567',
            'professional_title' => 'طبيب أسنان اختصاص',
            'owns_clinic' => true,
        ]);
    }

    public function test_scanning_the_qr_code_opens_the_attendees_invitation(): void
    {
        $registration = $this->osifRegistration();

        $this->get($registration->invitationUrl())
            ->assertOk()
            ->assertSee('دعوة صالحة', false)
            ->assertSee('د. حيدر عبد عون كاظم', false)
            ->assertSee('طبيب أسنان اختصاص', false)
            ->assertSee($registration->invitationCode(), false)
            ->assertSee('noindex', false);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $registration->invitation_token);
    }

    public function test_unknown_invitation_codes_say_so_instead_of_a_bare_404(): void
    {
        $this->osifRegistration();

        $this->get(route('osif.invitation', str_repeat('x', 40)))
            ->assertNotFound()
            ->assertSee('دعوة غير صالحة', false);
    }

    public function test_uploaded_cards_become_the_whatsapp_link_preview(): void
    {
        Storage::fake('public');
        $registration = $this->osifRegistration();
        $this->actingAsCoordinator();

        $this->post(route('osif.invitation.files', $registration), [
            'card' => UploadedFile::fake()->image('card.png', 1080, 1350),
            'preview' => UploadedFile::fake()->image('preview.jpg', 1200, 630),
            'pdf' => UploadedFile::fake()->createWithContent('invitation.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n"),
        ])->assertOk()->assertJsonStructure(['card', 'preview', 'pdf']);

        Storage::disk('public')->assertExists($registration->invitationFilePath('card.png'));
        Storage::disk('public')->assertExists($registration->invitationFilePath('preview.jpg'));
        Storage::disk('public')->assertExists($registration->invitationFilePath('invitation.pdf'));

        $this->get($registration->invitationUrl())
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.route('osif.invitation.card.image', $registration->invitation_token), false)
            ->assertSee('<meta property="og:image:type" content="image/png">', false)
            ->assertSee('<meta property="og:image:width" content="1080">', false)
            ->assertSee('<meta property="og:image:height" content="1350">', false)
            ->assertSee('تنزيل الدعوة (PDF)', false)
            ->assertSee('تنزيل كصورة', false);

        $this->get($registration->invitationShareUrl())
            ->assertOk()
            ->assertSee('<meta property="og:url" content="'.$registration->invitationShareUrl().'">', false);

        $this->get(route('osif.invitation.card', $registration->invitation_token))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.route('osif.invitation.card.image', $registration->invitation_token), false)
            ->assertSee('<meta property="og:image:type" content="image/png">', false)
            ->assertSee('تنزيل الصورة', false);

        $this->get(route('osif.invitation.card.image', $registration->invitation_token))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->get(route('osif.invitation.card.download', $registration->invitation_token))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=osif-invitation-'.$registration->id.'.png');

        $this->get(route('osif.invitation.pdf.download', $registration->invitation_token))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=osif-invitation-'.$registration->id.'.pdf');

        $registration->delete();
        Storage::disk('public')->assertMissing($registration->invitationFilePath('card.png'));
    }

    public function test_the_wide_preview_is_only_used_until_the_invitation_card_exists(): void
    {
        Storage::fake('public');
        $registration = $this->osifRegistration();
        Storage::disk('public')->put($registration->invitationFilePath('preview.jpg'), 'preview');

        $this->get($registration->invitationUrl())
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.route('osif.invitation.preview', $registration->invitation_token), false)
            ->assertSee('<meta property="og:image:type" content="image/jpeg">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false);
    }

    public function test_only_osif_managers_can_upload_cards(): void
    {
        Storage::fake('public');
        $registration = $this->osifRegistration();
        $files = fn () => [
            'card' => UploadedFile::fake()->image('card.png'),
            'preview' => UploadedFile::fake()->image('preview.jpg'),
        ];

        $this->postJson(route('osif.invitation.files', $registration), $files())->assertUnauthorized();

        $this->actingAs(User::factory()->create(['role' => 'member']));
        $this->postJson(route('osif.invitation.files', $registration), $files())->assertForbidden();

        Storage::disk('public')->assertMissing($registration->invitationFilePath('card.png'));
    }

    public function test_invitations_address_guests_as_doctors_without_doubling_a_title(): void
    {
        $guest = fn (string $name) => OsifInvitation::guestName(new EventRegistration(['name' => $name]));

        $this->assertSame('د. زينب علي حسن', $guest('زينب علي حسن'));
        $this->assertSame('د. داود سلمان', $guest('داود سلمان'));
        $this->assertSame('د. علي حسين', $guest('د. علي حسين'));
        $this->assertSame('د.علي حسين', $guest('د.علي حسين'));
        $this->assertSame('د علي حسين', $guest('د علي حسين'));
        $this->assertSame('الدكتورة زينب علي', $guest('الدكتورة زينب علي'));
        $this->assertSame('دكتور علي حسين', $guest('دكتور علي حسين'));
        $this->assertSame('Dr. Ali Hussein', $guest('Dr. Ali Hussein'));
    }

    public function test_admins_can_add_a_registration_with_only_a_name_and_title(): void
    {
        $event = $this->osifEvent();
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->get(EventRegistrationResource::getUrl('create', panel: 'admin'))->assertOk();

        Livewire::test(CreateEventRegistration::class)
            ->fillForm([
                'event_id' => $event->id,
                'name' => 'ضيف الملتقى',
                'professional_title' => 'طبيب أسنان اختصاص',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $registration = EventRegistration::sole();
        $this->assertSame('ضيف الملتقى', $registration->name);
        $this->assertSame('طبيب أسنان اختصاص', $registration->professional_title);
        $this->assertNull($registration->phone);
        $this->assertNotNull($registration->invitation_token);
    }

    public function test_added_registrations_need_a_name_and_title_and_a_phone_not_already_used(): void
    {
        $event = $this->osifEvent();
        $event->registrations()->create(['name' => 'زينب علي حسن', 'phone' => '07801234567']);
        $this->actingAsCoordinator();

        Livewire::test(CreateEventRegistration::class)
            ->fillForm(['name' => '', 'professional_title' => null, 'phone' => '07801234567'])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'professional_title' => 'required', 'phone' => 'unique']);

        $this->assertSame(1, EventRegistration::count());
    }

    public function test_coordinators_add_registrations_to_osif_without_picking_an_event(): void
    {
        $event = $this->osifEvent();
        $this->actingAsCoordinator();

        $this->get(EventRegistrationResource::getUrl('create', panel: 'osif'))->assertOk();

        Livewire::test(CreateEventRegistration::class)
            ->fillForm([
                'name' => 'علي حسين جواد',
                'professional_title' => 'طبيب أسنان مقيم',
                'phone' => '07712345678',
                'owns_clinic' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(EventRegistrationResource::getUrl('index'));

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'name' => 'علي حسين جواد',
            'whatsapp_phone' => '07712345678',
            'owns_clinic' => true,
        ]);
    }

    private function registerAs(Event $event, string $title, string $phone = '07801234567'): EventRegistration
    {
        return $event->registrations()->create([
            'name' => 'زينب علي حسن',
            'phone' => $phone,
            'professional_title' => $title,
        ]);
    }

    public function test_coordinators_set_seats_for_each_title_or_leave_it_open(): void
    {
        $event = $this->osifEvent();
        $this->registerAs($event, 'طبيب أسنان خريج جديد');
        $this->actingAsCoordinator();

        Livewire::test(OsifRegistrationSettings::class)
            ->assertSee('مسجّل حالياً: 1')
            ->fillForm([
                'professional_titles' => [
                    ['title' => 'طبيب أسنان اختصاص', 'limit' => ''],
                    ['title' => 'طبيب أسنان خريج جديد', 'limit' => '40'],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh();
        $this->assertSame(['طبيب أسنان اختصاص', 'طبيب أسنان خريج جديد'], $event->professionalTitleOptions());
        $this->assertSame(['طبيب أسنان خريج جديد' => 40], $event->professionalTitleLimits());
    }

    public function test_a_title_whose_seats_are_taken_is_shown_as_full_and_refused(): void
    {
        $event = $this->osifEvent(['professional_title_limits' => ['طبيب أسنان خريج جديد' => 1]]);
        $this->registerAs($event, 'طبيب أسنان خريج جديد');

        $this->get(route('osif.register'))
            ->assertOk()
            ->assertSee('<option value="طبيب أسنان خريج جديد" disabled>طبيب أسنان خريج جديد — اكتمل العدد</option>', false)
            ->assertSee('إرسال طلب التسجيل');

        $registration = ['name' => 'علي حسين محمد', 'phone' => '07801234568', 'email' => 'ali@example.com', 'owns_clinic' => '0'];

        $this->post(route('events.register', $event), [...$registration, 'professional_title' => 'طبيب أسنان خريج جديد'])
            ->assertSessionHasErrors(['professional_title' => 'نعتذر، اكتمل العدد المخصص لهذه الصفة.']);
        $this->assertSame(1, $event->registrations()->count());

        $this->post(route('events.register', $event), [...$registration, 'professional_title' => 'طبيب أسنان اختصاص'])
            ->assertSessionHasNoErrors();
        $this->assertSame(2, $event->registrations()->count());
    }

    public function test_registration_closes_once_every_title_is_full(): void
    {
        $event = $this->osifEvent([
            'professional_titles' => ['طبيب أسنان خريج جديد'],
            'professional_title_limits' => ['طبيب أسنان خريج جديد' => 1],
        ]);
        $this->registerAs($event, 'طبيب أسنان خريج جديد');

        $this->get(route('osif.register'))
            ->assertOk()
            ->assertSee('اكتمل العدد المخصص للتسجيل في جميع الفئات')
            ->assertDontSee('إرسال طلب التسجيل')
            ->assertDontSee('ماذا يحدث بعد التسجيل؟');
    }

    public function test_the_dashboard_shows_the_seats_left_for_each_title(): void
    {
        $event = $this->osifEvent([
            'professional_titles' => ['طبيب أسنان اختصاص', 'طبيب أسنان خريج جديد'],
            'professional_title_limits' => ['طبيب أسنان اختصاص' => 3, 'طبيب أسنان خريج جديد' => 1],
        ]);
        $this->registerAs($event, 'طبيب أسنان اختصاص');
        $this->registerAs($event, 'طبيب أسنان خريج جديد', '07801234568');
        $this->actingAsCoordinator();

        Livewire::test(OsifProfessionalTitleCountsWidget::class)
            ->assertSee('من أصل 3 · متبقٍ 2')
            ->assertSee('اكتمل العدد (1)');
    }

    public function test_organisers_can_still_add_someone_to_a_full_title_from_the_dashboard(): void
    {
        $event = $this->osifEvent(['professional_title_limits' => ['طبيب أسنان خريج جديد' => 1]]);
        $this->registerAs($event, 'طبيب أسنان خريج جديد');
        $this->actingAsCoordinator();

        Livewire::test(CreateEventRegistration::class)
            ->fillForm(['name' => 'ضيف الملتقى', 'professional_title' => 'طبيب أسنان خريج جديد'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $event->registrations()->where('professional_title', 'طبيب أسنان خريج جديد')->count());
    }

    public function test_invitations_are_standard_unless_vip_is_chosen_when_adding_someone(): void
    {
        $this->osifEvent();
        $this->actingAsCoordinator();

        Livewire::test(CreateEventRegistration::class)
            ->assertFormFieldExists('is_vip')
            ->assertSchemaStateSet(['is_vip' => false])
            ->fillForm(['name' => 'ضيف عادي', 'professional_title' => 'طبيب أسنان مقيم'])
            ->call('create')
            ->assertHasNoFormErrors();

        // VIP guests need no professional title; the field is hidden for them.
        Livewire::test(CreateEventRegistration::class)
            ->fillForm(['is_vip' => true])
            ->assertFormFieldHidden('professional_title')
            ->fillForm(['name' => 'ضيف مميز'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertFalse(EventRegistration::where('name', 'ضيف عادي')->sole()->is_vip);
        $vip = EventRegistration::where('name', 'ضيف مميز')->sole();
        $this->assertTrue($vip->is_vip);
        $this->assertNull($vip->professional_title);
    }

    public function test_vip_guests_get_their_own_message_page_label_and_dashboard_badge(): void
    {
        $registration = $this->osifRegistration();
        $registration->update(['is_vip' => true]);

        $payload = OsifInvitation::payload($registration);
        $this->assertTrue($payload['vip']);
        $this->assertSame($payload['chatUrl'], $payload['chatUrls']['vip']);
        $this->assertStringContainsString(rawurlencode('دعوة خاصة (VIP)'), $payload['chatUrls']['vip']);
        $this->assertStringContainsString(rawurlencode('يسعدنا تأكيد تسجيلك'), $payload['chatUrls']['standard']);
        $this->assertStringContainsString(rawurlencode($registration->invitationShareUrl()), $payload['chatUrls']['vip']);

        $this->get($registration->invitationUrl())
            ->assertOk()
            ->assertSee('دعوة VIP · '.$registration->invitationCode(), false)
            ->assertDontSee('طبيب أسنان اختصاص', false);

        $this->actingAsCoordinator();
        Livewire::test(ListEventRegistrations::class)
            ->assertSee('دعوة VIP')
            ->filterTable('is_vip', false)
            ->assertCanNotSeeTableRecords([$registration])
            ->filterTable('is_vip', true)
            ->assertCanSeeTableRecords([$registration])
            // The send dialog offers both types and links WhatsApp to the chosen one.
            ->mountAction(TestAction::make('sendInvitation')->table($registration))
            ->assertMountedActionModalSee('نوع الدعوة')
            ->assertMountedActionModalSeeHtml('x-on:click="setVip(true)"')
            ->assertMountedActionModalSeeHtml('x-bind:href="chatUrl"');
    }

    public function test_the_card_upload_saves_the_type_it_was_drawn_for(): void
    {
        Storage::fake('public');
        $registration = $this->osifRegistration();
        $this->actingAsCoordinator();
        $upload = fn (array $extra = []) => $this->post(route('osif.invitation.files', $registration), [
            'card' => UploadedFile::fake()->image('card.png', 1080, 1350),
            'preview' => UploadedFile::fake()->image('preview.jpg', 1200, 630),
            ...$extra,
        ])->assertOk();

        $upload(['vip' => '1']);
        $this->assertTrue($registration->refresh()->is_vip);

        // Older dashboards that send no type leave it alone.
        $upload();
        $this->assertTrue($registration->refresh()->is_vip);

        $upload(['vip' => '0']);
        $this->assertFalse($registration->refresh()->is_vip);
    }
}
