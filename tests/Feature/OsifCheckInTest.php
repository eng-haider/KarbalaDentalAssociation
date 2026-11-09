<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OsifCheckInTest extends TestCase
{
    use RefreshDatabase;

    private function osifRegistration(array $attributes = []): EventRegistration
    {
        $event = Event::firstOrCreate(['slug' => Event::OSIF_SLUG], [
            'title' => 'ملتقى كربلاء لجراحة الفم وزراعة الأسنان',
            'starts_at' => now()->addMonth()->setTime(9, 0),
            'registration_open' => true,
        ]);

        return $event->registrations()->create([
            'name' => 'حيدر عبد عون كاظم',
            'phone' => '07801234567',
            'professional_title' => 'طبيب أسنان اختصاص',
            'owns_clinic' => true,
            ...$attributes,
        ]);
    }

    private function actingAsCoordinator(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_OSIF_COORDINATOR]);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_scanner_sends_guests_to_the_login_and_back(): void
    {
        $this->withoutVite();

        $this->get('/osif/checkin')->assertRedirect(route('filament.osif.auth.login'));

        $this->actingAsCoordinator();

        $this->get('/osif/checkin')->assertOk()->assertSee('تسجيل الحضور', false);
    }

    public function test_company_accounts_cannot_check_guests_in(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_COMPANY]));

        $this->get('/osif/checkin')->assertForbidden();
        $this->postJson('/osif/checkin', ['code' => 'OSIF-0001'])->assertForbidden();
    }

    public function test_scanning_an_invitation_checks_the_guest_in_once(): void
    {
        $registration = $this->osifRegistration();
        $this->osifRegistration(['name' => 'ضيف آخر']);
        $this->actingAsCoordinator();

        $this->postJson('/osif/checkin', ['code' => $registration->invitationShareUrl()])
            ->assertOk()
            ->assertJson([
                'status' => 'checked_in',
                'name' => 'د. حيدر عبد عون كاظم',
                'code' => $registration->invitationCode(),
                'stats' => ['checkedIn' => 1, 'total' => 2],
            ]);

        $this->assertNotNull($registration->refresh()->checked_in_at);

        // The same card again, typed by its code this time.
        $this->postJson('/osif/checkin', ['code' => $registration->invitationCode()])
            ->assertOk()
            ->assertJson(['status' => 'already', 'time' => $registration->checkInTime()]);

        // The guest's own invitation page says so too.
        $this->get($registration->invitationUrl())->assertSee('تم تسجيل الدخول للمؤتمر', false);
    }

    public function test_the_code_can_be_typed_with_arabic_digits(): void
    {
        $registration = $this->osifRegistration();
        $this->actingAsCoordinator();

        $arabic = strtr((string) $registration->getKey(), ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);

        $this->postJson('/osif/checkin', ['code' => $arabic])->assertJson(['status' => 'checked_in']);
    }

    public function test_cards_from_elsewhere_are_refused(): void
    {
        $this->actingAsCoordinator();
        $other = Event::create(['title' => 'ندوة علمية', 'starts_at' => now()->addWeek()])
            ->registrations()->create(['name' => 'ضيف ندوة', 'phone' => '07700000000']);

        $this->postJson('/osif/checkin', ['code' => 'https://example.com/osif/invitation/'.str_repeat('a', 40)])
            ->assertNotFound()
            ->assertJson(['status' => 'invalid']);

        $this->postJson('/osif/checkin', ['code' => 'OSIF-'.$other->getKey()])->assertNotFound();
        $this->postJson('/osif/checkin', ['code' => 'مرحبا'])->assertNotFound();

        $this->assertNull($other->refresh()->checked_in_at);
    }
}
