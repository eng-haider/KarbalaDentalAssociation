<?php

namespace App\Filament\Resources\Complaints\Schemas;

use App\Models\Complaint;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplaintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الشكوى')
                    ->schema([
                        Textarea::make('body')
                            ->label('نص الشكوى')
                            ->rows(8)
                            ->disabled()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options(Complaint::statuses())
                            ->default(Complaint::STATUS_NEW)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}
