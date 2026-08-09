<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use Database\Seeders\CourseCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCategorySeederTest extends TestCase
{
    use RefreshDatabase;

    private function seed_(): void
    {
        $this->seed(CourseCategorySeeder::class);
    }

    public function test_it_creates_active_categories(): void
    {
        $this->seed_();

        $this->assertGreaterThan(0, CourseCategory::active()->count());
        $this->assertNotNull(CourseCategory::where('slug', 'implants')->first());
        $this->assertTrue(CourseCategory::where('slug', 'implants')->value('is_active'));
    }

    public function test_running_it_twice_does_not_duplicate_categories(): void
    {
        $this->seed_();
        $count = CourseCategory::count();

        $this->seed_();

        $this->assertSame($count, CourseCategory::count());
    }

    public function test_it_files_uncategorised_courses_by_title_keyword(): void
    {
        $implant = Course::create(['title' => 'دورة زراعة الأسنان المتقدمة', 'slug' => 'c1', 'is_published' => true]);
        $sterilisation = Course::create(['title' => 'ورشة عمل: التعقيم ومكافحة العدوى', 'slug' => 'c2', 'is_published' => true]);
        $unmatched = Course::create(['title' => 'موضوع عام بلا كلمة مفتاحية', 'slug' => 'c3', 'is_published' => true]);

        $this->seed_();

        $this->assertSame('implants', $implant->refresh()->category?->slug);
        $this->assertSame('infection-control', $sterilisation->refresh()->category?->slug);
        $this->assertNull($unmatched->refresh()->course_category_id);
    }

    public function test_it_never_reassigns_a_course_that_already_has_a_category(): void
    {
        $chosen = CourseCategory::create(['name' => 'تصنيف يدوي', 'slug' => 'manual', 'is_active' => true]);
        $course = Course::create([
            'title' => 'دورة زراعة الأسنان المتقدمة',
            'slug' => 'c1',
            'course_category_id' => $chosen->id,
            'is_published' => true,
        ]);

        $this->seed_();

        $this->assertSame('manual', $course->refresh()->category?->slug);
    }

    public function test_the_tabs_appear_on_the_courses_page_after_seeding(): void
    {
        Course::create(['title' => 'دورة زراعة الأسنان المتقدمة', 'slug' => 'c1', 'is_published' => true]);

        $this->get(route('courses.index'))->assertOk()->assertDontSee('course-tabs', false);

        $this->seed_();

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('course-tabs', false)
            ->assertSee('زراعة الأسنان', false)
            ->assertSee(route('courses.index', ['category' => 'implants']), false);
    }
}
