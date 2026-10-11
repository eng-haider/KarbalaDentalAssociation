<?php

namespace App\Filament\Resources\EventRegistrations\Pages;

use App\Filament\Resources\EventRegistrations\Actions\SendInvitationAction;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventRegistration extends EditRecord
{
    protected static string $resource = EventRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SendInvitationAction::make(),
            DeleteAction::make(),
        ];
    }
}
