<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSettings extends Page
{
    protected string $view = 'filament.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'الواجهة';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'إعدادات الموقع';

    protected static ?string $title = 'إعدادات الموقع';

    public ?array $data = [];

    /** The editable settings and their fallbacks (shown until first save). */
    public static function defaults(): array
    {
        return [
            'site_name' => 'نقابة أطباء الأسنان',
            'site_branch' => 'فرع كربلاء المقدسة',
            'site_country' => 'جمهورية العراق',
            'footer_about' => 'المؤسسة المهنية الرسمية التي تنظّم مهنة طب الأسنان في كربلاء المقدسة وتُعنى بشؤون أعضائها وخدمة المجتمع.',
            'phone' => '+964 780 123 4567',
            'email' => 'info@karbala-dental.iq',
            'address' => 'كربلاء المقدسة – حي الحسين',
            'working_hours' => 'الأحد – الخميس: ٩:٠٠ ص – ٣:٠٠ م',
            'map_url' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'telegram_url' => '',
            'youtube_url' => '',
            'whatsapp_url' => '',
        ];
    }

    public function mount(): void
    {
        $values = [];
        foreach (static::defaults() as $key => $default) {
            $values[$key] = Setting::get($key, $default);
        }
        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('التعريف العام')
                    ->schema([
                        TextInput::make('site_name')->label('اسم النقابة')->required(),
                        TextInput::make('site_branch')->label('الفرع'),
                        TextInput::make('site_country')->label('الدولة'),
                        Textarea::make('footer_about')
                            ->label('نبذة في التذييل')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('معلومات التواصل')
                    ->schema([
                        TextInput::make('phone')->label('رقم الهاتف')->tel(),
                        TextInput::make('email')->label('البريد الإلكتروني')->email(),
                        TextInput::make('address')->label('العنوان / الموقع'),
                        TextInput::make('working_hours')->label('أوقات الدوام'),
                        TextInput::make('map_url')
                            ->label('رابط الخريطة')
                            ->url()
                            ->placeholder('https://maps.app.goo.gl/...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('وسائل التواصل الاجتماعي')
                    ->description('اترك الحقل فارغاً لإخفاء الأيقونة.')
                    ->schema([
                        TextInput::make('facebook_url')->label('فيسبوك')->url(),
                        TextInput::make('instagram_url')->label('انستغرام')->url(),
                        TextInput::make('telegram_url')->label('تلغرام')->url(),
                        TextInput::make('youtube_url')->label('يوتيوب')->url(),
                        TextInput::make('whatsapp_url')->label('واتساب')->url(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
