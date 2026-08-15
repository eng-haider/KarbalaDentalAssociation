<?php

namespace App\Filament\Resources\BoardMembers\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BoardMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('العضو')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('position')
                            ->label('المنصب')
                            ->required()
                            ->maxLength(255),
                        ImageUpload::make('photo')
                            ->label('الصورة')
                            ->avatar()
                            ->directory('board')
                            ->imageEditor(),
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
