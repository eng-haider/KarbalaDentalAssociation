<?php

namespace App\Filament\Resources\EventRegistrations\Pages;

use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Resources\EventRegistrations\Schemas\EventRegistrationCreateForm;
use App\Models\Event;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateEventRegistration extends CreateRecord
{
    protected static string $resource = EventRegistrationResource::class;

    protected static ?string $title = 'إضافة تسجيل';

    public function form(Schema $schema): Schema
    {
        return EventRegistrationCreateForm::configure($schema);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['event_id'] ??= Event::query()->where('slug', Event::OSIF_SLUG)->value('id');
        // Invitations go to this number, as with the public OSIF form.
        $data['whatsapp_phone'] = $data['phone'] ?? null;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تمت إضافة التسجيل';
    }
}
