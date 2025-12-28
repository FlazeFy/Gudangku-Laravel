<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Model
use App\Models\DictionaryModel;

class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        // Delete All 
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DictionaryModel::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $dictionaries = [
            'inventory_category' => ['Food And Beverages','Skin & Body Care','Office Tools','Cleaning Supplies','Health & Medicine','Electronics','Home Appliances','Stationery','Personal Hygiene','Pet Supplies','Baby Care','Automotive Supplies'],
            'inventory_unit' => ['Kilogram','Gram','Pcs','Liter','Ml','Box','Pack','Bottle','Can','Sachet'],
            'inventory_capacity_unit' => ['Percentage'],
            'inventory_room' => ['Main Room','Bathroom','Car Cabin','Terrace','Kitchen','Bedroom','Dining Room','Garage','Storage Room','Office Room','Laundry Room'],
            'reminder_type' => ['Every Day','Every Week','Every Month','Every Year'],
            'report_category' => ['Shopping Cart','Wishlist','Checklist','Wash List','Others','Checkout','Maintenance','Refill','Consumption','Expiration','Damage','Lost'],
        ];
        
        $now = Carbon::now();

        // Note : reminder_context are generate manually after user select reminder_type
        foreach ($dictionaries as $type => $dt) {
            foreach ($dt as $name) {
                // Factory
                DictionaryModel::create([
                    'dictionary_type' => $type,
                    'dictionary_name' => $name,
                ]);
            }
        }
    }
}
