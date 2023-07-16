<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    private array $customers = [
        [
            'id' => 1,
            'name' => 'Arisha Barron'
        ],
        [
            'id' => 2,
            'name' => 'Branden Gibson'
        ],
        [
            'id' => 3,
            'name' => 'Rhonda Church'
        ],
        [
            'id' => 4,
            'name' => 'Georgina Hazel'
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::insert($this->customers);
    }
}
