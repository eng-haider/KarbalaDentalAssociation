<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\Company;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                static::details(),

                Section::make('حساب الشركة في لوحة التحكم')
                    ->description(fn (): HtmlString => new HtmlString(
                        'تدخل الشركة بهذا الحساب لإضافة منتجاتها وعروضها وتعديل صفحتها فقط. رابط الدخول: <span dir="ltr" style="font-weight:600">'.e(Filament::getPanel('company')->getLoginUrl()).'</span>'
                    ))
                    ->icon('heroicon-o-key')
                    ->schema([
                        TextInput::make('account_email')
                            ->label('البريد الإلكتروني للدخول')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('users', 'email', ignorable: fn (?Company $record) => $record?->account),
                        TextInput::make('account_password')
                            ->label('كلمة المرور')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255)
                            ->required(fn (?Company $record): bool => ! $record?->account)
                            ->helperText(fn (?Company $record): ?string => $record?->account ? 'اتركها فارغة لإبقاء كلمة المرور الحالية.' : null),
                    ])
                    ->columns(2),
            ]);
    }

    /** The company's public details; the company edits these itself (except its name and visibility). */
    public static function details(bool $forCompany = false): Section
    {
        return Section::make('بيانات الشركة')
            ->schema([
                TextInput::make('name')
                    ->label('اسم الشركة')
                    ->required()
                    ->maxLength(255)
                    ->disabled($forCompany)
                    ->dehydrated(! $forCompany)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?Company $record): void {
                        if (! $record && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('رابط الصفحة')
                    ->prefix('/osif/companies/')
                    ->alphaDash()
                    ->maxLength(120)
                    ->unique(ignoreRecord: true)
                    ->helperText('بالأحرف الإنجليزية، ويُملأ تلقائياً من الاسم.')
                    ->hidden($forCompany),
                TextInput::make('whatsapp_phone')
                    ->label('رقم واتساب لاستلام الطلبات')
                    ->tel()
                    ->required()
                    ->maxLength(32)
                    ->regex('/^[0-9+\s\-]{10,}$/')
                    ->validationMessages(['regex' => 'يرجى كتابة رقم واتساب صحيح.'])
                    ->placeholder('07XXXXXXXXX')
                    ->helperText('تصل طلبات السلة إلى هذا الرقم كرسالة واتساب جاهزة.'),
                TextInput::make('booth')
                    ->label('رقم الجناح في المعرض')
                    ->placeholder('مثال: A12')
                    ->maxLength(100),
                Textarea::make('description')
                    ->label('نبذة عن الشركة')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
                ImageUpload::make('logo')
                    ->label('الشعار')
                    ->disk('public')
                    ->directory('companies/logos')
                    ->image()
                    ->imageEditor()
                    ->maxSize(4096)
                    ->helperText('يُفضّل شعار مربع بخلفية بيضاء أو شفافة.'),
                ImageUpload::make('cover')
                    ->label('صورة الغلاف (اختياري)')
                    ->disk('public')
                    ->directory('companies/covers')
                    ->image()
                    ->imageEditor()
                    ->maxSize(8192)
                    ->helperText('صورة عريضة أعلى صفحة الشركة.'),
                Toggle::make('is_active')
                    ->label('ظاهرة في صفحة الملتقى')
                    ->default(true)
                    ->hidden($forCompany),
            ])
            ->columns(2);
    }
}
