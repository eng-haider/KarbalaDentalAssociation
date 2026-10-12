<?php

namespace App\Filament\Pages;

use App\Models\Event;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
        $limits = $event->professionalTitleLimits();

        $this->form->fill([
            'registration_open' => $event->registration_open,
            'professional_titles' => array_map(
                fn (string $title) => ['title' => $title, 'limit' => $limits[$title] ?? null],
                $event->professionalTitleOptions(),
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $counts = $this->event()->professionalTitleCounts();

        return $schema
            ->components([
                Section::make('حالة التسجيل')
                    ->schema([
                        Toggle::make('registration_open')
                            ->label('التسجيل مفتوح')
                            ->helperText('عند الإغلاق يختفي نموذج التسجيل من صفحة الملتقى.'),
                    ]),

                Section::make('خيارات «الصفة المهنية أو الأكاديمية» وعدد المقاعد')
                    ->description('تظهر في قائمة «الصفة المهنية أو الأكاديمية» بنموذج التسجيل بهذا الترتيب، ويمكن سحبها لتغيير الترتيب. تعديلها لا يغيّر التسجيلات السابقة. حدّد عدد المقاعد لكل صفة، أو اتركه فارغاً ليبقى التسجيل فيها مفتوحاً؛ وعند اكتمال العدد تظهر الصفة في النموذج بعبارة «اكتمل العدد» ولا يمكن اختيارها.')
                    ->schema([
                        Repeater::make('professional_titles')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('الصفة')->markAsRequired(),
                                TableColumn::make('عدد المقاعد'),
                            ])
                            ->schema([
                                TextInput::make('title')
                                    ->label('الصفة')
                                    ->placeholder('مثال: طبيب أسنان اختصاص')
                                    ->required()
                                    ->maxLength(120)
                                    ->distinct(),
                                TextInput::make('limit')
                                    ->label('عدد المقاعد')
                                    ->integer()
                                    ->minValue(0)
                                    ->placeholder('مفتوح')
                                    ->helperText(fn (Get $get): string => 'مسجّل حالياً: '.($counts[trim((string) $get('title'))] ?? 0)),
                            ])
                            ->minItems(1)
                            ->reorderable()
                            ->addActionLabel('إضافة صفة'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $titles = collect($state['professional_titles'] ?? [])
            ->map(fn (array $row) => ['title' => trim((string) $row['title']), 'limit' => $row['limit'] ?? null])
            ->values();

        $this->event()->update([
            'registration_open' => (bool) $state['registration_open'],
            'professional_titles' => $titles->pluck('title')->all(),
            'professional_title_limits' => $titles
                ->filter(fn (array $row) => filled($row['limit']))
                ->mapWithKeys(fn (array $row) => [$row['title'] => (int) $row['limit']])
                ->all() ?: null,
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
