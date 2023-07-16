<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use Database\Seeders\CustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CustomerSeeder::class);
    }

    /** @test */
    public function account_cannot_be_created_without_customer_id()
    {
        $account = Account::factory()->make();
        unset($account['customer_id']);

        $response = $this->postJson(route('accounts.store'), $account->toArray());

        $response->assertJsonValidationErrors('customer_id')
            ->assertUnprocessable();

        $this->assertDatabaseCount('accounts', 0);
    }

    /** @test */
    public function initial_deposit_must_be_numeric()
    {
        $account = Account::factory()->make(['balance' => 'invalid']);

        $response = $this->postJson(route('accounts.store'), $account->toArray());

        $response->assertJsonValidationErrors('balance')
            ->assertUnprocessable();

        $this->assertDatabaseCount('accounts', 0);
    }

    /** @test */
    public function account_cannot_be_created_with_nonexistent_customer_id()
    {
        $nonExistentCustomerId = Customer::max('id') + 1;
        $account = Account::factory()->make(['customer_id' => $nonExistentCustomerId]);

        $response = $this->postJson(route('accounts.store'), $account->toArray());

        $response->assertJsonValidationErrors('customer_id')
            ->assertUnprocessable();

        $this->assertDatabaseCount('accounts', 0);
    }

    /** @test */
    public function account_can_be_created_successfully_with_random_initial_deposit()
    {
        $account = Account::factory()->make();

        $response = $this->postJson(route('accounts.store'), $account->toArray());

        $response->assertCreated();

        $this->assertDatabaseCount('accounts', 1);
        $this->assertEquals($account->balance, Account::first()->balance);
    }

    /** @test */
    public function account_can_be_created_successfully_without_initial_deposit()
    {
        $account = Account::factory()->make();

        $response = $this->postJson(route('accounts.store'), $account->toArray());

        $response->assertCreated();

        $this->assertDatabaseCount('accounts', 1);
    }

    /** @test */
    public function customer_can_have_multiple_accounts()
    {
        $customer = Customer::all()->random();
        Account::factory()->create(['customer_id' => $customer->id]);

        $response = $this->postJson(route('accounts.store'), [
            'customer_id' => $customer->id,
            'balance' => 100,
        ]);

        $response->assertCreated();

        $this->assertDatabaseCount('accounts', 2);
        $this->assertCount(2, $customer->accounts);
    }

    /** @test */
    public function account_cannot_be_created_for_invalid_customer_id()
    {
        $nonExistentCustomerId = Customer::latest()->first()->id + 10;

        $this->accountAttributeValidation('customer_id', $nonExistentCustomerId);
    }

    /** @test */
    public function account_cannot_be_created_with_missing_customer_id()
    {
        $this->accountAttributeValidation('customer_id');
    }

    /** @test */
    public function initial_deposit_must_be_numeric_type()
    {
        $this->accountAttributeValidation('balance', 'string');
    }

    /** @test */
    public function initial_deposit_must_be_positive_number()
    {
        $this->accountAttributeValidation('balance', -rand(1, 1000));
    }

    /** @test */
    public function initial_deposit_must_be_below_maximum_number()
    {
        $this->accountAttributeValidation('balance', 99999999.99 + 1);
    }

    protected function accountAttributeValidation(string $attribute, string|null $value = null)
    {
        $account = Account::factory()->make([$attribute => $value]);

        $response = $this->postJson(route('accounts.store'), $account->toArray());

        $response->assertJsonValidationErrors($attribute)
            ->assertUnprocessable();

        $this->assertDatabaseCount('accounts', 0);
    }

    /** @test */
    public function account_balance_can_be_retrieved_successfully()
    {
        $account = Account::factory()->create();
        $this->getJson(route('accounts.getBalance', $account))
            ->assertOk()
            ->assertExactJson([
                'balance' => $account->balance,
            ]);
    }

    /** @test */
    public function balance_cannot_be_retrieved_for_nonexistent_account()
    {
        $nonExistentAccountId = Account::count() + 1;
        $this->getJson(route('accounts.getBalance', $nonExistentAccountId))
            ->assertNotFound();
    }

    /** @test */
    public function balance_can_be_retrieved_for_account_with_zero_balance()
    {
        $account = Account::factory()->create(['balance' => 0]);
        $this->getJson(route('accounts.getBalance', $account))
            ->assertOk()
            ->assertExactJson([
                'balance' => 0,
            ]);
    }

    /** @test */
    public function balance_can_be_retrieved_for_account_with_positive_balance()
    {
        $account = Account::factory()->create(['balance' => 100]);
        $this->getJson(route('accounts.getBalance', $account))
            ->assertOk()
            ->assertExactJson([
                'balance' => 100,
            ]);
    }
}
