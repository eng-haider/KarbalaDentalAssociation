<?php

namespace App\Filament\Resources\TransactionStatuses\Schemas;

use App\Models\TransactionStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الحالة')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الحالة')
                            ->placeholder('مثال: قيد المراجعة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('color')
                            ->label('اللون')
                            ->options(TransactionStatus::COLORS)
                            ->default('gray')
                            ->required()
                            ->native(false),
                        TextInput::make('icon')
                            ->label('الأيقونة')
                            ->helperText('اختياري — اسم أيقونة Bootstrap، مثال: bi-hourglass-split')
                            ->placeholder('bi-circle')
                            ->default('bi-circle')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('مفعّلة')
                            ->helperText('الحالات غير المفعّلة لا تظهر عند اختيار حالة المعاملة.')
                            ->default(true),
                        Toggle::make('is_default')
                            ->label('الحالة الافتراضية')
                            ->helperText('تُستخدم تلقائياً لأي معاملة جديدة لا تحدد لها حالة.'),
                    ])
                    ->columns(2),
            ]);
    }
}
