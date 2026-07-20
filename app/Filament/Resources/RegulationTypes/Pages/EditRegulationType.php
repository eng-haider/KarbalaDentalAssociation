<?php

namespace App\Filament\Resources\RegulationTypes\Pages;

use App\Filament\Resources\RegulationTypes\RegulationTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegulationType extends EditRecord
{
    protected static string $resource = RegulationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
