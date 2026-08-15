<?php

namespace Tests\Feature;

use App\Filament\Resources\News\Pages\CreateNews;
use App\Models\News;
use App\Models\User;
use App\Support\HeicConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HeicImageTest extends TestCase
{
    use RefreshDatabase;

    private const FIXTURE = __DIR__.'/../fixtures/sample.heic';

    protected function setUp(): void
    {
        parent::setUp();

        // Keep the conversions off the real storage directory.
        Storage::fake('public');
    }

    public function test_it_recognises_heic_files(): void
    {
        $this->assertTrue(HeicConverter::isHeic('news/photo.heic'));
        $this->assertTrue(HeicConverter::isHeic('news/photo.HEIF'));
        $this->assertTrue(HeicConverter::isHeic('news/photo.bin', 'image/heic'));
        $this->assertFalse(HeicConverter::isHeic('news/photo.jpg'));
    }

    public function test_it_renames_the_path_to_jpg(): void
    {
        $this->assertSame('news/photo.jpg', HeicConverter::jpegPath('news/photo.heic'));
        $this->assertSame('photo.jpg', HeicConverter::jpegPath('photo.HEIC'));
    }

    public function test_the_command_converts_stored_photos_and_repoints_the_record(): void
    {
        $disk = Storage::disk('public');
        $disk->put('news/legacy.heic', file_get_contents(self::FIXTURE));

        // Nothing on this machine can decode HEIC — the command's own job then
        // is to say so, which the --check output covers.
        if (! HeicConverter::convert($disk->path('news/legacy.heic'), $disk->path('news/probe.jpg'))) {
            $this->markTestSkipped('لا يوجد محرّك تحويل HEIC على هذه البيئة.');
        }

        $disk->delete('news/probe.jpg');

        $news = News::create([
            'title' => 'خبر بصورة آيفون',
            'slug' => 'heic-news',
            'excerpt' => 'اختبار',
            'body' => 'اختبار',
            'image' => 'news/legacy.heic',
            'published_at' => now(),
        ]);

        $this->artisan('images:convert-heic')->assertSuccessful();

        $this->assertSame('news/legacy.jpg', $news->fresh()->image);
        $this->assertTrue($disk->exists('news/legacy.jpg'));
        $this->assertSame('image/jpeg', getimagesize($disk->path('news/legacy.jpg'))['mime']);
    }

    public function test_uploading_an_iphone_photo_stores_a_jpg(): void
    {
        $disk = Storage::disk('public');

        if (! HeicConverter::convert(self::FIXTURE, $disk->path('probe.jpg'))) {
            $this->markTestSkipped('لا يوجد محرّك تحويل HEIC على هذه البيئة.');
        }

        $disk->delete('probe.jpg');

        $this->actingAs(User::factory()->create());

        Livewire::test(CreateNews::class)
            ->fillForm([
                'title' => 'خبر بصورة من الآيفون',
                'slug' => 'iphone-news',
                'excerpt' => 'اختبار',
                'body' => 'اختبار',
                'image' => UploadedFile::fake()->createWithContent('photo.heic', file_get_contents(self::FIXTURE)),
                'published_at' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $image = News::firstOrFail()->image;

        $this->assertStringEndsWith('.jpg', $image);
        $this->assertSame('image/jpeg', getimagesize($disk->path($image))['mime']);
    }

    public function test_the_dry_run_leaves_everything_alone(): void
    {
        $disk = Storage::disk('public');
        $disk->put('news/legacy.heic', file_get_contents(self::FIXTURE));

        $news = News::create([
            'title' => 'خبر بصورة آيفون',
            'slug' => 'heic-news-dry',
            'excerpt' => 'اختبار',
            'body' => 'اختبار',
            'image' => 'news/legacy.heic',
            'published_at' => now(),
        ]);

        $this->artisan('images:convert-heic --dry-run')->assertSuccessful();

        $this->assertSame('news/legacy.heic', $news->fresh()->image);
        $this->assertFalse($disk->exists('news/legacy.jpg'));
    }
}
