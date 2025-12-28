<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UserNotActiveExport implements FromCollection, WithHeadings, WithTitle
{
    private $error;
    private $title;

    public function __construct($error, $title)
    {
        $this->error = $error;
        $this->title = $title;
    }
    public function collection()
    {
        return $this->error;
    }
    public function headings(): array
    {
        return ['id', 'username', 'telegram_user_id','telegram_is_valid','firebase_fcm_token','line_user_id','email','phone','timezone','created_at', 'updated_at'];
    }
    public function title(): string
    {
        return $this->title;
    }
}
