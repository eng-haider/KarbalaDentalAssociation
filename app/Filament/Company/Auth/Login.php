<?php

namespace App\Filament\Company\Auth;

use App\Support\WhatsApp;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

/** Companies sign in with their phone number and password instead of an email. */
class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {

        return $schema
            ->components([
                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required()
                    ->placeholder('07XXXXXXXXX')
                    ->autocomplete('tel')
                    ->autofocus()
                    ->extraInputAttributes(['dir' => 'ltr']),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'لوحة الشركات المشاركة في ملتقى OSIF — ادخل برقم الهاتف وكلمة المرور.';
    }

    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            // 07…, +964 7…, or with spaces: every way of typing the number finds the account.
            'phone' => WhatsApp::number($data['phone']) ?? '',
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.phone' => 'رقم الهاتف أو كلمة المرور غير صحيحة.',
        ]);
    }
}
