<?php

namespace App\Http\Controllers\Api\ReportApi;

use App\Http\Controllers\Controller;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;

// Models
use App\Models\ReportItemModel;
use App\Models\ReportModel;
use App\Models\UserModel;
use App\Models\InventoryModel;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Validation;
use App\Helpers\Firebase;
use App\Helpers\Generator;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
    private $max_size_file;
    private $allowed_file_type;
    private $max_size_analyze_file;
    private $allowed_analyze_file_type;

    public function __construct()
    {
        $this->max_size_file = 7500000; // 7.5 Mb
        $this->allowed_file_type = ['jpg','jpeg','gif','png','pdf'];
        $this->max_size_analyze_file = 15000000; // 15.0 Mb
        $this->allowed_analyze_file_type = ['pdf'];
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/report/delete/item/{id}",
     *     summary="Hard delete report item by id",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report Item ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="report item deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report item deleted")
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
     *         response=404,
     *         description="report item failed to deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="report item not found")
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
    public function hard_delete_report_item_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $rows = ReportItemModel::where('id', $id)
                ->where('created_by', $user_id);

            $list_id = explode(",", $id);
            $rows = ReportItemModel::whereIn('id', $list_id)
                ->where('created_by', $user_id)
                ->delete();

            if($rows > 0){
                $extra = "";
                if(count($list_id) > 1){
                    $extra = count($list_id);
                }
                return response()->json([
                    'status' => 'success',
                    'message' => $extra." report item deleted",
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'report item not found',
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
     *     path="/api/v1/report/delete/report/{id}",
     *     summary="Hard delete report by id",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="report deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report deleted")
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
     *         response=404,
     *         description="report failed to deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="report not found")
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
    public function hard_delete_report_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $report = ReportModel::select('report_title')->where('id', $id)->first();

            $rows = ReportModel::where('id', $id)
                ->where('created_by', $user_id)
                ->delete();

            if($rows > 0){
                // History
                Audit::createHistory('Delete Report', $report->report_title, $user_id);
                
                ReportItemModel::where('report_id', $id)
                    ->where('created_by', $user_id)
                    ->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'report deleted',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'report not found',
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
     *     path="/api/v1/report/update/report/{id}",
     *     summary="Update report detail by id",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="report updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report updated")
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
     *         response=404,
     *         description="report failed to updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="report not found")
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
    public function update_report_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $rows = ReportModel::where('id', $id)
                ->where('created_by', $user_id)
                ->update([
                    'report_title' => $request->report_title,
                    'report_desc' => $request->report_desc,
                    'report_category' => $request->report_category
                ]);

            if($rows > 0){
                // History
                Audit::createHistory('Update Report', $request->report_title, $user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'report updated',
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'report not found',
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
     *     path="/api/v1/report/update/report_item/{id}",
     *     summary="Update report item by id",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report Item ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="report item updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report item updated")
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
     *         response=404,
     *         description="report item failed to updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="report item not found")
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
    public function update_report_item_by_id(Request $request, $id)
    {
        try{
            $validator = Validation::getValidateReportItem($request,'update');

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'validation failed : '.$validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;

                $rows = ReportItemModel::where('id', $id)
                    ->where('created_by', $user_id)
                    ->update([
                        'item_name' => $request->item_name,
                        'item_desc' => $request->item_desc,
                        'item_qty' => $request->item_qty,
                        'item_price' => $request->item_price
                    ]);

                if($rows > 0){
                    // History
                    Audit::createHistory('Update Report Item', $request->item_name, $user_id);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'report item updated',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'report item not found',
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
     * @OA\PUT(
     *     path="/api/v1/report/update/report_split/{id}",
     *     summary="Update report item by splitting it into a new report",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report ID to split from",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="report_title", type="string", example="New Balance"),
     *             @OA\Property(property="report_desc", type="string", example="Sepatu track"),
     *             @OA\Property(property="report_category", type="string", example="Home Supplies"),
     *             @OA\Property(property="is_reminder", type="boolean", example=true),
     *             @OA\Property(property="remind_at", type="string", format="date-time", example="2024-12-01T12:00:00Z"),
     *             @OA\Property(property="list_id", type="string", example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9,e1288783-a5d4-1c4c-2cd6-0e92f7cc4a0"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated and split report items",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="all report items updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="validation failed : {validation errors}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authorization required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report or Report Item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="report item not found")
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
    public function update_split_report_item_by_id(Request $request, $id){
        try{
            $validator = Validation::getValidateReport($request,'create');

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'validation failed : '.$validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;
                $list_item_id = explode(',',$request->list_id);

                $old_check_report = ReportModel::find($id);

                if($old_check_report){
                    if (Validation::getValidateUUID($request->list_id)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Validation failed: list item ID is not a valid UUID'
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    $report = ReportModel::create([
                        'id' => Generator::getUUID(), 
                        'report_title' => $request->report_title, 
                        'report_desc' => $request->report_desc, 
                        'report_category' => $request->report_category,  
                        'is_reminder' => $request->is_reminder,  
                        'remind_at' => $request->remind_at,  
                        'created_at' => date('Y-m-d H:i:s'), 
                        'created_by' => $user_id, 
                        'updated_at' => null, 
                        'deleted_at' => null
                    ]);

                    if($report){
                        $success_migrate = 0;
                        $failed_migrate = 0;
                        $list_item_name = "";

                        foreach ($list_item_id as $dt) {
                            $old_report_item = ReportItemModel::where('id', $dt)
                                ->where('created_by', $user_id)
                                ->where('report_id', $id)
                                ->first();

                            if ($old_report_item) {
                                $list_item_name .= "$old_report_item->item_name,";
                                $updated = ReportItemModel::where('id', $dt)
                                    ->update([
                                        'report_id' => $report->id,
                                        'created_at' => date('Y-m-d H:i:s'),
                                    ]);
                                if ($updated > 0) {
                                    $success_migrate++;
                                } else {
                                    $failed_migrate++;
                                }
                            } else {
                                $failed_migrate++;
                            }
                        }
        
                        if($success_migrate > 0){
                            // History
                            Audit::createHistory('Split Report', "From $old_check_report->report_title has been removed item of $list_item_name to $report->report_title", $user_id);
                            Audit::createHistory('Create Report', $request->report_title, $user_id);
        
                            if ($success_migrate > 0) {
                                $status_message = $failed_migrate == 0 ? 'all report items updated' : 'some report items updated';
                            }
                            
                            return response()->json([
                                'status' => 'success',
                                'message' => $status_message,
                            ], Response::HTTP_OK);
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => 'report item not found',
                            ], Response::HTTP_NOT_FOUND);
                        }
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'something wrong. please contact admin',
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'report not found',
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

    public function post_report(Request $request){
        try{
            // Validator
            $validator = Validation::getValidateReport($request,'create');

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'validation failed : '.$validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;
                $id_report = Generator::getUUID();
                $validation_image_failed = "";

                // Report image handling
                $report_image = null;  
                if($request->report_image){
                    $report_image = $request->report_image;  
                } 
                if ($request->hasFile('file')) {
                    $files = is_array($request->file('file')) ? $request->file('file') : [$request->file('file')];
                    $user = UserModel::find($user_id);
                
                    $report_image = []; 
                    foreach ($files as $idx => $file) {
                        if ($file->isValid()) {
                            $file_ext = $file->getClientOriginalExtension();
                            // Validate file type
                            if (!in_array($file_ext, $this->allowed_file_type)) {
                                $validation_image_failed .= 'The '.$idx.'-th file must be a ' . implode(', ', $this->allowed_file_type) . ' file type, ';
                                continue;
                            }
                            // Validate file size
                            if ($file->getSize() > $this->max_size_file) {
                                $validation_image_failed .= 'The '.$idx.'-th file size must be under ' . ($this->max_size_file / 1000000) . ' Mb, ';
                                continue; 
                            }
                
                            // Helper: Upload report image
                            try {
                                $fileUrl = Firebase::uploadFile('report', $user_id, $user->username, $file, $file_ext);
                                $report_image[] = ['url' => $fileUrl]; 
                            } catch (\Exception $e) {
                                $validation_image_failed .= 'Failed to upload the '.$idx.'-th file';
                            }
                        }
                    }
                }

                // Model : Create Report
                $report = ReportModel::create([
                    'id' => $id_report, 
                    'report_title' => $request->report_title,  
                    'report_desc' => $request->report_desc,  
                    'report_category' => $request->report_category, 
                    'report_image' => $report_image ? json_encode($report_image,true) : null,
                    'is_reminder' => 0, 
                    'remind_at' => null, 
                    'created_at' => date('Y-m-d H:i:s'), 
                    'created_by' => $user_id, 
                    'updated_at' => null, 
                    'deleted_at' => null
                ]);

                if($report){
                    $success_exec = 0;
                    $failed_exec = 0;

                    if($request->report_item){
                        $report_item = json_decode($request->report_item);
                        $item_count = count($report_item);

                        // Model : Create Report Item
                        foreach ($report_item as $idx => $dt) {
                            $res = ReportItemModel::create([
                                'id' => Generator::getUUID(), 
                                'inventory_id' => $dt->inventory_id ?? null, 
                                'report_id' => $id_report, 
                                'item_name' => $dt->item_name, 
                                'item_desc' => $dt->item_desc,  
                                'item_qty' => $dt->item_qty, 
                                'item_price' => $dt->item_price ?? null, 
                                'created_at' => date('Y-m-d H:i:s'), 
                                'created_by' => $user_id, 
                            ]);

                            if($res){
                                $success_exec++;
                            } else {
                                $failed_exec++;
                            }
                        }
                    }

                    if($success_exec > 0 || $request->report_item == null){
                        // History
                        Audit::createHistory('Create', $report->report_title, $user_id);
                    }

                    // Respond
                    if($failed_exec == 0 && $success_exec == $item_count && $validation_image_failed == ""){
                        return response()->json([
                            'status' => 'success',
                            'message' => 'report created and its item',
                        ], Response::HTTP_OK);
                    } else if($failed_exec > 0 && $success_exec > 0){
                        return response()->json([
                            'status' => 'success',
                            'message' => "report created and some item has been added: $success_exec. About $failed_exec inventory failed to add",
                            'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'message' => 'report created but failed to add item report',
                            'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                        ], Response::HTTP_OK);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'something wrong. please contact admin',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function post_analyze_report(Request $request){
        try{ 
            $user_id = $request->user()->id;
            $validation_image_failed = "";

            // Report file handling
            $report_doc = null;  
            if ($request->hasFile('file') && $request->report_doc == null) {
                $file = $request->file('file');
                if ($file->isValid()) {
                    $file_ext = $file->getClientOriginalExtension();
                    // Validate file type
                    if (!in_array($file_ext, $this->allowed_file_type)) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'The file must be a '.implode(', ', $this->allowed_analyze_file_type).' file type',
                        ], Response::Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    // Validate file size
                    if ($file->getSize() > $this->max_size_analyze_file) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'The file size must be under '.($this->max_size_analyze_file/1000000).' Mb',
                        ], Response::Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Parse the PDF
                    $parser = new Parser();
                    $pdf = $parser->parseFile($file);
                    $text = $pdf->getText();
                    $lines = explode("\n", $text);
                    $items = [];

                    // Item - Inventory Mapping : Get List Item
                    foreach ($lines as $lineIndex => $line) {
                        $rawData[] = $line;
    
                        if ($lineIndex > 0 && preg_match('/\t/', $line)) {
                            $columns = explode("\t", $line);
                            $itemName = trim($columns[0]); 
                            if (!empty($itemName) && $itemName !== "Item Name" && $itemName !== "Parts of FlazenApps") {
                                $items[] = $itemName;
                            }
                        }
                    }

                    // Date Analyze
                    $lastItem = end($lines);
                    preg_match('/Generated at\s([0-9\-]+\s[0-9\:]+)/', $lastItem, $matches);
                    $generated_date = isset($matches[1]) ? $matches[1] : null;
                    $generated_date_diff = Generator::getDateDiff($generated_date);

                    if (empty($items) && isset($lines[0])) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Report analyzed : But no item found on the document',
                            'data' => null,
                        ], Response::HTTP_NOT_FOUND);
                    } else {
                        $search = implode(',', $items);
                        // Query : Find Similar Inventory by List Item
                        $mapping_inventory = InventoryModel::getInventoryByNameSearch($search);

                        if($mapping_inventory){
                            $category_count = [];
                            $total_matched = 0;

                            // Analyze : Inventory Price
                            $total_price = 0;
                            $total_item = count($mapping_inventory);
                            foreach ($mapping_inventory as $dt) {
                                if($dt['status'] == 'matched'){
                                    $total_matched++;
                                }

                                $total_price = $total_price + $dt['inventory_price'];

                                // Analyze : Inventory Category
                                $category = $dt['inventory_category'];
                                if (!isset($category_count[$category])) {
                                    $category_count[$category] = 0;
                                }                        
                                $category_count[$category]++;
                            }
                            $average_price = $total_price / $total_item;     
                            
                            // Analyze : Inventory Category
                            $final_category_result = [];
                            foreach ($category_count as $category => $total) {
                                $final_category_result[] = (object)[
                                    'context' => $category,
                                    'total' => $total
                                ];
                            }

                            // Analyze : Not Existing Items Mapping
                            $not_existing_item = [];
                            if(count($items) != $total_matched){
                                foreach ($items as $dt) {
                                    foreach ($mapping_inventory as $map) {
                                        if($map->inventory_name == $dt){
                                            $not_existing_item[] = $dt;
                                            break;
                                        }
                                    }
                                }
                            }
                            $not_existing_item = count($not_existing_item) > 0 ? $not_existing_item : null;

                            return response()->json([
                                'status' => 'success',
                                'message' => 'Report analyzed',
                                'data' => [
                                    'analyze_item' => $items,
                                    'found_inventory_data' => $mapping_inventory,
                                    'found_inventory_category' => $final_category_result,
                                    'found_total_price' => $total_price,
                                    'found_total_item' => $total_item,
                                    'found_avg_price' => $average_price,
                                    'generated_at' => $generated_date_diff,
                                    'not_existing_item' => $not_existing_item
                                ]
                            ], Response::HTTP_OK);
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => 'Report analyzed : No similar inventory found based on the document item',
                                'data' => [
                                    'not_existing_item' => $items
                                ],
                            ], Response::HTTP_NOT_FOUND);
                        }
                    }
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'you need to attached a file',
                ], Response::HTTP_UNPROCESSABLE_CONTENT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function post_create_analyzed_report(Request $request){
        try{ 
            $user_id = $request->user()->id;
            $validation_image_failed = "";

            // Report file handling
            $report_doc = null;  
            if ($request->hasFile('file') && $request->report_doc == null) {
                $file = $request->file('file');
                if ($file->isValid()) {
                    $file_ext = $file->getClientOriginalExtension();
                    // Validate file type
                    if (!in_array($file_ext, $this->allowed_file_type)) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'The file must be a '.implode(', ', $this->allowed_analyze_file_type).' file type',
                        ], Response::Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    // Validate file size
                    if ($file->getSize() > $this->max_size_analyze_file) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'The file size must be under '.($this->max_size_analyze_file/1000000).' Mb',
                        ], Response::Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Parse the PDF
                    $parser = new Parser();
                    $pdf = $parser->parseFile($file);
                    $text = $pdf->getText();
                    $lines = explode("\n", $text);
                    $items = [];

                    // Report Config
                    $raw_report_title = explode("Report : ",$lines[2]);
                    $report_title = $raw_report_title[1];
                    $raw_report_category = explode("Category : ",$lines[4]);
                    $report_category = $raw_report_category[1];
                    $extracted_desc = Generator::extractText("middle", implode("",$lines), "in this report come with some notes :", ". You can also import this document into GudangKu Apps");
                    $report_desc = $extracted_desc["result"];

                    // Item - Inventory Mapping : Get List Item
                    foreach ($lines as $lineIndex => $line) {
                        $rawData[] = $line;
    
                        if ($lineIndex > 0 && preg_match('/\t/', $line)) {
                            $columns = explode("\t", $line);
                            $itemName = trim($columns[0]); 
                            if (!empty($itemName) && $itemName !== "Item Name" && $itemName !== "Parts of FlazenApps") {
                                $items[] = $itemName;
                            }
                        }
                    }

                    // Date Analyze
                    $lastItem = end($lines);
                    preg_match('/Generated at\s([0-9\-]+\s[0-9\:]+)/', $lastItem, $matches);
                    $generated_date = isset($matches[1]) ? $matches[1] : null;
                    $generated_date_diff = Generator::getDateDiff($generated_date);

                    if (empty($items) && isset($lines[0])) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'Report analyzed : But no item found on the document',
                            'data' => null,
                        ], Response::HTTP_NOT_FOUND);
                    } else {
                        $search = implode(',', $items);
                        // Query : Find Similar Inventory by List Item
                        $mapping_inventory = InventoryModel::getInventoryByNameSearch($search);

                        if($mapping_inventory){
                            $existing_inventory = []; 
                            foreach ($items as $it) { 
                                $is_exist = false;
                                foreach ($mapping_inventory as $dt) {
                                    if($dt['status'] == "matched" && $dt['inventory_name'] == $it){
                                        $existing_inventory[] = [
                                            'inventory_id' => $dt['id'],
                                            'item_name' => $dt['inventory_name'],
                                            'item_desc' => $dt['inventory_desc'],
                                            'item_qty' => 1,
                                            'item_price' => $dt['inventory_price'],
                                        ];                                        
                                        $is_exist = true;
                                        break;
                                    }
                                }

                                if(!$is_exist){
                                    $existing_inventory[] = [
                                        'inventory_id' => null,
                                        'item_name' => $it,
                                        'item_desc' => null,
                                        'item_qty' => null,
                                        'item_price' => null,
                                    ];
                                }
                            }

                            // Model : Create Report
                            $id_report = Generator::getUUID();
                            $report_image = null;
                            $report = ReportModel::create([
                                'id' => $id_report, 
                                'report_title' => $report_title,  
                                'report_desc' => $report_desc,  
                                'report_category' => $report_category, 
                                'report_image' => $report_image,
                                'is_reminder' => 0, 
                                'remind_at' => null, 
                                'created_at' => date('Y-m-d H:i:s'), 
                                'created_by' => $user_id, 
                                'updated_at' => null, 
                                'deleted_at' => null
                            ]);

                            if($report){
                                $success_exec = 0;
                                $failed_exec = 0;

                                if($existing_inventory){
                                    $item_count = count($existing_inventory);

                                    // Model : Create Report Item
                                    foreach ($existing_inventory as $idx => $dt) {
                                        $res = ReportItemModel::create([
                                            'id' => Generator::getUUID(), 
                                            'inventory_id' => $dt['inventory_id'] ?? null, 
                                            'report_id' => $id_report, 
                                            'item_name' => $dt['item_name'], 
                                            'item_desc' => $dt['item_desc'],  
                                            'item_qty' => $dt['item_qty'], 
                                            'item_price' => $dt['item_price'] ?? null, 
                                            'created_at' => date('Y-m-d H:i:s'), 
                                            'created_by' => $user_id, 
                                        ]);

                                        if($res){
                                            $success_exec++;
                                        } else {
                                            $failed_exec++;
                                        }
                                    }
                                }

                                if($success_exec > 0 || $request->report_item == null){
                                    // History
                                    Audit::createHistory('Create', $report->report_title, $user_id);
                                }

                                // Respond
                                if($failed_exec == 0 && $success_exec == $item_count && $validation_image_failed == ""){
                                    return response()->json([
                                        'status' => 'success',
                                        'message' => 'report created and its item',
                                        'data' => [
                                            'id' => $id_report
                                        ]
                                    ], Response::HTTP_OK);
                                } else if($failed_exec > 0 && $success_exec > 0){
                                    return response()->json([
                                        'status' => 'success',
                                        'message' => "report created and some item has been added: $success_exec. About $failed_exec inventory failed to add",
                                        'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                                    ], Response::HTTP_OK);
                                } else {
                                    return response()->json([
                                        'status' => 'success',
                                        'message' => 'report created but failed to add item report',
                                        'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                                    ], Response::HTTP_OK);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => 'something wrong. please contact admin',
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => 'Report analyzed : No similar inventory found based on the document item',
                                'data' => null,
                            ], Response::HTTP_NOT_FOUND);
                        }
                    }
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'you need to attached a file',
                ], Response::HTTP_UNPROCESSABLE_CONTENT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function post_analyze_image(Request $request){
        try{ 
            $user_id = $request->user()->id;
            $validation_image_failed = "";

            // Image file handling
            $report_doc = null;  
            if ($request->hasFile('file') && $request->report_doc == null) {
                $file = $request->file('file');
                if ($file->isValid()) {
                    $file_ext = $file->getClientOriginalExtension();
                    // Validate file type
                    if (!in_array($file_ext, $this->allowed_file_type)) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'The file must be a '.implode(', ', $this->allowed_file_type).' file type',
                        ], Response::Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    // Validate file size
                    if ($file->getSize() > $this->max_size_file) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => 'The file size must be under '.($this->max_size_file/1000000).' Mb',
                        ], Response::Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Get Text from Image
                    $filePath = $file->store('temp', 'public');
                    $fullPath = public_path("storage/{$filePath}");
                    $text = Generator::extractTextFromImage($fullPath);
                    \Storage::delete("public/{$filePath}");

                    // Check price miss read
                    $lines = explode("\r\n", $text);
                    $lines = Generator::checkPossiblePrice($lines);
                    $items_text = [];
                    $items_num = [];

                    foreach ($lines as $ln) {
                        if (trim($ln) != "" && ((strpos($ln, ":") === false && strpos($ln, ";") === false))) {
                            $ln = str_replace(", ", ",", $ln);
                            if (preg_match('/^\d{1,3}(?:,\d{3})*(?:\.\d+)?$/', $ln) || preg_match('/Rp\s*\d{1,3}(?:,\d{3})*(?:\.\d+)?/', $ln)) { # Check if string is full numeric char, or have comma, or have currency
                                $ln = str_replace('.','', $ln);
                                $ln = str_replace(',','', $ln);
                                $ln = str_replace('Rp','', $ln);
                                $ln = str_replace(' ','', $ln);
                                $items_num[] = (int)$ln;
                            } else if(preg_match('/[a-zA-Z]/', $ln)){ // Check if string contain alphabet
                                $items_text[] = $ln;
                            }
                        }
                    }

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Image analyzed successfully',
                        'data' => [
                            'text' => $items_text,
                            'number' => $items_num
                        ]
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'you need to attached a file',
                ], Response::HTTP_UNPROCESSABLE_CONTENT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
