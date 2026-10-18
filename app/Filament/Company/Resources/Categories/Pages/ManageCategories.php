<?php

namespace App\Filament\Company\Resources\Categories\Pages;

use App\Filament\Company\Resources\Categories\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected ?string $subheading = 'تظهر للأطباء في صفحة شركتك بهذا الترتيب ليصفّوا منتجاتك بها. اسحب لإعادة الترتيب.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة تصنيف')
                ->icon(Heroicon::OutlinedPlus)
                // Same as the + in the product form: the company's list, at the end.
                ->using(fn (array $data): Model => auth()->user()->company->addProductCategory($data['name'])),
        ];
    }
}
