<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Gov;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cairo areas
        $cairoAreas = [
            ['name_en' => 'Maadi', 'name_ar' => 'المعادي'],
            ['name_en' => 'Nasr City', 'name_ar' => 'مدينة نصر'],
            ['name_en' => 'Heliopolis', 'name_ar' => 'مصر الجديدة'],
            ['name_en' => 'Downtown', 'name_ar' => 'وسط البلد'],
        ];

        // Alexandria areas
        $alexAreas = [
            ['name_en' => 'Montazah', 'name_ar' => 'المنتزه'],
            ['name_en' => 'Sidi Gaber', 'name_ar' => 'سيدي جابر'],
            ['name_en' => 'Agami', 'name_ar' => 'العجمي'],
        ];

        // Giza areas
        $gizaAreas = [
            ['name_en' => 'Dokki', 'name_ar' => 'الدقي'],
            ['name_en' => 'Mohandessin', 'name_ar' => 'المهندسين'],
            ['name_en' => 'Sheikh Zayed', 'name_ar' => 'الشيخ زايد'],
        ];

        $govs = Gov::all();

        foreach ($govs as $gov) {
            $areas = [];
            switch ($gov->name_en) {
                case 'Cairo':
                    $areas = $cairoAreas;
                    break;
                case 'Alexandria':
                    $areas = $alexAreas;
                    break;
                case 'Giza':
                    $areas = $gizaAreas;
                    break;
                default:
                    // For other govs, generate some default areas
                    $areas = [
                        ['name_en' => 'City Center', 'name_ar' => 'وسط المدينة'],
                        ['name_en' => 'East District', 'name_ar' => 'الحي الشرقي'],
                        ['name_en' => 'West District', 'name_ar' => 'الحي الغربي'],
                    ];
            }

            foreach ($areas as $area) {
                Area::create([
                    'name_en' => $area['name_en'],
                    'name_ar' => $area['name_ar'],
                    'gov_id' => $gov->id,
                ]);
            }
        }
    }
}
