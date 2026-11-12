<?php

namespace Tests\Feature;

use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\OsifCheckIn;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $this->get('/osif/checkin/scanned')->assertRedirect(route('filament.osif.auth.login'));

        $this->actingAsCoordinator();

        $this->get('/osif/checkin')->assertOk()->assertSee('تسجيل الحضور', false);
    }

    public function test_company_accounts_cannot_check_guests_in(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_COMPANY]));

        $this->get('/osif/checkin')->assertForbidden();
        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => 'OSIF-0001'])->assertForbidden();
    }

    public function test_scanning_an_invitation_checks_the_guest_in_once(): void
    {
        $registration = $this->osifRegistration();
        $this->osifRegistration(['name' => 'ضيف آخر']);
        $this->actingAsCoordinator();

        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => $registration->invitationShareUrl()])
            ->assertOk()
            ->assertJson([
                'status' => 'checked_in',
                'name' => 'د. حيدر عبد عون كاظم',
                'code' => $registration->invitationCode(),
                'stats' => ['mine' => 1, 'hall' => 1, 'total' => 2],
            ]);

        $this->assertNotNull($registration->refresh()->checked_in_at);

        // The same card again, typed by its code this time.
        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => $registration->invitationCode()])
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

        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => $arabic])->assertJson(['status' => 'checked_in']);
    }

    public function test_a_reader_may_type_only_the_end_of_the_link(): void
    {
        $registration = $this->osifRegistration();
        $registration->forceFill(['invitation_token' => str_repeat('A', 30).'xY12345678'])->save();
        $other = $this->osifRegistration(['name' => 'ضيف آخر']);
        $other->forceFill(['invitation_token' => str_repeat('B', 30).'zW12345678'])->save();
        $this->actingAsCoordinator();

        // Eight characters both tokens end with: refused rather than guessed.
        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => '12345678'])->assertNotFound();
        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => 'Q12345678'])->assertNotFound();

        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => 'xY12345678'])
            ->assertOk()
            ->assertJson(['status' => 'checked_in', 'code' => $registration->invitationCode()]);

        $this->assertNull($other->refresh()->checked_in_at);
    }

    public function test_cards_from_elsewhere_are_refused(): void
    {
        $this->actingAsCoordinator();
        $other = Event::create(['title' => 'ندوة علمية', 'starts_at' => now()->addWeek()])
            ->registrations()->create(['name' => 'ضيف ندوة', 'phone' => '07700000000']);

        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => 'https://example.com/osif/invitation/'.str_repeat('a', 40)])
            ->assertNotFound()
            ->assertJson(['status' => 'invalid']);

        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => 'OSIF-'.$other->getKey()])->assertNotFound();
        $this->postJson('/osif/checkin', ['hall' => 1, 'code' => 'مرحبا'])->assertNotFound();

        $this->assertNull($other->refresh()->checked_in_at);
    }

    private function scanner(int $hall): User
    {
        return User::factory()->create(['role' => User::ROLE_OSIF_SCANNER, 'osif_hall' => $hall]);
    }

    public function test_staff_choose_their_hall_before_scanning(): void
    {
        $this->withoutVite();
        $this->actingAsCoordinator();

        $this->get('/osif/checkin')->assertOk()->assertSee('في أي قاعة أنت؟', false);
        $this->get('/osif/checkin?hall=2')->assertOk()->assertSee('القاعة الثانية', false)->assertSee('البطاقات التي قرأتها', false);

        $this->postJson('/osif/checkin', ['code' => 'OSIF-0001'])->assertStatus(422);

        // The list page needs a hall too, and keeps the one chosen.
        $this->get('/osif/checkin/scanned')->assertRedirect(route('osif.checkin'));
        $this->get('/osif/checkin/scanned?hall=2')
            ->assertOk()
            ->assertSee('لم تقرأ أي بطاقة بعد.', false)
            ->assertSee(route('osif.checkin', ['hall' => 2]), false);
    }

    public function test_a_guest_enters_each_hall_once_and_each_scanner_sees_their_own_count(): void
    {
        $guest = $this->osifRegistration();
        $other = $this->osifRegistration(['name' => 'ضيف آخر']);
        $hallOne = $this->scanner(1);
        $hallOneToo = $this->scanner(1);
        $hallTwo = $this->scanner(2);

        $this->actingAs($hallOne)
            ->postJson('/osif/checkin', ['code' => $guest->invitationCode(), 'hall' => 2])
            ->assertJson(['status' => 'checked_in', 'stats' => ['mine' => 1, 'hall' => 1, 'total' => 2]]);

        // The scanner's own hall counts, whatever the page sends.
        $this->assertDatabaseHas('osif_check_ins', ['event_registration_id' => $guest->getKey(), 'hall' => 1, 'user_id' => $hallOne->getKey()]);

        // The other door of the same hall: already in.
        $this->actingAs($hallOneToo)
            ->postJson('/osif/checkin', ['code' => $guest->invitationCode()])
            ->assertJson(['status' => 'already', 'stats' => ['mine' => 0, 'hall' => 1]]);

        $this->actingAs($hallOneToo)
            ->postJson('/osif/checkin', ['code' => $other->invitationCode()])
            ->assertJson(['status' => 'checked_in', 'stats' => ['mine' => 1, 'hall' => 2]]);

        // The second hall is a new entry, counted for its own scanner.
        $this->actingAs($hallTwo)
            ->postJson('/osif/checkin', ['code' => $guest->invitationCode()])
            ->assertJson(['status' => 'checked_in', 'stats' => ['mine' => 1, 'hall' => 1, 'total' => 2]]);

        $this->assertSame(3, OsifCheckIn::count());

        // Each scanner's list page shows the guests they let in, and no one else's.
        $this->withoutVite();
        $this->actingAs($hallOneToo)->get('/osif/checkin')
            ->assertSee(route('osif.checkin.scanned'), false)
            ->assertDontSee($other->invitationCode(), false);
        $this->actingAs($hallOneToo)->get('/osif/checkin/scanned')
            ->assertOk()
            ->assertSee('ضيف آخر', false)
            ->assertSee($other->invitationCode(), false)
            ->assertDontSee($guest->invitationCode(), false);
        $this->actingAs($hallTwo)->get('/osif/checkin/scanned')
            ->assertSee($guest->invitationCode(), false)
            ->assertDontSee($other->invitationCode(), false);
        $this->assertNotNull($guest->refresh()->checked_in_at);
    }

    public function test_scanners_see_only_the_scanning_page(): void
    {
        $this->withoutVite();
        $scanner = $this->scanner(2);

        // Signing in through the OSIF login is allowed…
        $this->assertTrue($scanner->canAccessPanel(Filament::getPanel('osif')));
        $this->assertFalse($scanner->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($scanner);

        // …but the dashboard sends them back to the scanner.
        $this->get('/osif-dashboard')->assertRedirect(route('osif.checkin'));
        $this->get('/osif-dashboard/event-registrations')->assertRedirect(route('osif.checkin'));

        $this->get('/osif/checkin')
            ->assertOk()
            ->assertSee('القاعة الثانية', false)
            ->assertDontSee('تغيير القاعة', false);

        $this->post('/osif-dashboard/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_the_registrations_list_filters_by_hall_and_by_scanner(): void
    {
        $guest = $this->osifRegistration();
        $other = $this->osifRegistration(['name' => 'ضيف آخر']);
        $hallOne = $this->scanner(1);
        $hallTwo = $this->scanner(2);
        OsifCheckIn::create(['event_registration_id' => $guest->getKey(), 'hall' => 1, 'user_id' => $hallOne->getKey(), 'checked_in_at' => now()]);
        OsifCheckIn::create(['event_registration_id' => $other->getKey(), 'hall' => 2, 'user_id' => $hallTwo->getKey(), 'checked_in_at' => now()]);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ListEventRegistrations::class)
            ->filterTable('scanned_by', $hallOne->getKey())
            ->assertCanSeeTableRecords([$guest])
            ->assertCanNotSeeTableRecords([$other])
            ->resetTableFilters()
            ->filterTable('hall', 2)
            ->assertCanSeeTableRecords([$other])
            ->assertCanNotSeeTableRecords([$guest]);
    }
}
