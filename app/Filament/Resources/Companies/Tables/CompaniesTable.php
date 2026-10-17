<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Models\Company;
use App\Support\WhatsApp;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('الشعار')
                    ->disk('osif')
                    ->circular()
                    ->imageSize(44),
                TextColumn::make('name')
                    ->label('الشركة')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Company $record): ?string => $record->booth ? 'جناح '.$record->booth : null)
                    ->searchable(),
                TextColumn::make('whatsapp_phone')
                    ->label('واتساب الطلبات')
                    ->icon(Heroicon::OutlinedPhone)
                    ->copyable()
                    ->copyMessage('تم نسخ الرقم'),
                TextColumn::make('products_count')
                    ->label('المنتجات')
                    ->counts('products')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('account.phone')
                    ->label('رقم الدخول')
                    ->formatStateUsing(fn (?string $state): ?string => WhatsApp::local($state))
                    ->placeholder('بدون حساب')
                    ->copyable()
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label('ظاهرة'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                Action::make('page')
                    ->label('الصفحة')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Company $record): string => route('osif.companies.show', $record), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedBuildingStorefront)
            ->emptyStateHeading('لا توجد شركات بعد')
            ->emptyStateDescription('أضف الشركات المشاركة وأنشئ لكل منها حساباً لرفع منتجاتها وعروضها.');
    }
}
