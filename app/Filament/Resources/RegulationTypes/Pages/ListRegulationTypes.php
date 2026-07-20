<?php

namespace App\Filament\Resources\RegulationTypes\Pages;

use App\Filament\Resources\RegulationTypes\RegulationTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegulationTypes extends ListRecords
{
    protected static string $resource = RegulationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
