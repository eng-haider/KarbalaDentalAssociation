<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        /** @var Event|null $event */
        $event = $this->route('event');

        if ($event?->isOsif()) {
            $this->merge([
                'whatsapp_phone' => $this->input('phone'),
            ]);
        }
    }

    public function rules(): array
    {
        /** @var Event|null $event */
        $event = $this->route('event');
        $isOsif = $event?->isOsif() ?? false;

        $rules = [
            'name' => ['required', 'string', 'min:5', 'max:255'],
            'phone' => [
                'required', 'string', 'regex:/^[0-9+\s\-]{10,}$/',
                Rule::unique('event_registrations', 'phone')->where('event_id', $event?->id),
            ],
            'whatsapp_phone' => [$isOsif ? 'required' : 'nullable', 'string', 'regex:/^[0-9+\s\-]{10,}$/'],
            'membership_number' => [$isOsif ? 'nullable' : 'required', 'string', 'max:64'],
            'professional_title' => $isOsif
                ? ['required', 'string', Rule::in($event->professionalTitleOptions())]
                : ['nullable', 'string', 'max:120'],
            'owns_clinic' => [$isOsif ? 'required' : 'nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($isOsif) {
            $rules['email'] = ['required', 'string', 'email', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى كتابة الاسم الثلاثي.',
            'name.min' => 'يرجى كتابة الاسم الثلاثي كاملاً.',
            'phone.required' => 'يرجى كتابة رقم الهاتف.',
            'phone.regex' => 'يرجى كتابة رقم هاتف صحيح.',
            'phone.unique' => 'هذا الرقم مسجّل مسبقاً في هذه الفعالية.',
            'whatsapp_phone.required' => 'يرجى كتابة رقم الواتساب.',
            'whatsapp_phone.regex' => 'يرجى كتابة رقم واتساب صحيح.',
            'email.required' => 'يرجى كتابة البريد الإلكتروني.',
            'email.email' => 'يرجى كتابة بريد إلكتروني صحيح.',
            'professional_title.required' => 'يرجى اختيار الصفة المهنية أو الأكاديمية.',
            'professional_title.in' => 'يرجى اختيار الصفة من القائمة.',
            'owns_clinic.required' => 'يرجى الإجابة: هل تمتلك عيادة؟',
            'owns_clinic.boolean' => 'يرجى الإجابة: هل تمتلك عيادة؟',
            'membership_number.required' => 'يرجى كتابة رقم العضوية.',
        ];
    }
}
