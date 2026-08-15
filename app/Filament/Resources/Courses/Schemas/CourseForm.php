<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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
                        Select::make('course_category_id')
                            ->label('التصنيف')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('اسم التصنيف')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->label('الرابط اللطيف')
                                    ->required()
                                    ->unique('course_categories', 'slug'),
                                ImageUpload::make('image')
                                    ->label('صورة التصنيف')
                                    ->directory('course-categories')
                                    ->imageEditor(),
                            ]),
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
                        ImageUpload::make('image')
                            ->label('صورة الدورة')
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
