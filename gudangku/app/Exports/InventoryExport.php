<?php

namespace App\Exports;

use App\Models\InventoryModel;
use Maatwebsite\Excel\Concerns\FromCollection;

class InventoryExport implements FromCollection
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
}
