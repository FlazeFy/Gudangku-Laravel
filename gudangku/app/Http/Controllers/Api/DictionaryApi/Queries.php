<?php

namespace App\Http\Controllers\Api\DictionaryApi;

use App\Http\Controllers\Controller;

// Models
use App\Models\DictionaryModel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/dictionary/type/{type}",
     *     summary="Get dictionary by type",
     *     description="This request is used to get dictionary by type. This request is using MySql database",
     *     tags={"Dictionary"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Dictionary type",
     *         example="report_category",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="dictionary fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="dictionary fetched"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="dictionary_name", type="string", example="Wishlist")
     *                  )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="dictionary failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="dictionary not found")
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
    public function get_dictionary_by_type(Request $request,$type)
    {
        try{
            $res = DictionaryModel::select('dictionary_name')
                ->where('dictionary_type',$type)
                ->orderby('dictionary_name', 'ASC')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'dictionary fetched',
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'dictionary not found',
                ], Response::HTTP_NOT_FOUND);
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'something wrong. please contact admin',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
