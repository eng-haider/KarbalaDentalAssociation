<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('نقابة', false);
    }

    public function test_valid_complaint_is_stored(): void
    {
        $this->post(route('complaints.store'), [
            'body' => 'شكوى تجريبية كافية الطول للتحقق من الحفظ.',
        ])->assertRedirect();

        $this->assertSame(1, Complaint::count());
        $this->assertSame(Complaint::STATUS_NEW, Complaint::first()->status);
    }

    public function test_short_complaint_is_rejected(): void
    {
        $this->post(route('complaints.store'), ['body' => 'قصير'])
            ->assertSessionHasErrors('body');

        $this->assertSame(0, Complaint::count());
    }

    public function test_member_can_register_for_open_event(): void
    {
        $event = Event::create([
            'title' => 'فعالية اختبار',
            'starts_at' => now()->addWeek(),
            'registration_open' => true,
        ]);

        $this->post(route('events.register', $event), [
            'name' => 'علي حسين محمد',
            'phone' => '07801234567',
            'membership_number' => 'KRB-1',
        ])->assertRedirect();

        $this->assertSame(1, EventRegistration::where('event_id', $event->id)->count());
    }

    public function test_registration_is_blocked_when_closed(): void
    {
        $event = Event::create([
            'title' => 'فعالية مغلقة',
            'starts_at' => now()->addWeek(),
            'registration_open' => false,
        ]);

        $this->post(route('events.register', $event), [
            'name' => 'علي حسين محمد',
            'phone' => '07801234567',
            'membership_number' => 'KRB-1',
        ])->assertNotFound();

        $this->assertSame(0, EventRegistration::count());
    }
}
