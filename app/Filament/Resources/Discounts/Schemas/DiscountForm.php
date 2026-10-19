<?php

namespace App\Filament\Resources\Discounts\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use App\Models\Discount;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('الجهة والعرض')
                    ->schema([
                        TextInput::make('brand')
                            ->label('اسم الجهة')
                            ->required()
                            ->placeholder('Smart Clinic · سمارت كلينك'),
                        TextInput::make('tag')
                            ->label('التصنيف')
                            ->placeholder('تطبيق إدارة العيادات'),
                        TextInput::make('title')
                            ->label('عنوان العرض')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->columnSpanFull(),
                        ImageUpload::make('image')
                            ->label('صورة العرض')
                            ->directory('discounts')
                            ->imageEditor()
                            ->helperText('الصورة الكبيرة في البطاقة. يُفضّل مقاس عريض (٤:٣ أو أعرض). بدونها تظهر لوحة ملوّنة بالأيقونة.'),
                        ImageUpload::make('logo')
                            ->label('شعار الجهة')
                            ->directory('discounts/logos')
                            ->imageEditor()
                            ->helperText('يظهر داخل دائرة بيضاء فوق الصورة. بدونه تظهر الأيقونة.'),
                    ])
                    ->columns(2),

                Section::make('صفحة العرض')
                    ->description('لكل عرض صفحة خاصة تُفتح عند الضغط على بطاقته: فيها الصورة والعنوان والوصف، وما تضيفه هنا.')
                    ->schema([
                        RichEditor::make('body')
                            ->label('تفاصيل العرض')
                            ->placeholder('الشروط، طريقة الاستفادة، المدة، أرقام التواصل…')
                            ->columnSpanFull(),
                        FileUpload::make('attachment')
                            ->label('ملف مرفق (PDF أو Word)')
                            ->disk('public')
                            ->directory('discounts/files')
                            ->acceptedFileTypes(array_values(Discount::ATTACHMENT_TYPES))
                            ->maxSize(10240)
                            // The stored name is random; the download keeps the name the file was uploaded with.
                            ->storeFileNamesIn('attachment_name')
                            ->downloadable()
                            ->openable()
                            ->helperText('يظهر في الصفحة زرّاً لفتح الملف أو تحميله. الحد الأقصى 10 ميغابايت.')
                            ->columnSpanFull(),
                    ]),

                Section::make('قيمة الخصم')
                    ->schema([
                        TextInput::make('value_label')
                            ->label('قيمة الخصم')
                            ->required()
                            ->default('خصم خاص')
                            ->helperText('النص الكبير في اللوحة الملوّنة، مثال: ٢٥٪'),
                        TextInput::make('value_caption')
                            ->label('تحت القيمة')
                            ->placeholder('على اشتراك التطبيق'),
                        TextInput::make('link')
                            ->label('رابط التسجيل')
                            ->url()
                            ->placeholder('https://')
                            ->helperText('يظهر زرّاً في صفحة العرض.')
                            ->columnSpanFull(),
                        TextInput::make('icon')
                            ->label('الأيقونة')
                            ->default('bi-tag')
                            ->helperText('اسم أيقونة Bootstrap Icons.'),
                        TextInput::make('note')
                            ->label('ملاحظة صغيرة')
                            ->placeholder('يُشترط أن يكون الطبيب مجدّداً للسنة الحالية.'),
                    ])
                    ->columns(2),

                Section::make('المزايا')
                    ->schema([
                        Repeater::make('perks')
                            ->label('قائمة المزايا')
                            ->hiddenLabel()
                            ->simple(
                                TextInput::make('perk')
                                    ->label('ميزة')
                                    ->required()
                            )
                            ->addActionLabel('إضافة ميزة')
                            // Perks are optional: a blank starter row is required and would block saving.
                            ->defaultItems(0)
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                Section::make('العرض')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('مفعّل')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
