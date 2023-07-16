<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition()
    {
        $source = Account::inRandomOrder()->first();
        $destination = Account::where('id', '!=', $source->id)->inRandomOrder()->first();
        $amount = $source->balance * fake()->randomFloat(2, 0.01, 1);

        return [
            'source_account_id' => $source->id,
            'destination_account_id' => $destination->id,
            'amount' => $amount,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }
}
