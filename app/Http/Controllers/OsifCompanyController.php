<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyProduct;
use App\Support\QrCode;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /** The company page's QR code as a large PNG, to share or hand to a designer. */
    public function qr(Company $company): Response
    {
        return response(QrCode::png(route('osif.companies.show', $company)), headers: [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qr-'.$company->slug.'.png"',
        ]);
    }

    /** A printable A4 sign for the company's booth: scan to open its page and order. */
    public function poster(Company $company): View
    {
        $url = route('osif.companies.show', $company);

        return view('osif.companies.poster', [
            'company' => $company,
            'url' => $url,
            'qr' => QrCode::svg($url),
        ]);
    }

    /**
     * A company image, streamed by Laravel rather than through /storage, which
     * some hosts block or lack (the same reason invitation cards are streamed).
     */
    public function media(string $path): StreamedResponse
    {
        $disk = Storage::disk('osif');
        abort_if(str_contains($path, '..') || ! $disk->exists($path), 404);

        return $disk->response($path, headers: [
            // A replaced image is stored under a new name, so a URL never changes content.
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
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
