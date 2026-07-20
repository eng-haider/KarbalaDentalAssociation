<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('courses.index', [
            'items' => Course::published()->orderByDesc('starts_at')->paginate(9),
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless($course->is_published, 404);

        return view('courses.show', [
            'course' => $course,
        ]);
    }
}
