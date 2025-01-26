<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UserExport implements FromCollection, WithHeadings, WithTitle
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
        return ['id', 'username', 'telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email','phone','timezone','created_at', 'updated_at', 'total_inventory', 'total_report'];
    }
    public function title(): string
    {
        return "All User";
    }
}
