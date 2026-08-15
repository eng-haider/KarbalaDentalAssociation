<?php

namespace App\Console\Commands;

use App\Support\HeicConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fixes HEIC photos that were uploaded before the dashboard converted them.
 * They display on Apple devices only, so every one still referenced by the
 * database is converted to JPEG and the reference is repointed.
 */
class ConvertHeicImages extends Command
{
    protected $signature = 'images:convert-heic
                            {--check : اعرض أدوات التحويل المتاحة على هذا الخادم فقط}
                            {--dry-run : اعرض ما سيتغيّر دون تنفيذ}';

    protected $description = 'تحويل صور HEIC المرفوعة سابقاً إلى JPG وتحديث روابطها';

    /** Every column that stores an uploaded image path. */
    private const COLUMNS = [
        'news' => ['image'],
        'hero_slides' => ['image'],
        'discounts' => ['image', 'logo'],
        'marketplace_listings' => ['image'],
        'job_openings' => ['logo'],
        'courses' => ['image'],
        'course_categories' => ['image'],
        'lessons' => ['image'],
        'board_members' => ['photo'],
    ];

    public function handle(): int
    {
        $this->table(['المحرّك', 'الحالة'], collect(HeicConverter::backends())
            ->map(fn (string $status, string $backend): array => [$backend, $status])
            ->values()
            ->all());

        if ($this->option('check')) {
            return self::SUCCESS;
        }

        $disk = Storage::disk('public');
        $dryRun = $this->option('dry-run');
        $converted = 0;
        $failed = 0;

        foreach (self::COLUMNS as $table => $columns) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $rows = DB::table($table)
                    ->select('id', $column)
                    ->where($column, 'like', '%.hei%')
                    ->get();

                foreach ($rows as $row) {
                    $path = $row->{$column};

                    if (! HeicConverter::isHeic($path)) {
                        continue;
                    }

                    $target = HeicConverter::jpegPath($path);
                    $this->line("{$table}#{$row->id}: {$path} → {$target}");

                    if ($dryRun) {
                        continue;
                    }

                    if (! $disk->exists($path)) {
                        $this->warn('  الملف غير موجود على القرص، تم تخطيه.');
                        $failed++;

                        continue;
                    }

                    $source = $disk->path($path);
                    $backend = HeicConverter::convert($source, $disk->path($target));

                    if (! $backend) {
                        $this->error('  تعذّر التحويل — أعد رفع الصورة بصيغة JPG من لوحة التحكم.');
                        $failed++;

                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update([$column => $target]);
                    $this->info("  تم التحويل عبر: {$backend}");
                    $converted++;
                }
            }
        }

        $this->newLine();
        $this->info("صور محوّلة: {$converted}".($failed ? " — تعذّر تحويل: {$failed}" : ''));

        // Originals stay on disk; delete them once the site looks right.
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
