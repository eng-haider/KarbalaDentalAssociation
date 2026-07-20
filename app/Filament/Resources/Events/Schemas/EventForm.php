<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الفعالية')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان الفعالية')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                        DateTimePicker::make('starts_at')
                            ->label('يبدأ في')
                            ->required()
                            ->helperText('يُحتسب العدّاد التنازلي في الصفحة الرئيسية من هذا التاريخ.'),
                        DateTimePicker::make('ends_at')
                            ->label('ينتهي في')
                            ->after('starts_at'),
                        TextInput::make('location')
                            ->label('المكان')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('العرض والتسجيل')
                    ->schema([
                        Toggle::make('is_featured')
                            ->label('عرضها في بانر الصفحة الرئيسية')
                            ->helperText('إن لم تُحدَّد أي فعالية، تُعرض أقرب فعالية قادمة تلقائياً.'),
                        Toggle::make('registration_open')
                            ->label('التسجيل مفتوح')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
