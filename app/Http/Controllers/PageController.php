<?php

namespace App\Http\Controllers;

use App\Models\BoardMember;
use App\Models\Company;
use App\Models\Discount;
use App\Models\RegulationType;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            // The OSIF exhibitors' offers are discounts for members too.
            'companies' => Company::query()->active()->ordered()->withShopCounts()->get(),
        ]);
    }

    public function discount(Discount $discount): View
    {
        abort_unless($discount->is_active, 404);

        return view('pages.discount', ['item' => $discount]);
    }

    /** The offer's file: a PDF opens in the browser, a Word file downloads, both under the uploaded name. */
    public function discountFile(Discount $discount): StreamedResponse
    {
        $disk = Storage::disk('public');
        abort_unless($discount->is_active && $discount->attachment && $disk->exists($discount->attachment), 404);

        return $disk->response(
            $discount->attachment,
            $discount->attachmentFileName(),
            ['X-Content-Type-Options' => 'nosniff'],
            $discount->attachmentIsPdf() ? 'inline' : 'attachment',
        );
    }

    public function apply(): View
    {
        return view('pages.apply');
    }

    public function app(): View
    {
        return view('pages.app');
    }

    public function transactionSearch(): View
    {
        return view('pages.transaction-search', [
            'analytics' => Transaction::analytics(),
        ]);
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
