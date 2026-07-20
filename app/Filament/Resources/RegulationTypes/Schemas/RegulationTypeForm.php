<?php

namespace App\Filament\Resources\RegulationTypes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegulationTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('نوع المعاملة')
                    ->schema([
                        TextInput::make('title')
                            ->label('اسم المعاملة')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('icon')
                            ->label('الأيقونة')
                            ->default('bi-file-earmark-text')
                            ->helperText('اسم أيقونة Bootstrap Icons، مثال: bi-hospital'),
                        Textarea::make('note')
                            ->label('ملاحظة إجرائية')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('تظهر في صندوق أزرق أعلى قائمة الشروط.'),
                        Textarea::make('preamble')
                            ->label('تمهيد')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('سطر تمهيدي اختياري يسبق الشروط المرقّمة.'),
                    ])
                    ->columns(2),

                Section::make('الشروط المطلوبة')
                    ->schema([
                        Repeater::make('conditions')
                            ->label('الشروط')
                            ->hiddenLabel()
                            ->simple(
                                Textarea::make('condition')
                                    ->label('الشرط')
                                    ->rows(2)
                                    ->required()
                            )
                            ->addActionLabel('إضافة شرط')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Section::make('العرض')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('مفعّلة')
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
