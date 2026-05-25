<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

/**
 * Seeds the `customers` table with a "walk-in" customer plus a couple of
 * named customers in different groups so the POS and sales reports have
 * realistic data.
 */
class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $default = CustomerGroup::query()->where('name', 'Default')->value('id');
        $vip = CustomerGroup::query()->where('name', 'VIP')->value('id');
        $wholesale = CustomerGroup::query()->where('name', 'Wholesale')->value('id');

        $customers = [
            [
                'code' => 'CUS-0000',
                'name' => 'Walk-in customer',
                'phone' => null,
                'email' => null,
                'address' => null,
                'customer_group_id' => $default,
                'opening_balance' => 0,
            ],
            [
                'code' => 'CUS-0001',
                'name' => 'Sok Dara',
                'phone' => '012 345 678',
                'email' => 'sok.dara@example.com',
                'address' => 'Phnom Penh',
                'customer_group_id' => $vip,
                'opening_balance' => 0,
            ],
            [
                'code' => 'CUS-0002',
                'name' => 'Chan Bopha',
                'phone' => '012 987 654',
                'email' => 'chan.bopha@example.com',
                'address' => 'Siem Reap',
                'customer_group_id' => $default,
                'opening_balance' => 0,
            ],
            [
                'code' => 'CUS-0003',
                'name' => 'Mini Mart Group',
                'phone' => '023 444 555',
                'email' => 'buying@minimart.example',
                'address' => 'Phnom Penh',
                'customer_group_id' => $wholesale,
                'opening_balance' => 0,
            ],
        ];

        foreach ($customers as $row) {
            Customer::updateOrCreate(
                ['code' => $row['code']],
                $row + ['is_active' => true]
            );
        }
    }
}
