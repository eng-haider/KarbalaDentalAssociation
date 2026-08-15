<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maestroerror\HeicToJpg;
use Throwable;

/**
 * Converts HEIC/HEIF photos to JPEG.
 *
 * iPhones shoot HEIC by default. Safari renders it, every other browser shows
 * a broken image, so nothing in that format may reach the public site. Servers
 * differ wildly in what they can decode, so each backend below is tried in
 * turn — from the best quality to the last resort — and `backends()` reports
 * which ones this machine actually has.
 */
class HeicConverter
{
    /** JPEG quality used by every backend that lets us pick one. */
    private const QUALITY = 88;

    public static function isHeic(string $path, ?string $mimeType = null): bool
    {
        if (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['heic', 'heif'], true)) {
            return true;
        }

        $mimeType ??= rescue(fn () => mime_content_type($path) ?: null, null, report: false);

        return in_array(strtolower((string) $mimeType), ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'], true);
    }

    /**
     * Write $source out as JPEG at $destination.
     *
     * @return string|null the backend that succeeded, or null if none could
     */
    public static function convert(string $source, string $destination): ?string
    {
        foreach (['imagick', 'cli', 'library', 'preview'] as $backend) {
            try {
                $succeeded = match ($backend) {
                    'imagick' => static::withImagick($source, $destination),
                    'library' => static::withLibrary($source, $destination),
                    'cli' => static::withCli($source, $destination),
                    'preview' => static::withEmbeddedPreview($source, $destination),
                };
            } catch (Throwable $exception) {
                $succeeded = false;
                // A server missing one decoder is normal; the log is what tells
                // an admin why the last resort failed too.
                Log::debug("HEIC conversion via [{$backend}] failed: ".$exception->getMessage());
            }

            if ($succeeded && file_exists($destination) && filesize($destination)) {
                return $backend;
            }
        }

        Log::warning('HEIC conversion failed on every backend.', static::backends());

        return null;
    }

    /**
     * Convert in place: the file keeps its path but holds JPEG bytes.
     * Used for Livewire's temporary upload before it is stored for good.
     */
    public static function convertInPlace(string $path): ?string
    {
        $temporary = tempnam(sys_get_temp_dir(), 'heic') ?: null;

        if (! $temporary) {
            return null;
        }

        try {
            $backend = static::convert($path, $temporary);

            if ($backend && copy($temporary, $path)) {
                return $backend;
            }

            return null;
        } finally {
            @unlink($temporary);
        }
    }

    /** @return array<string, string> backend => what it can do here */
    public static function backends(): array
    {
        return [
            'imagick' => static::imagickReadsHeic() ? 'متاح' : 'غير متاح (Imagick بلا دعم HEIC)',
            'library' => class_exists(HeicToJpg::class)
                ? (static::canRunCommands() ? 'متاح' : 'غير متاح (تشغيل الأوامر معطّل)')
                : 'غير متاح (الحزمة غير مثبّتة)',
            'cli' => ($binary = static::findBinary()) ? "متاح ({$binary})" : 'غير متاح (لا أداة تحويل مثبّتة)',
            'preview' => 'متاح (يستخرج النسخة المصغّرة المدمجة — جودة أقل)',
        ];
    }

    private static function imagickReadsHeic(): bool
    {
        return extension_loaded('imagick')
            && (bool) array_filter(\Imagick::queryFormats(), fn (string $format): bool => str_starts_with($format, 'HEI'));
    }

    private static function withImagick(string $source, string $destination): bool
    {
        if (! static::imagickReadsHeic()) {
            return false;
        }

        $image = new \Imagick($source);
        $image->setImageFormat('jpeg');
        $image->setImageCompressionQuality(self::QUALITY);
        // Orientation is baked in here; browsers ignore the EXIF tag on JPEG.
        $image->autoOrientImage();
        $image->stripImage();
        $written = $image->writeImage($destination);
        $image->clear();

        return $written;
    }

    /** Bundled decoder from maestroerror/php-heic-to-jpg — no system packages needed. */
    private static function withLibrary(string $source, string $destination): bool
    {
        if (! class_exists(HeicToJpg::class) || ! static::canRunCommands()) {
            return false;
        }

        static::makeBundledBinariesExecutable();

        return (bool) HeicToJpg::convert($source)->saveAs($destination);
    }

    /**
     * Composer does not always keep the executable bit when it unpacks a zip,
     * and the bundled decoder is useless without it.
     */
    private static function makeBundledBinariesExecutable(): void
    {
        $directory = dirname((new \ReflectionClass(HeicToJpg::class))->getFileName(), 2).'/bin';

        foreach ((array) glob($directory.'/php-heic-to-jpg-*') as $binary) {
            if (is_file($binary) && ! is_executable($binary)) {
                @chmod($binary, 0755);
            }
        }
    }

    private static function withCli(string $source, string $destination): bool
    {
        $binary = static::findBinary();

        if (! $binary) {
            return false;
        }

        $from = escapeshellarg($source);
        $to = escapeshellarg($destination);

        $command = match ($binary) {
            'sips' => "sips -s format jpeg {$from} --out {$to}",
            'heif-convert' => 'heif-convert -q '.self::QUALITY." {$from} {$to}",
            'heif-dec' => 'heif-dec -q '.self::QUALITY." {$from} {$to}",
            default => "{$binary} {$from} -auto-orient -quality ".self::QUALITY." {$to}",
        };

        exec($command.' 2>&1', $output, $status);

        return $status === 0 && file_exists($destination);
    }

    /**
     * Every iPhone HEIC carries a JPEG preview for non-Apple software. It is
     * smaller than the original, but a visible photo beats a broken image.
     */
    private static function withEmbeddedPreview(string $source, string $destination): bool
    {
        $bytes = file_get_contents($source);

        if ($bytes === false) {
            return false;
        }

        $best = null;
        $offset = 0;

        // A file can hold several previews (and random bytes can look like one),
        // so every candidate is decoded and the largest real image wins.
        while (($start = strpos($bytes, "\xFF\xD8\xFF", $offset)) !== false) {
            $offset = $start + 3;
            $end = strpos($bytes, "\xFF\xD9", $start);

            if ($end === false) {
                break;
            }

            $candidate = substr($bytes, $start, $end - $start + 2);
            $size = @getimagesizefromstring($candidate);

            if ($size && (! $best || ($size[0] * $size[1]) > $best['pixels'])) {
                $best = ['jpeg' => $candidate, 'pixels' => $size[0] * $size[1]];
            }
        }

        return $best !== null
            && file_put_contents($destination, $best['jpeg']) !== false;
    }

    private static function findBinary(): ?string
    {
        if (! static::canRunCommands()) {
            return null;
        }

        foreach (['magick', 'heif-convert', 'heif-dec', 'convert', 'sips'] as $binary) {
            $output = [];
            exec('command -v '.escapeshellarg($binary).' 2>/dev/null', $output, $status);

            if ($status === 0) {
                return $binary;
            }
        }

        return null;
    }

    private static function canRunCommands(): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        $disabled = array_map(trim(...), explode(',', (string) ini_get('disable_functions')));

        return ! in_array('exec', $disabled, true);
    }

    /** `photo.heic` → `photo.jpg`, keeping whatever directory it sits in. */
    public static function jpegPath(string $path): string
    {
        $directory = Str::beforeLast($path, '/');
        $name = pathinfo($path, PATHINFO_FILENAME);

        return ($directory === $path ? '' : $directory.'/').$name.'.jpg';
    }
}
