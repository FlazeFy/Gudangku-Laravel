<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReminderExport implements FromCollection, WithHeadings, WithTitle
{
    private $reminder;

    public function __construct($reminder)
    {
        $this->inventory = $reminder;
    }
    public function collection()
    {
        return $this->reminder;
    }
    public function headings(): array
    {
        return ['id', 'inventory_id', 'reminder_desc', 'reminder_type', 'reminder_context', 'created_at', 'created_by', 'updated_at'];
    }
    public function title(): string
    {
        return "Reminder";
    }
}
