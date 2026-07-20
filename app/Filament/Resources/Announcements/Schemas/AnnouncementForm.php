<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الإعلان')
                    ->schema([
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->label('النص')
                            ->required()
                            ->columnSpanFull(),
                        Toggle::make('is_pinned')
                            ->label('تثبيت في الأعلى'),
                        DateTimePicker::make('published_at')
                            ->label('تاريخ النشر')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
