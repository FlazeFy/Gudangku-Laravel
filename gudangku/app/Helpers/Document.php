<?php
namespace App\Helpers;
use Illuminate\Support\Collection;

class Document
{
    public static function documentTemplateReport($header_template,$style_template,$footer_template,$report,$report_item){ 
        $tbody = "";
        $datetime = now();
        if($header_template == null){
            $header_template = Generator::generateDocTemplate('header');
        }
        if($style_template == null){
            $style_template = Generator::generateDocTemplate('style');
        }
        if($footer_template == null){
            $footer_template = Generator::generateDocTemplate('footer');
        }

        $extra_template = "";
        $sub_total = 0;
        $total = 0;
        $total_qty = 0;

        foreach($report_item as $dt){
            if($dt->item_desc){
                $item_desc = $dt->item_desc;
            } else {
                $item_desc = "<i style='color:grey;'>- No Description Provided-</i>";
            }
            if($report->report_category == "Shopping Cart" || $report->report_category == "Wishlist"){
                $total = $dt->item_qty * $dt->item_price;
                $sub_total = $sub_total + $total;

                $tbody_template = "
                    <td>Rp. ".number_format($dt->item_price)."</td>
                    <td>Rp. ".number_format($total)."</td>
                    <td></td>
                ";
            } else {
                $tbody_template = "
                    <td></td>
                ";
            }

            $tbody .= "
                <tr>
                    <td>$dt->item_name</td>
                    <td>$item_desc</td>
                    <td style='text-align:center;'>$dt->item_qty</td>
                    $tbody_template
                </tr>
            ";

            $total_qty = $total_qty + $dt->item_qty;
        }

        $report_desc = "Also, in this report come with some notes : $report->report_desc.";
        
        if($report->report_category == "Shopping Cart" || $report->report_category == "Wishlist"){
            $thead_template = "
                <th>Price</th>
                <th>Total</th>
                <th>Checklist</th>
            ";
            $extra_template = "<h5 style='margin-bottom:0;'>Total Item : $total_qty</h5><h5 style='margin:0;'>Sub-Total : Rp. ".number_format($sub_total)."</h5>";
        } else {
            $thead_template = "
                <th>Checklist</th>
            ";
            $extra_template = "<h5 style='margin-bottom:0;'>Total Item : $total_qty</h5>";
        }

        $html = "
            <html>
                <head>
                    $style_template
                </head>
                <body>
                    $header_template
                    <h3 style='margin:0 0 6px 0;'>Report : $report->report_title</h3>
                    <p style='margin:0; font-size:14px;'>ID : $report->id</p>
                    <p style='margin-top:0; font-size:14px;'>Category : $report->report_category</p><br>
                    <p style='font-size:13px; text-align: justify;'>
                        At $datetime, this document has been generated from the report titled <b>$report->report_title</b>. It is intended for the context of <b>$report->report_category</b>. 
                        $report_desc You can also import this document into GudangKu Apps or send it to our Telegram Bot if you wish to analyze the items in this document for comparison with your inventory. Important to know, that
                        this document is <b>accessible for everyone</b> by using this link. Here you can see the item in this report :
                    </p>                    
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Qty</th>
                                $thead_template
                            </tr>
                        </thead>
                        <tbody>$tbody</tbody>
                    </table>
                    $extra_template
                    $footer_template
                </body>
            </html>";

        return $html;
    }

