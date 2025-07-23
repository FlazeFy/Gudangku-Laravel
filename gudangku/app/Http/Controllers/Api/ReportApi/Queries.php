<?php

namespace App\Http\Controllers\Api\ReportApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

// Helpers
use App\Helpers\Document;
use App\Helpers\Validation;
use App\Helpers\Generator;

// Models
use App\Models\ReportModel;
use App\Models\AdminModel;
use App\Models\ReportItemModel;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/report",
     *     summary="Get all report",
     *     description="This request is used to get all report. This request is using MySql database, have a protected routes, and have template pagination.",
     *     tags={"Report"},
     *     security={{"bearerAuth":{}}},
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
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
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
     *     ),
     * )
     */
    public function get_all_report(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);
            $search_key = $request->query('search_key');
            $filter_category = $request->query('filter_category') ?? null;
            $sorting = $request->query('sorting') ?? 'desc_created';

            // Report fetching
            $res = ReportModel::getMyReport(!$check_admin ? $user_id : null,null,$search_key,null,$filter_category);
            
            if (count($res) > 0) {
                $res_header = [];
                foreach($res as $dt){
                    $res_header[] = [
                        'report_title' => $dt->report_title,
                        'report_items' => $dt->report_items
                    ];
                }

                $collection = collect($res);

                // Sorting
                if ($sorting == 'desc_created') {
                    $collection = $collection->sortByDesc('created_at');
                } else if ($sorting == 'asc_created') {
                    $collection = $collection->sortBy('created_at');
                } else if ($sorting == 'desc_title') {
                    $collection = $collection->sortByDesc('report_title');
                } else if ($sorting == 'asc_title') {
                    $collection = $collection->sortBy('report_title');
                }
                
                // Paginate
                $perPage = $request->query('per_page_key') ?? 12;
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
                    'message' => Generator::getMessageTemplate("fetch", 'report'),
                    'data' => $res,
                    'report_header' => $res_header
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
     * @OA\GET(
     *     path="/api/v1/report/{search}/{id}",
     *     summary="Get all report by inventory",
     *     description="This request is used to get all report found in a inventory by inventory name and report id. This request is using MySql database, have a protected routes, and have template pagination.",
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
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
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
     *     ),
     * )
     */
    public function get_my_report_by_inventory(Request $request,$search,$id)
    {
        try{
            $user_id = $request->user()->id;

            $res = ReportModel::getMyReport($user_id,$search,null,$id,null);
            
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
                    'message' =>  Generator::getMessageTemplate("fetch", 'report'),
                    'data' => $res
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
     * @OA\GET(
     *     path="/api/v1/report/detail/item/{id}",
     *     summary="Get report detail by id",
     *     description="This request is used to get report detail by id and all items found in the report. This request is using MySQL database, and has protected routes.",
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
     *         description="Report fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="6f59235e-c398-8a83-2f95-3f1fbe95ca6e"),
     *                 @OA\Property(property="report_title", type="string", example="Report A"),
     *                 @OA\Property(property="report_desc", type="string", example="This is a report description"),
     *                 @OA\Property(property="report_category", type="string", example="Shopping Cart"),
     *                 @OA\Property(property="report_image", type="string", example="https://..."),
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
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
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
                    'message' => Generator::getMessageTemplate("fetch", 'report'),
                    'data' => $res,
                    'data_item' => $res_item
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
     * @OA\GET(
     *     path="/api/v1/report/detail/item/{id}/doc",
     *     summary="Get report detail document html format by id",
     *     description="This request is used to get report detail by id and all items found in the report. This request is using MySQL database, and has protected routes.",
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
     *         description="Report document generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="report generated"),
     *             @OA\Property(property="data", type="string", example="<p>Ini document</p>"),
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
     *         description="report document failed to generated",
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
    public function get_document(Request $request,$id)
    {
        try{
            $user_id = $request->user()->id;
            $report = ReportModel::getReportDetail(null,$id,'doc');
            $filter_in = $request->query('filter_in', null);

            if($filter_in){
                if(Validation::getValidateUUID($filter_in)){
                    return response()->json([
                        'status' => 'failed',
                        'message' =>  Generator::getMessageTemplate("custom", 'selected item not valid'),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            if ($report) {                
                $report_item = ReportItemModel::getReportItem(null,$id,'doc',$filter_in);
                $res = Document::documentTemplateReport(null,null,null,$report,$report_item);
     
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("generate", 'report'),
                    'data' => $res
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
}
