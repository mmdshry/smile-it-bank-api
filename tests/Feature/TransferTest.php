<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Transfer;
use Database\Seeders\AccountSeeder;
use Database\Seeders\CustomerSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CustomerSeeder::class);
        $this->seed(AccountSeeder::class);
    }

    /** @test */
    public function a_transfer_between_two_customers_accounts_can_be_done_successfully()
    {
        $source = Account::all()->random();
        $destination = Account::factory()->create(['customer_id' => Customer::whereNot('id', $source->customer_id)
            ->get()->random()->id]);
        $transfer = Transfer::factory()->make([
            'source_account_id' => $source->id,
            'destination_account_id' => $destination->id,
            'amount' => fake()->randomFloat(2, 0, $source->balance)
        ]);

        $this->postJson(route('transfers.store'), $transfer->toArray())->assertCreated();
        $this->assertSuccessfulTransfer($source, $destination, $transfer);

    }

    /** @test */
    public function a_transfer_between_one_customer_accounts_can_be_done_successfully()
    {
        $source = Account::all()->random();
        $destination = Account::factory()->create(['customer_id' => Customer::whereNot('id', $source->customer_id)->get()->random()->id]);

        $transfer = Transfer::factory()->make([
            'source_account_id' => $source->id,
            'destination_account_id' => $destination->id,
            'amount' => fake()->randomFloat(2, 0, $source->balance)
        ]);

        $this->postJson(route('transfers.store'), $transfer->toArray())->assertCreated();
        $this->assertSuccessfulTransfer($source, $destination, $transfer);

    }

    /** @test */
    public function an_account_transfer_cannot_be_done_due_insufficient_balance()
    {
        $transfer = Transfer::factory()->make();
        $source = Account::find($transfer->source_account_id);
        $transfer['amount'] = $source->balance + 1;
        $this->postJson(route('transfers.store'), $transfer->toArray())->assertUnprocessable();
    }

    /** @test */
    public function an_account_cannot_be_created_for_an_invalid_source_account_id()
    {
        $non_existent_account_id = Account::latest()->first()->id + 10;

        $this->transferAttributeValidation('source_account_id', $non_existent_account_id);

    }

    /** @test */
    public function a_transfer_cannot_be_done_with_missing_source_account_id()
    {
        $this->transferAttributeValidation('source_account_id');
    }

    /** @test */
    public function a_transfer_cannot_be_done_with_missing_destination_account_id()
    {
        $this->transferAttributeValidation('destination_account_id');
    }

    /** @test */
    public function a_transfer_cannot_be_done_with_missing_amount()
    {
        $this->transferAttributeValidation('amount');
    }

    /** @test */
    public function amount_must_be_of_numeric_type()
    {
        $this->transferAttributeValidation('amount', 'string');
    }

    /** @test */
    public function amount_must_be_a_positive_number()
    {
        $this->transferAttributeValidation('amount', -1165);
    }


    /** @test */
    public function a_transfer_cannot_be_done_with_same_source_and_destination_account_ids()
    {

        $source = Account::all()->random();
        $transfer = Transfer::factory()->make([
            'source_account_id' => $source->id,
            'destination_account_id' => $source->id,
        ]);


        $this->postJson(route('transfers.store'), $transfer->toArray())
            ->assertJsonValidationErrors('destination_account_id')
            ->assertUnprocessable();
        $this->assertDatabaseCount('transfers', 0);
    }

    protected function transferAttributeValidation($attribute, $value = null)
    {
        // Base data for the route
        // and add a fault to the attribute and expect failure
        $transfer = Transfer::factory()->make([$attribute => $value]);


        $this->postJson(route('transfers.store'), $transfer->toArray())
            ->assertJsonValidationErrors($attribute)
            ->assertUnprocessable();
        $this->assertDatabaseCount('transfers', 0);

    }

    private function assertSuccessfulTransfer($source, $destination, $transfer)
    {
        // Ensure that the transfer has been submitted
        $this->assertDatabaseCount('transfers', 1);
        $this->assertCount(1, $source->outgoingTransfers);
        $this->assertCount(1, $destination->incomingTransfers);

        // Format the number subtraction to match database format
        $sourceBalance = number_format(
            $source->balance - $transfer->amount, 2, '.', ''
        );
        $destBalance = number_format(
            $destination->balance + $transfer->amount, 2, '.', ''
        );

        // Ensure that the amount has been transferred
        $this->assertEquals(
            $sourceBalance,

            number_format($source->refresh()->balance, 2, '.', '')
        );
        $this->assertEquals(
            $destBalance,
            number_format($destination->refresh()->balance, 2, '.', '')
        );
    }

    /** @test */
    public function outgoing_transfer_history_can_be_retrieved_successfully()
    {
        $sourceAccount = $this->transfer();

        $this->get(route('transfers.index', $sourceAccount))
            ->assertOk()
            ->assertJsonStructure([
                'transfers' => [
                    'incoming_transfers' => [],
                    'outgoing_transfers' => [
                        '*' => [
                            'id',
                            'source_account_id',
                            'destination_account_id',
                            'amount',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function incoming_transfer_history_can_be_retrieved_successfully()
    {
        $destAccount = $this->transfer();

        $this->get(route('transfers.index', $destAccount))
            ->assertOk()
            ->assertJsonStructure([
                'transfers' => [
                    'outgoing_transfers' => [],
                    'incoming_transfers' => [
                        '*' => [
                            'id',
                            'source_account_id',
                            'destination_account_id',
                            'amount',
                        ],
                    ],
                ],
            ]);
    }

    /** @test */
    public function transfer_history_cannot_be_retrieved_for_nonexistent_account()
    {
        $nonExistentAccountId = Account::latest()->first()->id + 10;
        $this->get(route('transfers.index', $nonExistentAccountId))
            ->assertNotFound();
    }

    /** @test */
    public function transfer_history_can_be_retrieved_for_account_with_no_transfers()
    {
        $account = Account::factory()->create();
        $this->get(route('transfers.index', $account->id))
            ->assertOk()
            ->assertJsonStructure([
                'transfers' => [
                    'incoming_transfers' => [],
                    'outgoing_transfers' => [],
                ],
            ]);
    }

    /**
     * @return array
     * @throws Exception
     */
    private function transfer(): array
    {
        $sourceAccount = Account::all()->random();
        $destAccount = Account::whereNot('id', $sourceAccount->id)->get()->random();
        Transfer::create([
            'source_account_id' => $sourceAccount->id,
            'destination_account_id' => $destAccount->id,
            'amount' => random_int(100, 1000),
        ]);
        return [$sourceAccount, $destAccount];
    }
}
