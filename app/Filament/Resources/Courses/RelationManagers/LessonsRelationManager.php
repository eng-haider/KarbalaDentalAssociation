<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'دروس الدورة';

    protected static ?string $modelLabel = 'درس';

    protected static ?string $pluralModelLabel = 'الدروس';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('عنوان الدرس')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('رابط الدرس')
                    ->helperText('رابط الفيديو أو الملف الذي يفتح عند الضغط على زر التشغيل.')
                    ->url()
                    ->required()
                    ->maxLength(2048)
                    ->placeholder('https://')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('صورة الدرس')
                    ->image()
                    ->directory('lessons')
                    ->imageEditor()
                    ->columnSpanFull(),
                TextInput::make('duration')
                    ->label('المدة')
                    ->placeholder('مثال: 12:30'),
                TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_published')
                    ->label('منشور')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة'),
                TextColumn::make('title')
                    ->label('الدرس')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('url')
                    ->label('الرابط')
                    ->limit(40)
                    ->url(fn ($record): string => $record->url, shouldOpenInNewTab: true)
                    ->color('primary'),
                TextColumn::make('duration')
                    ->label('المدة')
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()->label('إضافة درس'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
