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

    public function test_osif_page_and_registration_capture_contact_details(): void
    {
        $event = Event::create([
            'title' => 'ملتقى OSIF',
            'slug' => Event::OSIF_SLUG,
            'location' => 'جامعة السبطين للعلوم الطبية',
            'starts_at' => now()->addMonth(),
            'registration_open' => true,
        ]);

        $this->get(route('osif'))
            ->assertOk()
            ->assertSee('OSIF KARBALA', false)
            ->assertSee(route('osif.register'), false)
            ->assertDontSee('name="whatsapp_phone"', false);

        $this->get(route('osif.register'))
            ->assertOk()
            ->assertSee('سجّل حضورك', false)
            ->assertSee('عن OSIF', false)
            ->assertSee('images/osif/partners/sums-c.svg', false)
            ->assertSee('images/osif/logos/syndicate.png', false)
            ->assertSee('navbar-gov', false)
            ->assertSee('navbar-toggler', false)
            ->assertSee('البحث عن معاملة', false)
            ->assertSee('إرسال شكوى', false)
            ->assertSee('رقم يحتوي على واتساب', false)
            ->assertSee('هل تمتلك عيادة؟', false)
            ->assertSee('طبيب أسنان خريج جديد', false)
            ->assertDontSee('name="whatsapp_phone"', false)
            ->assertDontSee('name="workplace"', false)
            ->assertDontSee('name="city"', false)
            ->assertDontSee('name="attendance_type"', false)
            ->assertSee('name="email"', false)
            ->assertDontSee('name="whatsapp_consent"', false);

        $this->post(route('events.register', $event), [
            'name' => 'علي حسين محمد',
            'phone' => '07801234567',
            'email' => 'ali@example.com',
            'professional_title' => 'طبيب أسنان مقيم',
            'owns_clinic' => '1',
        ])->assertRedirect(route('osif.register').'#osif-form');

        $this->get(route('osif.register'))
            ->assertSee('تم التسجيل بنجاح', false)
            ->assertSee('سوف نقوم بمراجعة البيانات وإرسال دعوة الحضور إليك عبر واتساب والبريد الإلكتروني.', false)
            ->assertDontSee('name="professional_title"', false);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'whatsapp_phone' => '07801234567',
            'email' => 'ali@example.com',
            'professional_title' => 'طبيب أسنان مقيم',
            'owns_clinic' => true,
        ]);
    }

    public function test_osif_email_is_optional_but_must_be_valid_when_provided(): void
    {
        $event = Event::create([
            'title' => 'ملتقى OSIF',
            'slug' => Event::OSIF_SLUG,
            'starts_at' => now()->addMonth(),
            'registration_open' => true,
        ]);

        $this->post(route('events.register', $event), [
            'name' => 'علي حسين محمد',
            'phone' => '07801234567',
            'email' => 'not-an-email',
            'professional_title' => 'طبيب أسنان مقيم',
            'owns_clinic' => '1',
        ])->assertSessionHasErrors('email');

        $this->assertSame(0, EventRegistration::count());
    }

    public function test_osif_registration_uses_one_phone_number_and_prevents_duplicates(): void
    {
        $event = Event::create([
            'title' => 'ملتقى OSIF',
            'slug' => Event::OSIF_SLUG,
            'starts_at' => now()->addMonth(),
            'registration_open' => true,
        ]);

        $payload = [
            'name' => 'علي حسين محمد',
            'phone' => '07801234567',
            'professional_title' => 'طبيب أسنان تدرج',
            'owns_clinic' => '0',
        ];

        $this->post(route('events.register', $event), $payload)->assertSessionHasNoErrors();

        $this->get(route('osif.register'))
            ->assertSee('سوف نقوم بمراجعة البيانات وإرسال دعوة الحضور إليك عبر واتساب.', false)
            ->assertDontSee('إليك عبر واتساب والبريد الإلكتروني.', false);

        $this->post(route('events.register', $event), $payload)->assertSessionHasErrors('phone');
        $this->assertSame(1, EventRegistration::count());
    }

    public function test_register_now_leads_to_a_registration_page_not_a_dialog(): void
    {
        Event::create([
            'title' => 'ملتقى OSIF',
            'slug' => Event::OSIF_SLUG,
            'starts_at' => now()->addMonth(),
            'registration_open' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('osif.register').'"', false)
            ->assertSee('التسجيل مجاني بالكامل', false)
            ->assertDontSee('eventRegModal', false);

        // Association events register in a section on their own page instead.
        $event = Event::create([
            'title' => 'ندوة علمية',
            'starts_at' => now()->addWeek(),
            'registration_open' => true,
        ]);

        $this->get(route('home'))
            ->assertSee('href="'.route('events.show', $event).'#registration"', false);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('id="registration"', false)
            ->assertSee('action="'.route('events.register', $event).'"', false)
            ->assertDontSee('eventRegModal', false);

        $this->followingRedirects()
            ->post(route('events.register', $event), [
                'name' => 'علي حسين محمد',
                'phone' => '07801234567',
                'membership_number' => 'KRB-1',
            ])
            ->assertOk()
            ->assertSee('تم استلام طلب التسجيل', false);
    }
}
