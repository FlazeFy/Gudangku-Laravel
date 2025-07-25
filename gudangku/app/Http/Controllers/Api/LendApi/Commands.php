<?php

namespace App\Http\Controllers\Api\LendApi;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\FileUpload\InputFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\File;
use Carbon\Carbon;

// Models
use App\Models\UserModel;
use App\Models\LendModel;

// Helpers
use App\Helpers\Audit;
use App\Helpers\QRGenerate;
use App\Helpers\Generator;
use App\Helpers\Validation;
use App\Helpers\Firebase;

class Commands extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/lend",
     *     summary="Create lend QR",
     *     tags={"Lend"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="lend created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="lend created, inventory can now seen by others")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Data is already exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="lend is already exist")
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
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login")
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
    public function post_lend_qr(Request $request)
    {
        try{
            $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
            $user_id = $request->user()->id;

            $validator = Validation::getValidateLend($request,'create_qr');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $check_lend = LendModel::getLendByUserId($user_id);

                if($check_lend){
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("conflict", "qr code"),
                    ], Response::HTTP_CONFLICT);
                } else {
                    $qrPeriodHours = $request->qr_period;
                    $lend = LendModel::createLend(null,$qrPeriodHours,null,'open',$user_id);
                    $lend_expired_datetime = Carbon::parse($lend->created_at)->addHours($qrPeriodHours);
                    $lend_id = $lend->id;
                    $qr_path = QRGenerate::generateQR("https://gudangku.leonardhors.com/lend/$lend_id");

                    $file = new File($qr_path);
                    $file_ext = pathinfo($qr_path, PATHINFO_EXTENSION);

                    // Helper: Upload qr image
                    try {
                        $user = UserModel::find($user_id);
                        $qr_image = Firebase::uploadFile('lend', $user_id, $user->username, $file, $file_ext); 

                        unlink($qr_path);
                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => 'error',
                            'message' => Generator::getMessageTemplate("unknown_error", null),
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    $data = [
                        'lend_qr_url' => $qr_image
                    ];
                    LendModel::updateLendByUserId($data,$user_id);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'lend created, inventory can now seen by others',
                        'data' => [
                            'qr_code' => $qr_image,
                            'qr_period' => $qrPeriodHours,
                            'lend_expired_datetime' => $lend_expired_datetime
                        ]
                    ], Response::HTTP_CREATED);
                }
            }
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => Generator::getMessageTemplate("unknown_error", null),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
