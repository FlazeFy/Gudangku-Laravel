<?php

namespace App\Exports;

use App\Models\ErrorModel;
use Maatwebsite\Excel\Concerns\FromCollection;

class ErrorExport implements FromCollection
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
}
