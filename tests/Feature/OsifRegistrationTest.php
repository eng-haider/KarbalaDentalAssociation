<?php

namespace Tests\Feature;

use App\Filament\Pages\OsifRegistrationSettings;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_fields_removed_from_the_form_are_not_stored(): void
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
        $this->assertNull($registration->email);
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
            ->assertSee('خيارات «الصفة المهنية»', false);
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

        Livewire::test(ListEventRegistrations::class)
            ->assertCanSeeTableRecords([$registration])
            ->mountAction(TestAction::make('sendInvitation')->table($registration))
            ->assertMountedActionModalSeeHtml('https://wa.me/9647801234567?text=')
            ->assertMountedActionModalSee('OSIF-'.str_pad((string) $registration->id, 4, '0', STR_PAD_LEFT))
            ->callMountedAction()
            ->assertHasNoErrors();

        $registration->refresh();
        $this->assertNotNull($registration->invitation_sent_at);
        $this->assertTrue($registration->is_handled);
    }
}
