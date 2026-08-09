<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Database\Seeder;

/**
 * Creates the course categories that drive the tab bar on /courses, and files
 * any still-uncategorised course under the first category whose keywords match
 * its title.
 *
 * Safe to run on a live site: categories are matched by slug, and a course that
 * already has a category is never reassigned.
 */
class CourseCategorySeeder extends Seeder
{
    /**
     * Category definitions, plus the title keywords used to place existing
     * courses automatically.
     */
    private const CATEGORIES = [
        ['slug' => 'implants', 'name' => 'زراعة الأسنان', 'keywords' => ['زراعة', 'زرع']],
        ['slug' => 'orthodontics', 'name' => 'تقويم الأسنان', 'keywords' => ['تقويم']],
        ['slug' => 'endodontics', 'name' => 'علاج الجذور', 'keywords' => ['جذور', 'عصب', 'لبية']],
        ['slug' => 'prosthodontics', 'name' => 'التركيبات والتعويضات', 'keywords' => ['تركيبات', 'تعويضات', 'بدلة']],
        ['slug' => 'pedodontics', 'name' => 'طب أسنان الأطفال', 'keywords' => ['أطفال', 'الأطفال']],
        ['slug' => 'surgery', 'name' => 'جراحة الفم والوجه', 'keywords' => ['جراحة', 'قلع']],
        ['slug' => 'periodontics', 'name' => 'أمراض اللثة', 'keywords' => ['لثة', 'اللثة']],
        ['slug' => 'cosmetic', 'name' => 'تجميل الأسنان', 'keywords' => ['تجميل', 'ابتسامة', 'تبييض']],
        ['slug' => 'infection-control', 'name' => 'التعقيم ومكافحة العدوى', 'keywords' => ['تعقيم', 'العدوى', 'مكافحة']],
        ['slug' => 'practice-management', 'name' => 'إدارة العيادة', 'keywords' => ['إدارة', 'المحاسبة', 'التسويق']],
    ];

    public function run(): void
    {
        $categories = [];

        foreach (self::CATEGORIES as $index => $definition) {
            $categories[$definition['slug']] = CourseCategory::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );
        }

        $this->fileUncategorisedCourses($categories);
    }

    /**
     * @param  array<string, CourseCategory>  $categories
     */
    private function fileUncategorisedCourses(array $categories): void
    {
        Course::whereNull('course_category_id')
            ->get()
            ->each(function (Course $course) use ($categories): void {
                foreach (self::CATEGORIES as $definition) {
                    foreach ($definition['keywords'] as $keyword) {
                        if (str_contains($course->title, $keyword)) {
                            $course->update(['course_category_id' => $categories[$definition['slug']]->id]);

                            return;
                        }
                    }
                }
            });
    }
}
