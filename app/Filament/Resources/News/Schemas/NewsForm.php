<?php

namespace App\Filament\Resources\News\Schemas;

use App\Filament\Forms\Components\ImageUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('محتوى الخبر')
                    ->schema([
                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->label('الرابط اللطيف')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('يُستخدم في رابط صفحة الخبر.'),
                        Textarea::make('excerpt')
                            ->label('مقتطف')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->label('النص الكامل')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('الوسيلة الرئيسية (صورة أو فيديو)')
                    ->description('تظهر أعلى صفحة الخبر. إذا أضفت رابط فيديو سيُعرض الفيديو بدلاً من الصورة.')
                    ->schema([
                        ImageUpload::make('image')
                            ->label('صورة الخبر')
                            ->directory('news')
                            ->imageEditor()
                            ->helperText('تُستخدم كصورة مصغّرة، وكغلاف للفيديو إن وُجد.'),
                        TextInput::make('video_url')
                            ->label('رابط الفيديو')
                            ->url()
                            ->placeholder('https://youtu.be/...')
                            ->helperText('رابط يوتيوب أو Vimeo أو رابط مباشر لملف MP4. اتركه فارغاً لعرض الصورة فقط.'),
                    ])
                    ->columns(2),

                Section::make('النشر')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('منشور')
                            ->default(true),
                        DateTimePicker::make('published_at')
                            ->label('تاريخ النشر')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
