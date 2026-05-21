<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the `categories` table with a default catalogue of product
 * categories used across the application.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Grocery', 'គ្រឿងបរិក្ខារ'],
            ['Beverage', 'ភេសជ្ជៈ'],
            ['Bakery', 'នំប៉័ង'],
            ['Dairy', 'ផលិតផលទឹកដោះ'],
            ['Snacks', 'អាហារសម្រន់'],
            ['Personal care', 'ការថែទាំខ្លួន'],
            ['Household', 'គ្រឿងប្រើប្រាស់ផ្ទះ'],
        ];

        foreach ($categories as [$en, $kh]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($en)],
                ['name_en' => $en, 'name_kh' => $kh, 'is_active' => true]
            );
        }
    }
}
