<?php

namespace App\Http\Controllers\Api\ReportApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Smalot\PdfParser\Parser;
use thiagoalessio\TesseractOCR\TesseractOCR;

// Models
use App\Models\ReportItemModel;
use App\Models\ReportModel;
use App\Models\UserModel;
use App\Models\InventoryModel;
use App\Models\AdminModel;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Validation;
use App\Helpers\Firebase;
use App\Helpers\Generator;

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
                    'message' => Generator::getMessageTemplate("delete", $extra." report item"),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'report item'),
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
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
            $check_admin = AdminModel::find($user_id);
            $report = ReportModel::select('report_title')->where('id', $id)->first();
            $rows = ReportModel::where('id', $id);

            if(!$check_admin){
                $rows = $rows->where('created_by', $user_id);
            }
            $rows = $rows->delete();

            if($rows > 0){
                // History
                Audit::createHistory('Delete Report', $report->report_title, $user_id);
                
                ReportItemModel::where('report_id', $id)
                    ->where('created_by', $user_id)
                    ->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("delete", 'report'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'report'),
                ], Response::HTTP_NOT_FOUND);
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
    public function update_report_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateReport($request,'update');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'result' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {  
                $rows = ReportModel::where('id', $id)
                    ->where('created_by', $user_id)
                    ->update([
                        'report_title' => $request->report_title,
                        'report_desc' => $request->report_desc,
                        'report_category' => $request->report_category,
                        'created_at' => $request->created_at
                    ]);

                if($rows > 0){
                    // History
                    Audit::createHistory('Update Report', $request->report_title, $user_id);

                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", 'report'),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'report'),
                    ], Response::HTTP_NOT_FOUND);
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
    public function update_report_item_by_id(Request $request, $id)
    {
        try{
            $validator = Validation::getValidateReportItem($request,'update');

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("validation_failed", $validator->errors())
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
                        'message' => Generator::getMessageTemplate("update", 'report item'),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'report item'),
                    ], Response::HTTP_NOT_FOUND);
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
                    'message' => Generator::getMessageTemplate("validation_failed", $validator->errors())
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;
                $list_item_id = explode(',',$request->list_id);

                $old_check_report = ReportModel::find($id);

                if($old_check_report){
                    if (Validation::getValidateUUID($request->list_id)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("validation_failed", 'list item ID is not a valid UUID')
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    $report = ReportModel::createReport($request->report_title, $request->report_desc, $request->report_category, null, $request->is_reminder, $request->remind_at, $user_id, null);
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
                                'message' => Generator::getMessageTemplate("not_found", 'report item'),
                            ], Response::HTTP_NOT_FOUND);
                        }
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'report'),
                    ], Response::HTTP_NOT_FOUND);
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
     *     path="/api/v1/report",
     *     summary="Create a new report",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="report_title", type="string", example="New Balance"),
     *             @OA\Property(property="report_desc", type="string", example="Sepatu track"),
     *             @OA\Property(property="report_category", type="string", example="Checkout"),
     *             @OA\Property(property="report_item", type="string", example="[{'inventory_id': '0216dd75-8ea6-3779-2ea6-9121c1a8c447','item_name': 'New Balance','item_desc': 'Sepatu','item_qty': 1,'item_price': 2249000}]"),
     *             @OA\Property(property="is_reminder", type="integer", example=1),
     *             @OA\Property(property="remind_at", type="string", format="date-time", example="2024-12-01T12:00:00Z"),
     *             @OA\Property(property="file", type="file", example="image.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created report",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Report created and its item | Report created and some item has been added: item a, item b. About 1 inventory failed to add"),
     *             @OA\Property(property="data", type="string", example="| Failed to upload the 2-th file"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed: {validation errors}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authorization required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="You need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please contact admin")
     *         )
     *     )
     * )
     */
    public function post_report(Request $request){
        try{
            // Validator
            $validator = Validation::getValidateReport($request,'create');

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("validation_failed", $validator->errors())
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;
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
                $report = ReportModel::createReport(
                    $request->report_title, $request->report_desc, $request->report_category, $report_image ? json_encode($report_image,true) : null, 0, $request->remind_at, $user_id, $request->created_at ?? date('Y-m-d H:i:s')
                );
                $id_report = $report->id;

                if($report){
                    $success_exec = 0;
                    $failed_exec = 0;

                    if($request->report_item){
                        $report_item = json_decode($request->report_item);
                        $item_count = count($report_item);

                        // Model : Create Report Item
                        foreach ($report_item as $idx => $dt) {
                            $res = ReportItemModel::createReportItem(
                                $dt->inventory_id ?? null, $id_report, $dt->item_name, $dt->item_desc, $dt->item_qty, $dt->item_price ?? null, $user_id
                            );

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
                            'message' => Generator::getMessageTemplate("create", 'report'),
                        ], Response::HTTP_CREATED);
                    } else if($failed_exec > 0 && $success_exec > 0){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", "report created and some item has been added: $success_exec. about $failed_exec inventory failed to add"),
                            'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", 'report created but failed to add item report'),
                            'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                        ], Response::HTTP_CREATED);
                    }
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => Generator::getMessageTemplate("unknown_error", null),
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
     *     path="/api/v1/report/item/{id}",
     *     summary="Create a new report item",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="report_item", type="string", example="[{'inventory_id': '0216dd75-8ea6-3779-2ea6-9121c1a8c447','item_name': 'New Balance','item_desc': 'Sepatu','item_qty': 1,'item_price': 2249000}]"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created report",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Report item created"),
     *             @OA\Property(property="data", type="string", example="| Failed to upload the 2-th file"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed: {validation errors}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authorization required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="You need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please contact admin")
     *         )
     *     )
     * )
     */
    public function post_report_item(Request $request,$id){
        try{
            // Validator
            $validator = Validation::getValidateReportItem($request,'create');

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("validation_failed", $validator->errors())
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;
                $report_item = json_decode($request->report_item);
                $item_count = count($report_item);
                $success_exec = 0;
                $failed_exec = 0;

                // Model : Create Report Item
                foreach ($report_item as $idx => $dt) {
                    $res = ReportItemModel::createReportItem(
                        $dt->inventory_id ?? null, $id, $dt->item_name, $dt->item_desc, $dt->item_qty, $dt->item_price ?? null, $user_id
                    );

                    if($res){
                        $success_exec++;
                    } else {
                        $failed_exec++;
                    }
                }

                // Respond
                if($failed_exec == 0 && $success_exec == $item_count){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("create", 'report item'),
                    ], Response::HTTP_CREATED);
                } else if($failed_exec > 0 && $success_exec > 0){
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", "some item has been added: $success_exec. about $failed_exec inventory failed to add"),
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("custom", 'failed to add item report'),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
     *     path="/api/v1/analyze/report",
     *     summary="Analyze report",
     *     tags={"Analyze"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="file", type="file", example="image.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully analyzed report",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Report analyzed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="analyze_item",
     *                     type="array",
     *                     @OA\Items(type="string", example="New Balance")
     *                 ),
     *                 @OA\Property(
     *                     property="found_inventory_data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", example="d6ecca7c-bf04-f2a9-123a-378b6cb999de"),
     *                         @OA\Property(property="inventory_name", type="string", example="New Balance"),
     *                         @OA\Property(property="inventory_desc", type="string", example="Sepatu High"),
     *                         @OA\Property(property="inventory_vol", type="integer", example=1),
     *                         @OA\Property(property="inventory_unit", type="string", example="Kilogram"),
     *                         @OA\Property(property="inventory_category", type="string", example="Food And Beverages"),
     *                         @OA\Property(property="inventory_price", type="integer", example=2249000),
     *                         @OA\Property(property="inventory_room", type="string", example="Bathroom"),
     *                         @OA\Property(property="inventory_storage", type="string", example="Wardrobe"),
     *                         @OA\Property(property="status", type="string", example="matched")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="found_inventory_category",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="context", type="string", example="Food And Beverages"),
     *                         @OA\Property(property="total", type="integer", example=1)
     *                     )
     *                 ),
     *                 @OA\Property(property="found_total_price", type="integer", example=9014000),
     *                 @OA\Property(property="found_total_item", type="integer", example=5),
     *                 @OA\Property(property="found_avg_price", type="integer", example=1802800),
     *                 @OA\Property(property="generated_at", type="string", example="3 days and 8 hours ago"),
     *                 @OA\Property(property="not_existing_item", type="string", example="Item L")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed: {validation errors}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authorization required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="You need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Item not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="failed"),
     *              @OA\Property(property="message", type="string", example="Report analyzed : No similar inventory found based on the document item"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                      property="not_existing_item",
     *                      type="array",
     *                      @OA\Items(type="string", example="Item L")
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please contact admin")
     *         )
     *     )
     * )
     */
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
                            'message' => Generator::getMessageTemplate("custom", 'The file must be a '.implode(', ', $this->allowed_analyze_file_type).' file type'),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    // Validate file size
                    if ($file->getSize() > $this->max_size_analyze_file) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("custom", 'The file size must be under '.($this->max_size_analyze_file/1000000).' Mb'),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
                            'message' => Generator::getMessageTemplate("custom", 'report analyzed : but no item found on the document'),
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
                                'message' => Generator::getMessageTemplate("analyze", 'report'),
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
                                'message' => Generator::getMessageTemplate("custom", 'Report analyzed : No similar inventory found based on the document item'),
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
                    'message' => Generator::getMessageTemplate("custom", 'you need to attach a file'),
                ], Response::HTTP_UNPROCESSABLE_CONTENT);
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
     *     path="/api/v1/analyze/report/new",
     *     summary="Create Analyze report",
     *     tags={"Analyze"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to be analyzed"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully analyzed report",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Report analyzed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="analyze_item",
     *                     type="array",
     *                     @OA\Items(type="string", example="New Balance")
     *                 ),
     *                 @OA\Property(
     *                     property="found_inventory_data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", example="d6ecca7c-bf04-f2a9-123a-378b6cb999de"),
     *                         @OA\Property(property="inventory_name", type="string", example="New Balance"),
     *                         @OA\Property(property="inventory_desc", type="string", example="Sepatu High"),
     *                         @OA\Property(property="inventory_vol", type="integer", example=1),
     *                         @OA\Property(property="inventory_unit", type="string", example="Kilogram"),
     *                         @OA\Property(property="inventory_category", type="string", example="Food And Beverages"),
     *                         @OA\Property(property="inventory_price", type="integer", example=2249000),
     *                         @OA\Property(property="inventory_room", type="string", example="Bathroom"),
     *                         @OA\Property(property="inventory_storage", type="string", example="Wardrobe"),
     *                         @OA\Property(property="status", type="string", example="matched")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="found_inventory_category",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="context", type="string", example="Food And Beverages"),
     *                         @OA\Property(property="total", type="integer", example=1)
     *                     )
     *                 ),
     *                 @OA\Property(property="found_total_price", type="integer", example=9014000),
     *                 @OA\Property(property="found_total_item", type="integer", example=5),
     *                 @OA\Property(property="found_avg_price", type="integer", example=1802800),
     *                 @OA\Property(property="generated_at", type="string", example="3 days and 8 hours ago"),
     *                 @OA\Property(property="not_existing_item", type="string", example="Item L")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed: {validation errors}")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authorization required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="You need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="Report analyzed: No similar inventory found based on the document item"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="not_existing_item",
     *                     type="array",
     *                     @OA\Items(type="string", example="Item L")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please contact admin")
     *         )
     *     )
     * )
     */
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
                            'message' => Generator::getMessageTemplate("custom", 'The file must be a '.implode(', ', $this->allowed_analyze_file_type).' file type'),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    // Validate file size
                    if ($file->getSize() > $this->max_size_analyze_file) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("custom", 'The file size must be under '.($this->max_size_analyze_file/1000000).' Mb'),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
                            'message' => Generator::getMessageTemplate("custom", 'report analyzed : but no item found on the document'),
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
                            $report_image = null;
                            $report = ReportModel::createReport($report_title, $report_desc, $report_category, $report_image, 0, null, $user_id, null);
                            $id_report = $report->id;

                            if($report){
                                $success_exec = 0;
                                $failed_exec = 0;

                                if($existing_inventory){
                                    $item_count = count($existing_inventory);

                                    // Model : Create Report Item
                                    foreach ($existing_inventory as $idx => $dt) {
                                        $res = ReportItemModel::createReportItem(
                                            $dt['inventory_id'] ?? null, $id_report, $dt['item_name'],$dt['item_desc'], $dt['item_qty'], $dt['item_price'] ?? null, $user_id
                                        );

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
                                        'message' => Generator::getMessageTemplate("create", 'report'),
                                        'data' => [
                                            'id' => $id_report
                                        ]
                                    ], Response::HTTP_OK);
                                } else if($failed_exec > 0 && $success_exec > 0){
                                    return response()->json([
                                        'status' => 'success',
                                        'message' => Generator::getMessageTemplate("custom", "report created and some item has been added: $success_exec. About $failed_exec inventory failed to add"),
                                        'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                                    ], Response::HTTP_OK);
                                } else {
                                    return response()->json([
                                        'status' => 'success',
                                        'message' => Generator::getMessageTemplate("custom", 'report created but failed to add item report'),
                                        'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null
                                    ], Response::HTTP_OK);
                                }
                            } else {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => Generator::getMessageTemplate("unknown_error", null),
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        } else {
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("custom", 'Report analyzed : No similar inventory found based on the document item'),
                                'data' => null,
                            ], Response::HTTP_NOT_FOUND);
                        }
                    }
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("custom", 'you need to attach a file'),
                ], Response::HTTP_UNPROCESSABLE_CONTENT);
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
     *     path="/api/v1/analyze/report/bill",
     *     summary="Analyze bill or receipt",
     *     tags={"Analyze"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Bill or receipt file to be analyzed"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File successfully analyzed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="File analyzed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="item_name", type="string", example="New Balance"),
     *                     @OA\Property(property="item_price", type="integer", example=2249000),
     *                     @OA\Property(property="item_qty", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="The file must be a jpg, jpeg, gif, png, or pdf file type | you need to attached a file")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authorization required",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="You need to include the authorization token from login")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="No matching inventory items found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong. Please contact admin")
     *         )
     *     )
     * )
     */
    public function post_analyze_bill(Request $request){
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
                            'message' => Generator::getMessageTemplate("custom", 'The file must be a '.implode(', ', $this->allowed_file_type).' file type'),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    // Validate file size
                    if ($file->getSize() > $this->max_size_file) {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("custom", 'The file size must be under '.($this->max_size_file/1000000).' Mb'),
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    if($file_ext != "pdf"){
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

                        $total_text = count($items_text);
                        $total_num = count($items_num);
                        $items =  $total_text > $total_num ? $items_text : $items_num;
                        $itemQty = 1;
                        $res = [];
                        foreach ($items as $idx => $dt) {
                            $res[] = (object)[
                                "item_name" => $items_text[$idx] ?? null,
                                "item_price" => $items_num[$idx] ?? null,
                                "item_qty" => $itemQty
                            ];
                        }
                    } else {
                        // Parse the PDF
                        $parser = new Parser();
                        $pdf = $parser->parseFile($file);
                        $text = $pdf->getText();
                        $lines = explode("\n", $text);
                        $res = [];

                        foreach ($lines as $lineIndex => $line) {
                            $rawData[] = $line;
        
                            if ($lineIndex > 0 && preg_match('/\t/', $line)) {
                                $columns = explode("\t", $line);
                                $itemName = trim($columns[0]); 
                                if (!empty($itemName) && $itemName !== "Item Name" && $itemName !== "Parts of FlazenApps") {
                                    $founded_price = null;
                                    $itemQty = 1;

                                    if(count($columns) > 2){
                                        $itemPrice = trim($columns[2]); 
                                        if (!empty($itemPrice) && $itemPrice !== "Description" && $itemPrice !== "Parts of FlazenApps") {
                                            $founded_price_raw = explode(" Rp. ", $itemPrice);
                                            $itemQty = (int)$founded_price_raw[0];
                                            $founded_price = (int)str_replace(',','', $founded_price_raw[1]);
                                        }
                                    }
                                    $res[] = [
                                        "item_name" => $itemName,
                                        "item_price" => $founded_price,
                                        "item_qty" => $itemQty
                                    ];
                                }
                            }
                        }
                    }

                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("analyze", 'file'),
                        'data' => $res
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("custom", 'you need to attach a file'),
                ], Response::HTTP_UNPROCESSABLE_CONTENT);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
