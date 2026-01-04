<?php

namespace App\Http\Controllers\Api\LendApi;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\File;
use Carbon\Carbon;

// Models
use App\Models\UserModel;
use App\Models\LendModel;
use App\Models\InventoryModel;
use App\Models\LendInventoryRelModel;
// Helpers
use App\Helpers\Audit;
use App\Helpers\QRGenerate;
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Firebase;
use App\Helpers\TelegramMessage;

class Commands extends Controller
{
    private $module;
    private $firebaseMessaging;

    public function __construct()
    {
        $this->module = "inventory lend";
        $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
        $this->firebaseMessaging = $factory->createMessaging();
    }

    /**
     * @OA\POST(
     *     path="/api/v1/lend",
     *     summary="Post Create Lend QR",
     *     description="This request is used to create an inventory lend by using given `qr_period`. This request interacts with the MySQL database, firebase storage, broadcast message using Telegram, has a protected routes, and audited activity (history).",
     *     tags={"Lend"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"qr_period"},
     *             @OA\Property(property="qr_period", type="integer", example="3")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="lend created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lend created, inventory can now seen by others")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Data is already exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="lend is already exist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function postCreateLendQR(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateLend($request,'create_qr');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Get active lend
                $check_lend = LendModel::getLendActive($user_id);
                $is_expired = true;

                if ($check_lend) {
                    $lend_expired_datetime = Carbon::parse($check_lend->created_at)->addHours($check_lend->qr_period);
                    $is_expired = Carbon::now()->greaterThan($lend_expired_datetime);
                }

                // If active QR exists and not expired, block creation
                if ($check_lend && !$is_expired) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'qr code is already exist',
                    ], Response::HTTP_CONFLICT);
                } else {
                    $message = 'lend created, inventory can now seen by others';

                    // Create a new lend
                    $qrPeriodHours = $request->qr_period;
                    $lend = LendModel::createLend(null, $qrPeriodHours, null, 'open', $user_id);
                    $lend_expired_datetime = Carbon::parse($lend->created_at)->addHours($qrPeriodHours);
                    $lend_id = $lend->id;

                    // Generate QR
                    $qr_path = QRGenerate::generateQR("https://gudangku.leonardhors.com/lend/$lend_id");

                    $file = new File($qr_path);
                    $file_ext = pathinfo($qr_path, PATHINFO_EXTENSION);
                    try {
                        // Upload qr image
                        $qr_image = Firebase::uploadFile('lend', $user_id, $user->username, $file, $file_ext);

                        // Get user's contact to broadcast
                        $user = UserModel::getSocial($user_id);
                        if($user && $user->telegram_is_valid == 1 && $user->telegram_user_id){
                            // Check if user Telegram ID is valid
                            if(TelegramMessage::checkTelegramID($owner->telegram_user_id)){
                                // Send telegram message with image
                                $response = Telegram::sendPhoto([
                                    'chat_id' => $user->telegram_user_id,
                                    'photo' => fopen($file, 'rb'),
                                    'caption' => $message,
                                    'parse_mode' => 'HTML'
                                ]);
                            } else {
                                // Reset telegram from user account if not valid
                                UserModel::updateUserById(['telegram_user_id' => null, 'telegram_is_valid' => 0],$user_id);
                                $extra_msg = ' Telegram ID is invalid. Please check your Telegram ID';
                            }
                        }
                        
                        unlink($qr_path);
                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    // Update lend by user ID
                    if ($check_lend && $is_expired) {
                        LendModel::updateLendByUserId(['lend_status' => 'expired'], $user_id, $check_lend->id);
                    }

                    // Update lend by user ID
                    LendModel::updateLendByUserId(['lend_qr_url' => $qr_image], $user_id, $lend_id);

                    // Create history
                    Audit::createHistory('Create', 'QR Generate', $user_id);

                    // Return success
                    return response()->json([
                        'status' => 'success',
                        'message' => $message,
                        'data' => [
                            'qr_code' => $qr_image,
                            'qr_period' => $qrPeriodHours,
                            'lend_expired_datetime' => $lend_expired_datetime
                        ]
                    ], Response::HTTP_CREATED);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/lend/inventory/{lend_id}",
     *     summary="Post Create Request Borrow",
     *     description="This request is used to create an inventory request borrow by using given `borrower_name` and `inventory_list`. This request interacts with the MySQL database, firebase storage, returned document, broadcast message using Telegram, has a protected routes, and audited activity (history).",
     *     tags={"Lend"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"borrower_name","inventory_list"},
     *             @OA\Property(property="borrower_name", type="string", example="Leo"),
     *             @OA\Property(
     *                 property="inventory_list",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="borrow created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="borrow has sended, we also give you the evidence")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="lend is expired",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="is_expired", type="bool", example=true),
     *             @OA\Property(property="message", type="string", example="lend already expired, inform the owner to create a new lend")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function postCreateBorrowInventory(Request $request,$lend_id)
    {
        try{
            // Validate request body
            $validator = Validation::getValidateLend($request,'create_borrow');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'is_expired' => false,
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                // Get lend by ID
                $check_lend = LendModel::find($lend_id);

                $lend_expired_datetime = Carbon::parse($check_lend->created_at)->addHours($check_lend->qr_period);
                $is_expired = Carbon::now()->greaterThan($lend_expired_datetime);

                if($is_expired){
                    // Update lend by ID
                    LendModel::updateLendByUserId(['lend_status' => 'expired'], null, $lend_id);

                    return response()->json([
                        'status' => 'failed',
                        'is_expired' => true,
                        'message' => 'lend already expired, inform the owner to create a new lend'
                    ], Response::HTTP_BAD_REQUEST);
                } else {
                    $success_add = 0;
                    $failed_add = 0;
                    $inventory_id_list = $request->inventory_list;
                    $borrower_name = $request->borrower_name;
                    $tbody = "";

                    foreach($inventory_id_list as $id){
                        // Add borrowed inventory
                        $res = LendInventoryRelModel::createLendInventoryRel($lend_id,$id,$borrower_name);
                        if($res){
                            $inv = InventoryModel::find($id);
                            $tbody .= "
                                <tr>
                                    <td>$inv->inventory_name</td>
                                    <td>$inv->inventory_category</td>
                                    <td>$inv->inventory_room</td>
                                    <td>".$inv->inventory_name ?? "-"."</td>
                                    <td> </td>
                                </tr>
                            ";
                            $success_add++;
                        } else {
                            $failed_add++;
                        }
                    }

                    if($success_add > 0){
                        // Update lend status
                        LendModel::updateLendByUserId(['lend_status' => 'used'], null, $lend_id);

                        // Get user's contact to broadcast
                        $owner = UserModel::getSocial($check_lend->created_by);

                        // Init Doc
                        $options = new DompdfOptions();
                        $options->set('defaultFont', 'Helvetica');
                        $dompdf = new Dompdf($options);
                        $datetime = now();

                        // Document template
                        $header_template = Generator::getDocTemplate('header');
                        $style_template = Generator::getDocTemplate('style');
                        $footer_template = Generator::getDocTemplate('footer');

                        // Build HTML document
                        $html = "
                            <html>
                                <head>
                                    $style_template
                                </head>
                                <body>
                                    $header_template
                                    <h3 style='margin:0 0 6px 0;'>Lend ID : $lend_id</h3>
                                    <p style='margin:0; font-size:14px;'>Owner : $owner->username</p>
                                    <p style='margin-top:0; font-size:14px;'>Borrower : $borrower_name</p><br>
                                    <p style='font-size:13px; text-align: justify;'>
                                        At $datetime, this document has been generated for the new lend that has been requested by $borrower_name and ask about to borrow $success_add item. Here you can see the item in this report:
                                    </p>                    
                                    <table>
                                        <thead>
                                            <tr>
                                                <td>Inventory Name</td>
                                                <td>Category</td>
                                                <td>Room</td>
                                                <td>Storage</td>
                                                <td style='min-width:60px !important;'>Check</td>
                                            </tr>
                                        </thead>
                                        <tbody>$tbody</tbody>
                                    </table>
                                    $footer_template
                                </body>
                            </html>";

                        // Render document
                        $dompdf->loadHtml($html);
                        $dompdf->setPaper('A4', 'portrait');
                        $dompdf->render();

                        // Export local document
                        $pdfContent = $dompdf->output();
                        $pdfFilePath = public_path("lend inventory-$lend_id-$borrower_name.pdf");
                        file_put_contents($pdfFilePath, $pdfContent);

                        if($owner && $owner->telegram_is_valid == 1 && $owner->telegram_user_id){
                            // Check if user Telegram ID is valid
                            if(TelegramMessage::checkTelegramID($owner->telegram_user_id)){
                                // Send telegram message with document
                                $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);
                                $response = Telegram::sendDocument([
                                    'chat_id' => $owner->telegram_user_id,
                                    'document' => $inputFile,
                                    'caption' => "$borrower_name has been requested you to borrow some item from your inventory",
                                    'parse_mode' => 'HTML'
                                ]);
                            } else {
                                // Reset telegram from user account if not valid
                                UserModel::updateUserById(['telegram_user_id' => null, 'telegram_is_valid' => 0],$user_id);
                                $extra_msg = ' Telegram ID is invalid. Please check your Telegram ID';
                            }
                        }

                        $file = new File($pdfFilePath);
                        $file_ext = pathinfo($pdfFilePath, PATHINFO_EXTENSION);
                        try {
                            // Upload file
                            $url_evidence = Firebase::uploadFile('lend', $owner->id, $owner->username, $file, $file_ext);

                            unlink($pdfFilePath);
                        } catch (\Exception $e) {
                            return response()->json([
                                'status' => 'error',
                                'message' => Generator::getMessageTemplate("unknown_error", null),
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }

                        // Return success
                        return response()->json([
                            'status' => 'success',
                            'message' => 'borrow has sended, we also give you the evidence',
                            'data' => $url_evidence
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }                    
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/lend/update_status/{lend_id}",
     *     summary="Put Update Returned Status Of Lend Inventory",
     *     description="This request is used to update returned inventory that has been borrowed by using given `lend_id`, and the updated field is `inventory_list`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
     *     tags={"Lend"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"inventory_list"},
     *             @OA\Property(
     *                 property="inventory_list",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="lend created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lend updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function putConfirmationReturned(Request $request, $lend_id)
    {
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateLend($request,'update_returned');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $success = 0;
                $failed = 0;
                $list_inventory = $request->list_inventory;
                $returned_all = true;

                foreach ($list_inventory as $dt) {
                    $returned_at = $dt['is_returned'] ? date('Y-m-d H:i:s') : null;

                    // Update lend inventory by ID
                    $inventory_rel = LendInventoryRelModel::updateLendInventoryById($dt['id'], $lend_id, ['returned_at' => $returned_at]);
                    if (!$dt['is_returned']) {
                        $returned_all = false;
                    }
                }
                    
                if($returned_all){
                    // Uodate lend by user ID
                    $lend = LendModel::updateLendByUserId(['lend_status' => 'finished'], $user_id, $lend_id);

                    if($lend){
                        // Create history
                        Audit::createHistory('Returned', 'Lend is finished', $user_id);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }

                // Return success
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("update", 'lend'),
                ], Response::HTTP_OK);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
