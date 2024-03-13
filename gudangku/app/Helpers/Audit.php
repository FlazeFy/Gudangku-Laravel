<?php
namespace App\Helpers;

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
}