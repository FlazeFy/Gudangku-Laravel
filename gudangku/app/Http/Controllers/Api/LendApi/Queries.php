<?php

namespace App\Http\Controllers\Api\LendApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

// Helpers
use App\Helpers\Generator;

// Models
use App\Models\LendModel;

class Queries extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/v1/lend",
     *     summary="Get active qr code",
     *     description="This request is used to get active qr code. This request is using MySQL database, and has protected routes.",
     *     tags={"Lend"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="lend qr code fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lend qr code fetched"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="d8b5d4cc-805d-3303-3966-dc767c062d27"),
     *                 @OA\Property(property="lend_qr_url", type="string", format="url", example="https://storage.googleapis.com/download/storage/v1/b/gudangku-94edc.appspot.com/o/lend%2F..."),
     *                 @OA\Property(property="qr_period", type="integer", example=2),
     *                 @OA\Property(property="lend_desc", type="string", example="lorem ipsum")
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
     *         description="lend qr code failed to fetched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="lend qr code not found")
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
    public function get_lend_active(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $res = LendModel::getLendActive($user_id);
            if($res) {    
                $res->lend_expired_datetime = Carbon::parse($res->created_at)->addHours($res->qr_period);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("fetch", "lend qr code"),
                    'data' => $res
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'lend qr code'),
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
