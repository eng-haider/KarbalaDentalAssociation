<?php

namespace App\Filament\Resources\TransactionStatuses\Pages;

use App\Filament\Resources\TransactionStatuses\TransactionStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransactionStatus extends CreateRecord
{
    protected static string $resource = TransactionStatusResource::class;
}
