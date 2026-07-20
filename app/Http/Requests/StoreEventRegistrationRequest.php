<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:5', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\s\-]{10,}$/'],
            'membership_number' => ['required', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى كتابة الاسم الثلاثي.',
            'name.min' => 'يرجى كتابة الاسم الثلاثي كاملاً.',
            'phone.required' => 'يرجى كتابة رقم الهاتف.',
            'phone.regex' => 'يرجى كتابة رقم هاتف صحيح.',
            'membership_number.required' => 'يرجى كتابة رقم العضوية.',
        ];
    }
}
