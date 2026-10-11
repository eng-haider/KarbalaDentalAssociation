<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class OsifController extends Controller
{
    public function __invoke(): View
    {
        return view('osif.show', ['event' => $this->event()]);
    }

    public function registration(): View
    {
        return view('osif.registration', ['event' => $this->event()]);
    }

    private function event(): Event
    {
        return Event::query()
            ->where('slug', Event::OSIF_SLUG)
            ->firstOrFail();
    }
}
