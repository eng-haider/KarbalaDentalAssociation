<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('news.index', [
            'items' => News::published()->latest('published_at')->paginate(9),
        ]);
    }

    public function show(News $news): View
    {
        abort_unless($news->is_published, 404);

        return view('news.show', [
            'item' => $news,
            'related' => News::published()
                ->whereKeyNot($news->getKey())
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}
