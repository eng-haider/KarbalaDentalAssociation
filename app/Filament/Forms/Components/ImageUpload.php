<?php

namespace App\Filament\Forms\Components;

use App\Support\HeicConverter;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Image upload that guarantees a web-safe file lands on the disk.
 *
 * An iPhone shoots HEIC by default, and a HEIC on the site renders in Safari
 * and nowhere else — Chrome, Firefox and Android show a broken image. Such an
 * upload is still accepted here, but it is converted to JPEG on its way to
 * storage, so admins can keep uploading straight from their phone.
 */
class ImageUpload extends FileUpload
{
    /** @var array<int, string> */
    public const TYPES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml',
        'image/heic', 'image/heif',
    ];

    public const HINT = 'الصيغ المقبولة: JPG، PNG، WEBP، وصور الآيفون HEIC (تُحوّل تلقائياً إلى JPG).';

    protected function setUp(): void
    {
        parent::setUp();

        // In Filament v4 helper texts stack, so a field can still add its own.
        $this->acceptedFileTypes(static::TYPES)
            ->helperText(static::HINT);

        // The stored name must match the bytes we are about to write.
        $this->getUploadedFileNameForStorageUsing(static function (BaseFileUpload $component, TemporaryUploadedFile $file): string {
            $extension = strtolower($file->getClientOriginalExtension());
            $isHeic = in_array($extension, ['heic', 'heif'], true);

            if ($component->shouldPreserveFilenames()) {
                $name = $file->getClientOriginalName();

                return $isHeic ? HeicConverter::jpegPath($name) : $name;
            }

            return Str::ulid().'.'.($isHeic ? 'jpg' : $extension);
        });

        $this->saveUploadedFileUsing(static function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
            if (HeicConverter::isHeic($file->getClientOriginalName(), $file->getMimeType())) {
                $path = $file->getRealPath();

                // Rewrite the temporary file, then let Filament store it as usual.
                // The name is already a .jpg, so an unconverted file must never
                // reach the disk — better a clear error than a broken image.
                if (! $path || ! HeicConverter::convertInPlace($path)) {
                    throw ValidationException::withMessages([
                        $component->getStatePath() => 'تعذّر تحويل صورة HEIC على الخادم. يرجى حفظ الصورة بصيغة JPG ثم رفعها. (للمسؤول التقني: نفّذ php artisan images:convert-heic --check لمعرفة السبب)',
                    ]);
                }
            }

            return $component->saveUploadedFile($file);
        });
    }
}
