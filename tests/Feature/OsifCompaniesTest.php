<?php

namespace Tests\Feature;

use App\Filament\Company\Auth\Login;
use App\Filament\Company\Pages\CompanyProfile;
use App\Filament\Company\Resources\Categories\Pages\ManageCategories;
use App\Filament\Company\Resources\Products\Pages\ManageProducts;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\RelationManagers\ProductsRelationManager;
use App\Models\Company;
use App\Models\CompanyProduct;
use App\Models\Event;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
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
        $this->actingAs($user, 'company');
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

    public function test_a_company_page_filters_its_products_by_category_in_the_companys_order(): void
    {
        $company = $this->company();
        $implants = $company->productCategories()->create(['name' => 'زراعة', 'sort_order' => 1]);
        $fillings = $company->productCategories()->create(['name' => 'حشوات', 'sort_order' => 2]);
        $hidden = $company->productCategories()->create(['name' => 'مخفي', 'sort_order' => 3]);
        $company->products()->createMany([
            ['name' => 'Composite A2', 'product_category_id' => $fillings->id],
            ['name' => 'Composite A3', 'product_category_id' => $fillings->id],
            ['name' => 'Implant kit', 'product_category_id' => $implants->id],
            ['name' => 'Gloves'],
            ['name' => 'Hidden', 'product_category_id' => $hidden->id, 'is_active' => false],
        ]);

        $this->get(route('osif.companies.show', $company))
            ->assertOk()
            ->assertSeeInOrder(['كل التصنيفات', 'زراعة', '1', 'حشوات', '2', 'أخرى', '1'])
            ->assertDontSee('مخفي');
    }

    public function test_a_single_category_shows_no_filter(): void
    {
        $company = $this->company();
        $fillings = $company->productCategories()->create(['name' => 'حشوات']);
        $company->products()->createMany([
            ['name' => 'Composite A2', 'product_category_id' => $fillings->id],
            ['name' => 'Composite A3', 'product_category_id' => $fillings->id],
        ]);

        $this->get(route('osif.companies.show', $company))
            ->assertOk()
            ->assertDontSee('كل التصنيفات')
            // Nothing else to show either, so no empty toolbar box.
            ->assertDontSee('shop-toolbar');
    }

    public function test_companies_keep_their_own_category_list(): void
    {
        $mine = $this->company();
        $other = $this->company(['name' => 'Other Co']);
        $theirs = $other->productCategories()->create(['name' => 'زراعة']);
        $this->actingAsCompany($mine);

        $this->get('/company-dashboard/categories')->assertOk();

        Livewire::test(ManageCategories::class)
            ->assertCanNotSeeTableRecords([$theirs])
            ->callAction('create', data: ['name' => 'حشوات'])
            ->assertHasNoFormErrors()
            // Another company's «زراعة» doesn't stop this one having its own.
            ->callAction('create', data: ['name' => 'زراعة'])
            ->assertHasNoFormErrors()
            ->callAction('create', data: ['name' => 'حشوات'])
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertSame(['حشوات', 'زراعة'], $mine->productCategories()->pluck('name')->all());
        $this->assertSame(['زراعة'], $other->productCategories()->pluck('name')->all());

        // Removing a category keeps its products, just without one.
        $fillings = $mine->productCategories()->where('name', 'حشوات')->sole();
        $product = $mine->products()->create(['name' => 'Composite', 'product_category_id' => $fillings->id]);
        $fillings->delete();
        $this->assertNull($product->fresh()->product_category_id);
    }

    public function test_companies_pick_one_of_their_own_categories_or_add_one_from_the_product_form(): void
    {
        $mine = $this->company();
        $fillings = $mine->productCategories()->create(['name' => 'حشوات']);
        $theirs = $this->company(['name' => 'Other Co'])->productCategories()->create(['name' => 'سرّي']);
        $this->actingAsCompany($mine);

        Livewire::test(ManageProducts::class)
            ->mountAction('create')
            ->assertFormFieldExists('product_category_id', fn (Select $field): bool => $field->getOptions() === [$fillings->id => 'حشوات'])
            // The + next to the list adds to this company's categories.
            ->callAction(TestAction::make('createOption')->schemaComponent('product_category_id'), data: ['name' => 'زراعة'])
            ->assertHasNoFormErrors();

        $implants = $mine->productCategories()->where('name', 'زراعة')->sole();

        Livewire::test(ManageProducts::class)
            ->callAction('create', data: ['name' => 'Kit', 'product_category_id' => $implants->id, 'is_active' => true])
            ->assertHasNoFormErrors()
            ->callAction('create', data: ['name' => 'Gloves', 'is_active' => true])
            ->assertHasNoFormErrors()
            // Another company's category can't be chosen.
            ->callAction('create', data: ['name' => 'Spy', 'product_category_id' => $theirs->id, 'is_active' => true])
            ->assertHasFormErrors(['product_category_id']);

        $this->assertSame('زراعة', CompanyProduct::where('name', 'Kit')->sole()->category->name);
        $this->assertNull(CompanyProduct::where('name', 'Gloves')->sole()->category);
        $this->assertFalse(CompanyProduct::where('name', 'Spy')->exists());
    }

    public function test_organisers_editing_a_companys_products_use_that_companys_categories(): void
    {
        $company = $this->company();
        $implants = $company->productCategories()->create(['name' => 'زراعة']);
        $this->company(['name' => 'Other Co'])->productCategories()->create(['name' => 'سرّي']);
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(ProductsRelationManager::class, ['ownerRecord' => $company, 'pageClass' => EditCompany::class])
            ->mountAction(TestAction::make('create')->table())
            ->assertFormFieldExists('product_category_id', fn (Select $field): bool => $field->getOptions() === [$implants->id => 'زراعة']);
    }

    public function test_a_company_page_and_its_products_can_be_shared(): void
    {
        $company = $this->company();
        $kit = $company->products()->create(['name' => 'Implant kit', 'price' => 100000, 'offer_price' => 80000]);

        $this->assertSame(route('osif.companies.show', [$company, 'product' => $kit->id]), $kit->url());
        $this->assertSame('Implant kit من Dental Pro · بخصم 20٪: 80,000 د.ع', $kit->shareData()['text']);

        $this->get(route('osif.companies.show', $company))
            ->assertOk()
            ->assertSee('مشاركة الصفحة', false)
            ->assertSee('مشاركة المنتج', false)
            ->assertSee('shareProduct('.$kit->id.')', false)
            ->assertSee('<meta property="og:title" content="Dental Pro — الشركات المشاركة', false);
    }

    public function test_a_shared_product_link_opens_and_previews_that_product(): void
    {
        $company = $this->company();
        $kit = $company->products()->create(['name' => 'Implant kit', 'price' => 100000, 'image' => 'companies/products/kit.jpg']);
        $hidden = $company->products()->create(['name' => 'Retired item', 'is_active' => false]);
        $elsewhere = $this->company(['name' => 'Other Co'])->products()->create(['name' => 'Their item']);

        $this->get($kit->url())
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Implant kit — Dental Pro', false)
            ->assertSee('<meta property="og:image" content="'.url('/osif/media/companies/products/kit.jpg').'"', false)
            ->assertViewHas('opened', fn ($opened): bool => $opened?->is($kit) ?? false);

        // A product that's hidden, or another company's, just opens the company page.
        foreach ([$hidden, $elsewhere] as $product) {
            $this->get(route('osif.companies.show', [$company, 'product' => $product->id]))
                ->assertOk()
                ->assertSee('<meta property="og:title" content="Dental Pro — الشركات المشاركة', false)
                ->assertViewHas('opened', null);
        }
    }

    public function test_companies_share_their_page_and_products_from_the_dashboard(): void
    {
        $company = $this->company();
        $shown = $company->products()->create(['name' => 'Kit']);
        $hidden = $company->products()->create(['name' => 'Old', 'is_active' => false]);
        $this->actingAsCompany($company);

        $this->get('/company-dashboard')
            ->assertOk()
            ->assertSee('مشاركة صفحتي', false)
            ->assertSee('dashboard-share', false);

        Livewire::test(ManageProducts::class)
            ->assertActionVisible('share')
            ->assertActionVisible(TestAction::make('share')->table($shown))
            // A hidden product isn't on the page, so there's nothing to share.
            ->assertActionHidden(TestAction::make('share')->table($hidden));
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
        // A company session is separate: the other panels just ask for their own login.
        $this->get('/osif-dashboard')->assertRedirect('/osif-dashboard/login');
        $this->get('/admin')->assertRedirect('/admin/login');

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
        $this->assertGuest('company');

        Livewire::test(Login::class)
            ->fillForm(['phone' => '+964 780 111 2222', 'password' => 'secret-pass'])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertRedirect('/company-dashboard');
        $this->assertAuthenticatedAs($account, 'company');
        $this->assertGuest('web');
    }

    public function test_an_admin_signed_in_elsewhere_still_gets_the_company_login(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]));

        $this->get('/admin')->assertOk();
        $this->get('/company-dashboard')->assertRedirect('/company-dashboard/login');
        $this->get('/company-dashboard/login')->assertOk()->assertSee('رقم الهاتف', false);
    }

    public function test_companies_get_a_qr_code_and_a_printable_sign_for_their_page(): void
    {
        $company = $this->company();

        $png = $this->get(route('osif.companies.qr', $company))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Content-Disposition', 'attachment; filename="qr-dental-pro.png"')
            ->getContent();
        $this->assertStringStartsWith("\x89PNG", $png);
        $this->assertSame([936, 936], array_slice(getimagesizefromstring($png), 0, 2));

        $this->get(route('osif.companies.poster', $company))
            ->assertOk()
            ->assertSee('Dental Pro')
            ->assertSee('جناح A12', false)
            ->assertSee('امسح الرمز بكاميرا هاتفك', false)
            ->assertSee('<svg', false)
            ->assertSee(route('osif.companies.show', $company), false);

        // The company finds both on its dashboard.
        $this->actingAsCompany($company);
        $this->get('/company-dashboard')
            ->assertOk()
            ->assertSee('رمز QR لصفحتك', false)
            ->assertSee(route('osif.companies.qr', $company), false)
            ->assertSee(route('osif.companies.poster', $company), false);
    }
}
