<?php

namespace Tests\Feature;

use App\Filament\Resources\CourseCategories\Pages\CreateCourseCategory;
use App\Filament\Resources\CourseCategories\Pages\ListCourseCategories;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\RelationManagers\LessonsRelationManager;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseLessonsTest extends TestCase
{
    use RefreshDatabase;

    private function makeCourse(): Course
    {
        $category = CourseCategory::create([
            'name' => 'زراعة الأسنان',
            'slug' => 'implants',
        ]);

        return Course::create([
            'title' => 'دورة الزراعة',
            'slug' => 'implants-course',
            'course_category_id' => $category->id,
            'description' => 'وصف الدورة',
            'is_published' => true,
        ]);
    }

    public function test_course_page_lists_published_lessons_with_their_links(): void
    {
        $course = $this->makeCourse();

        $course->lessons()->create([
            'title' => 'الدرس الأول',
            'url' => 'https://example.com/lesson-one',
            'sort_order' => 1,
        ]);

        $course->lessons()->create([
            'title' => 'درس مخفي',
            'url' => 'https://example.com/hidden',
            'is_published' => false,
            'sort_order' => 2,
        ]);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('https://example.com/lesson-one', false)
            ->assertSee('الدرس الأول', false)
            ->assertDontSee('درس مخفي', false)
            ->assertDontSee('https://example.com/hidden', false);
    }

    public function test_courses_index_filters_by_category(): void
    {
        $course = $this->makeCourse();

        $other = CourseCategory::create(['name' => 'تقويم', 'slug' => 'ortho']);
        Course::create([
            'title' => 'دورة التقويم',
            'slug' => 'ortho-course',
            'course_category_id' => $other->id,
            'is_published' => true,
        ]);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('دورة الزراعة', false)
            ->assertSee('دورة التقويم', false);

        $this->get(route('courses.index', ['category' => $course->category->slug]))
            ->assertOk()
            ->assertSee('دورة الزراعة', false)
            ->assertDontSee('دورة التقويم', false);
    }

    public function test_admin_can_add_a_lesson_to_a_course(): void
    {
        $this->actingAs(User::factory()->create());

        $course = $this->makeCourse();

        Livewire::test(LessonsRelationManager::class, [
            'ownerRecord' => $course,
            'pageClass' => EditCourse::class,
        ])
            ->assertSuccessful()
            ->callAction(TestAction::make('create')->table(), data: [
                'title' => 'درس جديد',
                'url' => 'https://example.com/new-lesson',
                'duration' => '10:00',
                'is_published' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(Lesson::class, [
            'course_id' => $course->id,
            'title' => 'درس جديد',
            'url' => 'https://example.com/new-lesson',
        ]);
    }

    public function test_admin_can_create_a_course_category_with_an_image(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ListCourseCategories::class)->assertSuccessful();

        Livewire::test(CreateCourseCategory::class)
            ->assertFormFieldExists('image')
            ->fillForm([
                'name' => 'جراحة الفم',
                'slug' => 'oral-surgery',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(CourseCategory::class, [
            'slug' => 'oral-surgery',
        ]);
    }
}
