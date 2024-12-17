<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ReminderExport implements FromCollection
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
}
