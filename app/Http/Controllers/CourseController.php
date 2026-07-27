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
        $categories = CourseCategory::active()->withCount(['courses' => fn ($query) => $query->published()])->get();

        $active = $categories->firstWhere('slug', $request->query('category'));

        $items = Course::published()
            ->with('category')
            ->withCount('publishedLessons')
            ->when($active, fn ($query) => $query->where('course_category_id', $active->id))
            ->orderByDesc('starts_at')
            ->paginate(9)
            ->withQueryString();

        return view('courses.index', [
            'items' => $items,
            'categories' => $categories,
            'activeCategory' => $active,
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
