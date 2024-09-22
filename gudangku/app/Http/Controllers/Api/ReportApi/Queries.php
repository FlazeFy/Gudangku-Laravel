<?php

namespace App\Http\Controllers\Api\ReportApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\ReportModel;
use App\Models\ReportItemModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/report",
     *     summary="Get all report",
     *     description="This request is used to get all report. This request is using MySql database, have a protected routes, and have template pagination.",
     *     tags={"Report"},
     *     @OA\Response(
     *         response=200,
     *         description="report fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                         @OA\Property(property="report_title", type="string", example="Report A"),
     *                         @OA\Property(property="report_desc", type="string", example="This is a report description"),
     *                         @OA\Property(property="report_category", type="string", example="Shopping Cart"),
     *                         @OA\Property(property="is_reminder", type="integer", example="0"),
     *                         @OA\Property(property="reminder_at", type="object", example=""),
     *                         @OA\Property(property="total_variety", type="integer", example="1"),
     *                         @OA\Property(property="total_item", type="integer", example="1"),
     *                         @OA\Property(property="report_items", type="string", example="Nivea Extra White Repair & Protect"),
     *                         @OA\Property(property="item_price", type="integer", example="20000"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                     )
     *                 ),
     *             ),
     *             @OA\Property(property="report_header", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="report_title", type="string", example="Report A"),
     *                     @OA\Property(property="report_items", type="string", example="Nivea Extra White Repair & Protect"),
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="report failed to fetched"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_my_report(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = ReportModel::getMyReport($user_id,null,null);
            
            if (count($res) > 0) {
                $res_header = [];
                foreach($res as $dt){
                    $res_header[] = [
                        'report_title' => $dt->report_title,
                        'report_items' => $dt->report_items
                    ];
                }

                $collection = collect($res);
                $collection = $collection->sortBy('created_at')->values();
                $perPage = 12;
                $page = request()->input('page', 1);
                $paginator = new LengthAwarePaginator(
                    $collection->forPage($page, $perPage)->values(),
                    $collection->count(),
                    $perPage,
                    $page,
                    ['path' => url()->current()]
                );
                $res = $paginator->appends(request()->except('page'));
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'report fetched',
                    'data' => $res,
                    'report_header' => $res_header
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'report failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/report/{search}/{id}",
     *     summary="Get all report by inventory",
     *     description="This request is used to get all report found in a inventory by inventory name and report id. This request is using MySql database, have a protected routes, and have template pagination.",
     *     tags={"Report"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Report ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Inventory Name",
     *         example="Nivea%20Extra%20White%20Repair%20&%20Protect",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="report fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="history fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                         @OA\Property(property="report_title", type="string", example="Report A"),
     *                         @OA\Property(property="report_desc", type="string", example="This is a report description"),
     *                         @OA\Property(property="report_category", type="string", example="Shopping Cart"),
     *                         @OA\Property(property="is_reminder", type="integer", example="0"),
     *                         @OA\Property(property="reminder_at", type="object", example=""),
     *                         @OA\Property(property="total_variety", type="integer", example="1"),
     *                         @OA\Property(property="total_item", type="integer", example="1"),
     *                         @OA\Property(property="report_items", type="string", example="Nivea Extra White Repair & Protect"),
     *                         @OA\Property(property="item_price", type="integer", example="20000"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47"),
     *                     )
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="report failed to fetched"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     * )
     */
    public function get_my_report_by_inventory(Request $request,$search,$id)
    {
        try{
            $user_id = $request->user()->id;

            $res = ReportModel::getMyReport($user_id,$search,$id);
            
            if (count($res) > 0) {
                $collection = collect($res);
                $collection = $collection->sortBy('created_at')->values();
                $perPage = 12;
                $page = request()->input('page', 1);
                $paginator = new LengthAwarePaginator(
                    $collection->forPage($page, $perPage)->values(),
                    $collection->count(),
                    $perPage,
                    $page,
                    ['path' => url()->current()]
                );
                $res = $paginator->appends(request()->except('page'));
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'report fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'report failed to fetched',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/v1/report/detail/item/{id}",
     *     summary="Get report detail by id",
     *     description="This request is used to get report detail by id and all items found in the report. This request is using MySQL database, and has protected routes.",
     *     tags={"Report"},
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
     *         description="Report fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                 @OA\Property(property="report_title", type="string", example="Report A"),
     *                 @OA\Property(property="report_desc", type="string", example="This is a report description"),
     *                 @OA\Property(property="report_category", type="string", example="Shopping Cart"),
     *                 @OA\Property(property="is_reminder", type="integer", example="0"),
     *                 @OA\Property(property="reminder_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="total_item", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47")
     *             ),
     *             @OA\Property(property="data_item", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                     @OA\Property(property="item_name", type="string", example="Nivea Extra White Repair & Protect"),
     *                     @OA\Property(property="item_desc", type="string", example="This is a report description"),
     *                     @OA\Property(property="item_qty", type="integer", example=1),
     *                     @OA\Property(property="item_price", type="integer", example=20000),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-20 22:53:47")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Report not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Report not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function get_my_report_detail(Request $request,$id)
    {
        try{
            $user_id = $request->user()->id;

            $res = ReportModel::getReportDetail($user_id,$id,'data');
            $res_item = ReportItemModel::getReportItem($user_id,$id,'data');
            
            if ($res) {      
                $total_item = 0;
                $total_price = 0;   

                if($res_item){ 
                    foreach($res_item as $dt){
                        $total_item = $total_item + $dt->item_qty;
                        $total_price = $total_price + $dt->item_price;
                    }
                }

                $res['total_item'] = $total_item;
                $res['total_price'] = $total_price; 
                   
                return response()->json([
                    'status' => 'success',
                    'message' => 'report fetched',
                    'data' => $res,
                    'data_item' => $res_item
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'report failed to fetched',
                    'data' => null,
                    'data_item' => null
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. Please contact admin'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
