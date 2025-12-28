<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class HistoryExport implements FromCollection, WithHeadings, WithTitle
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
    public function headings(): array
    {
        return ['id', 'history_type', 'history_context', 'created_at', 'created_by'];
    }
    public function title(): string
    {
        return "History";
    }
}
