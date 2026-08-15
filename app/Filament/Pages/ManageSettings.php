<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\HeicConverter;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
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
            'app_android_url' => 'https://play.google.com/store/apps/details?id=dev.flutter.dental.dental_app',
            'app_ios_url' => 'https://apps.apple.com/app/id6756843335',
            'app_intro' => 'التطبيق الرسمي المعتمد من نقابة أطباء الأسنان – فرع كربلاء المقدسة، يتيح لك إنجاز معاملاتك ومتابعة حالتها ومواكبة تعاميم النقابة ودوراتها من هاتفك.',
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

                Section::make('تطبيق النقابة')
                    ->description('روابط المتاجر تظهر في صفحة التطبيق وقسم التجديد والانتماء. اترك الرابط فارغاً ليظهر "قريباً".')
                    ->schema([
                        TextInput::make('app_android_url')
                            ->label('رابط Google Play')
                            ->url()
                            ->placeholder('https://play.google.com/store/apps/details?id=...'),
                        TextInput::make('app_ios_url')
                            ->label('رابط App Store')
                            ->url()
                            ->placeholder('https://apps.apple.com/app/id...'),
                        Textarea::make('app_intro')
                            ->label('نبذة عن التطبيق')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('تحويل صور الآيفون (HEIC)')
                    ->description('صور HEIC تُحوَّل إلى JPG داخل المتصفح عند الرفع؛ وهذه محرّكات التحويل الاحتياطية على الخادم نفسه.')
                    ->collapsed()
                    ->schema([
                        Placeholder::make('heic_backends')
                            ->label('حالة المحرّكات')
                            ->content(fn (): HtmlString => new HtmlString(
                                collect(HeicConverter::backends())
                                    ->map(fn (string $status, string $backend): string => e($backend).': '.e($status))
                                    ->implode('<br>')
                            )),
                    ]),

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
