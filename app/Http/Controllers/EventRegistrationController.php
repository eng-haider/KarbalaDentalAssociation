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

        DB::transaction(function () use ($request, $event) {
            // Locking the event makes registrations for it take turns, so the
            // last seat (overall, or of a professional title) cannot be given out twice.
            $event = Event::query()->lockForUpdate()->findOrFail($event->getKey());

            if ($event->hasReachedRegistrationLimit()) {
                throw ValidationException::withMessages([
                    'name' => 'نعتذر، اكتمل العدد المخصص للتسجيل.',
                ]);
            }

            if ($event->isProfessionalTitleFull($request->validated('professional_title'))) {
                throw ValidationException::withMessages([
                    'professional_title' => 'نعتذر، اكتمل العدد المخصص لهذه الصفة.',
                ]);
            }

            $event->registrations()->create($request->validated());
        });

        return redirect()
            ->to($event->isOsif() ? route('osif.register').'#osif-form' : route('events.show', $event).'#registration')
            ->with('registration_ok', true);
    }
}
