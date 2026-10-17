<?php

namespace App\Filament\Resources\Companies;

use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Companies\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Companies\Tables\CompaniesTable;
use App\Models\Company;
use App\Models\User;
use App\Support\WhatsApp;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** The companies exhibiting at OSIF, managed by organisers from the OSIF and admin panels. */
class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'شركة';

    protected static ?string $pluralModelLabel = 'الشركات المشاركة';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'osif' ? null : 'الوارد';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /** Creates or updates the company's dashboard login from the form's account fields. */
    public static function saveAccount(Company $company, array $data): void
    {
        if (blank($data['account_phone'] ?? null)) {
            return;
        }

        $account = $company->account()->first() ?? new User;
        $account->forceFill([
            'name' => $company->name,
            'phone' => WhatsApp::number($data['account_phone']),
            'role' => User::ROLE_COMPANY,
            'company_id' => $company->getKey(),
        ]);

        if (filled($data['account_password'] ?? null)) {
            $account->password = $data['account_password'];
        }

        $account->save();
        $company->unsetRelation('account');
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
            'create' => CreateCompany::route('/create'),
            'edit' => EditCompany::route('/{record}/edit'),
        ];
    }
}
