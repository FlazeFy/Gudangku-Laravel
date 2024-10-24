<?php

namespace App\Http\Controllers\Api\InventoryApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\InventoryModel;
use App\Models\InventoryLayoutModel;
use App\Models\UserModel;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Generator;
use App\Helpers\Validation;

use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\Canvas\Factory as CanvasFactory;
use Dompdf\Options as DompdfOptions;
use Dompdf\Adapter\CPDF;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
    /**
     * @OA\DELETE(
     *     path="/api/v1/inventory/delete/{id}",
     *     summary="Soft delete inventory by id",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory deleted"
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
     *         response=404,
     *         description="inventory failed to deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
    public function soft_delete_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            if($rows > 0){
                // History
                Audit::createHistory('Delete', $inventory->inventory_name, $user_id);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory deleted',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/inventory/destroy/{id}",
     *     summary="Edit inventory image by id",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory image updated"
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
     *         response=404,
     *         description="inventory image failed to updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
    public function edit_image_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            if($inventory->image == ""){
                $inventory_image = null;
            } else {
                $inventory_image = $inventory->image;
            }
            $rows = InventoryModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'inventory_image' => $inventory_image,
                    'updated_at' => date('Y-m-d H:i:s'),
            ]);
            

            if($rows > 0){
                // History
                Audit::createHistory('Update Image', $inventory->inventory_name, $user_id);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory image updated',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/inventory/destroy/{id}",
     *     summary="Hard delete inventory by id",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory permentally deleted"
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
     *         response=404,
     *         description="inventory failed to permentally deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
    public function hard_delete_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::destroy($id);

            if($rows > 0){
                // History
                Audit::createHistory('Permentally delete', $inventory->inventory_name, $user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory permentally deleted',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/inventory/fav_toggle/{id}",
     *     summary="Toogle favorite inventory by id",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory updated"
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
     *         response=404,
     *         description="inventory failed to updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
    public function fav_toogle_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::where('id',$id)
                ->where('created_by', $user_id)
                ->update([
                    'is_favorite' => $request->is_favorite
            ]);

            if($rows > 0){
                // History
                $ctx = 'Set';
                if($request->is_favorite == 0){
                    $ctx = 'Unset';
                }
                Audit::createHistory($ctx.' to favorite', $inventory->inventory_name, $user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory updated',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/inventory/recover/{id}",
     *     summary="Recover inventory by id",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory recovered"
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
     *         response=404,
     *         description="inventory failed to recovered",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory not found")
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
    public function recover_inventory_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $inventory = InventoryModel::select('inventory_name')->where('id',$id)->first();

            $rows = InventoryModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'deleted_at' => null,
            ]);

            if($rows > 0){
                // History
                Audit::createHistory('Delete', $inventory->inventory_name, $user_id);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'inventory recovered',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'inventory not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/inventory",
     *     summary="Create inventory",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="inventory created"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Data is already exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory is already exist")
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
    public function post_inventory(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateInventory($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {  
                $is_exist = InventoryModel::selectRaw('1')
                    ->where('inventory_name',$request->inventory_name)
                    ->where('created_by',$user_id)
                    ->first();

                if(!$is_exist){
                    $id = Generator::getUUID();
                    $res = InventoryModel::create([
                        'id' => $id, 
                        'inventory_name' => $request->inventory_name, 
                        'inventory_category' => $request->inventory_category, 
                        'inventory_desc' => $request->inventory_desc, 
                        'inventory_merk' => $request->inventory_merk, 
                        'inventory_color' => $request->inventory_color, 
                        'inventory_room' => $request->inventory_room, 
                        'inventory_storage' => $request->inventory_storage, 
                        'inventory_rack' => $request->inventory_rack, 
                        'inventory_price' => $request->inventory_price, 
                        'inventory_image' => $request->inventory_image, 
                        'inventory_unit' => $request->inventory_unit, 
                        'inventory_vol' => $request->inventory_vol, 
                        'inventory_capacity_unit' => $request->inventory_capacity_unit, 
                        'inventory_capacity_vol' => $request->inventory_capacity_vol, 
                        'is_favorite' => $request->is_favorite, 
                        'is_reminder' => $request->is_reminder, 
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $user_id, 
                        'updated_at' => null, 
                        'deleted_at' => null
                    ]);

                    if($res){
                        // History
                        Audit::createHistory('Create', $request->inventory_name, $user_id);
                        $user = UserModel::getSocial($user_id);

                        $options = new DompdfOptions();
                        $options->set('defaultFont', 'Helvetica');
                        $dompdf = new Dompdf($options);
                        $datetime = now();
                        $header_template = Generator::generateDocTemplate('header');
                        $style_template = Generator::generateDocTemplate('style');
                        $footer_template = Generator::generateDocTemplate('footer');
                        $html = "
                            <html>
                                <head>
                                    $style_template
                                </head>
                                <body>
                                    $header_template
                                    <h3 style='margin:0 0 6px 0;'>Inventory : {$request->inventory_name}</h3>
                                    <p style='margin:0; font-size:14px;'>ID : $id</p>
                                    <p style='margin-top:0; font-size:14px;'>Category : {$request->inventory_category}</p><br>
                                    <p style='font-size:13px; text-align: justify;'>
                                        At $datetime, this document has been generated from the new inventory called <b>{$request->inventory_name}</b>. You can also import this document into GudangKu Apps or send it to our Telegram Bot if you wish to analyze the inventory. Important to know, that
                                        this document is <b>accessible for everyone</b> by using this link. Here you can see the item in this report:
                                    </p>                    
                                    <table>
                                        <tbody>
                                            <tr>
                                                <th>Description</th>
                                                <td>" . ($request->inventory_desc ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Merk</th>
                                                <td>" . ($request->inventory_merk ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Color</th>
                                                <td>" . ($request->inventory_color ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Room</th>
                                                <td>{$request->inventory_room}</td>
                                            </tr>
                                            <tr>
                                                <th>Storage</th>
                                                <td>" . ($request->inventory_storage ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Rack</th>
                                                <td>" . ($request->inventory_rack ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Price</th>
                                                <td>Rp. " . number_format($request->inventory_price, 2, ',', '.') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Unit</th>
                                                <td>{$request->inventory_unit}</td>
                                            </tr>
                                            <tr>
                                                <th>Volume</th>
                                                <td>{$request->inventory_vol}</td>
                                            </tr>
                                            <tr>
                                                <th>Capacity Unit</th>
                                                <td>" . ($request->inventory_capacity_unit ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Capacity Volume</th>
                                                <td>" . ($request->inventory_capacity_vol ?? '-') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Is Favorite</th>
                                                <td>" . ($request->is_favorite == 1 ? 'Yes' : 'No') . "</td>
                                            </tr>
                                            <tr>
                                                <th>Is Reminder</th>
                                                <td>" . ($request->is_reminder == 1 ? 'Yes' : 'No') . "</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    $footer_template
                                </body>
                            </html>";


                        $dompdf->loadHtml($html);
                        $dompdf->setPaper('A4', 'portrait');
                        $dompdf->render();

                        $message = "inventory created, its called '$request->inventory_name'";

                        if($user && $user->telegram_is_valid == 1 && $user->telegram_user_id){
                            $pdfContent = $dompdf->output();
                            $pdfFilePath = public_path("inventory-$id-$request->inventory_name.pdf");
                            file_put_contents($pdfFilePath, $pdfContent);
                            $inputFile = InputFile::create($pdfFilePath, $pdfFilePath);
                            
                            $response = Telegram::sendDocument([
                                'chat_id' => $user->telegram_user_id,
                                'document' => $inputFile,
                                'caption' => $message,
                                'parse_mode' => 'HTML'
                            ]);
                            unlink($pdfFilePath);
                        }
                        if($user->firebase_fcm_token){
                            $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                            $messaging = $factory->createMessaging();
                            $fcm = CloudMessage::withTarget('token', $user->firebase_fcm_token)
                                ->withNotification(Notification::create($message))
                                ->withData([
                                    'inventory_id' => $id,
                                ]);
                            $response = $messaging->send($fcm);
                        }
                        
                        return response()->json([
                            'status' => 'success',
                            'message' => $message,
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'something wrong. please contact admin',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'inventory is already exist',
                    ], Response::HTTP_CONFLICT);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/v1/inventory/edit_layout/{id}",
     *     summary="Update inventory layout by id",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory Layout ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="inventory layout updated"
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
     *         response=404,
     *         description="inventory failed to updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="inventory layout not found")
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="something wrong. please contact admin")
     *         )
     *     ),
     * )
     */
    public function edit_layout_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateInventory($request,'update_layout');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {  
                $old_data = InventoryLayoutModel::find($id);

                if($old_data){
                    $rows = InventoryLayoutModel::where('id',$id)
                        ->where('created_by', $user_id)
                        ->update([
                            'inventory_storage' => $request->inventory_storage,
                            'storage_desc' => $request->storage_desc
                    ]);

                    if($rows > 0){
                        $rows_inventory = InventoryModel::where('inventory_storage',$old_data->inventory_storage)
                            ->where('created_by', $user_id)
                            ->update([
                                'inventory_storage' => $request->inventory_storage,
                        ]);
                        
                        Audit::createHistory('Update Layout', $request->inventory_storage, $user_id);
                        return response()->json([
                            'status' => 'success',
                            'message' => "inventory layout updated and impacted to $rows_inventory inventory",
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'nothing has change',
                        ], Response::HTTP_OK);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'inventory layout not found',
                    ], Response::HTTP_NOT_FOUND);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/v1/inventory/layout",
     *     summary="Post inventory layout",
     *     tags={"Inventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="inventory layout coordinate created"
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
     *         response=422,
     *         description="{validation_msg}",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="{field validation message}")
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
    public function post_inventory_layout(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateInventory($request,'create_layout');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {  
                $rows = InventoryLayoutModel::where('inventory_room',$request->inventory_room)
                    ->where('created_by', $user_id)
                    ->where('layout', 'like', '%' . $request->layout . '%')
                    ->first();

                if($rows){
                    $rows_layout = InventoryLayoutModel::where('id',$rows->id)
                        ->update([
                            'layout' => $rows->layout.':'.$request->layout,
                    ]);
                    
                    Audit::createHistory('Create Layout', $request->inventory_storage, $user_id);
                    return response()->json([
                        'status' => 'success',
                        'message' => "inventory layout coordinate created",
                    ], Response::HTTP_OK);
                } else {
                    $check_layout = InventoryLayoutModel::where('inventory_storage',$request->inventory_storage)
                        ->where('inventory_room',$request->inventory_room)
                        ->where('created_by', $user_id)
                        ->first();

                    if($check_layout){
                        $rows_layout = InventoryLayoutModel::where('id',$check_layout->id)
                            ->update([
                                'layout' => $check_layout->layout.':'.$request->layout
                            ]);
                    } else {
                        $rows_layout = InventoryLayoutModel::create([
                            'id' => Generator::getUUID(),
                            'inventory_room' => $request->inventory_room,
                            'inventory_storage' => $request->inventory_storage,
                            'storage_desc' => $request->storage_desc,
                            'layout' => $request->layout,
                            'created_at' => date('Y-m-d H:i'),
                            'created_by' => $user_id
                        ]);
                    }
                    
                    Audit::createHistory('Create Layout', $request->inventory_storage, $user_id);
                    return response()->json([
                        'status' => 'success',
                        'message' => "inventory layout coordinate created",
                    ], Response::HTTP_OK);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
