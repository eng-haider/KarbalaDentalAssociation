<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'المعاملات';
    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return 'معاملة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المعاملات';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('الاسم')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('transaction_type')
                ->label('نوع المعاملة')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('نوع المعاملة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('clinic')
                    ->label('مع عيادة')
                    ->query(fn ($query) => $query->where('transaction_type', 'like', '%عيادة%')),
                Tables\Filters\Filter::make('no_clinic')
                    ->label('بدون عيادة')
                    ->query(fn ($query) => $query->where('transaction_type', 'like', '%بدون عيادة%')),
                Tables\Filters\Filter::make('join')
                    ->label('طلب انتماء')
                    ->query(fn ($query) => $query->where('transaction_type', 'like', '%انتماء%')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }
}
