<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'company_name' => ['Super Market ERP', 'string'],
            'company_email' => ['contact@example.com', 'string'],
            'company_phone' => ['023 000 000', 'string'],
            'company_address' => ['Phnom Penh, Cambodia', 'string'],
            'default_currency' => ['USD', 'string'],
            'default_currency_symbol' => ['$', 'string'],
            'date_format' => ['d M Y', 'string'],
            'time_format' => ['H:i', 'string'],
        ];
        foreach ($defaults as $key => [$value, $type]) {
            Setting::put($key, $value, $type);
        }
    }
}
