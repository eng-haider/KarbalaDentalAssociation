<?php

namespace App\Filament\Resources\MarketplaceListings\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\MarketplaceListing;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketplaceListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الإعلان')
                    ->schema([
                        Select::make('type')
                            ->label('نوع الإعلان')
                            ->options(MarketplaceListing::types())
                            ->default(MarketplaceListing::TYPE_SALE)
                            ->required()
                            ->native(false),
                        Select::make('category')
                            ->label('التصنيف')
                            ->options(MarketplaceListing::categories())
                            ->native(false),
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label('السعر (دينار)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('اتركه فارغاً ليظهر "السعر عند التواصل".'),
                        ImageUpload::make('image')
                            ->label('الصورة')
                            ->directory('marketplace')
                            ->imageEditor(),
                    ])
                    ->columns(2),

                Section::make('بيانات التواصل')
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('اسم صاحب الإعلان')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('contact_phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(32),
                        TextInput::make('city')
                            ->label('المدينة / المنطقة')
                            ->maxLength(64),
                    ])
                    ->columns(3),

                Section::make('المراجعة')
                    ->schema([
                        Select::make('status')
                            ->label('الحالة')
                            ->options(MarketplaceListing::statuses())
                            ->default(MarketplaceListing::STATUS_PENDING)
                            ->required()
                            ->native(false)
                            ->helperText('لا يظهر الإعلان في الموقع إلا بعد اختيار "منشور".'),
                    ]),
            ]);
    }
}
