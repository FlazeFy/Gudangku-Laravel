<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportItemModel extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'report_item';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'inventory_id', 'report_id', 'item_name', 'item_desc', 'item_qty', 'item_price', 'created_at', 'created_by'];
}
