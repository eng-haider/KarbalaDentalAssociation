<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRegistrationRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventRegistrationController extends Controller
{
    public function store(StoreEventRegistrationRequest $request, Event $event): RedirectResponse
    {
        if (! $event->registration_open) {
            throw new NotFoundHttpException('التسجيل في هذه الفعالية مغلق.');
        }

        $registration = DB::transaction(function () use ($request, $event) {
            // Locking the event makes registrations for it take turns, so the
            // last seat of a professional title cannot be given out twice.
            $event = Event::query()->lockForUpdate()->findOrFail($event->getKey());

            if ($event->isProfessionalTitleFull($request->validated('professional_title'))) {
                throw ValidationException::withMessages([
                    'professional_title' => 'نعتذر، اكتمل العدد المخصص لهذه الصفة.',
                ]);
            }

            return $event->registrations()->create($request->validated());
        });

        return redirect()
            ->to($event->isOsif() ? route('osif.register').'#osif-form' : route('events.show', $event).'#registration')
            ->with([
                'registration_ok' => true,
                'registration_has_email' => $event->isOsif() && filled($registration->email),
            ]);
    }
}
