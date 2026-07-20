<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'يرجى كتابة نص الشكوى.',
            'body.min' => 'نص الشكوى قصير جداً (١٠ أحرف على الأقل).',
            'body.max' => 'نص الشكوى طويل جداً.',
        ];
    }
}
