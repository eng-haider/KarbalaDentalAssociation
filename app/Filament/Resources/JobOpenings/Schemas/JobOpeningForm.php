<?php

namespace App\Filament\Resources\JobOpenings\Schemas;

use App\Models\JobOpening;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobOpeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل الوظيفة')
                    ->schema([
                        TextInput::make('title')
                            ->label('العنوان الوظيفي')
                            ->placeholder('طبيب أسنان عام')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('employer')
                            ->label('جهة العمل')
                            ->placeholder('عيادة / مركز / مستشفى')
                            ->required()
                            ->maxLength(150),
                        Select::make('type')
                            ->label('نوع الدوام')
                            ->options(JobOpening::types())
                            ->default(JobOpening::TYPE_FULL_TIME)
                            ->required()
                            ->native(false),
                        TextInput::make('specialty')
                            ->label('التخصص')
                            ->placeholder('تقويم، جراحة فم، طب أسنان عام…')
                            ->maxLength(150),
                        TextInput::make('city')
                            ->label('المدينة / المنطقة')
                            ->placeholder('كربلاء المقدسة')
                            ->maxLength(64),
                        TextInput::make('salary')
                            ->label('الراتب / المخصصات')
                            ->placeholder('حسب الخبرة')
                            ->maxLength(150)
                            ->helperText('اتركه فارغاً ليظهر "يُحدد عند المقابلة".'),
                        Textarea::make('description')
                            ->label('وصف الوظيفة')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('requirements')
                            ->label('الشروط والمتطلبات')
                            ->rows(5)
                            ->helperText('اكتب شرطاً واحداً في كل سطر ليظهر كقائمة نقطية.')
                            ->columnSpanFull(),
                        FileUpload::make('logo')
                            ->label('شعار جهة العمل')
                            ->image()
                            ->directory('jobs')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('طريقة التقديم')
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('اسم المسؤول')
                            ->maxLength(120),
                        TextInput::make('contact_phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(32),
                        TextInput::make('contact_email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->maxLength(150),
                        TextInput::make('apply_link')
                            ->label('رابط التقديم')
                            ->url()
                            ->maxLength(255)
                            ->helperText('استمارة إلكترونية أو رابط خارجي، إن وُجد.')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('النشر')
                    ->schema([
                        DatePicker::make('closes_at')
                            ->label('آخر موعد للتقديم')
                            ->native(false)
                            ->helperText('تُخفى الفرصة من الموقع تلقائياً بعد هذا التاريخ. اتركه فارغاً لتبقى مفتوحة.'),
                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->helperText('الأصغر يظهر أولاً.'),
                        Toggle::make('is_featured')
                            ->label('فرصة مميزة')
                            ->helperText('تظهر في مقدمة القائمة.'),
                        Toggle::make('is_active')
                            ->label('منشورة')
                            ->default(true)
                            ->helperText('أوقفها لإخفاء الفرصة من الموقع دون حذفها.'),
                    ])
                    ->columns(2),
            ]);
    }
}
