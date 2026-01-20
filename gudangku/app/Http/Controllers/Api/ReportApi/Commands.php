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
    private $module;

    public function __construct()
    {
        $this->max_size_file = 7500000; // 7.5 Mb
        $this->allowed_file_type = ['jpg','jpeg','gif','png','pdf'];
        $this->max_size_analyze_file = 15000000; // 15.0 Mb
        $this->allowed_analyze_file_type = ['pdf'];
        $this->module = 'report';
    }

    /**
     * @OA\DELETE(
     *     path="/api/v1/report/destroy/item/{id}",
     *     summary="Hard Delete Report Item By ID",
     *     description="This request is used to delete a report item based on the given `ID`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
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
    public function hardDeleteReportItemByID(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;
            $list_id = explode(",", $id);

            // Define user id by role
            $check_admin = AdminModel::find($user_id);
            $user_id = $check_admin ? null : $user_id;

            // Hard Delete report item
            $rows = ReportItemModel::deleteManyReportItemById($list_id, $user_id);
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
     *     path="/api/v1/report/destroy/report/{id}",
     *     summary="Hard Delete Report By ID",
     *     description="This request is used to delete a report based on the given `ID`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
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
    public function hardDeleteReportByID(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            // Get report by ID
            $report = ReportModel::find($id);            

            // Define user id by role
            $check_admin = AdminModel::find($user_id);
            $user_id = $check_admin ? null : $user_id; 

            // Hard delete report by ID
            $rows = ReportModel::deleteReportById($user_id,$id);
            if($rows > 0){
                if(!$check_admin){
                    // Create history
                    Audit::createHistory('Delete Report', $report->report_title, $user_id);
                }
                
                // Hard delete report item by report ID
                ReportItemModel::deleteReportItemByReportId($id, $user_id);

                // Return success response
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
     *     summary="Put Update Report Detail By ID",
     *     description="This request is used to update a report based on the given `ID`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"report_title","report_category","created_at"},
     *             @OA\Property(property="report_title", type="string", example="Do the dishes"),
     *             @OA\Property(property="report_category", type="string", example="Maintenance"),
     *             @OA\Property(property="report_desc", type="string", nullable=true, example="Wash the dishes"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-19 10:30:00")
     *         )
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
    public function putUpdateReportByID(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateReport($request,'update');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {  
                // Update report by ID
                $rows = ReportModel::updateReportById($user_id, $id, [
                    'report_title' => $request->report_title,
                    'report_desc' => $request->report_desc,
                    'report_category' => $request->report_category,
                    'created_at' => $request->created_at
                ]);

                if($rows > 0){
                    // Create history
                    Audit::createHistory('Update Report', $request->report_title, $user_id);

                    // Return success response
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
     *     summary="Put Update Report Item By ID",
     *     description="This request is used to update a report item based on the given `ID`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"item_name","item_qty","item_price"},
     *             @OA\Property(property="item_name", type="string", example="Desk"),
     *             @OA\Property(property="item_desc", type="string", example="clean the desk"),
     *             @OA\Property(property="item_qty", type="integer", nullable=true, example=2),
     *             @OA\Property(property="item_price", type="integer", nullable=true, example=10000)
     *         )
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
    public function putUpdateReportItemByID(Request $request, $id)
    {
        try{
            // Validate request body
            $validator = Validation::getValidateReportItem($request,'update');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("validation_failed", $validator->errors())
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;

                // Update report item by ID
                $rows = ReportItemModel::updateReportItemById($user_id, $id, [
                    'item_name' => $request->item_name,
                    'item_desc' => $request->item_desc,
                    'item_qty' => $request->item_qty,
                    'item_price' => $request->item_price
                ]);
                if($rows > 0){
                    // Create history
                    Audit::createHistory('Update Report Item', $request->item_name, $user_id);

                    // Return success response
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
     *     summary="Update Report Item By Splitting It Into A New Report",
     *     description="This request is used to update a report item based on the given `ID`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
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
    public function putUpdateSplitReportItemByID(Request $request, $id){
        try{
            $validator = Validation::getValidateReport($request,'create');

            // Validate request body
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("validation_failed", $validator->errors())
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;
                $list_item_id = explode(',',$request->list_id);

                // Get report by ID
                $old_check_report = ReportModel::find($id);
                if($old_check_report){
                    // Validate uuid
                    if (Validation::getValidateUUID($request->list_id)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("validation_failed", 'list item ID is not a valid UUID')
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    // Create report
                    $report = ReportModel::createReport($request->report_title, $request->report_desc, $request->report_category, null, $request->is_reminder, $request->remind_at, $user_id, null);
                    if($report){
                        $success_migrate = 0;
                        $failed_migrate = 0;
                        $list_item_name = "";

                        foreach ($list_item_id as $dt) {
                            // Get report item by ID and report ID
                            $old_report_item = ReportItemModel::getReportItemByIdAndReportId($dt, $user_id, $id);
                            if ($old_report_item) {
                                // Update report item by ID (move to a new report)
                                $list_item_name .= "$old_report_item->item_name,";
                                $updated = ReportItemModel::updateReportItemById($user_id, $dt, [
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
        
                            if($success_migrate > 0) {
                                $status_message = $failed_migrate == 0 ? 'all report items updated' : 'some report items updated';
                            }
                            
                            // Return success response
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
     *     summary="Post Create Report",
     *     description="This request is used to create a report based on the given `report_title`, `report_desc`, `report_category`, and `report_item`. This request interacts with the MySQL database, firebase storage, has a protected routes, and audited activity (history).",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"report_title","report_desc","report_category","report_item"},
     *                 @OA\Property(property="report_title", type="string", example="New Balance"),
     *                 @OA\Property(property="report_desc", type="string", example="Sepatu track"),
     *                 @OA\Property(property="report_category", type="string", example="Checkout"),
     *                 @OA\Property(property="report_item", type="string", example="[{\'inventory_id\':\'0216dd75-8ea6-3779-2ea6-9121c1a8c447\',\'item_name\':\'New Balance\',\'item_desc\':\'Sepatu\',\'item_qty\':1,\'item_price\':2249000}]"),
     *                 @OA\Property(property="is_reminder", type="integer", example=1),
     *                 @OA\Property(property="remind_at", type="string", format="date-time", nullable=true, example="2024-12-01T12:00:00Z"),
     *                 @OA\Property(property="report_image", type="string", format="binary", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
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
    public function postCreateReport(Request $request){
        try{
           // Validate request body
            $validator = Validation::getValidateReport($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => Generator::getMessageTemplate("validation_failed", $validator->errors())
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {   
                $user_id = $request->user()->id;
                $validation_image_failed = "";

                $report_image = []; 
                if ($request->hasFile('report_image')) {                    
                    // Get user's contact
                    $user = UserModel::getSocial($user_id);

                    // Iterate to upload file
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
                
                            try {
                                // Upload file to Firebase storage
                                $fileUrl = Firebase::uploadFile('report', $user_id, $user->username, $file, $file_ext);
                                $report_image[] = [
                                    'image_id' => Generator::getUUID(),
                                    'image_url' => $fileUrl
                                ]; 
                            } catch (\Exception $e) {
                                $validation_image_failed .= 'Failed to upload the '.$idx.'-th file';
                            }
                        }
                    }
                }

                // Create report
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

                        // Iterate to create report item
                        foreach ($report_item as $idx => $dt) {
                            $item_desc = trim($dt->item_desc);

                            // Create report item
                            $res = ReportItemModel::createReportItem(
                                $dt->inventory_id ?? null, $id_report, $dt->item_name, $item_desc === "" ? null : $item_desc, $dt->item_qty, ($dt->item_price && $dt->item_price) > 0 ?? null, $user_id
                            );

                            if($res){
                                $success_exec++;
                            } else {
                                $failed_exec++;
                            }
                        }
                    }

                    if($success_exec > 0 || $request->report_item == null){
                        // Create history
                        Audit::createHistory('Create', $report->report_title, $user_id);
                    }

                    // Return success response
                    if($failed_exec == 0 && $success_exec == $item_count && $validation_image_failed == ""){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("create", 'report'),
                            'data' => $report
                        ], Response::HTTP_CREATED);
                    } else if($failed_exec > 0 && $success_exec > 0){
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", "report created and some item has been added: $success_exec. about $failed_exec inventory failed to add"),
                            'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null,
                            'data' => $report
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("custom", 'report created but failed to add item report'),
                            'image_upload_detail' => $validation_image_failed != "" ? $validation_image_failed : null,
                            'data' => $report
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
     *     summary="Post Create Report Item",
     *     description="This request is used to create a report item based on the given `report_item`. This request interacts with the MySQL database, and has a protected routes.",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report ID to attach the item",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
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
    public function postCreateReportItem(Request $request,$id){
        try{
            // Validate request body
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

                // Iterate to create report item
                foreach ($report_item as $idx => $dt) {
                    // Create report item
                    $res = ReportItemModel::createReportItem(
                        $dt->inventory_id ?? null, $id, $dt->item_name, $dt->item_desc, $dt->item_qty, $dt->item_price ?? null, $user_id
                    );

                    if($res){
                        $success_exec++;
                    } else {
                        $failed_exec++;
                    }
                }

                // Return success response
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
     *     path="/api/v1/report/report_image/{id}",
     *     summary="Post Update Report Image By Id",
     *     description="This request is used to update report image by given report's `ID`. The updated field is `report_image`. This request interacts with the MySQL database, firebase storage, and has a protected routes.",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"report_image"},
     *                  @OA\Property(property="report_image", type="string", format="binary"),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Report report image update successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report update")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="report report image is a required field")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="report failed to fetched",
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
     *     )
     * )
     */
    public function postUpdateReportImageByReportID(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            // Get report by ID
            $report = ReportModel::getReportDetail($user_id,$id,'doc');
            if($report){
                $report_images = [];
                // Check if file attached
                if($request->hasFile('report_image')){
                    // Iterate to upload file
                    foreach ($request->file('report_image') as $file) {
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
        
                            try {
                                // Get user data
                                $user = UserModel::getSocial($user_id);
                                // Upload file to Firebase storage
                                $report_image = Firebase::uploadFile('report', $user_id, $user->username, $file, $file_ext); 
                                $report_images[] = (object)[
                                    'report_image_id' => Generator::getUUID(),
                                    'report_image_url' => $report_image
                                ];
                            } catch (\Exception $e) {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => Generator::getMessageTemplate("unknown_error", null),
                                ], Response::HTTP_INTERNAL_SERVER_ERROR);
                            }
                        }
                    }
                } else if($report->report_image && !$request->hasFile('report_image')){
                    // If file not attached and there is some image exist in the old data
                    foreach ($report->report_image as $dt) {
                        // Delete failed if file not found (already gone)
                        if(!Firebase::deleteFile($dt['report_image_url'])){
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("not_found", 'failed to delete report image'),
                            ], Response::HTTP_NOT_FOUND);
                        }
                    }
                }

                // Make null if array image empty
                if(count($report_images) === 0){
                    $report_images = null;
                } else {
                    if($report->report_image){
                        // If old report image not empty, combine with the new report image
                        $report_images = array_merge($report_images, $report->report_image);
                    }
                }

                // Update report by ID
                $rows = ReportModel::updateReportById($user_id, $id, ['report_image' => $report_images]);
                if($rows > 0){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("update", $this->module),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", $this->module),
                    ], Response::HTTP_NOT_FOUND);
                } 
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", $this->module),
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
     *     path="/api/v1/report/report_image/destroy/{report_id}/{image_id}",
     *     summary="Delete Report Image By Image ID",
     *     description="This request is used to delete an image in report by given `report_id` and `image_id` for the image. Updated field is `report_image`. This request interacts with the MySQL database, firebase storage, and has a protected routes.",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="report_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Parameter(
     *         name="image_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report Image ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Report image update successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report image update")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="failed"),
     *                     @OA\Property(property="message", type="string", example="report image is a required field")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="report failed to fetched",
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
     *     )
     * )
     */
    public function hardDeleteReportImageByReportIDAndImageID(Request $request, $report_id, $image_id)
    {
        try{
            $user_id = $request->user()->id;

            // Get report by ID
            $report = ReportModel::getReportDetail($user_id,$report_id,'doc');
            if($report){
                $report_images = $report->report_image;
                // Iterate to delete file
                foreach ($report_images as $dt) {
                    if ($dt['report_image_id'] === $image_id) {
                        // Delete failed if file not found (already gone)
                        if(!Firebase::deleteFile($dt['report_image_url'])){
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("not_found", 'failed to delete report image'),
                            ], Response::HTTP_NOT_FOUND);
                        }
                        break;
                    }
                }
            
                // Remove image from report image by its image ID
                $report_images = array_filter($report_images, function ($dt) use ($image_id) {
                    return $dt['report_image_id'] !== $image_id;
                });
                $report_image = array_values($report_images);
                
                // Update report by ID
                $rows = ReportModel::updateReportById($user_id, $report_id, [
                    'report_image' => count($report_image) > 0 ? $report_image : null,
                ]);

                if($rows > 0){
                    // Return success response
                    return response()->json([
                        'status' => 'success',
                        'message' => Generator::getMessageTemplate("delete", "$this->module image"),
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", "$this->module image"),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", "$this->module image"),
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
     * @OA\POST(
     *     path="/api/v1/analyze/report",
     *     summary="Post Analyze Report",
     *     description="This request is used to create an analyze report based on the given `file`. This request interacts with the MySQL database, and has a protected routes.",
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
    public function postAnalyzeReport(Request $request){
        try{ 
            $user_id = $request->user()->id;
            $validation_image_failed = "";

            // Check if file attached
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
                        // Get similar inventory by list item
                        $mapping_inventory = InventoryModel::getInventoryByNameSearch($search);

                        if($mapping_inventory){
                            $category_count = [];
                            $total_matched = 0;

                            // Analyze inventory price
                            $total_price = 0;
                            $total_item = count($mapping_inventory);
                            foreach ($mapping_inventory as $dt) {
                                if($dt['status'] == 'matched'){
                                    $total_matched++;
                                }

                                $total_price = $total_price + $dt['inventory_price'];

                                // Analyze inventory category
                                $category = $dt['inventory_category'];
                                if (!isset($category_count[$category])) {
                                    $category_count[$category] = 0;
                                }                        
                                $category_count[$category]++;
                            }
                            $average_price = $total_price / $total_item;     
                            
                            // Analyze inventory category
                            $final_category_result = [];
                            foreach ($category_count as $category => $total) {
                                $final_category_result[] = (object)[
                                    'context' => $category,
                                    'total' => $total
                                ];
                            }

                            // Analyze items that not exist
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

                            // Return success response
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
     *     summary="Post Create Analyze Report (New)",
     *     description="This request is used to create an analyze report based on the given `file`. This request interacts with the MySQL database, and has a protected routes.",
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
    public function postCreateAnalyzedReport(Request $request){
        try{ 
            $user_id = $request->user()->id;
            $validation_image_failed = "";

            // Check if file attached
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
                        // Get inventory by name
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

                            // Create report
                            $report_image = null;
                            $report = ReportModel::createReport($report_title, $report_desc, $report_category, $report_image, 0, null, $user_id, null);
                            $id_report = $report->id;
                            if($report){
                                $success_exec = 0;
                                $failed_exec = 0;

                                if($existing_inventory){
                                    $item_count = count($existing_inventory);

                                    // Create report item
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
                                    // Create history
                                    Audit::createHistory('Create', $report->report_title, $user_id);
                                }

                                // Return success response
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
     *     summary="Post Analyze Bill / Receipt",
     *     description="This request is used to create an analyze from bill / receipt based on the given `file`. This request interacts with the MySQL database, and has a protected routes.",
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
     *                 property="data", type="array",
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
    public function postAnalyzeBill(Request $request){
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

                        // Extract information
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
                        // Parse the PDF (Generate from GudangKu)
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
                                        // Extract information
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

                    // Return success response
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
