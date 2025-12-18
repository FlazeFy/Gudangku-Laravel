<?php

namespace App\Http\Controllers\Api\DictionaryApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Model
use App\Models\DictionaryModel;
// Helper
use App\Helpers\Generator;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/dictionary/type/{type}",
     *     summary="Get dictionary by type",
     *     description="This request is used to get dictionary by type. Can be multiple dictionary type if separate using `,`. This request is using MySql database",
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
    public function getDictionaryByType(Request $request,$type)
    {
        try{
            $res = DictionaryModel::select('dictionary_name','dictionary_type');
            if(strpos($type, ',')){
                $dcts = explode(",", $type);
                foreach ($dcts as $dt) {
                    $res = $res->orwhere('dictionary_type',$dt); 
                }
            } else {
                $res = $res->where('dictionary_type',$type); 
            }

            $res = $res->orderby('dictionary_type', 'ASC')
                ->orderby('dictionary_name', 'ASC')
                ->get();
            
            if (count($res) > 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", 'dictionary'),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'dictionary'),
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
