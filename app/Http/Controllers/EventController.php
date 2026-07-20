<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('events.index', [
            'upcoming' => Event::upcoming()->get(),
            'past' => Event::where('starts_at', '<', now())
                ->orderByDesc('starts_at')
                ->take(12)
                ->get(),
        ]);
    }

    public function show(Event $event): View
    {
        return view('events.show', [
            'event' => $event,
        ]);
    }
}
