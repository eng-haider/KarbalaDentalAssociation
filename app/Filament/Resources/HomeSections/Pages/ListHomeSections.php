<?php

namespace App\Filament\Resources\HomeSections\Pages;

use App\Filament\Resources\HomeSections\HomeSectionResource;
use Filament\Resources\Pages\ListRecords;

class ListHomeSections extends ListRecords
{
    protected static string $resource = HomeSectionResource::class;

    public function getSubheading(): ?string
    {
        return 'اسحب الأقسام لإعادة ترتيبها كما ستظهر في الصفحة الرئيسية، أو أطفئ أي قسم لإخفائه.';
    }
}
