<?php

namespace App\Filament\Company\Resources\Products\Pages;

use App\Filament\Company\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected ?string $subheading = 'ما تضيفه هنا يظهر مباشرة في صفحة شركتك، ويمكن للأطباء إضافته إلى السلة وإرسال الطلب إليك على واتساب.';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('share')
                ->label('مشاركة صفحتي')
                ->icon(Heroicon::OutlinedShare)
                ->color('gray')
                ->alpineClickHandler(fn (): string => 'shareLink('.Js::from(auth()->user()->company->shareData()).')'),
            Action::make('page')
                ->label('عرض صفحتي')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => route('osif.companies.show', auth()->user()->company), shouldOpenInNewTab: true),
            CreateAction::make()
                ->label('إضافة منتج')
                ->icon(Heroicon::OutlinedPlus)
                ->mutateDataUsing(fn (array $data): array => [...$data, 'company_id' => auth()->user()->company_id]),
        ];
    }
}
