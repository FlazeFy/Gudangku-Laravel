<?php

namespace App\Http\Controllers\Api\ReportApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\ReportModel;

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
     *     tags={"Report"},
     *     @OA\Response(
     *         response=200,
     *         description="report fetched"
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
}
