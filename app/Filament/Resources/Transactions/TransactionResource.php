<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

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

    /**
     * Fields shown when adding a status that does not exist yet, both from the
     * transaction form and from the bulk "change status" action.
     */
    public static function statusOptionForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('اسم الحالة')
                ->placeholder('مثال: قيد المراجعة')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('color')
                ->label('اللون')
                ->options(TransactionStatus::COLORS)
                ->default('gray')
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('icon')
                ->label('الأيقونة')
                ->helperText('اسم أيقونة Bootstrap، مثال: bi-hourglass-split')
                ->default('bi-circle'),
        ];
    }

    /** Creates the new status and returns the slug stored on the transaction. */
    public static function createStatusUsing(array $data): string
    {
        return TransactionStatus::create($data)->slug;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label('الاسم')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('transaction_type')
                ->label('نوع المعاملة')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options(fn (): array => Transaction::statuses())
                ->default(fn (): string => Transaction::defaultStatus())
                ->required()
                ->searchable()
                ->native(false)
                ->helperText('إذا لم تجد الحالة المطلوبة، أضفها من زر (+).')
                ->createOptionForm(static::statusOptionForm())
                ->createOptionUsing(fn (array $data): string => static::createStatusUsing($data)),
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
                Tables\Columns\SelectColumn::make('status')
                    ->label('الحالة')
                    ->options(fn (): array => Transaction::statuses())
                    ->selectablePlaceholder(false)
                    ->rules(['required'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(fn (): array => Transaction::statuses()),
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
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('change_status')
                        ->label('تغيير الحالة')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('primary')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options(fn (): array => Transaction::statuses())
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->createOptionForm(static::statusOptionForm())
                                ->createOptionUsing(fn (array $data): string => static::createStatusUsing($data)),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('تم تحديث حالة المعاملات المحددة.')
                        ->action(fn (Collection $records, array $data) => Transaction::whereKey($records->modelKeys())
                            ->update(['status' => $data['status']])),
                    DeleteBulkAction::make(),
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
