<?php

namespace App\Filament\Resources\TransactionStatuses\Pages;

use App\Filament\Resources\TransactionStatuses\TransactionStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransactionStatuses extends ListRecords
{
    protected static string $resource = TransactionStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('إضافة حالة'),
        ];
    }
}
