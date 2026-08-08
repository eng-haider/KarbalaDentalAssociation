<?php

namespace App\Http\Requests;

use App\Models\MarketplaceListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMarketplaceListingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(MarketplaceListing::types()))],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'category' => ['nullable', Rule::in(array_keys(MarketplaceListing::categories()))],
            'price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'contact_name' => ['required', 'string', 'min:3', 'max:120'],
            'contact_phone' => ['required', 'string', 'min:7', 'max:32'],
            'city' => ['nullable', 'string', 'max:64'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'يرجى اختيار نوع الإعلان.',
            'type.in' => 'نوع الإعلان غير صحيح.',
            'title.required' => 'يرجى كتابة عنوان الإعلان.',
            'title.min' => 'عنوان الإعلان قصير جداً.',
            'description.required' => 'يرجى كتابة وصف الإعلان.',
            'description.min' => 'وصف الإعلان قصير جداً (١٠ أحرف على الأقل).',
            'category.in' => 'التصنيف غير صحيح.',
            'price.integer' => 'يرجى إدخال السعر بالأرقام فقط.',
            'contact_name.required' => 'يرجى كتابة اسم صاحب الإعلان.',
            'contact_phone.required' => 'يرجى كتابة رقم الهاتف للتواصل.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'صيغة الصورة غير مدعومة (JPG أو PNG أو WEBP).',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز ٢ ميغابايت.',
        ];
    }
}
