<?php

namespace App\Http\Controllers;

use App\Models\JobOpening;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $types = JobOpening::types();
        $active = $request->query('type');

        if (! array_key_exists((string) $active, $types)) {
            $active = null;
        }

        return view('pages.jobs', [
            'items' => JobOpening::open()
                ->ofType($active)
                ->paginate(9)
                ->withQueryString(),
            'activeType' => $active,
            'counts' => JobOpening::open()
                ->reorder()
                ->selectRaw('type, count(*) as aggregate')
                ->groupBy('type')
                ->pluck('aggregate', 'type'),
            'totalCount' => JobOpening::open()->count(),
        ]);
    }
}
