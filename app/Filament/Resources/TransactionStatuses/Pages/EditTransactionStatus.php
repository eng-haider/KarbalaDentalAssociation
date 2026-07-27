<?php

namespace App\Filament\Resources\TransactionStatuses\Pages;

use App\Filament\Resources\TransactionStatuses\TransactionStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransactionStatus extends EditRecord
{
    protected static string $resource = TransactionStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn (): bool => $this->getRecord()->transactions()->exists()),
        ];
    }
}
