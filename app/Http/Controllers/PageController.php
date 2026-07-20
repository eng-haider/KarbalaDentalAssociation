<?php

namespace App\Http\Controllers;

use App\Models\BoardMember;
use App\Models\Discount;
use App\Models\RegulationType;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function board(): View
    {
        return view('pages.board', [
            'members' => BoardMember::orderBy('sort_order')->get(),
        ]);
    }

    public function regulations(): View
    {
        return view('pages.regulations', [
            'types' => RegulationType::active()->get(),
        ]);
    }

    public function discounts(): View
    {
        return view('pages.discounts', [
            'items' => Discount::active()->get(),
        ]);
    }

    public function apply(): View
    {
        return view('pages.apply');
    }

    public function transactionSearch(): View
    {
        return view('pages.transaction-search');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function complaint(): View
    {
        return view('pages.complaint');
    }
}
