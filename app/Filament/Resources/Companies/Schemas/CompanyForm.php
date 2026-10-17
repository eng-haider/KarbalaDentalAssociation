<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\Company;
use App\Models\User;
use App\Support\WhatsApp;
use Closure;
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
                        'تدخل الشركة برقم الهاتف وكلمة المرور لإضافة منتجاتها وعروضها وتعديل صفحتها فقط. رابط الدخول: <span dir="ltr" style="font-weight:600">'.e(Filament::getPanel('company')->getLoginUrl()).'</span>'
                    ))
                    ->icon('heroicon-o-key')
                    ->schema([
                        TextInput::make('account_phone')
                            ->label('رقم الهاتف للدخول')
                            ->tel()
                            ->required()
                            ->maxLength(32)
                            ->regex('/^[0-9+\s\-]{10,}$/')
                            ->placeholder('07XXXXXXXXX')
                            ->helperText('يُملأ تلقائياً من رقم واتساب، ويمكن تغييره.')
                            ->validationMessages(['regex' => 'يرجى كتابة رقم هاتف صحيح.'])
                            ->rules([
                                fn (?Company $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    $taken = User::query()
                                        ->where('phone', WhatsApp::number($value))
                                        ->when($record?->account, fn ($query, User $account) => $query->whereKeyNot($account->getKey()))
                                        ->exists();

                                    if ($taken) {
                                        $fail('هذا الرقم مستخدم لحساب آخر.');
                                    }
                                },
                            ]),
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
                    ->helperText('تصل طلبات السلة إلى هذا الرقم كرسالة واتساب جاهزة.')
                    // Organisers: the login phone starts as the WhatsApp number.
                    ->live(onBlur: true, condition: ! $forCompany)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($forCompany): void {
                        if (! $forCompany && blank($get('account_phone'))) {
                            $set('account_phone', $state);
                        }
                    }),
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
                    ->disk('osif')
                    ->acceptedFileTypes(ImageUpload::PHOTO_TYPES)
                    ->directory('companies/logos')
                    ->image()
                    ->imageEditor()
                    ->maxSize(4096)
                    ->helperText('يُفضّل شعار مربع بخلفية بيضاء أو شفافة.'),
                ImageUpload::make('cover')
                    ->label('صورة الغلاف (اختياري)')
                    ->disk('osif')
                    ->acceptedFileTypes(ImageUpload::PHOTO_TYPES)
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
