<?php

namespace Tests\Feature;

use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\TransactionStatuses\Pages\CreateTransactionStatus;
use App\Filament\Resources\TransactionStatuses\Pages\ListTransactionStatuses;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_two_original_statuses_survive_the_migration(): void
    {
        $this->assertSame(
            ['pending' => 'قيد الانجاز', 'completed' => 'منجزة'],
            Transaction::statuses(),
        );
    }

    public function test_admin_can_add_a_new_status_from_the_dashboard(): void
    {
        Livewire::test(ListTransactionStatuses::class)->assertSuccessful();

        Livewire::test(CreateTransactionStatus::class)
            ->fillForm([
                'name' => 'قيد المراجعة',
                'color' => 'info',
                'icon' => 'bi-search',
                'sort_order' => 3,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $status = TransactionStatus::where('name', 'قيد المراجعة')->firstOrFail();

        $this->assertSame('info', $status->color);
        $this->assertNotEmpty($status->slug, 'An Arabic-only name must still produce a usable slug.');
        $this->assertContains('قيد المراجعة', Transaction::statuses());
    }

    public function test_an_arabic_name_produces_a_unique_non_empty_slug(): void
    {
        $first = TransactionStatus::create(['name' => 'مؤجلة']);
        $second = TransactionStatus::create(['name' => 'مؤجلة']);

        $this->assertNotEmpty($first->slug);
        $this->assertNotEmpty($second->slug);
        $this->assertNotSame($first->slug, $second->slug);
    }

    public function test_a_status_created_inline_can_be_assigned_to_a_transaction(): void
    {
        // Mirrors the "+" button on the status select in the transaction form.
        $slug = TransactionResource::createStatusUsing([
            'name' => 'مرفوضة',
            'color' => 'danger',
            'icon' => 'bi-x-circle',
        ]);

        $transaction = Transaction::create([
            'name' => 'علي حسن',
            'transaction_type' => 'طلب انتماء',
        ]);

        Livewire::test(EditTransaction::class, ['record' => $transaction->getKey()])
            ->fillForm(['status' => $slug])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($slug, $transaction->refresh()->status);
        $this->assertSame('مرفوضة', $transaction->statusLabel());
    }

    public function test_new_transactions_use_the_default_status(): void
    {
        TransactionStatus::create(['name' => 'جديدة', 'is_default' => true]);

        $transaction = Transaction::create([
            'name' => 'سارة كريم',
            'transaction_type' => 'مع عيادة',
        ]);

        $this->assertSame('جديدة', $transaction->statusLabel());
    }

    public function test_marking_a_status_default_clears_the_previous_default(): void
    {
        $previous = TransactionStatus::where('slug', Transaction::STATUS_COMPLETED)->firstOrFail();
        $this->assertTrue($previous->is_default);

        TransactionStatus::create(['name' => 'جديدة', 'is_default' => true]);

        $this->assertFalse($previous->refresh()->is_default);
    }

    public function test_inactive_statuses_are_not_offered(): void
    {
        TransactionStatus::where('slug', Transaction::STATUS_PENDING)->update(['is_active' => false]);

        $this->assertArrayNotHasKey(Transaction::STATUS_PENDING, Transaction::statuses());
    }

    public function test_search_api_returns_the_label_and_colour_of_a_custom_status(): void
    {
        $slug = TransactionResource::createStatusUsing([
            'name' => 'مرفوضة',
            'color' => 'danger',
            'icon' => 'bi-x-circle',
        ]);

        Transaction::create([
            'name' => 'حيدر التميمي',
            'transaction_type' => 'طلب انتماء',
            'status' => $slug,
        ]);

        $this->getJson(route('transactions.search', ['q' => 'حيدر']))
            ->assertOk()
            ->assertJsonPath('results.0.status', $slug)
            ->assertJsonPath('results.0.status_label', 'مرفوضة')
            ->assertJsonPath('results.0.status_color', 'bg-danger')
            ->assertJsonPath('results.0.status_icon', 'bi-x-circle');
    }

    public function test_search_api_falls_back_when_a_status_record_is_missing(): void
    {
        Transaction::create([
            'name' => 'زينب محمد',
            'transaction_type' => 'مع عيادة',
            'status' => 'legacy-value',
        ]);

        $this->getJson(route('transactions.search', ['q' => 'زينب']))
            ->assertOk()
            ->assertJsonPath('results.0.status_label', 'legacy-value')
            ->assertJsonPath('results.0.status_color', 'bg-secondary');
    }

    public function test_a_status_in_use_cannot_be_deleted(): void
    {
        Transaction::create([
            'name' => 'مثال',
            'transaction_type' => 'مع عيادة',
            'status' => Transaction::STATUS_PENDING,
        ]);

        $inUse = TransactionStatus::where('slug', Transaction::STATUS_PENDING)->firstOrFail();
        $unused = TransactionStatus::create(['name' => 'غير مستخدمة']);

        $this->assertTrue($inUse->transactions()->exists());
        $this->assertFalse($unused->transactions()->exists());
    }
}
