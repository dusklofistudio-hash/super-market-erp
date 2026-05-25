<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

/**
 * Seeds the `translations` table with a handful of high-value strings that
 * editors are most likely to tweak from the admin UI. The bulk of the
 * application copy still lives in lang/en/messages.php and lang/kh/messages.php
 * — this table is meant to be a runtime override layer.
 */
class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['en', 'messages', 'app.name', 'Super Market ERP'],
            ['kh', 'messages', 'app.name', 'ប្រព័ន្ធគ្រប់គ្រងផ្សារទំនើប'],
            ['en', 'messages', 'dashboard.welcome', 'Welcome back'],
            ['kh', 'messages', 'dashboard.welcome', 'សូមស្វាគមន៍ការត្រឡប់មកវិញ'],
            ['en', 'messages', 'common.save', 'Save'],
            ['kh', 'messages', 'common.save', 'រក្សាទុក'],
            ['en', 'messages', 'common.cancel', 'Cancel'],
            ['kh', 'messages', 'common.cancel', 'បោះបង់'],
        ];

        foreach ($rows as [$lang, $group, $key, $value]) {
            Translation::updateOrCreate(
                ['language_code' => $lang, 'group' => $group, 'key' => $key],
                ['value' => $value]
            );
        }
    }
}
