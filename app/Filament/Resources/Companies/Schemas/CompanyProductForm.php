<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\Company;
use App\Models\CompanyProduct;
use App\Models\ProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

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
                Select::make('product_category_id')
                    ->label('التصنيف')
                    // Only this company's own categories.
                    ->relationship('category', 'name', fn (Builder $query, $livewire, ?CompanyProduct $record) => $query
                        ->where('company_id', static::companyOf($livewire, $record)?->getKey())
                        ->ordered())
                    ->placeholder('بدون تصنيف')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([static::categoryNameInput()])
                    ->createOptionModalHeading('تصنيف جديد')
                    ->createOptionUsing(fn (array $data, $livewire, ?CompanyProduct $record): int => static::companyOf($livewire, $record)
                        ->addProductCategory($data['name'])->getKey())
                    ->helperText('اختر من تصنيفاتك، أو أضف تصنيفاً جديداً بزر +. يظهر في صفحة الشركة ليصفّي الطبيب المنتجات به.')
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

    /** A category's name, unique within the company: shared by the product form and «تصنيفاتي». */
    public static function categoryNameInput(): TextInput
    {
        return TextInput::make('name')
            ->label('اسم التصنيف')
            ->required()
            ->maxLength(60)
            ->placeholder('مثال: زراعة الأسنان')
            ->unique(
                table: 'product_categories',
                ignoreRecord: true,
                modifyRuleUsing: fn (Unique $rule, $livewire, $record) => $rule->where(
                    'company_id',
                    $record instanceof ProductCategory ? $record->company_id : static::companyOf($livewire)?->getKey(),
                ),
            )
            ->validationMessages(['unique' => 'لديك تصنيف بهذا الاسم.']);
    }

    /** The company whose products are being edited: from the organisers' company page, or the signed-in company. */
    public static function companyOf(mixed $livewire, ?CompanyProduct $record = null): ?Company
    {
        return $record?->company
            ?? ($livewire instanceof RelationManager ? $livewire->getOwnerRecord() : auth()->user()?->company);
    }
}
