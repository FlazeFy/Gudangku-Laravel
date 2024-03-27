<?php

namespace App\Exports;

use App\Models\HistoryModel;
use Maatwebsite\Excel\Concerns\FromCollection;

class HistoryExport implements FromCollection
{
    private $history;

    public function __construct($history)
    {
        $this->history = $history;
    }

    public function collection()
    {
        return $this->history;
    }
}
