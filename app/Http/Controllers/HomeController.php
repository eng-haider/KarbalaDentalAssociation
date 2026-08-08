<?php

namespace App\Http\Controllers;

use App\Models\BoardMember;
use App\Models\Course;
use App\Models\Discount;
use App\Models\Event;
use App\Models\HeroSlide;
use App\Models\MarketplaceListing;
use App\Models\News;
use App\Models\RegulationType;
use App\Models\Transaction;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'heroSlides' => HeroSlide::active()->get(),
            'featuredEvent' => Event::featured(),
            'news' => News::published()->latest('published_at')->take(6)->get(),
            'boardMembers' => BoardMember::orderBy('sort_order')->get(),
            'courses' => Course::published()
                ->with('category')
                ->withCount('publishedLessons')
                ->orderByDesc('starts_at')
                ->take(6)
                ->get(),
            'regulationTypes' => RegulationType::active()->get(),
            'discounts' => Discount::active()->get(),
            'transactionAnalytics' => Transaction::analytics(),
            'listings' => MarketplaceListing::published()->latest()->take(3)->get(),
        ]);
    }
}
