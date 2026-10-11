<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OsifEventSeeder extends Seeder
{
    public function run(): void
    {
        Event::updateOrCreate(
            ['slug' => Event::OSIF_SLUG],
            [
                'title' => 'ملتقى كربلاء لجراحة الفم وزراعة الأسنان',
                'description' => 'النسخة الأولى من ملتقى كربلاء لجراحة الفم وزراعة الأسنان، تجمع محاضرات علمية وورش عمل ومشاركة الشركات والمختبرات الرائدة في المجال.',
                'location' => 'جامعة السبطين للعلوم الطبية — كربلاء المقدسة',
                'starts_at' => Carbon::create(2026, 11, 13, 9, 0),
                'ends_at' => Carbon::create(2026, 11, 13, 18, 0),
                'is_featured' => false,
                'registration_open' => true,
            ],
        );
    }
}
