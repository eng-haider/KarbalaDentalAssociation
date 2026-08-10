<?php

namespace Tests\Feature;

use App\Filament\Resources\HomeSections\Pages\ListHomeSections;
use App\Models\HomeSection;
use App\Models\User;
use Database\Seeders\HomeSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeSectionOrderTest extends TestCase
{
    use RefreshDatabase;

    /** Position of each section id in the rendered page, in document order. */
    private function renderedOrder(string $html, array $ids): array
    {
        $found = [];

        foreach ($ids as $id) {
            $at = strpos($html, 'id="'.$id.'"');

            if ($at !== false) {
                $found[$id] = $at;
            }
        }

        asort($found);

        return array_keys($found);
    }

    public function test_the_home_page_renders_without_any_section_rows(): void
    {
        $this->assertSame(0, HomeSection::count());

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="about"', false)
            ->assertSee('id="contact"', false);
    }

    public function test_the_seeder_registers_every_known_section(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $this->assertSame(count(HomeSection::defaults()), HomeSection::count());
        $this->assertSame(array_keys(HomeSection::defaults()), HomeSection::visible()->pluck('key')->all());
    }

    public function test_the_seeder_does_not_reset_an_order_the_admin_arranged(): void
    {
        $this->seed(HomeSectionSeeder::class);

        // sort_order is unsigned, so stay non-negative — the same range
        // Filament writes when rows are dragged.
        HomeSection::where('key', 'contact')->update(['sort_order' => 99]);
        HomeSection::where('key', 'news')->update(['is_visible' => false]);

        $this->seed(HomeSectionSeeder::class);

        $this->assertSame(99, HomeSection::where('key', 'contact')->value('sort_order'));
        $this->assertFalse((bool) HomeSection::where('key', 'news')->value('is_visible'));
        $this->assertSame(count(HomeSection::defaults()), HomeSection::count());
    }

    public function test_reordering_sections_changes_the_order_on_the_page(): void
    {
        $this->seed(HomeSectionSeeder::class);

        // Put contact above about, the reverse of the default order.
        HomeSection::where('key', 'contact')->update(['sort_order' => 1]);
        HomeSection::where('key', 'about')->update(['sort_order' => 2]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(['contact', 'about'], $this->renderedOrder($html, ['about', 'contact']));
    }

    public function test_hiding_a_section_removes_it_from_the_page(): void
    {
        $this->seed(HomeSectionSeeder::class);

        $this->get(route('home'))->assertOk()->assertSee('id="complaint"', false);

        HomeSection::where('key', 'complaint')->update(['is_visible' => false]);

        $this->get(route('home'))->assertOk()->assertDontSee('id="complaint"', false);
    }

    public function test_an_unknown_section_key_is_ignored_rather_than_breaking_the_page(): void
    {
        $this->seed(HomeSectionSeeder::class);

        HomeSection::create(['key' => 'not-a-real-section', 'name' => 'قسم غير معروف', 'sort_order' => 0]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="about"', false)
            ->assertDontSee('not-a-real-section', false);
    }

    public function test_dragging_rows_in_the_dashboard_persists_the_new_order(): void
    {
        $this->seed(HomeSectionSeeder::class);
        $this->actingAs(User::factory()->create());

        $ids = HomeSection::orderBy('sort_order')->pluck('id')->all();
        $reversed = array_reverse($ids);

        // reorderTable is the action Filament's drag handler calls on drop.
        Livewire::test(ListHomeSections::class)->call('reorderTable', $reversed);

        $this->assertSame($reversed, HomeSection::orderBy('sort_order')->pluck('id')->all());

        // …and the page follows it: contact was last, it should now be first.
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertSame(['contact', 'about'], $this->renderedOrder($html, ['about', 'contact']));
    }

    public function test_every_default_key_actually_renders_a_section(): void
    {
        $this->seed(HomeSectionSeeder::class);

        // Sections that hide themselves when they have no content (news, courses,
        // discounts, marketplace, regulations, board, featured-event, hero) are
        // excluded; the rest must each put their anchor on the page.
        $alwaysRendered = ['transaction-search', 'about', 'statistics',
            'apply', 'partners', 'social', 'complaint', 'contact', 'clinic-analytics'];

        $html = $this->get(route('home'))->assertOk()->getContent();

        foreach ($alwaysRendered as $key) {
            $this->assertStringContainsString('id="'.$key.'"', $html, "Section [{$key}] did not render.");
        }
    }
}
