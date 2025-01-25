<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ErrorExport implements FromCollection, WithHeadings, WithTitle
{
    private $error;

    public function __construct($error)
    {
        $this->error = $error;
    }
    public function collection()
    {
        return $this->error;
    }
    public function headings(): array
    {
        return ['id', 'message', 'stack_trace', 'file', 'line', 'faced_by','is_fixed','created_at'];
    }
    public function title(): string
    {
        return "Error";
    }
}
