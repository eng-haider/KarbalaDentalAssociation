<?php

namespace Tests\Feature;

use App\Filament\Company\Auth\Login;
use App\Filament\Company\Pages\CompanyProfile;
use App\Filament\Company\Resources\Products\Pages\ManageProducts;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Models\Company;
use App\Models\CompanyProduct;
use App\Models\Event;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OsifCompaniesTest extends TestCase
{
    use RefreshDatabase;

    private function company(array $attributes = []): Company
    {
        return Company::create([
            'name' => 'Dental Pro',
            'whatsapp_phone' => '07701234567',
            'booth' => 'A12',
            ...$attributes,
        ]);
    }

    private function actingAsCompany(Company $company): User
    {
        $user = User::factory()->create(['role' => User::ROLE_COMPANY, 'company_id' => $company->id]);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('company'));

        return $user;
    }

    public function test_the_companies_page_lists_visible_companies_with_their_products_and_offers(): void
    {
        $company = $this->company();
        $company->products()->createMany([
            ['name' => 'Implant kit', 'price' => 100000, 'offer_price' => 80000],
            ['name' => 'Gloves', 'price' => 5000, 'offer_note' => '10 + 1'],
            ['name' => 'Suture', 'price' => 3000],
            ['name' => 'Hidden', 'price' => 3000, 'offer_price' => 1000, 'is_active' => false],
        ]);
        $this->company(['name' => 'Secret Co', 'is_active' => false]);

        $this->get(route('osif.companies'))
            ->assertOk()
            ->assertSee('Dental Pro')
            ->assertSee('جناح A12', false)
            ->assertSee('3 منتجات', false)
            ->assertSee('عرضان', false)
            ->assertDontSee('Secret Co');
    }

    public function test_a_company_page_shows_its_products_offers_and_a_whatsapp_cart(): void
    {
        $company = $this->company(['description' => 'Implants and surgical kits.']);
        $company->products()->createMany([
            ['name' => 'Implant kit', 'price' => 100000, 'offer_price' => 80000, 'description' => 'Full surgical kit'],
            ['name' => 'Bone graft', 'offer_note' => 'هدية مع كل طلب'],
            ['name' => 'Retired item', 'price' => 1000, 'is_active' => false],
        ]);

        $this->get(route('osif.companies.show', $company))
            ->assertOk()
            ->assertSee('Implants and surgical kits.')
            ->assertSee('Implant kit')
            ->assertSee('80,000 د.ع', false)
            ->assertSee('100,000 د.ع', false)
            ->assertSee('خصم 20٪', false)
            ->assertSee('هدية مع كل طلب', false)
            ->assertSee('السعر عند الطلب', false)
            ->assertSee('إرسال الطلب عبر واتساب', false)
            // The cart sends orders to the company's number in wa.me form.
            ->assertSee('9647701234567')
            ->assertDontSee('Retired item');
    }

    public function test_hidden_companies_have_no_page(): void
    {
        $company = $this->company(['is_active' => false]);

        $this->get(route('osif.companies.show', $company))->assertNotFound();
    }

    public function test_the_osif_page_introduces_the_participating_companies(): void
    {
        Event::create([
            'title' => 'ملتقى كربلاء لجراحة الفم وزراعة الأسنان',
            'slug' => Event::OSIF_SLUG,
            'location' => 'جامعة السبطين للعلوم الطبية',
            'starts_at' => now()->addMonth(),
            'registration_open' => true,
        ]);
        $company = $this->company();

        $this->get(route('osif'))
            ->assertOk()
            ->assertSee('الشركات المشاركة', false)
            ->assertSee(route('osif.companies.show', $company), false);
    }

    public function test_coordinators_add_a_company_together_with_its_dashboard_account(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_OSIF_COORDINATOR]));
        Filament::setCurrentPanel(Filament::getPanel('osif'));

        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => 'Implant House',
                'whatsapp_phone' => '07801112222',
                'account_phone' => '0780 111 2222',
                'account_password' => 'secret-pass',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $company = Company::sole();
        $this->assertSame('implant-house', $company->slug);

        // The login is the phone, stored in one form however it was typed; no email is needed.
        $account = $company->account;
        $this->assertSame('9647801112222', $account->phone);
        $this->assertNull($account->email);
        $this->assertSame(User::ROLE_COMPANY, $account->role);
        $this->assertTrue(Hash::check('secret-pass', $account->password));

        $this->assertTrue($account->canAccessPanel(Filament::getPanel('company')));
        $this->assertFalse($account->canAccessPanel(Filament::getPanel('osif')));
        $this->assertFalse($account->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_company_accounts_only_see_and_add_their_own_products(): void
    {
        $mine = $this->company();
        $other = $this->company(['name' => 'Other Co']);
        $own = $mine->products()->create(['name' => 'My gloves', 'price' => 5000]);
        $theirs = $other->products()->create(['name' => 'Their gloves', 'price' => 5000]);
        $this->actingAsCompany($mine);

        $this->get('/company-dashboard')->assertOk()->assertSee(route('osif.companies.show', $mine), false);
        $this->get('/company-dashboard/products')->assertOk();
        $this->get('/osif-dashboard')->assertForbidden();
        $this->get('/admin')->assertForbidden();

        Livewire::test(ManageProducts::class)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$theirs])
            ->callAction('create', data: [
                'name' => 'Composite',
                'price' => 25000,
                'offer_price' => 20000,
                'offer_note' => 'عرض المعرض',
                'is_active' => true,
            ])
            ->assertHasNoErrors();

        $product = CompanyProduct::where('name', 'Composite')->sole();
        $this->assertSame($mine->id, $product->company_id);
        $this->assertTrue($product->hasOffer());
        $this->assertSame(20, $product->discountPercent());
    }

    public function test_an_offer_price_must_be_below_the_original_price(): void
    {
        $this->actingAsCompany($this->company());

        Livewire::test(ManageProducts::class)
            ->callAction('create', data: ['name' => 'Kit', 'price' => 1000, 'offer_price' => 1500])
            ->assertHasFormErrors(['offer_price' => 'lt']);

        $this->assertSame(0, CompanyProduct::count());
    }

    public function test_companies_edit_their_page_details_but_not_their_name(): void
    {
        $company = $this->company();
        $this->actingAsCompany($company);

        Livewire::test(CompanyProfile::class)
            ->assertSchemaStateSet(['name' => 'Dental Pro'])
            ->fillForm([
                'name' => 'Renamed',
                'description' => 'Trusted supplier since 2010.',
                'whatsapp_phone' => '07709998888',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $company->refresh();
        $this->assertSame('Dental Pro', $company->name);
        $this->assertSame('Trusted supplier since 2010.', $company->description);
        $this->assertSame('9647709998888', $company->whatsappNumber());
    }

    public function test_deleting_a_product_removes_its_image(): void
    {
        Storage::fake('osif');
        $path = UploadedFile::fake()->image('kit.jpg')->store('companies/products', 'osif');
        $product = $this->company()->products()->create(['name' => 'Kit', 'image' => $path]);

        $product->delete();

        Storage::disk('osif')->assertMissing($path);
    }

    public function test_company_images_are_served_by_laravel_without_the_storage_link(): void
    {
        Storage::fake('osif', ['url' => config('filesystems.disks.osif.url')]);
        $path = UploadedFile::fake()->image('logo.png')->store('companies/logos', 'osif');
        $company = $this->company(['logo' => $path]);

        $this->assertSame('/osif/media/'.$path, $company->logoUrl());

        $this->get($company->logoUrl())
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->get('/osif/media/companies/logos/missing.png')->assertNotFound();
        $this->get('/osif/media/companies/../../.env')->assertNotFound();
        $this->get('/osif/media/osif-invitations/secret/card.png')->assertNotFound();
    }

    public function test_companies_sign_in_with_their_phone_and_password(): void
    {
        $company = $this->company();
        $account = User::factory()->create([
            'email' => null,
            'phone' => '9647801112222',
            'password' => 'secret-pass',
            'role' => User::ROLE_COMPANY,
            'company_id' => $company->id,
        ]);
        Filament::setCurrentPanel(Filament::getPanel('company'));

        $this->get('/company-dashboard/login')->assertOk()->assertSee('رقم الهاتف', false);

        Livewire::test(Login::class)
            ->fillForm(['phone' => '0780 111 2223', 'password' => 'secret-pass'])
            ->call('authenticate')
            ->assertHasFormErrors(['phone']);
        $this->assertGuest();

        Livewire::test(Login::class)
            ->fillForm(['phone' => '+964 780 111 2222', 'password' => 'secret-pass'])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/company-dashboard');
        $this->assertAuthenticatedAs($account);
    }
}
