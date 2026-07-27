<?php

namespace App\Filament\Resources\TransactionStatuses;

use App\Filament\Resources\TransactionStatuses\Pages\CreateTransactionStatus;
use App\Filament\Resources\TransactionStatuses\Pages\EditTransactionStatus;
use App\Filament\Resources\TransactionStatuses\Pages\ListTransactionStatuses;
use App\Filament\Resources\TransactionStatuses\Schemas\TransactionStatusForm;
use App\Filament\Resources\TransactionStatuses\Tables\TransactionStatusesTable;
use App\Models\TransactionStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransactionStatusResource extends Resource
{
    protected static ?string $model = TransactionStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'حالات المعاملات';

    protected static ?int $navigationSort = 11;

    public static function getModelLabel(): string
    {
        return 'حالة معاملة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'حالات المعاملات';
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionStatusesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactionStatuses::route('/'),
            'create' => CreateTransactionStatus::route('/create'),
            'edit' => EditTransactionStatus::route('/{record}/edit'),
        ];
    }
}
