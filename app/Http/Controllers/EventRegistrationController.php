<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRegistrationRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventRegistrationController extends Controller
{
    public function store(StoreEventRegistrationRequest $request, Event $event): RedirectResponse
    {
        if (! $event->registration_open) {
            throw new NotFoundHttpException('التسجيل في هذه الفعالية مغلق.');
        }

        $event->registrations()->create($request->validated());

        return redirect()
            ->to($event->isOsif() ? route('osif.register').'#osif-form' : route('events.show', $event).'#registration')
            ->with('registration_ok', true);
    }
}
