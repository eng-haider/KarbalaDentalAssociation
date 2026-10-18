<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
