<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMarketplaceListingRequest;
use App\Models\MarketplaceListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $types = MarketplaceListing::types();
        $active = $request->query('type');

        if (! array_key_exists((string) $active, $types)) {
            $active = null;
        }

        return view('pages.marketplace', [
            'items' => MarketplaceListing::published()
                ->ofType($active)
                ->latest()
                ->paginate(9)
                ->withQueryString(),
            'activeType' => $active,
            'counts' => MarketplaceListing::published()
                ->selectRaw('type, count(*) as aggregate')
                ->groupBy('type')
                ->pluck('aggregate', 'type'),
            'totalCount' => MarketplaceListing::published()->count(),
        ]);
    }

    public function store(StoreMarketplaceListingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('marketplace', 'public');
        }

        // Everything the public submits waits for the dashboard to approve it.
        $data['status'] = MarketplaceListing::STATUS_PENDING;

        MarketplaceListing::create($data);

        return redirect()
            ->route('marketplace.index')
            ->with('marketplace_ok', true);
    }
}
