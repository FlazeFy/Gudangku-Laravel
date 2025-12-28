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
use App\Models\GoogleTokensModel;
// Helpers
use App\Helpers\Audit;
use App\Helpers\Generator;
use App\Helpers\LineMessage;
use App\Helpers\Validation;

// Service
use App\Service\GoogleCalendar;

class Commands extends Controller
{
    private $module;
    private $firebaseMessaging;

    public function __construct()
    {
        $this->module = "reminder";
        $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
        $this->firebaseMessaging = $factory->createMessaging();
    }

    /**
     * @OA\POST(
     *     path="/api/v1/reminder",
     *     summary="Post Create Reminder",
     *     description="This request is used to create a reminder by using given `reminder_type`, `reminder_context`, `inventory_id`, and `reminder_desc`. This request interacts with the MySQL database, sync with Google Calendar, broadcast message using Firebase FCM and Telegram, has a protected routes, and audited activity (history).",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reminder_type","reminder_context","reminder_desc","inventory_id"},
     *             @OA\Property(property="reminder_type", type="string", example="Every Week"),
     *             @OA\Property(property="reminder_context", type="string", example="Every Day 1"),
     *             @OA\Property(property="reminder_desc", type="string", example="testing reminder"),
     *             @OA\Property(property="send_demo", type="boolean", example=true),
     *             @OA\Property(property="inventory_id", type="string", example="5994fb22-30ae-c088-3543-8d12f487539a"),
     *         )
     *     ),
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
    public function postReminder(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateReminder($request,'create');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $reminder_type = $request->reminder_type;
                $reminder_context = $request->reminder_context;
                $inventory_id = $request->inventory_id;
                $reminder_desc = $request->reminder_desc;

                // Get reminder data
                $is_exist = ReminderModel::getReminderByInventoryIdReminderTypeReminderContext($inventory_id,$reminder_type,$reminder_context,$user_id);
                if(!$is_exist){
                    // Get inventory name by ID
                    $inventory = InventoryModel::getInventoryNameById($request->inventory_id);
                    if($inventory){
                        // Create reminder
                        ReminderModel::createReminder($inventory_id, $reminder_desc, $reminder_type, $reminder_context, $user_id);

                        // Get google token by user
                        $google_token = GoogleTokensModel::getGoogleTokensByUserId($user_id);
                        $reminder_desc = "$reminder_desc for inventory $inventory->inventory_name";
                        if($google_token){
                            $access_token = $google_token->access_token;

                            // Define event in google calendar
                            if($reminder_type == 'Every Day'){
                                $hour = (int) str_replace("Every ", "", $reminder_context);
                                $start = now()->setTime($hour, 0)->toRfc3339String();

                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc, $start,'DAILY');
                            } else if ($reminder_type == 'Every Week') {
                                $day = (int) str_replace("Every Day ", "", $reminder_context);
                                $weekdayMap = [1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU'];
                                $byDay = $weekdayMap[$day];
                                $carbonDayMap = ['MO' => 'Monday','TU' => 'Tuesday','WE' => 'Wednesday','TH' => 'Thursday','FR' => 'Friday','SA' => 'Saturday','SU' => 'Sunday',];
                                $start = now()->next($carbonDayMap[$byDay])->setTime(4, 0)->toRfc3339String();
                            
                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc, $start, 'WEEKLY', $byDay);
                            } else if($reminder_type == 'Every Month'){
                                $day = (int) str_replace("Every ", "", $reminder_context);
                                $start = now()->startOfMonth()->addDays($day - 1)->setTime(9, 0)->toRfc3339String();

                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc,$start,'MONTHLY',null,$byMonthDay = $day);
                            } else if($reminder_type == 'Every Year'){
                                [$day, $month] = explode(' ', str_replace("Every ", "", $reminder_context));
                                $monthMap = ['Jan'=>1, 'Feb'=>2, 'Mar'=>3, 'Apr'=>4, 'May'=>5, 'Jun'=>6,'Jul'=>7, 'Aug'=>8, 'Sep'=>9, 'Oct'=>10, 'Nov'=>11, 'Dec'=>12];
                                $monthNumber = $monthMap[$month];
                                $start = now()->setDate(now()->year, $monthNumber, (int)$day)->setTime(9, 0)->toRfc3339String();

                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc,$start,'YEARLY',null,null,$byMonth = $monthNumber,$byMonthDay = (int)$day);
                            }         
                        }               

                        // History
                        Audit::createHistory('Create Reminder', $reminder_desc, $user_id);

                        $msg = "You have create a reminder. Here's the reminder description for [DEMO]. $reminder_desc";
                        if($request->send_demo == "true"){
                            // Demo Reminder
                            // Get user data
                            $user = UserModel::getSocial($user_id);
                            // Send Firebase notification (mobile)
                            if($user->firebase_fcm_token){
                                $fcm = CloudMessage::withTarget('token', $user->firebase_fcm_token)->withNotification(Notification::create($msg));
                                $response = $messaging->send($fcm);
                            }
                            if($user && $user->telegram_is_valid == 1 && $user->telegram_user_id){
                                // Check if user Telegram ID is valid
                                if(TelegramMessage::checkTelegramID($user->telegram_user_id)){
                                    // Send telegram message
                                    $response = Telegram::sendMessage([
                                        'chat_id' => $user->telegram_user_id,
                                        'text' => $msg,
                                        'parse_mode' => 'HTML'
                                    ]);
                                } else {
                                    // Reset telegram from user account if not valid
                                    UserModel::updateUserById([ 'telegram_user_id' => null, 'telegram_is_valid' => 0],$user_id);
                                }
                            }
                        }

                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => Generator::getMessageTemplate("create", $this->module),
                        ], Response::HTTP_CREATED);
                    } else {
                        return response()->json([
                            'status' => 'failed',
                            'message' => Generator::getMessageTemplate("not_found", "inventory"),
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
     * @OA\PUT(
     *     path="/api/v1/reminder/{id}",
     *     summary="Put Update Reminder By ID",
     *     description="This request is used to update a reminder by using given reminder's `id` and `inventory_id`. The updated fields are `reminder_type`, `reminder_context`, and `reminder_desc`. This request interacts with the MySQL database, sync with Google Calendar, has a protected routes, and audited activity (history).",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reminder_type","reminder_context","reminder_desc","inventory_id"},
     *             @OA\Property(property="reminder_type", type="string", example="Every Week"),
     *             @OA\Property(property="reminder_context", type="string", example="Every Day 1"),
     *             @OA\Property(property="reminder_desc", type="string", example="testing reminder"),
     *             @OA\Property(property="inventory_id", type="string", example="5994fb22-30ae-c088-3543-8d12f487539a")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="reminder updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="reminder updated")
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
    public function putReminderByID(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            // Validate request body
            $validator = Validation::getValidateReminder($request,'update');
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $reminder_type = $request->reminder_type;
                $reminder_context = $request->reminder_context;
                $inventory_id = $request->inventory_id;
                $reminder_desc = $request->reminder_desc;

                // Get reminder data
                $is_exist = ReminderModel::getReminderByInventoryIdReminderTypeReminderContext($inventory_id,$reminder_type,$reminder_context,$user_id,$id);
                if(!$is_exist){
                    // Update reminder
                    $res = ReminderModel::updateReminderByID([
                        'reminder_type' => $reminder_type,
                        'reminder_context' => $reminder_context,
                        'reminder_desc' => $reminder_desc]
                    , $id, $user_id);

                    if($res > 0){
                        // Get google token by user
                        $google_token = GoogleTokensModel::getGoogleTokensByUserId($user_id);
                        
                        // Get inventory name by ID
                        $inventory = InventoryModel::getInventoryNameById($inventory_id);
                        if($inventory === null){
                            return response()->json([
                                'status' => 'failed',
                                'message' => Generator::getMessageTemplate("not_found", "inventory"),
                            ], Response::HTTP_NOT_FOUND);
                        }
                            
                        if($google_token){
                            $reminder_desc = "$reminder_desc for inventory $inventory->inventory_name";
                            $access_token = $google_token->access_token;

                            // Define event in google calendar
                            if($reminder_type == 'Every Day'){
                                $hour = (int) str_replace("Every ", "", $reminder_context);
                                $start = now()->setTime($hour, 0)->toRfc3339String();

                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc, $start,'DAILY');
                            } else if ($reminder_type == 'Every Week') {
                                $day = (int) str_replace("Every Day ", "", $reminder_context);
                                $weekdayMap = [1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU'];
                                $byDay = $weekdayMap[$day];
                                $carbonDayMap = ['MO' => 'Monday','TU' => 'Tuesday','WE' => 'Wednesday','TH' => 'Thursday','FR' => 'Friday','SA' => 'Saturday','SU' => 'Sunday',];
                                $start = now()->next($carbonDayMap[$byDay])->setTime(4, 0)->toRfc3339String();
                            
                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc, $start, 'WEEKLY', $byDay);
                            } else if($reminder_type == 'Every Month'){
                                $day = (int) str_replace("Every ", "", $reminder_context);
                                $start = now()->startOfMonth()->addDays($day - 1)->setTime(9, 0)->toRfc3339String();

                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc,$start,'MONTHLY',null,$byMonthDay = $day);
                            } else if($reminder_type == 'Every Year'){
                                [$day, $month] = explode(' ', str_replace("Every ", "", $reminder_context));
                                $monthMap = ['Jan'=>1, 'Feb'=>2, 'Mar'=>3, 'Apr'=>4, 'May'=>5, 'Jun'=>6,'Jul'=>7, 'Aug'=>8, 'Sep'=>9, 'Oct'=>10, 'Nov'=>11, 'Dec'=>12];
                                $monthNumber = $monthMap[$month];
                                $start = now()->setDate(now()->year, $monthNumber, (int)$day)->setTime(9, 0)->toRfc3339String();

                                GoogleCalendar::createRecurringEvent($access_token, $reminder_desc,$start,'YEARLY',null,null,$byMonth = $monthNumber,$byMonthDay = (int)$day);
                            }         
                        }               

                        // History
                        Audit::createHistory('Update Reminder', $reminder_desc, $user_id);

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
     *     summary="Post Create Copy Reminder",
     *     description="This request is used to create a copy reminder to another inventory by using given `list_inventory_id`, `reminder_context`, `reminder_type`, and `reminder_desc`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reminder_type","reminder_context","reminder_desc","list_inventory_id"},
     *             @OA\Property(property="reminder_type", type="string", example="Every Week"),
     *             @OA\Property(property="reminder_context", type="string", example="Every Day 1"),
     *             @OA\Property(property="reminder_desc", type="string", example="testing reminder"),
     *             @OA\Property(property="list_inventory_id", type="string", example="eb763050-ca6e-73e4-201d-935912ede04d,e0de852b-8a17-a450-0832-8a1154e1a71c")
     *         )
     *     ),
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
    public function postCopyReminder(Request $request){
        try{
            $user_id = $request->user()->id;

            // Validate request body
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
                    // Get reminder data
                    $is_exist = ReminderModel::getReminderByInventoryIdReminderTypeReminderContext($id, $request->reminder_type, $request->reminder_context, $user_id);

                    if (!$is_exist) {
                        // Get inventory name by ID
                        $inventory = InventoryModel::getInventoryNameById($id);    
                        if ($inventory) {
                            // Create reminder
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

                // Tidy up sentence
                if ($count_names === 1) {
                    $list_inventory_name = $names[0];
                } elseif ($count_names === 2) {
                    $list_inventory_name = $names[0] . ' and ' . $names[1];
                } elseif ($count_names > 2) {
                    $list_inventory_name = implode(', ', array_slice($names, 0, -1));
                    $list_inventory_name .= ', and ' . end($names);
                }
                
                if($total_success > 0){
                    // Create history
                    Audit::createHistory('Create Reminder', "$request->reminder_desc for inventory $list_inventory_name", $user_id);

                    // Return success response
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
     *     summary="Post Create Re-Reminder",
     *     description="This request is used to resend a reminder by given `reminder_id`. This request interacts with the MySQL database, broadcast using Firebase FCM, Telegram, and Line, has a protected routes, and audited activity (history).",
     *     tags={"Reminder"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reminder_id"},
     *             @OA\Property(property="reminder_id", type="string", example="5994fb22-30ae-c088-3543-8d12f487539a"),
     *         )
     *     ),
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
    public function postReRemind(Request $request)
    {
        try{
            $user_id = $request->user()->id;

            // Make user only admin can use this request
            $check_admin = AdminModel::find($user_id);
            if($check_admin){
                $id = $request->reminder_id;

                // Get reminder by ID
                $reminder = ReminderModel::getReminderJob($id);
                if($reminder){
                    // Hard delete schedule (reminder) mark
                    $deleted = ScheduleMarkModel::deleteScheduleMarkById($id);

                    if($deleted > 0){
                        // Re insert schedule (reminder) mark
                        ScheduleMarkModel::createScheduleMark($id);

                        $message = "Hello $reminder->username, your inventory $reminder->inventory_name has remind $reminder->reminder_desc";
                        if($reminder->telegram_user_id){
                            // Check if user Telegram ID is valid
                            if(TelegramMessage::checkTelegramID($user->telegram_user_id)){
                                // Send telegram message
                                $response = Telegram::sendMessage([
                                    'chat_id' => $reminder->telegram_user_id,
                                    'text' => $message,
                                    'parse_mode' => 'HTML'
                                ]);
                            } else {
                                // Reset telegram from user account if not valid
                                UserModel::updateUserById([ 'telegram_user_id' => null, 'telegram_is_valid' => 0],$user_id);
                            }
                        }

                        // Send line message
                        if($reminder->line_user_id){
                            LineMessage::sendMessage('text',$message,$reminder->line_user_id);
                        }
                        
                        // Send Firebase notification (mobile)
                        if($reminder->firebase_fcm_token){
                            $fcm = CloudMessage::withTarget('token', $reminder->firebase_fcm_token)
                                ->withNotification(Notification::create($message, $id))
                                ->withData(['id_context' => $id]);
                            $response = $this->firebaseMessaging->send($fcm);
                        }

                        // Return success response
                        return response()->json([
                            'status' => 'success',
                            'message' => 'reminder re-executed',
                        ], Response::HTTP_CREATED);
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
     *     summary="Hard Delete Reminder By ID",
     *     description="This request is used to permanently delete a reminder by given `id`. This request interacts with the MySQL database, has a protected routes, and audited activity (history).",
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
    public function hardDeleteReminderByID(Request $request, $id)
    {
        try{
            $user_id = $request->user()->id;

            // Get reminder by ID
            $reminder = ReminderModel::getReminderAndInventoryById($id,$user_id);
            
            // Hard delete reminder by ID
            $res = ReminderModel::hardDeleteReminder($id, $user_id);
            if($reminder && $res > 0){
                // Create history
                Audit::createHistory('Delete Reminder', "$reminder->reminder_desc for inventory $reminder->inventory_name", $user_id);

                // Return success response
                return response()->json([
                    'status' => 'success',
                    'message' => Generator::getMessageTemplate("permentally delete", $this->module),
                ], Response::HTTP_OK);
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
}
