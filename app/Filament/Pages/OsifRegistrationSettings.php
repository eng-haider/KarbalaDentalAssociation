<?php

namespace App\Filament\Pages;

use App\Models\Event;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class OsifRegistrationSettings extends Page
{
    protected string $view = 'filament.pages.osif-registration-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'إعدادات تسجيل OSIF';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::inOsifPanel() ? null : 'الوارد';
    }

    public static function getNavigationLabel(): string
    {
        return static::inOsifPanel() ? 'إعدادات التسجيل' : 'إعدادات تسجيل OSIF';
    }

    public function mount(): void
    {
        $event = $this->event();

        $this->form->fill([
            'registration_open' => $event->registration_open,
            'professional_titles' => $event->professionalTitleOptions(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('حالة التسجيل')
                    ->schema([
                        Toggle::make('registration_open')
                            ->label('التسجيل مفتوح')
                            ->helperText('عند الإغلاق يختفي نموذج التسجيل من صفحة الملتقى.'),
                    ]),

                Section::make('خيارات «الصفة المهنية»')
                    ->description('تظهر في قائمة الصفة المهنية بنموذج التسجيل بهذا الترتيب، ويمكن سحبها لتغيير الترتيب. تعديلها لا يغيّر التسجيلات السابقة.')
                    ->schema([
                        Repeater::make('professional_titles')
                            ->hiddenLabel()
                            ->simple(
                                TextInput::make('title')
                                    ->placeholder('مثال: طبيب أسنان اختصاص')
                                    ->required()
                                    ->maxLength(120)
                                    ->distinct(),
                            )
                            ->minItems(1)
                            ->reorderable()
                            ->addActionLabel('إضافة صفة مهنية'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $this->event()->update([
            'registration_open' => (bool) $state['registration_open'],
            'professional_titles' => array_values(array_map(
                fn ($title) => trim((string) $title),
                $state['professional_titles'] ?? [],
            )),
        ]);

        Notification::make()
            ->title('تم حفظ إعدادات التسجيل')
            ->success()
            ->send();
    }

    private function event(): Event
    {
        return Event::query()->where('slug', Event::OSIF_SLUG)->firstOrFail();
    }

    private static function inOsifPanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'osif';
    }
}
