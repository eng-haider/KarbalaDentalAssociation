<?php

namespace Tests\Unit;

use App\Models\News;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NewsVideoTest extends TestCase
{
    public static function videoProvider(): array
    {
        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'iframe', 'https://www.youtube.com/embed/aqz-KE-bpKQ'],
            'youtu.be short' => ['https://youtu.be/aqz-KE-bpKQ', 'iframe', 'https://www.youtube.com/embed/aqz-KE-bpKQ'],
            'youtube embed' => ['https://www.youtube.com/embed/aqz-KE-bpKQ', 'iframe', 'https://www.youtube.com/embed/aqz-KE-bpKQ'],
            'vimeo' => ['https://vimeo.com/76979871', 'iframe', 'https://player.vimeo.com/video/76979871'],
            'mp4 absolute' => ['https://cdn.example.com/clip.mp4', 'file', 'https://cdn.example.com/clip.mp4'],
        ];
    }

    #[DataProvider('videoProvider')]
    public function test_video_embed_normalises_links(string $input, string $type, string $url): void
    {
        $news = new News(['video_url' => $input]);
        $embed = $news->videoEmbed();

        $this->assertSame($type, $embed['type']);
        $this->assertSame($url, $embed['url']);
    }

    public function test_no_video_returns_null(): void
    {
        $this->assertNull((new News(['video_url' => null]))->videoEmbed());
        $this->assertFalse((new News(['video_url' => null]))->hasVideo());
        $this->assertTrue((new News(['video_url' => 'https://youtu.be/aqz-KE-bpKQ']))->hasVideo());
    }
}
