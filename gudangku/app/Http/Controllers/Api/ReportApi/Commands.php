<?php

namespace App\Http\Controllers\Api\ReportApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\ReportItemModel;
use App\Models\ReportModel;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Validation;
use App\Helpers\Generator;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Commands extends Controller
{
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
                'message' => 'something wrong. please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }   
}
