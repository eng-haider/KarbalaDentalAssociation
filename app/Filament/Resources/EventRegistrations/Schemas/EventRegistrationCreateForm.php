<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use App\Models\Event;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Unique;

/**
 * Registrations added by hand from the dashboard: only the name and the
 * professional title are required (the edit page stays read-only).
 */
class EventRegistrationCreateForm
{
    public static function configure(Schema $schema): Schema
    {
        $osifId = Event::query()->where('slug', Event::OSIF_SLUG)->value('id');
        $inOsifPanel = Filament::getCurrentPanel()?->getId() === 'osif';
        // The OSIF panel has no event picker: its registrations are always for OSIF.
        $eventId = fn (Get $get) => $get('event_id') ?: $osifId;

        return $schema
            ->components([
                Section::make('بيانات المسجّل')
                    ->description('الاسم والصفة مطلوبان، وبقية الحقول اختيارية.')
                    ->schema([
                        Select::make('event_id')
                            ->label('الفعالية')
                            ->options(fn () => Event::query()->orderByDesc('starts_at')->pluck('title', 'id'))
                            ->default($osifId)
                            ->required()
                            ->live()
                            ->hidden($inOsifPanel)
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('الاسم الثلاثي')
                            ->required()
                            ->maxLength(255),
                        Select::make('professional_title')
                            ->label('الصفة المهنية أو الأكاديمية')
                            ->options(fn (Get $get): array => static::titles($eventId($get)))
                            ->searchable()
                            ->required(),
                        TextInput::make('phone')
                            ->label('رقم الموبايل (واتساب)')
                            ->tel()
                            ->maxLength(32)
                            ->regex('/^[0-9+\s\-]{10,}$/')
                            ->unique('event_registrations', 'phone', modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('event_id', $eventId($get)))
                            ->validationMessages([
                                'regex' => 'يرجى كتابة رقم موبايل صحيح.',
                                'unique' => 'هذا الرقم مسجّل مسبقاً في هذه الفعالية.',
                            ])
                            ->helperText('بدون رقم لا يمكن إرسال الدعوة على واتساب، لكن يمكن تنزيلها.'),
                        ToggleButtons::make('owns_clinic')
                            ->label('هل يمتلك عيادة؟')
                            ->boolean('نعم', 'لا')
                            ->inline(),
                        ToggleButtons::make('is_vip')
                            ->label('نوع الدعوة')
                            ->boolean()
                            ->options([0 => 'عادية', 1 => 'VIP'])
                            ->colors([0 => 'gray', 1 => 'warning'])
                            ->icons([0 => Heroicon::OutlinedEnvelope, 1 => Heroicon::OutlinedStar])
                            ->default(false)
                            ->inline()
                            ->helperText('يمكن تغييرها لاحقاً من نافذة «إرسال الدعوة».')
                            // Invitation cards are an OSIF feature.
                            ->visible(fn (Get $get): bool => $eventId($get) == $osifId),
                        TextInput::make('membership_number')
                            ->label('رقم العضوية')
                            ->maxLength(64)
                            ->visible(fn (Get $get): bool => ! $inOsifPanel && filled($get('event_id')) && $get('event_id') != $osifId),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    /** @return array<string, string> */
    private static function titles(mixed $eventId): array
    {
        $event = Event::query()->find($eventId);
        $full = $event?->fullProfessionalTitles() ?? [];

        // Full titles stay selectable here: organisers may add someone beyond the seats.
        return collect($event?->professionalTitleOptions() ?? Event::DEFAULT_PROFESSIONAL_TITLES)
            ->mapWithKeys(fn (string $title) => [$title => in_array($title, $full, true) ? $title.' — اكتمل العدد' : $title])
            ->all();
    }
}
