<?php

namespace App\Filament\Company\Pages;

use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Models\Company;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** The signed-in company edits its own public details. */
class CompanyProfile extends Page
{


    protected string $view = 'filament.company.pages.company-profile';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'بيانات صفحتي';

    protected static ?string $title = 'بيانات صفحة الشركة';

    protected static ?string $slug = 'profile';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->company()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                CompanyForm::details(forCompany: true),
            ])
            ->model($this->company())
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('page')
                ->label('عرض صفحتي')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => route('osif.companies.show', $this->company()), shouldOpenInNewTab: true),
        ];
    }

    public function save(): void
    {
        $this->company()->update($this->form->getState());

        Notification::make()
            ->title('تم حفظ بيانات الصفحة')
            ->success()
            ->send();
    }

    private function company(): Company
    {
        return auth()->user()->company;
    }
}
