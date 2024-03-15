<?php
namespace App\Helpers;

use App\Models\HistoryModel;
use App\Helpers\Generator;

class Audit
{
    public static function auditRecord($ctx, $name, $data){ 
        $props = time(); 
        $filePath = "tests_reports/text/$name-$props.txt";

        $file = fopen($filePath, 'w');

        if ($file) {
            $text = "Context   : $ctx\nTitle     : $name\nRecord    : \n\n$data";
           
            fwrite($file, $text);
            fclose($file);

            echo "Audit record of '$name' created successfully\n";
        } else {
            echo "Error creating audit record '$name'\n";
        }
    }

    public static function countTime($start){
        $end = microtime(true);
        $elp = round($end - $start, 4); 

        return "Time taken: {$elp} seconds";
    }

    public static function createHistory($type, $ctx){
        $user_id = Generator::getUserId(session()->get('role_key'));
        
        HistoryModel::create([
            'id' => Generator::getUUID(), 
            'history_type' => $type, 
            'history_context' => $ctx, 
            'created_at' => date("Y-m-d H:i:s"), 
            'created_by' => $user_id,
        ]);
    }
}