<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\ShippingCost;
use Illuminate\Database\Seeder;

class ShippingCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = Area::all();

        foreach ($areas as $area) {
            // Base shipping cost is 20, with some variations based on area and gov
            $baseShippingCost = 20.00;

            // If it's in Cairo, Alexandria, or Giza, shipping is cheaper
            if (in_array($area->gov->name_en, ['Cairo', 'Alexandria', 'Giza'])) {
                $baseShippingCost = 15.00;
            }

            // Some specific areas have different rates
            if (in_array($area->name_en, ['Maadi', 'Dokki', 'Sidi Gaber'])) {
                $baseShippingCost = 12.50;
            }

            // More remote areas cost more
            if (in_array($area->gov->name_en, ['Aswan', 'Luxor'])) {
                $baseShippingCost = 30.00;
            }

            ShippingCost::create([
                'value' => $baseShippingCost,
                'area_id' => $area->id,
            ]);
        }
    }
}
