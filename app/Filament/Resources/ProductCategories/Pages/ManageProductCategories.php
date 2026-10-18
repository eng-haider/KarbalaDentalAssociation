<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Models\ProductCategory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageProductCategories extends ManageRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected ?string $subheading = 'القائمة التي تختار منها الشركات تصنيف كل منتج، وتظهر للأطباء في صفحة الشركة بهذا الترتيب. اسحب لإعادة الترتيب.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة تصنيف')
                ->icon(Heroicon::OutlinedPlus)
                // New categories go to the end of the list.
                ->mutateDataUsing(fn (array $data): array => [...$data, 'sort_order' => (int) ProductCategory::max('sort_order') + 1]),
        ];
    }
}
