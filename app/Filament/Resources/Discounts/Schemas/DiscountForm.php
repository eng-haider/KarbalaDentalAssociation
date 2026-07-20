<?php

namespace App\Filament\Resources\Discounts\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الجهة والعرض')
                    ->schema([
                        TextInput::make('brand')
                            ->label('اسم الجهة')
                            ->required()
                            ->placeholder('Smart Clinic · سمارت كلينك'),
                        TextInput::make('tag')
                            ->label('التصنيف')
                            ->placeholder('تطبيق إدارة العيادات'),
                        TextInput::make('title')
                            ->label('عنوان العرض')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('قيمة الخصم')
                    ->schema([
                        TextInput::make('value_label')
                            ->label('قيمة الخصم')
                            ->required()
                            ->default('خصم خاص')
                            ->helperText('النص الكبير في اللوحة الملوّنة، مثال: ٢٥٪'),
                        TextInput::make('value_caption')
                            ->label('تحت القيمة')
                            ->placeholder('على اشتراك التطبيق'),
                        TextInput::make('link')
                            ->label('رابط التسجيل')
                            ->url()
                            ->placeholder('https://')
                            ->columnSpanFull(),
                        TextInput::make('icon')
                            ->label('الأيقونة')
                            ->default('bi-tag')
                            ->helperText('اسم أيقونة Bootstrap Icons.'),
                        TextInput::make('note')
                            ->label('ملاحظة صغيرة')
                            ->placeholder('يُشترط أن يكون الطبيب مجدّداً للسنة الحالية.'),
                    ])
                    ->columns(2),

                Section::make('المزايا')
                    ->schema([
                        Repeater::make('perks')
                            ->label('قائمة المزايا')
                            ->hiddenLabel()
                            ->simple(
                                TextInput::make('perk')
                                    ->label('ميزة')
                                    ->required()
                            )
                            ->addActionLabel('إضافة ميزة')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Section::make('العرض')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
