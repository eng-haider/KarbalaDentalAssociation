<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClinicAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public static function typeProvider(): array
    {
        return [
            'plain renewal' => ['تجديد بدون عيادة', 'renewal'],
            'renewal with clinic' => ['تجديد ممارسه مع عيادة', 'renewal'],
            'membership' => ['انتماء ( هوية + جدارية عربي )', 'join'],
            'clinic opening' => ['فتح عيادة ( قرار فتح + جدارية )', 'open'],
            'clinic closure' => ['غلق عيادة', 'close'],
            // A closure filed alongside a renewal belongs to the clinic change.
            'renewal plus closure' => ['تجديد ممارسه مع عيادة + غلق عيادة', 'close'],
            'opening plus renewal' => ['تجديد + فتح عيادة', 'open'],
            'unmatched type' => ['تأييد حسن سيرة وسلوك', 'other'],
        ];
    }

    #[DataProvider('typeProvider')]
    public function test_transaction_type_is_categorised(string $type, string $expected): void
    {
        $this->assertSame($expected, Transaction::categorise($type));
    }

    public function test_analytics_counts_every_transaction_exactly_once(): void
    {
        $types = ['تجديد بدون عيادة', 'تجديد مع عيادة', 'انتماء', 'فتح عيادة', 'غلق عيادة', 'سرتفكيت'];

        foreach ($types as $index => $type) {
            Transaction::create([
                'name' => 'طبيب '.$index,
                'transaction_type' => $type,
                'status' => $index % 2 === 0 ? Transaction::STATUS_COMPLETED : Transaction::STATUS_PENDING,
            ]);
        }

        $analytics = Transaction::analytics();

        $this->assertSame(6, $analytics['total']);
        $this->assertSame(6, array_sum(array_column($analytics['slices'], 'count')));
        $this->assertSame(6, $analytics['doctors']);
        $this->assertSame(3, $analytics['completed']);
        $this->assertSame(3, $analytics['pending']);

        $counts = array_column($analytics['slices'], 'count', 'key');
        $this->assertSame(['renewal' => 2, 'join' => 1, 'open' => 1, 'close' => 1, 'other' => 1], $counts);
    }

    public function test_empty_categories_are_left_off_the_chart(): void
    {
        Transaction::create(['name' => 'طبيب', 'transaction_type' => 'انتماء']);

        $keys = array_column(Transaction::analytics()['slices'], 'key');

        $this->assertSame(['join'], $keys);
    }

    public function test_analytics_section_renders_on_home_and_search_pages(): void
    {
        Transaction::create(['name' => 'طبيب', 'transaction_type' => 'فتح عيادة']);

        foreach ([route('home'), route('transaction-search')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('id="clinic-analytics"', false)
                ->assertSee('التحليل الإحصائي للعيادات', false)
                ->assertSee('فتح عيادة', false);
        }
    }
}
