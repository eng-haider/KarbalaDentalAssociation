<?php

namespace App\Filament\Resources\EventRegistrations\Tables;

use App\Filament\Resources\EventRegistrations\Actions\SendInvitationAction;
use App\Models\Event;
use App\Models\EventRegistration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        $inAdminPanel = Filament::getCurrentPanel()?->getId() !== 'osif';

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('event'))
            // A list of cards: one row per person on wide screens, stacked on phones.
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('name')
                            ->label('الاسم')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('professional_title')
                            ->label('الصفة المهنية أو الأكاديمية')
                            ->badge()
                            ->color('primary'),
                        TextColumn::make('invitation_type')
                            ->label('نوع الدعوة')
                            ->state(fn (EventRegistration $record): ?string => $record->is_vip ? 'دعوة VIP' : null)
                            ->badge()
                            ->color('warning')
                            ->icon(Heroicon::Star),
                    ])->space(2),

                    Stack::make([
                        TextColumn::make('phone')
                            ->label('الهاتف')
                            ->icon(Heroicon::OutlinedPhone)
                            ->copyable()
                            ->copyMessage('تم نسخ الرقم')
                            ->searchable(),
                        TextColumn::make('owns_clinic')
                            ->label('العيادة')
                            ->badge()
                            ->formatStateUsing(fn (?bool $state): string => $state ? 'يمتلك عيادة' : 'لا يمتلك عيادة')
                            ->color(fn (?bool $state): string => $state ? 'info' : 'gray')
                            ->icon(fn (?bool $state) => $state ? Heroicon::OutlinedBuildingStorefront : null),
                    ])->space(2),

                    Stack::make([
                        // OSIF attendees are tracked by their invitation; other events by follow-up.
                        TextColumn::make('status')
                            ->label('الحالة')
                            ->state(fn (EventRegistration $record): string => match (true) {
                                $record->event?->isOsif() => $record->invitation_sent_at ? 'sent' : 'pending',
                                default => $record->is_handled ? 'handled' : 'new',
                            })
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => [
                                'sent' => 'تم إرسال الدعوة — تمت المعالجة',
                                'pending' => 'بانتظار الدعوة',
                                'handled' => 'تمت المعالجة',
                                'new' => 'قيد المتابعة',
                            ][$state])
                            ->color(fn (string $state): string => in_array($state, ['sent', 'handled'], true) ? 'success' : 'warning')
                            ->icon(fn (string $state) => in_array($state, ['sent', 'handled'], true) ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedClock)
                            ->tooltip(fn (EventRegistration $record): ?string => $record->invitation_sent_at?->translatedFormat('j F Y، g:i A')),
                        TextColumn::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->since()
                            ->dateTimeTooltip('Y-m-d H:i')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->sortable()
                            ->visibleFrom('md'),
                    ])->space(2),

                    Stack::make([
                        TextColumn::make('event.title')
                            ->label('الفعالية')
                            ->limit(40)
                            ->color('gray'),
                        TextColumn::make('membership_number')
                            ->label('رقم العضوية')
                            ->prefix('عضوية: ')
                            ->searchable(),
                    ])->space(2)->visible($inAdminPanel),
                ])->from('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('ابحث بالاسم أو الرقم')
            ->filters([
                SelectFilter::make('professional_title')
                    ->label('الصفة المهنية أو الأكاديمية')
                    ->options(fn (): array => static::professionalTitleOptions()),
                TernaryFilter::make('owns_clinic')
                    ->label('يمتلك عيادة')
                    ->trueLabel('نعم')
                    ->falseLabel('لا'),
                TernaryFilter::make('is_vip')
                    ->label('نوع الدعوة')
                    ->trueLabel('VIP')
                    ->falseLabel('عادية'),
                SelectFilter::make('event_id')
                    ->label('الفعالية')
                    ->relationship('event', 'title')
                    ->visible($inAdminPanel),
                TernaryFilter::make('is_handled')
                    ->label('تمت المعالجة')
                    ->visible($inAdminPanel),
            ])
            ->recordActions([
                SendInvitationAction::make()->button(),
                EditAction::make()
                    ->label('التفاصيل')
                    ->icon(Heroicon::OutlinedEye)
                    ->iconButton(),
            ])
            // Coordinators cannot delete, so the OSIF panel skips row selection entirely.
            ->toolbarActions($inAdminPanel ? [
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ] : [])
            ->emptyStateIcon(Heroicon::OutlinedUserGroup)
            ->emptyStateHeading('لا توجد تسجيلات بعد')
            ->emptyStateDescription('تظهر هنا طلبات التسجيل فور وصولها من صفحة الملتقى.');
    }

    /** Current choices plus any older values already stored on registrations. */
    private static function professionalTitleOptions(): array
    {
        $titles = collect(Event::query()->where('slug', Event::OSIF_SLUG)->first()?->professionalTitleOptions() ?? [])
            ->merge(EventRegistration::query()->whereNotNull('professional_title')->distinct()->pluck('professional_title'))
            ->filter()
            ->unique()
            ->values();

        return $titles->combine($titles)->all();
    }
}
