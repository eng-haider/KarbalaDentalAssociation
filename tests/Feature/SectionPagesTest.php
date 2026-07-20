<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Event;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SectionPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pageProvider(): array
    {
        return [
            'about' => ['about'],
            'board' => ['board'],
            'regulations' => ['regulations'],
            'discounts' => ['discounts'],
            'apply' => ['apply'],
            'transaction search' => ['transaction-search'],
            'contact' => ['contact'],
            'complaint' => ['complaint'],
            'news index' => ['news.index'],
            'courses index' => ['courses.index'],
            'events index' => ['events.index'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_section_page_loads(string $routeName): void
    {
        $this->get(route($routeName))->assertOk();
    }

    public function test_news_detail_loads_and_hides_unpublished(): void
    {
        $published = News::create([
            'title' => 'خبر منشور', 'slug' => 'published', 'body' => 'المحتوى',
            'is_published' => true, 'published_at' => now(),
        ]);
        $draft = News::create([
            'title' => 'مسودة', 'slug' => 'draft', 'body' => 'المحتوى', 'is_published' => false,
        ]);

        $this->get(route('news.show', $published))->assertOk()->assertSee('خبر منشور', false);
        $this->get(route('news.show', $draft))->assertNotFound();
    }

    public function test_course_and_event_detail_load(): void
    {
        $course = Course::create([
            'title' => 'دورة', 'slug' => 'course', 'description' => 'وصف', 'is_published' => true,
        ]);
        $event = Event::create(['title' => 'فعالية', 'starts_at' => now()->addWeek()]);

        $this->get(route('courses.show', $course))->assertOk();
        $this->get(route('events.show', $event))->assertOk()->assertSee('فعالية', false);
    }
}
