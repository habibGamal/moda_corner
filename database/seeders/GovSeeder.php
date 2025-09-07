<?php

namespace Database\Seeders;

use App\Models\Gov;
use Illuminate\Database\Seeder;

class GovSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $govs = [
            ['name_en' => 'Cairo', 'name_ar' => 'القاهرة'],
            ['name_en' => 'Alexandria', 'name_ar' => 'الإسكندرية'],
            ['name_en' => 'Giza', 'name_ar' => 'الجيزة'],
            ['name_en' => 'Aswan', 'name_ar' => 'أسوان'],
            ['name_en' => 'Luxor', 'name_ar' => 'الأقصر'],
            ['name_en' => 'Sharm El Sheikh', 'name_ar' => 'شرم الشيخ'],
        ];

        foreach ($govs as $gov) {
            Gov::create($gov);
        }
    }
}
