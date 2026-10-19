<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DiscountsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_the_osif_companies_with_a_link_to_each(): void
    {
        $company = Company::create(['name' => 'Dental Pro', 'whatsapp_phone' => '07701234567']);
        Company::create(['name' => 'Hidden Co', 'whatsapp_phone' => '07701234568', 'is_active' => false]);

        $this->get(route('discounts'))
            ->assertOk()
            ->assertSee('عروض الشركات المشاركة')
            ->assertSee('Dental Pro')
            ->assertSee(route('osif.companies.show', $company), false)
            ->assertDontSee('Hidden Co');
    }

    public function test_the_section_stays_hidden_without_companies(): void
    {
        $this->get(route('discounts'))
            ->assertOk()
            ->assertDontSee('عروض الشركات المشاركة');
    }

    private function offer(array $attributes = []): Discount
    {
        return Discount::create([
            'brand' => 'سمارت كلينك',
            'title' => 'خصم على اشتراك التطبيق',
            'description' => 'خصم خاص لأعضاء النقابة.',
            'body' => '<p>يُشترط إبراز هوية النقابة.</p>',
            'link' => 'https://example.com/register',
            ...$attributes,
        ]);
    }

    public function test_each_offer_card_opens_a_page_with_its_text_file_and_link(): void
    {
        Storage::fake('public')->put('discounts/files/terms.pdf', '%PDF-1.4');
        $offer = $this->offer(['attachment' => 'discounts/files/terms.pdf', 'attachment_name' => 'شروط العرض.pdf']);

        $this->get(route('discounts'))->assertSee(route('discounts.show', $offer), false);

        $this->get(route('discounts.show', $offer))
            ->assertOk()
            ->assertSee('خصم على اشتراك التطبيق')
            ->assertSee('خصم خاص لأعضاء النقابة.')
            ->assertSee('<p>يُشترط إبراز هوية النقابة.</p>', false)
            ->assertSee('شروط العرض.pdf')
            ->assertSee('فتح الملف')
            ->assertSee(route('discounts.file', $offer), false)
            ->assertSee('https://example.com/register', false);
    }

    public function test_an_offer_without_a_file_shows_no_file_box(): void
    {
        $this->get(route('discounts.show', $this->offer()))
            ->assertOk()
            ->assertDontSee('offer-file');
    }

    public function test_a_pdf_opens_in_the_browser_and_a_word_file_downloads_under_its_name(): void
    {
        $disk = Storage::fake('public');
        $disk->put('discounts/files/a.pdf', '%PDF-1.4');
        $disk->put('discounts/files/b.docx', 'word');
        $pdf = $this->offer(['attachment' => 'discounts/files/a.pdf', 'attachment_name' => 'terms.pdf']);
        $word = $this->offer(['attachment' => 'discounts/files/b.docx', 'attachment_name' => 'terms.docx']);

        $this->get(route('discounts.file', $pdf))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=terms.pdf');
        $this->get(route('discounts.file', $word))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=terms.docx');
    }

    public function test_hidden_offers_have_no_page_or_file(): void
    {
        Storage::fake('public')->put('discounts/files/a.pdf', '%PDF-1.4');
        $offer = $this->offer(['is_active' => false, 'attachment' => 'discounts/files/a.pdf']);

        $this->get(route('discounts.show', $offer))->assertNotFound();
        $this->get(route('discounts.file', $offer))->assertNotFound();
        $this->get(route('discounts.file', $this->offer()))->assertNotFound();
    }
}
