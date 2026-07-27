<?php

namespace App\Filament\Resources\CourseCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات التصنيف')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم التصنيف')
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
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('الصورة والنشر')
                    ->schema([
                        FileUpload::make('image')
                            ->label('صورة التصنيف')
                            ->image()
                            ->directory('course-categories')
                            ->imageEditor()
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
