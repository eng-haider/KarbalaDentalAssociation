<?php

namespace Tests\Feature;

use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function makeTransaction(string $status = Transaction::STATUS_COMPLETED): Transaction
    {
        return Transaction::create([
            'name' => 'محمد علي',
            'transaction_type' => 'طلب انتماء',
            'status' => $status,
        ]);
    }

    public function test_edit_page_loads_the_current_status(): void
    {
        $transaction = $this->makeTransaction(Transaction::STATUS_PENDING);

        Livewire::test(EditTransaction::class, ['record' => $transaction->getKey()])
            ->assertSuccessful()
            ->assertFormFieldExists('status')
            ->assertFormSet(['status' => Transaction::STATUS_PENDING]);
    }

    public function test_status_can_be_changed_from_the_edit_page(): void
    {
        $transaction = $this->makeTransaction(Transaction::STATUS_PENDING);

        Livewire::test(EditTransaction::class, ['record' => $transaction->getKey()])
            ->fillForm(['status' => Transaction::STATUS_COMPLETED])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(Transaction::STATUS_COMPLETED, $transaction->refresh()->status);
    }

    public function test_status_can_be_changed_inline_from_the_list(): void
    {
        $transaction = $this->makeTransaction(Transaction::STATUS_COMPLETED);

        Livewire::test(ListTransactions::class)
            ->assertSuccessful()
            ->assertTableColumnExists('status')
            ->assertTableSelectColumnHasOptions('status', Transaction::statuses(), $transaction)
            ->call('updateTableColumnState', 'status', (string) $transaction->getKey(), Transaction::STATUS_PENDING);

        $this->assertSame(Transaction::STATUS_PENDING, $transaction->refresh()->status);
    }

    public function test_an_invalid_status_is_rejected(): void
    {
        $transaction = $this->makeTransaction(Transaction::STATUS_PENDING);

        Livewire::test(EditTransaction::class, ['record' => $transaction->getKey()])
            ->fillForm(['status' => 'archived'])
            ->call('save')
            ->assertHasFormErrors(['status']);

        $this->assertSame(Transaction::STATUS_PENDING, $transaction->refresh()->status);
    }

    public function test_bulk_action_changes_status_of_selected_transactions_only(): void
    {
        $selected = $this->makeTransaction(Transaction::STATUS_PENDING);
        $untouched = $this->makeTransaction(Transaction::STATUS_PENDING);

        Livewire::test(ListTransactions::class)
            ->selectTableRecords([$selected])
            ->callAction(
                TestAction::make('change_status')->table()->bulk(),
                data: ['status' => Transaction::STATUS_COMPLETED],
            )
            ->assertHasNoActionErrors();

        $this->assertSame(Transaction::STATUS_COMPLETED, $selected->refresh()->status);
        $this->assertSame(Transaction::STATUS_PENDING, $untouched->refresh()->status);
    }

    public function test_list_can_be_filtered_by_status(): void
    {
        $pending = $this->makeTransaction(Transaction::STATUS_PENDING);
        $completed = $this->makeTransaction(Transaction::STATUS_COMPLETED);

        Livewire::test(ListTransactions::class)
            ->filterTable('status', Transaction::STATUS_PENDING)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$completed]);
    }
}
