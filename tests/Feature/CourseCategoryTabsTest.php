<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCategoryTabsTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(string $name, string $slug, array $attributes = []): CourseCategory
    {
        return CourseCategory::create(array_merge([
            'name' => $name,
            'slug' => $slug,
        ], $attributes));
    }

    private function makeCourse(string $title, string $slug, ?CourseCategory $category = null, bool $published = true): Course
    {
        return Course::create([
            'title' => $title,
            'slug' => $slug,
            'course_category_id' => $category?->id,
            'is_published' => $published,
        ]);
    }

    public function test_a_tab_is_rendered_for_each_category_that_has_courses(): void
    {
        $implants = $this->makeCategory('زراعة الأسنان', 'implants');
        $ortho = $this->makeCategory('تقويم الأسنان', 'ortho');

        $this->makeCourse('دورة الزراعة', 'c1', $implants);
        $this->makeCourse('دورة التقويم', 'c2', $ortho);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('course-tabs', false)
            ->assertSee('الكل', false)
            ->assertSee('زراعة الأسنان', false)
            ->assertSee('تقويم الأسنان', false)
            ->assertSee(route('courses.index', ['category' => 'implants']), false);
    }

    public function test_categories_without_published_courses_are_not_shown_as_tabs(): void
    {
        $used = $this->makeCategory('زراعة الأسنان', 'implants');
        $this->makeCategory('تصنيف فارغ', 'empty-one');
        $unpublishedOnly = $this->makeCategory('تصنيف مخفي', 'hidden-one');

        $this->makeCourse('دورة الزراعة', 'c1', $used);
        $this->makeCourse('دورة غير منشورة', 'c2', $unpublishedOnly, published: false);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('زراعة الأسنان', false)
            ->assertDontSee('تصنيف فارغ', false)
            ->assertDontSee('تصنيف مخفي', false);
    }

    public function test_inactive_categories_are_not_shown_as_tabs(): void
    {
        $inactive = $this->makeCategory('تصنيف معطل', 'disabled', ['is_active' => false]);
        $this->makeCourse('دورة مخفية التصنيف', 'c1', $inactive);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertDontSee('تصنيف معطل', false);
    }

    public function test_the_all_tab_counts_every_published_course_including_uncategorised(): void
    {
        $implants = $this->makeCategory('زراعة الأسنان', 'implants');

        $this->makeCourse('دورة الزراعة', 'c1', $implants);
        $this->makeCourse('دورة بدون تصنيف', 'c2');
        $this->makeCourse('دورة غير منشورة', 'c3', $implants, published: false);

        $response = $this->get(route('courses.index'))->assertOk();

        $response->assertViewHas('totalCount', 2);
        // The uncategorised course is only reachable from the "all" tab.
        $response->assertSee('دورة بدون تصنيف', false);
    }

    public function test_selecting_a_tab_filters_the_courses_and_marks_it_active(): void
    {
        $implants = $this->makeCategory('زراعة الأسنان', 'implants');
        $ortho = $this->makeCategory('تقويم الأسنان', 'ortho');

        $this->makeCourse('دورة الزراعة', 'c1', $implants);
        $this->makeCourse('دورة التقويم', 'c2', $ortho);
        $this->makeCourse('دورة بدون تصنيف', 'c3');

        $response = $this->get(route('courses.index', ['category' => 'implants']))->assertOk();

        $response->assertViewHas('activeCategory', fn (?CourseCategory $category): bool => $category?->slug === 'implants');
        $response->assertSee('دورة الزراعة', false)
            ->assertDontSee('دورة التقويم', false)
            ->assertDontSee('دورة بدون تصنيف', false);
    }

    public function test_an_unknown_category_slug_falls_back_to_showing_everything(): void
    {
        $implants = $this->makeCategory('زراعة الأسنان', 'implants');
        $this->makeCourse('دورة الزراعة', 'c1', $implants);
        $this->makeCourse('دورة بدون تصنيف', 'c2');

        $this->get(route('courses.index', ['category' => 'no-such-category']))
            ->assertOk()
            ->assertViewHas('activeCategory', null)
            ->assertSee('دورة الزراعة', false)
            ->assertSee('دورة بدون تصنيف', false);
    }

    public function test_no_tabs_are_rendered_when_no_categories_exist(): void
    {
        $this->makeCourse('دورة بدون تصنيف', 'c1');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertDontSee('course-tabs', false)
            ->assertSee('دورة بدون تصنيف', false);
    }

    public function test_an_empty_selected_category_keeps_its_tab_and_explains_why(): void
    {
        $implants = $this->makeCategory('زراعة الأسنان', 'implants');
        $ortho = $this->makeCategory('تقويم الأسنان', 'ortho');

        $this->makeCourse('دورة الزراعة', 'c1', $implants);
        $this->makeCourse('دورة التقويم', 'c2', $ortho, published: false);

        // Selecting a category whose courses are all unpublished must not drop
        // the tab the visitor is standing on.
        $this->get(route('courses.index', ['category' => 'ortho']))
            ->assertOk()
            ->assertSee('تقويم الأسنان', false)
            ->assertSee('لا توجد دورات منشورة ضمن تصنيف', false);
    }
}
