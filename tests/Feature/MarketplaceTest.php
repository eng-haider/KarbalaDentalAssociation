<?php

namespace Tests\Feature;

use App\Models\MarketplaceListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private function listing(array $overrides = []): MarketplaceListing
    {
        return MarketplaceListing::create($overrides + [
            'type' => MarketplaceListing::TYPE_SALE,
            'title' => 'جهاز أشعة',
            'description' => 'جهاز أشعة بانوراما بحالة ممتازة.',
            'contact_name' => 'د. علي',
            'contact_phone' => '07701234567',
            'status' => MarketplaceListing::STATUS_PUBLISHED,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return $overrides + [
            'type' => MarketplaceListing::TYPE_WANTED,
            'title' => 'مطلوب كرسي أسنان',
            'description' => 'مطلوب كرسي أسنان مستعمل بحالة جيدة.',
            'category' => 'devices',
            'price' => 750000,
            'contact_name' => 'د. حسن',
            'contact_phone' => '07709876543',
            'city' => 'كربلاء المقدسة',
        ];
    }

    public function test_marketplace_page_loads(): void
    {
        $this->get(route('marketplace.index'))
            ->assertOk()
            ->assertSee('بيع وشراء', false)
            ->assertSee('أضف إعلانك', false);
    }

    public function test_only_published_listings_are_shown(): void
    {
        $this->listing(['title' => 'إعلان منشور']);
        $this->listing(['title' => 'إعلان معلّق', 'status' => MarketplaceListing::STATUS_PENDING]);
        $this->listing(['title' => 'إعلان مرفوض', 'status' => MarketplaceListing::STATUS_REJECTED]);

        $response = $this->get(route('marketplace.index'))->assertOk();

        $response->assertSee('إعلان منشور', false);
        $response->assertDontSee('إعلان معلّق', false);
        $response->assertDontSee('إعلان مرفوض', false);
    }

    public function test_listings_can_be_filtered_by_type(): void
    {
        $this->listing(['title' => 'معروض للبيع', 'type' => MarketplaceListing::TYPE_SALE]);
        $this->listing(['title' => 'مطلوب للشراء', 'type' => MarketplaceListing::TYPE_WANTED]);

        $this->get(route('marketplace.index', ['type' => MarketplaceListing::TYPE_WANTED]))
            ->assertOk()
            ->assertSee('مطلوب للشراء', false)
            ->assertDontSee('معروض للبيع', false);
    }

    public function test_unknown_type_filter_falls_back_to_all_listings(): void
    {
        $this->listing(['title' => 'معروض للبيع']);

        $this->get(route('marketplace.index', ['type' => 'bogus']))
            ->assertOk()
            ->assertSee('معروض للبيع', false);
    }

    public function test_a_visitor_can_submit_a_listing_and_it_waits_for_review(): void
    {
        $this->post(route('marketplace.store'), $this->validPayload())
            ->assertRedirect(route('marketplace.index'))
            ->assertSessionHas('marketplace_ok');

        $listing = MarketplaceListing::sole();

        $this->assertSame('مطلوب كرسي أسنان', $listing->title);
        $this->assertSame(MarketplaceListing::TYPE_WANTED, $listing->type);
        $this->assertSame(750000, $listing->price);
        $this->assertSame(MarketplaceListing::STATUS_PENDING, $listing->status);

        // It stays out of the public list until the dashboard approves it.
        $this->get(route('marketplace.index'))->assertDontSee('مطلوب كرسي أسنان', false);
    }

    public function test_a_submitted_status_cannot_be_forced_by_the_request(): void
    {
        $this->post(route('marketplace.store'), $this->validPayload([
            'status' => MarketplaceListing::STATUS_PUBLISHED,
        ]))->assertRedirect();

        $this->assertSame(MarketplaceListing::STATUS_PENDING, MarketplaceListing::sole()->status);
    }

    public function test_submission_is_validated(): void
    {
        $this->post(route('marketplace.store'), [
            'type' => 'nonsense',
            'title' => 'x',
            'description' => 'قصير',
            'contact_name' => '',
            'contact_phone' => '',
        ])->assertSessionHasErrors(['type', 'title', 'description', 'contact_name', 'contact_phone']);

        $this->assertSame(0, MarketplaceListing::count());
    }

    public function test_an_uploaded_image_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->post(route('marketplace.store'), $this->validPayload([
            'image' => UploadedFile::fake()->image('device.jpg'),
        ]))->assertRedirect();

        $image = MarketplaceListing::sole()->image;

        $this->assertNotNull($image);
        Storage::disk('public')->assertExists($image);
    }

    public function test_a_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $this->post(route('marketplace.store'), $this->validPayload([
            'image' => UploadedFile::fake()->create('payload.php', 16, 'application/x-php'),
        ]))->assertSessionHasErrors('image');

        $this->assertSame(0, MarketplaceListing::count());
    }

    public function test_price_falls_back_to_a_contact_label(): void
    {
        $this->assertSame('السعر عند التواصل', $this->listing(['price' => null])->priceLabel());
        $this->assertSame('500,000 د.ع', $this->listing(['price' => 500000])->priceLabel());
    }

    public function test_published_listings_appear_on_the_home_page(): void
    {
        $this->listing(['title' => 'جهاز معروض على الرئيسية']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('جهاز معروض على الرئيسية', false)
            ->assertSee('بيع وشراء', false);
    }

    public function test_navbar_links_to_the_marketplace(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('marketplace.index').'"', false);
    }
}
