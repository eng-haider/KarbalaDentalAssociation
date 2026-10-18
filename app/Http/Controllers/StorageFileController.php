<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves an uploaded file from the public disk when the web server can't.
 *
 * Normally `public/storage` links to `storage/app/public` and the web server
 * sends these files itself, so this never runs. On hosting where that link is
 * missing, or is a stale copy of the folder, new uploads would otherwise 403;
 * this keeps every `/storage/...` URL working without touching the server.
 */
class StorageFileController extends Controller
{
    /** Kept out of reach: invitation cards have their own token routes, and Livewire's are unsaved uploads. */
    private const PRIVATE_PREFIXES = ['osif-invitations/', 'livewire-tmp/'];

    public function __invoke(string $path): StreamedResponse
    {
        $disk = Storage::disk('public');

        abort_if(
            str_contains($path, '..') || Str::startsWith($path, self::PRIVATE_PREFIXES) || ! $disk->exists($path),
            404
        );

        return $disk->response($path, headers: [
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
