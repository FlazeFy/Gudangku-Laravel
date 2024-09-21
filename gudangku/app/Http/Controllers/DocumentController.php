<?php

namespace App\Http\Controllers;

use App\Helpers\Generator;

use Illuminate\Http\Request;

use App\Models\ReportModel;
use App\Models\UserModel;
use App\Models\ReportItemModel;

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;

use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $report = ReportModel::getReportDetail(null,$id,'doc');

        if($report){
            $report_item = ReportItemModel::getReportItem(null,$id,'doc');
            $options = new DompdfOptions();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $datetime = now();

            $tbody = "";
            $header_template = Generator::generateDocTemplate('header');
            $style_template = Generator::generateDocTemplate('style');
            $footer_template = Generator::generateDocTemplate('footer');
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
                        <td>Rp. $dt->item_price</td>
                        <td>Rp. ".$total."</td>
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
                $extra_template = "<h5 style='margin-bottom:0;'>Total Item : $total_qty</h5><h5 style='margin:0;'>Sub-Total : Rp. $sub_total</h5>";
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
    
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $user_id = Generator::getUserId(session()->get('role_key'));

            if($user_id){
                $user = UserModel::select('telegram_user_id','username')
                    ->where('id',$user_id)
                    ->where('telegram_is_valid',1)
                    ->first();
                if($user->telegram_user_id){
                    $pdfContent = $dompdf->output();
                    $pdfFilePath = public_path("report-$id-$datetime.pdf");
                    file_put_contents($pdfFilePath, $pdfContent);
                    $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);

                    $response = Telegram::sendDocument([
                        'chat_id' => $user->telegram_user_id,
                        'document' => $inputFile,
                        'caption' => "Hello $user->username, you just preview the report document. Here's the document",
                        'parse_mode' => 'HTML'
                    ]);

                    unlink($pdfFilePath);
                }
            }

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "inline; filename='report-$id-$datetime.pdf'");
        } else {
            return redirect("/login");
        }
    }
}
