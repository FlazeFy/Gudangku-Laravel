<?php

namespace App\Http\Controllers\Api\ReminderApi;
use App\Http\Controllers\Controller;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Telegram\Bot\FileUpload\InputFile;

// Models
use App\Models\ReminderModel;
use App\Models\ScheduleMarkModel;
use App\Models\InventoryModel;
use App\Models\UserModel;
use App\Models\AdminModel;

// Helpers
use App\Helpers\Audit;
use App\Helpers\Generator;
use App\Helpers\LineMessage;
use App\Helpers\Validation;

class Commands extends Controller
{
    /**
     * @OA\POST(
     *     path="/api/v1/reminder",
     *     summary="Create a reminder",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="reminder created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder created")
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
     *         description="reminder not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="reminder with same type and context has been used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder with same type and context has been used")
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
    public function post_reminder(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateReminder($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $is_exist = ReminderModel::getReminderByInventoryIdReminderTypeReminderContext($request->inventory_id,$request->reminder_type,$request->reminder_context,$user_id);

                if(!$is_exist){
                    $inventory = InventoryModel::select('inventory_name')
                        ->where('created_by', $user_id)
                        ->where('id', $request->inventory_id)
                        ->first();

                    if($inventory){
                        ReminderModel::createReminder($request->inventory_id, $request->reminder_desc, $request->reminder_type, $request->reminder_context, $user_id);

                        // History
                        Audit::createHistory('Create Reminder', "$request->reminder_desc for inventory $inventory->inventory_name", $user_id);
                        $msg = "You have create a reminder. Here's the reminder description for [DEMO]. $request->reminder_desc";
                        if($request->send_demo == "true"){
                            $user = UserModel::getSocial($user_id);
                            if($user->firebase_fcm_token){
                                $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                                $messaging = $factory->createMessaging();
                                $fcm = CloudMessage::withTarget('token', $user->firebase_fcm_token)
                                    ->withNotification(Notification::create($msg));
                                $response = $messaging->send($fcm);
                            }
                            if($user->telegram_user_id){
                                $response = Telegram::sendMessage([
                                    'chat_id' => $user->telegram_user_id,
                                    'text' => $msg,
                                    'parse_mode' => 'HTML'
                                ]);
                            }
                        }

                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("create", 'reminder'),
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("not_found", 'reminder'),
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'reminder with same type and context has been used',
                    ], Response::HTTP_CONFLICT);
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
     *     path="/api/v1/reminder/copy",
     *     summary="Create a copy of reminder to another inventory",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="reminder created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder created")
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
     *         description="reminder not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="reminder with same type and context has been used",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder with same type and context has been used or inventory not found")
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
    public function copy_reminder(Request $request){
        try{
            $user_id = $request->user()->id;

            $validator = Validation::getValidateReminder($request,'create_copy');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $list_inventory_id = explode(",", $request->list_inventory_id);
                $total_inventory = count($list_inventory_id);
                $total_success = 0;
                $names = [];

                foreach ($list_inventory_id as $id) {
                    $is_exist = ReminderModel::getReminderByInventoryIdReminderTypeReminderContext($id, $request->reminder_type, $request->reminder_context, $user_id);

                    if (!$is_exist) {
                        $inventory = InventoryModel::getInventoryNameById($id);    
                        if ($inventory) {
                            $res = ReminderModel::createReminder($id, $request->reminder_desc, $request->reminder_type, $request->reminder_context, $user_id);

                            if ($res) {
                                $total_success++;
                                $names[] = $inventory->inventory_name;
                            }
                        }
                    } 
                }

                $list_inventory_name = '';
                $count_names = count($names);

                if ($count_names === 1) {
                    $list_inventory_name = $names[0];
                } elseif ($count_names === 2) {
                    $list_inventory_name = $names[0] . ' and ' . $names[1];
                } elseif ($count_names > 2) {
                    $list_inventory_name = implode(', ', array_slice($names, 0, -1));
                    $list_inventory_name .= ', and ' . end($names);
                }
                
                if($total_success > 0){
                    // History
                    Audit::createHistory('Create Reminder', "$request->reminder_desc for inventory $list_inventory_name", $user_id);

                    return response()->json([
                        'status' => 'success',
                        'message' => "reminder created for inventory : $list_inventory_name",
                    ], Response::HTTP_CREATED);
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'reminder with same type and context has been used to all selected inventory',
                    ], Response::HTTP_CONFLICT);
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
     *     path="/api/v1/reminder/re_remind",
     *     summary="Create a reminder",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="reminder re-executed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder re-executed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="protected route need to include sign in token as authorization bearer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="you need to include the authorization token from login | permission denied. only admin can use this request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="reminder not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder not found")
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
    public function post_re_remind(Request $request)
    {
        try{
            $user_id = $request->user()->id;
            $check_admin = AdminModel::find($user_id);

            if($check_admin){
                $id = $request->reminder_id;
                $reminder = ReminderModel::getReminderJob($id);

                if($reminder){
                    $deleted = ScheduleMarkModel::where('reminder_id',$id)->delete();

                    if($deleted > 0){
                        ScheduleMarkModel::create([
                            'id' => Generator::getUUID(), 
                            'reminder_id' => $id,
                            'last_execute' => date('Y-m-d H:i:s'), 
                        ]);

                        $message = "Hello $reminder->username, your inventory $reminder->inventory_name has remind $reminder->reminder_desc";

                        if($reminder->telegram_user_id){
                            $response = Telegram::sendMessage([
                                'chat_id' => $reminder->telegram_user_id,
                                'text' => $message,
                                'parse_mode' => 'HTML'
                            ]);
                        }
                        if($reminder->line_user_id){
                            LineMessage::sendMessage('text',$message,$reminder->line_user_id);
                        }
                        if($reminder->firebase_fcm_token){
                            $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                            $messaging = $factory->createMessaging();
                            $message = CloudMessage::withTarget('token', $reminder->firebase_fcm_token)
                                ->withNotification(Notification::create($message, $id))
                                ->withData([
                                    'id_context' => $id,
                                ]);
                            $response = $messaging->send($message);
                        }

                        return response()->json([
                            'status' => 'success',
                            'message' => 'reminder re-executed',
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("not_found", 'reminder'),
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'status' => 'failed',
                        'message' => Generator::getMessageTemplate("not_found", 'reminder'),
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("permission", 'admin'),
                ], Response::HTTP_UNAUTHORIZED);
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
     *     path="/api/v1/reminder/{id}",
     *     summary="Delete reminder by id",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Reminder ID",
     *         example="e1288783-a5d4-1c4c-2cd6-0e92f7cc3bf9",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="reminder deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder deleted")
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
     *         description="reminder not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failed"),
     *             @OA\Property(property="message", type="string", example="reminder not found")
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
    public function delete_reminder_by_id(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            $reminder = ReminderModel::select('reminder_desc','inventory_name')
                ->join('inventory','inventory.id','=','reminder.inventory_id')
                ->where('reminder.id',$id)
                ->first();

            $res = ReminderModel::where('created_by', $user_id)
                ->where('id',$id)
                ->delete();

            if($reminder && $res > 0){
                // History
                Audit::createHistory('Delete Reminder', "$reminder->reminder_desc for inventory $reminder->inventory_name", $user_id);

                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permentally delete", 'reminder'),
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => Generator::getMessageTemplate("not_found", 'reminder'),
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
