<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('طلب التسجيل')
                    ->schema([
                        Select::make('event_id')
                            ->label('الفعالية')
                            ->relationship('event', 'title')
                            ->disabled(),
                        TextInput::make('name')
                            ->label('الاسم الثلاثي')
                            ->disabled(),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->disabled(),
                        TextInput::make('membership_number')
                            ->label('رقم العضوية')
                            ->disabled(),
                        Toggle::make('is_handled')
                            ->label('تمت المعالجة'),
                    ])
                    ->columns(2),
            ]);
    }
}
