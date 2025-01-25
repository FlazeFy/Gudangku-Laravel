<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ActiveInventoryExport implements FromCollection, WithHeadings, WithTitle
{
    private $inventory;

    public function __construct($inventory)
    {
        $this->inventory = $inventory;
    }
    public function collection()
    {
        return $this->inventory;
    }
    public function headings(): array
    {
        return ['inventory_name', 'inventory_category', 'inventory_desc', 'inventory_merk', 'inventory_color', 'inventory_room', 'inventory_storage', 'inventory_rack', 'inventory_price', 'inventory_image', 'inventory_unit', 'inventory_vol', 'inventory_capacity_unit', 'inventory_capacity_vol', 'is_favorite', 'is_reminder', 'created_at', 'updated_at'];
    }
    public function title(): string
    {
        return "Active Inventory";
    }
}
