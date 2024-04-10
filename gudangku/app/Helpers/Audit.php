<?php
namespace App\Helpers;

use App\Models\HistoryModel;
use App\Helpers\Generator;

class Audit
{
    public static function auditRecordText($ctx, $name, $data){ 
        $props = time(); 
        $filePath = "tests_reports/text/$name-$props.txt";

        $file = fopen($filePath, 'w');

        if ($file) {
            $text = "Context   : $ctx\nTitle     : $name\nRecord    : \n\n$data";
           
            fwrite($file, $text);
            fclose($file);

            echo "Audit (text) record of '$name' created successfully\n";
        } else {
            echo "Error creating audit (text) record '$name'\n";
        }
    }

    public static function auditRecordSheet($ctx, $name, $request, $response){ 
        $props = time(); 
        $filePath = "tests_reports/csv/template-report.csv";
    
        if (file_exists($filePath)) {
            $file = fopen($filePath, 'a');
        } else {
            $file = fopen($filePath, 'w');
            fputcsv($file, ['Context', 'Title', 'Request', 'Response', 'Created At']);
        }
    
        if ($file) {
            $record = [$ctx, $name, $request, $response, $props];
            fputcsv($file, $record);
            fclose($file);
    
            echo "Audit (CSV) record of '$name' created successfully\n";
        } else {
            echo "Error adding audit (CSV) record '$name'\n";
        }
    }

    public static function countTime($start){
        $end = microtime(true);
        $elp = round($end - $start, 4); 

        return "Time taken: {$elp} seconds";
    }

    public static function createHistory($type, $ctx, $user_id){        
        HistoryModel::create([
            'id' => Generator::getUUID(), 
            'history_type' => $type, 
            'history_context' => $ctx, 
            'created_at' => date("Y-m-d H:i:s"), 
            'created_by' => $user_id,
        ]);
    }
}