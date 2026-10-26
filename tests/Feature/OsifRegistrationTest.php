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
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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

    /**
     * Ad Scientist's answers: the template's layout (an image header and three
     * buttons, the last a quick reply, like oct_26), an id for uploaded media,
     * and success for sends.
     */
    private function fakeWhatsAppApi(?string $header = 'IMAGE'): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'ai.adscientist.iq/v1/templates' => Http::response(['success' => true, 'data' => [[
                'name' => 'oct_26',
                'language' => 'ar',
                'status' => 'APPROVED',
                'components' => array_values(array_filter([
                    $header ? ['type' => 'HEADER', 'format' => $header] : null,
                    ['type' => 'BODY', 'text' => 'مرحباً د {{1}} … {{2}}'],
                    ['type' => 'BUTTONS', 'buttons' => [['type' => 'URL'], ['type' => 'PHONE_NUMBER'], ['type' => 'QUICK_REPLY']]],
                ])),
            ]]]),
            'ai.adscientist.iq/v1/media/upload' => Http::response(['success' => true, 'data' => ['id' => 'media-123']]),
            'ai.adscientist.iq/*' => Http::response(['success' => true]),
        ]);
    }

    /** The only role that sends through the WhatsApp API. */
    private function actingAsWhatsAppAdmin(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_WHATSAPP_ADMIN]);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('osif'));

        return $user;
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
        // The discounts page follows the invitation link, which stays first for the card preview.
        $this->assertStringContainsString(route('discounts'), $payload['message']);
        $this->assertLessThan(strpos($payload['message'], route('discounts')), strpos($payload['message'], $registration->invitationShareUrl()));
        $this->assertStringContainsString(rawurlencode(route('discounts')), $payload['chatUrls']['vip']);

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

    public function test_the_card_shows_residents_graduates_and_rotation_dentists_as_dentists(): void
    {
        $event = $this->osifEvent();

        foreach ([...EventRegistration::TITLES_SHOWN_AS_DENTIST, 'طبيب اسنان مقيم اقدم'] as $i => $title) {
            $registration = $this->registerAs($event, $title, '0780123456'.$i);

            $this->assertSame('طبيب أسنان', $registration->cardTitle(), $title);
            $this->assertSame('طبيب أسنان', OsifInvitation::payload($registration)['title']);
            $this->get($registration->invitationUrl())
                ->assertOk()
                ->assertSee('<p class="osif-pass-title">طبيب أسنان</p>', false);
            // The registration itself keeps the full title.
            $this->assertSame($title, $registration->fresh()->professional_title);
        }

        // The specialist keeps theirs.
        $specialist = $this->registerAs($event, 'طبيب أسنان اختصاص', '07801234599');
        $this->assertSame('طبيب أسنان اختصاص', $specialist->cardTitle());
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
            ->assertSee('اكتمل العدد المخصص للتسجيل في الملتقى')
            ->assertDontSee('إرسال طلب التسجيل')
            ->assertDontSee('ماذا يحدث بعد التسجيل؟');
    }

    public function test_the_form_reminds_applicants_they_answer_for_their_data(): void
    {
        $this->osifEvent();

        $this->get(route('osif.register'))
            ->assertOk()
            ->assertSeeInOrder(['يتحمل المتقدم صحة كافة البيانات، وبخلاف ذلك يتحمل المسؤولية القانونية.', 'إرسال طلب التسجيل']);
    }

    public function test_closed_or_full_registration_says_every_seat_is_booked(): void
    {
        $event = $this->osifEvent(['registration_open' => false]);

        $this->get(route('osif'))->assertOk()->assertSee('تم حجز كافة المقاعد')->assertDontSee('احجز مقعدك');
        $this->get(route('osif.register'))->assertOk()->assertSee('تم حجز كافة المقاعد')->assertDontSee('إرسال طلب التسجيل');

        // Open, but the overall limit is reached: the main page's button says so too.
        $event->update(['registration_open' => true, 'registration_limit' => 1]);
        $this->registerAs($event, 'طبيب أسنان خريج جديد');

        $this->get(route('osif'))->assertOk()->assertSee('تم حجز كافة المقاعد')->assertDontSee('احجز مقعدك');
    }

    public function test_registration_closes_by_itself_at_the_overall_limit(): void
    {
        $event = $this->osifEvent(['registration_limit' => 2]);
        $this->registerAs($event, 'طبيب أسنان خريج جديد', '07801234561');

        $this->get(route('osif.register'))->assertOk()->assertSee('إرسال طلب التسجيل');

        $this->registerAs($event, 'طبيب أسنان خريج جديد', '07801234562');

        $this->get(route('osif.register'))
            ->assertOk()
            ->assertSee('اكتمل العدد المخصص للتسجيل في الملتقى')
            ->assertDontSee('إرسال طلب التسجيل');

        // A form opened before the limit was reached is refused when sent.
        $this->post(route('events.register', $event), [
            'name' => 'علي حسين محمد', 'phone' => '07801234568', 'email' => 'ali@example.com', 'owns_clinic' => '0',
            'professional_title' => 'طبيب أسنان اختصاص',
        ])->assertSessionHasErrors(['name' => 'نعتذر، اكتمل العدد المخصص للتسجيل.']);
        $this->assertSame(2, $event->registrations()->count());
    }

    public function test_coordinators_set_the_overall_limit_or_leave_it_open(): void
    {
        $event = $this->osifEvent();
        $this->actingAsCoordinator();

        Livewire::test(OsifRegistrationSettings::class)
            ->fillForm(['registration_limit' => '600'])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertSame(600, $event->fresh()->registration_limit);

        Livewire::test(OsifRegistrationSettings::class)
            ->assertSchemaStateSet(['registration_limit' => 600], 'form')
            ->fillForm(['registration_limit' => ''])
            ->call('save')
            ->assertHasNoErrors();
        $this->assertNull($event->fresh()->registration_limit);
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

    public function test_registrations_filter_by_title_and_by_whether_the_invitation_was_sent(): void
    {
        $event = $this->osifEvent();
        $invited = $this->registerAs($event, 'طبيب أسنان اختصاص', '07801234561');
        $invited->update(['invitation_sent_at' => now()]);
        $waiting = $this->registerAs($event, 'طبيب أسنان خريج جديد', '07801234562');
        $this->actingAsCoordinator();

        Livewire::test(ListEventRegistrations::class)
            ->assertSee('لم تُرسل بعد')
            ->filterTable('invitation_sent_at', true)
            ->assertCanSeeTableRecords([$invited])
            ->assertCanNotSeeTableRecords([$waiting])
            ->filterTable('invitation_sent_at', false)
            ->assertCanSeeTableRecords([$waiting])
            ->assertCanNotSeeTableRecords([$invited])
            ->resetTableFilters()
            ->filterTable('professional_title', 'طبيب أسنان خريج جديد')
            ->assertCanSeeTableRecords([$waiting])
            ->assertCanNotSeeTableRecords([$invited]);
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

    public function test_the_invitation_can_be_sent_through_the_whatsapp_api(): void
    {
        Storage::fake('public');
        config(['services.adscientist.key' => 'test-key', 'services.adscientist.invitation_template' => 'oct_26']);
        $this->fakeWhatsAppApi();
        $registration = $this->osifRegistration();
        $registration->update(['name' => 'د. حيدر عبد عون كاظم']);
        Storage::disk('public')->put($registration->invitationFilePath(OsifInvitation::CARD), 'png');
        $this->actingAsWhatsAppAdmin();

        Livewire::test(ListEventRegistrations::class)
            ->mountAction(TestAction::make('sendInvitation')->table($registration))
            ->assertMountedActionModalSee('إرسال الدعوة تلقائياً')
            ->callMountedAction(['api' => true])
            ->assertHasNoErrors();

        // The card goes to the media library first: the API takes no image links.
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://ai.adscientist.iq/v1/media/upload'
            && $request->hasHeader('x-api-key', 'test-key')
            && $request->hasFile('file', 'png'));

        Http::assertSent(function (Request $request) use ($registration): bool {
            if ($request->url() !== 'https://ai.adscientist.iq/v1/messages/send-template') {
                return false;
            }

            $components = collect($request['components'])->keyBy('type');

            return $request->hasHeader('x-api-key', 'test-key')
                && $request['phoneNumber'] === '9647801234567'
                && $request['templateName'] === 'oct_26'
                && $request['languageCode'] === 'ar'
                && $components['header']['parameters'][0]['image'] === ['media_id' => 'media-123']
                // The template already says «مرحباً د», so the typed title is dropped.
                && $components['body']['parameters'][0]['text'] === 'حيدر عبد عون كاظم'
                && $components['body']['parameters'][1]['text'] === $registration->invitationUrl()
                && $components['button']['index'] === 2;
        });
        $this->assertNotNull($registration->refresh()->invitation_sent_at);
    }

    public function test_a_refused_api_send_leaves_the_invitation_unsent(): void
    {
        Storage::fake('public');
        config(['services.adscientist.key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['ai.adscientist.iq/*' => Http::response(['success' => false, 'error' => 'Template not approved'], 400)]);
        $registration = $this->osifRegistration();
        Storage::disk('public')->put($registration->invitationFilePath(OsifInvitation::CARD), 'png');
        $this->actingAsWhatsAppAdmin();

        Livewire::test(ListEventRegistrations::class)
            ->mountAction(TestAction::make('sendInvitation')->table($registration))
            ->callMountedAction(['api' => true])
            ->assertNotified('تعذّر إرسال الدعوة');

        $this->assertNull($registration->refresh()->invitation_sent_at);

        // Without a drawn card, or for a VIP guest, nothing is sent at all.
        Storage::disk('public')->delete($registration->invitationFilePath(OsifInvitation::CARD));
        Livewire::test(ListEventRegistrations::class)
            ->mountAction(TestAction::make('sendInvitation')->table($registration))
            ->callMountedAction(['api' => true])
            ->assertNotified('تعذّر إرسال الدعوة');
        Http::assertSentCount(1);
        $this->assertNull($registration->refresh()->invitation_sent_at);
    }

    public function test_the_automatic_send_is_hidden_without_an_api_key(): void
    {
        config(['services.adscientist.key' => null]);
        $registration = $this->osifRegistration();
        $this->actingAsWhatsAppAdmin();

        Livewire::test(ListEventRegistrations::class)
            ->mountAction(TestAction::make('sendInvitation')->table($registration))
            ->assertMountedActionModalDontSee('إرسال الدعوة تلقائياً')
            ->assertMountedActionModalSee('إرسال رابط الدعوة');
    }

    public function test_send_to_all_lists_only_attendees_not_invited_yet(): void
    {
        config(['services.adscientist.key' => 'test-key']);
        $event = $this->osifEvent();
        $person = fn (string $name, array $extra = []) => $event->registrations()->create([
            'name' => $name,
            'phone' => '07801234567',
            'professional_title' => 'طبيب أسنان اختصاص',
            ...$extra,
        ]);
        $waiting = $person('زينب علي');
        $person('علي حسن', ['invitation_sent_at' => now()]);
        $person('سارة كريم', ['is_vip' => true]);
        $person('محمد جواد', ['phone' => '—']);
        $this->actingAsWhatsAppAdmin();

        Livewire::test(ListEventRegistrations::class)
            ->assertActionVisible('sendAllInvitations')
            ->mountAction('sendAllInvitations')
            ->assertMountedActionModalSee('ستُرسل الدعوة إلى 1 مسجّل لم تُرسل لهم بعد')
            ->assertMountedActionModalSee('1 بدعوة VIP')
            ->assertMountedActionModalSee('1 بلا رقم واتساب صالح')
            ->assertMountedActionModalSeeHtml('د. زينب علي')
            ->assertMountedActionModalDontSeeHtml('د. علي حسن');

        $this->getJson(route('osif.invitation.payload', $waiting))
            ->assertOk()
            ->assertJsonPath('name', 'د. زينب علي')
            ->assertJsonPath('sendUrl', route('osif.invitation.send', $waiting, absolute: false));
    }

    public function test_send_to_all_needs_the_api_key(): void
    {
        config(['services.adscientist.key' => null]);
        $this->actingAsWhatsAppAdmin();

        // The button stays, and its dialog says what the server is missing.
        Livewire::test(ListEventRegistrations::class)
            ->assertActionVisible('sendAllInvitations')
            ->mountAction('sendAllInvitations')
            ->assertMountedActionModalSee('مفتاح الـ API غير مضبوط على السيرفر')
            ->assertMountedActionModalDontSee('ابدأ الإرسال');
    }

    public function test_send_to_all_invites_each_attendee_only_once(): void
    {
        Storage::fake('public');
        config(['services.adscientist.key' => 'test-key', 'services.adscientist.invitation_template' => 'oct_26']);
        $this->fakeWhatsAppApi();
        $registration = $this->osifRegistration();
        Storage::disk('public')->put($registration->invitationFilePath(OsifInvitation::CARD), 'png');

        $this->postJson(route('osif.invitation.send', $registration))->assertUnauthorized();

        $this->actingAsWhatsAppAdmin();
        $this->postJson(route('osif.invitation.send', $registration))->assertOk()->assertJson(['sent' => true]);
        $this->assertNotNull($registration->refresh()->invitation_sent_at);
        $this->assertTrue($registration->is_handled);

        $this->postJson(route('osif.invitation.send', $registration))->assertStatus(409);
        // The template's layout, the card upload and the message: nothing more for the second try.
        Http::assertSentCount(3);
    }

    public function test_a_failed_send_to_all_leaves_the_attendee_waiting(): void
    {
        Storage::fake('public');
        config(['services.adscientist.key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['ai.adscientist.iq/*' => Http::response(['success' => false, 'error' => ['code' => 'TEMPLATE', 'message' => 'Template not approved']], 400)]);
        $registration = $this->osifRegistration();
        $this->actingAsWhatsAppAdmin();

        // No card has been drawn yet, so nothing is sent.
        $this->postJson(route('osif.invitation.send', $registration))
            ->assertStatus(422)
            ->assertJson(['message' => 'لم تُجهَّز بطاقة الدعوة بعد.']);
        Http::assertNothingSent();

        Storage::disk('public')->put($registration->invitationFilePath(OsifInvitation::CARD), 'png');
        $this->postJson(route('osif.invitation.send', $registration))
            ->assertStatus(422)
            ->assertJson(['message' => 'Template not approved']);

        $registration->refresh();
        $this->assertNull($registration->invitation_sent_at);
        $this->assertFalse($registration->is_handled);
    }

    public function test_only_the_whatsapp_admin_role_sends_through_the_api(): void
    {
        Storage::fake('public');
        config(['services.adscientist.key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake();
        $registration = $this->osifRegistration();
        Storage::disk('public')->put($registration->invitationFilePath(OsifInvitation::CARD), 'png');

        foreach ([User::ROLE_OSIF_COORDINATOR, User::ROLE_ADMIN] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            Filament::setCurrentPanel(Filament::getPanel('osif'));

            Livewire::test(ListEventRegistrations::class)
                ->mountAction(TestAction::make('sendInvitation')->table($registration))
                ->assertMountedActionModalDontSee('إرسال الدعوة تلقائياً')
                // Administrators are told why; coordinators are not shown the feature at all.
                ->{$role === User::ROLE_ADMIN ? 'assertMountedActionModalSee' : 'assertMountedActionModalDontSee'}('الإرسال التلقائي غير متاح لحسابك الآن')
                // The wa.me link stays for everyone.
                ->assertMountedActionModalSee('إرسال رابط الدعوة')
                ->callMountedAction(['api' => true])
                ->assertNotified('تعذّر إرسال الدعوة');

            $this->postJson(route('osif.invitation.send', $registration))->assertForbidden();
        }

        Http::assertNothingSent();
        $this->assertNull($registration->refresh()->invitation_sent_at);

        // Coordinators never see «إرسال للجميع»; other administrators see why they cannot use it.
        $this->actingAs(User::factory()->create(['role' => User::ROLE_OSIF_COORDINATOR]));
        Livewire::test(ListEventRegistrations::class)->assertActionHidden('sendAllInvitations');

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
        Livewire::test(ListEventRegistrations::class)
            ->assertActionVisible('sendAllInvitations')
            ->mountAction('sendAllInvitations')
            ->assertMountedActionModalSee('ودور حسابك الآن: «مدير النظام» (admin)')
            ->assertMountedActionModalDontSee('ابدأ الإرسال');
    }

    public function test_send_to_all_is_also_in_the_admin_panel(): void
    {
        config(['services.adscientist.key' => 'test-key']);
        $this->osifRegistration();
        $this->actingAs(User::factory()->create(['role' => User::ROLE_WHATSAPP_ADMIN]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ListEventRegistrations::class)
            ->assertActionVisible('sendAllInvitations')
            ->mountAction('sendAllInvitations')
            ->assertMountedActionModalSee('ستُرسل الدعوة إلى 1 مسجّل لم تُرسل لهم بعد');
    }

    public function test_a_text_only_template_is_sent_without_uploading_the_card(): void
    {
        Storage::fake('public');
        config(['services.adscientist.key' => 'test-key', 'services.adscientist.invitation_template' => 'oct_26']);
        $this->fakeWhatsAppApi(header: null);
        $registration = $this->osifRegistration();
        Storage::disk('public')->put($registration->invitationFilePath(OsifInvitation::CARD), 'png');

        OsifInvitation::send($registration);

        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/media/upload'));
        Http::assertSent(function (Request $request) use ($registration): bool {
            if (! str_ends_with($request->url(), '/messages/send-template')) {
                return false;
            }

            $components = collect($request['components']);

            return $components->pluck('type')->all() === ['body', 'button']
                && $components[0]['parameters'][1]['text'] === $registration->invitationUrl();
        });
    }
}
