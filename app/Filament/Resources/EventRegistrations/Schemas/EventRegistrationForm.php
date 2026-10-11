<?php

namespace App\Filament\Resources\EventRegistrations\Schemas;

use App\Models\EventRegistration;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        $filled = fn (string $attribute) => fn (?EventRegistration $record): bool => filled($record?->{$attribute});

        return $schema
            ->components([
                Section::make('طلب التسجيل')
                    ->schema([
                        Select::make('event_id')
                            ->label('الفعالية')
                            ->relationship('event', 'title')
                            ->disabled()
                            ->visible(fn (): bool => Filament::getCurrentPanel()?->getId() !== 'osif'),
                        TextInput::make('name')
                            ->label('الاسم الثلاثي')
                            ->disabled(),
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->disabled(),
                        TextInput::make('whatsapp_phone')
                            ->label('رقم الواتساب')
                            ->disabled()
                            ->visible(fn (?EventRegistration $record): bool => filled($record?->whatsapp_phone) && $record->whatsapp_phone !== $record->phone),
                        TextInput::make('professional_title')
                            ->label('الصفة المهنية')
                            ->disabled()
                            ->visible($filled('professional_title')),
                        ToggleButtons::make('owns_clinic')
                            ->label('هل يمتلك عيادة؟')
                            ->boolean('نعم', 'لا')
                            ->inline()
                            ->disabled()
                            ->visible(fn (?EventRegistration $record): bool => $record?->owns_clinic !== null),
                        TextInput::make('membership_number')
                            ->label('رقم العضوية')
                            ->disabled()
                            ->visible($filled('membership_number')),
                        // Collected by an earlier version of the OSIF form; shown only when present.
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->disabled()
                            ->visible($filled('email')),
                        TextInput::make('workplace')
                            ->label('مكان العمل')
                            ->disabled()
                            ->visible($filled('workplace')),
                        TextInput::make('city')
                            ->label('المحافظة')
                            ->disabled()
                            ->visible($filled('city')),
                        Select::make('attendance_type')
                            ->label('نوع الحضور')
                            ->options([
                                'attendee' => 'حضور الملتقى',
                                'workshop' => 'مهتم بالورش',
                                'exhibition' => 'زيارة المعرض',
                            ])
                            ->disabled()
                            ->visible($filled('attendance_type')),
                    ])
                    ->columns(2),

                Section::make('المتابعة')
                    ->schema([
                        Toggle::make('is_handled')
                            ->label('تمت المعالجة'),
                        DateTimePicker::make('invitation_sent_at')
                            ->label('أُرسلت الدعوة في')
                            ->disabled()
                            ->visible($filled('invitation_sent_at')),
                    ])
                    ->columns(2),
            ]);
    }
}
