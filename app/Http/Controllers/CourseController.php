<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $categories = CourseCategory::active()
            ->withCount(['courses' => fn ($query) => $query->published()])
            ->get();

        $active = $categories->firstWhere('slug', $request->query('category'));

        // An empty tab is a dead end, so only offer categories that actually have
        // published courses — plus whichever one is currently selected.
        $tabs = $categories
            ->filter(fn (CourseCategory $category): bool => $category->courses_count > 0 || $active?->is($category))
            ->values();

        $items = Course::published()
            ->with('category')
            ->withCount('publishedLessons')
            ->when($active, fn ($query) => $query->where('course_category_id', $active->id))
            ->orderByDesc('starts_at')
            ->paginate(9)
            ->withQueryString();

        return view('courses.index', [
            'items' => $items,
            'categories' => $tabs,
            'activeCategory' => $active,
            'totalCount' => Course::published()->count(),
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless($course->is_published, 404);

        $course->load(['category', 'publishedLessons']);

        return view('courses.show', [
            'course' => $course,
        ]);
    }
}
