<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('محتوى الشريحة')
                    ->schema([
                        FileUpload::make('image')
                            ->label('صورة الخلفية')
                            ->image()
                            ->directory('hero')
                            ->imageEditor()
                            ->columnSpanFull(),
                        TextInput::make('badge')
                            ->label('الشارة (نص صغير أعلى العنوان)')
                            ->placeholder('الموقع الرسمي المعتمد'),
                        TextInput::make('badge_icon')
                            ->label('أيقونة الشارة')
                            ->default('bi-patch-check-fill')
                            ->helperText('اسم أيقونة Bootstrap Icons.'),
                        Textarea::make('title')
                            ->label('العنوان الرئيسي')
                            ->required()
                            ->rows(2)
                            ->helperText('اضغط Enter لبدء سطر جديد.')
                            ->columnSpanFull(),
                        Textarea::make('subtitle')
                            ->label('النص التوضيحي')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('الأزرار')
                    ->description('اترك الحقول فارغة لإخفاء الزر.')
                    ->schema([
                        TextInput::make('button_label')
                            ->label('اسم الزر الأساسي'),
                        TextInput::make('button_url')
                            ->label('رابط الزر الأساسي')
                            ->placeholder('#services أو https://...'),
                        TextInput::make('button2_label')
                            ->label('اسم الزر الثانوي'),
                        TextInput::make('button2_url')
                            ->label('رابط الزر الثانوي')
                            ->placeholder('#news أو https://...'),
                    ])
                    ->columns(2),

                Section::make('العرض')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('مفعّلة')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->helperText('الأصغر يظهر أولاً.'),
                    ])
                    ->columns(2),
            ]);
    }
}
