<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(['code' => 'HQ'], [
            'name_en' => 'Headquarters',
            'name_kh' => 'ការិយាល័យកណ្តាល',
            'phone' => '023 000 000',
            'email' => 'hq@example.com',
            'address' => 'Phnom Penh',
            'is_active' => true,
        ]);

        Branch::updateOrCreate(['code' => 'BR01'], [
            'name_en' => 'Branch 01',
            'name_kh' => 'សាខា ០១',
            'phone' => '023 111 111',
            'is_active' => true,
        ]);
    }
}
