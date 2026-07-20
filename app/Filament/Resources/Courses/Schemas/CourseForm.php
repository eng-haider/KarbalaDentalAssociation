<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الدورة')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان الدورة')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->label('الرابط اللطيف')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('instructor')
                            ->label('المحاضر'),
                        TextInput::make('duration')
                            ->label('المدة')
                            ->placeholder('مثال: يومان'),
                        DateTimePicker::make('starts_at')
                            ->label('تاريخ البدء'),
                        TextInput::make('seats')
                            ->label('عدد المقاعد')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(2),

                Section::make('النشر')
                    ->schema([
                        FileUpload::make('image')
                            ->label('صورة الدورة')
                            ->image()
                            ->directory('courses')
                            ->imageEditor(),
                        Toggle::make('is_published')
                            ->label('منشورة')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
