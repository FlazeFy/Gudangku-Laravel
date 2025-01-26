<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportItemExport implements FromCollection, WithHeadings, WithTitle
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
        return ['item_name', 'item_desc', 'item_qty', 'item_price', 'created_at'];
    }
    public function title(): string
    {
        return "Report Item";
    }
}