    public static function documentTemplateLayout($header_template,$style_template,$footer_template,$layout,$inventory,$room){ 
        $tbody = "";
        $datetime = now();
        if($header_template == null){
            $header_template = Generator::generateDocTemplate('header');
        }
        if($style_template == null){
            $style_template = Generator::generateDocTemplate('style');
        }
        if($footer_template == null){
            $footer_template = Generator::generateDocTemplate('footer');
        }
        $extra_template = "";
            $layout_template = "<div id='room-container'>";

            $rawLetter = [];
            $rawNum = [];
            foreach ($layout as $dt) {
                if (!empty($dt->layout)) {
                    $coor = explode(':', $dt->layout);
                    foreach ($coor as $cr) {
                        if (preg_match('/^([A-Z]+)(\d+)$/', $cr, $match)) {
                            $letters = $match[1];
                            $numbers = $match[2];
                            $rawLetter[] = $letters;
                            $rawNum[] = $numbers;
                        }
                    }
                }
            }
            $highestLetter = array_reduce($rawLetter, function($max, $current) {
                return $current > $max ? $current : $max;
            }, 'A');
            $highestNumber = !empty($rawNum) ? max($rawNum) : 0;
            $letters = substr('ABCDEFGHIJKLMNOPQRSTUVWXYZ', 0, strpos('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $highestLetter) + 1);
            $rows = $highestNumber + 1;
            $cols = strlen($letters);

            for ($row = 1; $row < count($layout); $row++) {
                $layout_template .= "<div class='row'>";
                for ($col = 0; $col < $cols; $col++) {
                    $label = $letters[$col] . $row;
                    $used = false;
                    $inventory_storage = null;
                    $storage_desc = null;
                    $id = null;

                    foreach ($layout as $dt) {
                        $coor = explode(':', $dt->layout);
                        if (in_array($label, $coor)) {
                            $used = true;
                            $inventory_storage = $dt->inventory_storage;
                            $storage_desc = $dt->storage_desc;
                            $id = $dt->id;
                            break;
                        }
                    }

                    $buttonClass = $used ? 'active' : '';

                    $layout_template .= "<a class='room-floor $buttonClass'><h6 class='coordinate'>$label</h6></a>";
                }
                $layout_template .= "</div>";
            }
            $layout_template .= "</div>";

            foreach ($inventory as $dt) {
                $tbody .= "
                    <tr>
                        <td>$dt->inventory_name</td>
                        <td>".($dt->inventory_desc ?? '-')."</td>
                        <td>".($dt->inventory_desc ?? '-')."</td>
                        <td>$dt->inventory_category</td>
                        <td>$dt->inventory_vol $dt->inventory_unit</td>
                        <td>Rp. ".number_format($dt->inventory_price)."</td>
                    </tr>
                ";   
            }

            $html = "
            <html>
                <head>
                    $style_template
                    <style>
                        .row {
                            display: flex;
                        }
                        .room-floor {
                            border-radius: 0 !important;
                            width: 60px;
                            height: 60px;
                            text-align: center;
                            border: 0.5px solid black !important;
                            position: relative;
                            display:inline-block;
                        }
                        .room-floor .coordinate {
                            font-size: 12px;
                            font-weight: 600;
                            position: absolute;
                            bottom: 5px;
                            right: 5px;
                        }
                        .room-floor.active {
                            background: #3b82f6;
                        }
                    </style>
                </head>
                <body>
                    $header_template
                    <h3 style='margin:0 0 6px 0;'>Room : $room</h3>
                    <p style='font-size:13px; text-align: justify;'>
                        At $datetime, this layout document has been generated. You can also import this document into GudangKu Apps or send it to our Telegram Bot if you wish to analyze the items in this document for comparison with your inventory. Important to know, that
                        this document is <b>accessible for everyone</b> by using this link. Here you can see the item in this report :
                    </p>
                    <h4 style='text-align:center;'>Room Layout</h4>
                    <div style='text-align:center;'>
                        $layout_template
                    </div>    
                    <h4 style='text-align:center;'>Attached Inventory</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Storage</th>
                                <th>Category</th>
                                <th>Volume</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>$tbody</tbody>
                    </table>
                    $extra_template
                    $footer_template
                </body>
            </html>";

        return $html;
    }

    public static function documentTemplateInventory($header_template,$style_template,$footer_template,$inventory,$reminder){ 
        $tbody = "";
        $datetime = now();
        if($header_template == null){
            $header_template = Generator::generateDocTemplate('header');
        }
        if($style_template == null){
            $style_template = Generator::generateDocTemplate('style');
        }
        if($footer_template == null){
            $footer_template = Generator::generateDocTemplate('footer');
        }
        $reminder_template = "";
        foreach ($reminder as $dt) {
            $reminder_template .= "
                <p>- <b>Reminder $dt->reminder_type $dt->reminder_context</b> : $dt->reminder_desc</p>
            ";   
        }

        $html = "<html>
            <head>
                $style_template
                <style>
                    .row {
                        display: flex;
                    }
                </style>
            </head>
            <body>
            $header_template";

        $inventory_identity = null;
        
        if (!is_array($inventory) && !($inventory instanceof Collection)) {
            $arr_inventory[] = $inventory; 
            $inventory_identity = "
                <h3 style='margin:0 0 6px 0;'>Inventory: $inventory->inventory_name</h3>
                <p style='margin:0; font-size:14px;'>ID: $inventory->id</p>
                <p style='margin-top:0; font-size:14px;'>Category: $inventory->inventory_category</p>
                <p style='font-size:13px; text-align: justify;'>
                    At $datetime, this document has been created from the inventory called <b>$inventory->inventory_name</b>. 
                    You can also import this document into GudangKu Apps or send it to our Telegram Bot if you wish to analyze the inventory.
                    Important to know, that this document is <b>accessible for everyone</b> by using this link. Here you can see the item in this report:
                </p>
            ";
        } else {
            $arr_inventory = $inventory;
        }

        foreach ($arr_inventory as $dt) {
            if($dt->is_favorite == 1){
                $is_favorite = "True";
            } else {
                $is_favorite = "False";
            }
    
            $img = "";
            if($dt->inventory_image){
                $img = "
                <tr>
                    <th>Image</th>
                    <td style='text-align:center'><img style='margin:10px; width:500px;' src='$dt->inventory_image'></td>
                </tr>";
            }
    
            $html .= 
                    (
                        $inventory_identity ?? " <h3 style='margin:0 0 6px 0;'>Inventory: $dt->inventory_name</h3>
                        <p style='margin:0; font-size:14px;'>ID: $dt->id</p>
                        <p style='margin-top:0; font-size:14px;'>Category: $dt->inventory_category</p>"
                    )
                ."
                <table>
                    <tbody>
                        <tr>
                            <th>Description</th>
                            <td>" . (!empty($dt->inventory_desc) ? $dt->inventory_desc : '-') . "</td>
                        </tr>
                        <tr>
                            <th>Merk</th>
                            <td>" . (!empty($dt->inventory_merk) ? $dt->inventory_merk : '-') . "</td>
                        </tr>
                        <tr>
                            <th>Room</th>
                            <td>$dt->inventory_room</td>
                        </tr>
                        <tr>
                            <th>Storage</th>
                            <td>" . (!empty($dt->inventory_storage) ? $dt->inventory_storage : '-') . "</td>
                        </tr>
                        <tr>
                            <th>Rack</th>
                            <td>" . (!empty($dt->inventory_rack) ? $dt->inventory_rack : '-') . "</td>
                        </tr>
                        <tr>
                            <th>Price</th>
                            <td>Rp. ".number_format($dt->inventory_price)."</td>
                        </tr>
                        <tr>
                            <th>Unit</th>
                            <td>$dt->inventory_unit</td>
                        </tr>
                        <tr>
                            <th>Volume</th>
                            <td>" . (!empty($dt->inventory_vol) ? $dt->inventory_vol : '-') . "</td>
                        </tr>
                        <tr>
                            <th>Capacity Unit</th>
                            <td>$dt->inventory_capacity</td>
                        </tr>
                        <tr>
                            <th>Is Favorite</th>
                            <td>$is_favorite</td>
                        </tr>
                        $img
                    </tbody>
                </table><br>";
        }
        $html .= "
                <h4>Reminder : </h4>
                $reminder_template
                $footer_template
            </body>
        </html>";

        return $html;
    }
}