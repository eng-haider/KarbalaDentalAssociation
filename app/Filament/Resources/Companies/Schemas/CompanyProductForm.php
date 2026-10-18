<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\Company;
use App\Models\CompanyProduct;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * One product on a company's page. Shared by the company dashboard and the
 * organisers' «الشركات المشاركة» page, so both edit products the same way.
 */
class CompanyProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageUpload::make('image')
                    ->label('صورة المنتج')
                    ->disk('osif')
                    ->acceptedFileTypes(ImageUpload::PHOTO_TYPES)
                    ->directory('companies/products')
                    ->image()
                    ->imageEditor()
                    ->maxSize(8192)
                    ->helperText('صورة واضحة للمنتج، ويُفضّل أن تكون مربعة بخلفية بيضاء.')
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('category')
                    ->label('التصنيف')
                    ->placeholder('مثال: حشوات، أدوات جراحية، زراعة')
                    ->maxLength(60)
                    // Free text, with the company's own categories suggested so one name stays one category.
                    ->datalist(fn (?CompanyProduct $record, $livewire): array => static::company($record, $livewire)?->productCategories() ?? [])
                    ->helperText('اختر تصنيفاً سابقاً أو اكتب تصنيفاً جديداً. تظهر التصنيفات في صفحة الشركة ليصفّي الطبيب المنتجات بها.')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('الوصف')
                    ->placeholder('المواصفات، المقاسات، المنشأ…')
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(4_000_000_000)
                    ->suffix('د.ع')
                    ->requiredWith('offer_price')
                    ->helperText('اتركه فارغاً ليظهر «السعر عند الطلب».'),
                Toggle::make('is_active')
                    ->label('ظاهر في صفحة الشركة')
                    ->default(true)
                    ->inline(false),

                Section::make('العرض (اختياري)')
                    ->description('سعر مخفّض، أو عرض مثل «اشترِ 10 واحصل على 1 مجاناً». يظهر المنتج في تبويب «العروض» بشارة مميزة.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        TextInput::make('offer_price')
                            ->label('سعر العرض')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->suffix('د.ع')
                            ->lt('price')
                            ->validationMessages([
                                'lt' => 'سعر العرض يجب أن يكون أقل من السعر الأصلي.',
                            ]),
                        TextInput::make('offer_note')
                            ->label('وصف العرض')
                            ->placeholder('مثال: اشترِ 10 واحصل على 1 مجاناً')
                            ->maxLength(120),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    /** The company being edited: the organisers' company page, or the signed-in company. */
    private static function company(?CompanyProduct $record, mixed $livewire): ?Company
    {
        return $record?->company
            ?? ($livewire instanceof RelationManager ? $livewire->getOwnerRecord() : auth()->user()?->company);
    }
}
