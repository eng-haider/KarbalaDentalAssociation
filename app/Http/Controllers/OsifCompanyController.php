<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyProduct;
use Illuminate\View\View;

/** The companies exhibiting at OSIF: their pages, products, offers and cart. */
class OsifCompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::query()
            ->active()
            ->ordered()
            ->withCount([
                'products' => fn ($query) => $query->active(),
                'products as offers_count' => fn ($query) => $query->active()->withOffer(),
            ])
            ->get();

        return view('osif.companies.index', ['companies' => $companies]);
    }

    public function show(Company $company): View
    {
        abort_unless($company->is_active, 404);

        $products = $company->products()->active()->get();

        return view('osif.companies.show', [
            'company' => $company,
            'products' => $products,
            'offersCount' => $products->filter->hasOffer()->count(),
            // What the cart needs; prices are re-read from here on every visit,
            // so a saved cart never keeps an outdated price.
            'shop' => [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'whatsapp' => $company->whatsappNumber(),
                ],
                'event' => 'OSIF KARBALA 2026',
                'products' => $products->map(fn (CompanyProduct $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->currentPrice(),
                    'original' => $product->hasDiscount() ? $product->price : null,
                    'discount' => $product->discountPercent(),
                    'image' => $product->imageUrl(),
                    'offer' => $product->hasOffer(),
                    'offerNote' => $product->offer_note,
                ])->values(),
            ],
        ]);
    }
}
