<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use Illuminate\Support\Facades\Storage;
use App\Helpers\LineMessage;
use Telegram\Bot\Laravel\Facades\Telegram;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Service\FirebaseRealtime;
use App\Models\AdminModel;
use Telegram\Bot\FileUpload\InputFile;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     */
    public function testBasicExample(): void
    {
        $this->browse(function (Browser $browser) {
            $username = 'flazefy';
            $password = 'nopass123';
            $date = now()->format('Y-m-d_H-i-s');
            $baseUrl = "http://127.0.0.1:8000";

            // Step 1: Log in to the application
            $browser->visit("$baseUrl/login")
                ->type('username', $username)
                ->type('password', $password)
                ->press('#submit_btn')
                ->pause(3000);

            // Step 2: Pengguna menekan menu Stats (View total by item)
            $fileNames = [
                [
                    'file_name' => "Stats-$date-by_item",
                    'context' => 'Statistic By Item'
                ],
                [
                    'file_name' => "Stats-$date-by_price",
                    'context' => 'Statistic By Price'
                ]
            ];

            $browser->click('#nav_stats_btn')
                ->pause(3000)
                ->screenshot($fileNames[0]['file_name']);

            // Test Step 3 : Pengguna memilih toogle total (View total by price)
            $browser->script("
                const select = document.querySelector('#toogle_total');
                const options = Array.from(select.options);
                options.forEach((option, index) => {
                    if (index !== 0) {
                        option.selected = true;
                    } else {
                        option.selected = false;
                    }
                });
                select.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->pause(3000)
                ->screenshot($fileNames[1]['file_name']);

            $screenshots = ["tests/Browser/screenshots/".$fileNames[0]['file_name'].".png","tests/Browser/screenshots/".$fileNames[1]['file_name'].".png"];

            $admin = AdminModel::getAllContact();
            $firebaseRealtime = new FirebaseRealtime();

            foreach($admin as $dt){
                $message = "[ADMIN] Hello $dt->username, the system just run an audit stats. Here's the capture";
                    
                if($dt->telegram_user_id){
                    $response = Telegram::sendMessage([
                        'chat_id' => $dt->telegram_user_id,
                        'text' => $message,
                        'parse_mode' => 'HTML'
                    ]);
                }

                foreach($screenshots as $idxCapture => $capture){                    
                    if($dt->telegram_user_id){
                        $response = Telegram::sendPhoto([
                            'chat_id' => $dt->telegram_user_id,
                            'photo' => InputFile::create($capture, $fileNames[$idxCapture]['file_name']),
                            'caption' => $fileNames[$idxCapture]['context'],
                        ]);
                    }
                }

                if($dt->line_user_id){
                    LineMessage::sendMessage('text',"Error has been audited",$dt->line_user_id);
                }
                if($dt->firebase_fcm_token){
                    $factory = (new Factory)->withServiceAccount(base_path('/firebase/gudangku-94edc-firebase-adminsdk-we9nr-31d47a729d.json'));
                    $messaging = $factory->createMessaging();
                    $message = CloudMessage::withTarget('token', $dt->firebase_fcm_token)
                        ->withNotification(Notification::create("Error has been audited", $dt->id))
                        ->withData([
                            'id_context' => $dt->id,
                        ]);
                    $response = $messaging->send($message);
                }
    
                // Audit to firebase realtime
                $record = [
                    'context' => 'audit',
                    'context_id' => $dt->id,
                    'clean_type' => 'error',
                    'telegram_message' => $dt->telegram_user_id,
                    'line_message' => $dt->line_user_id,
                    'firebase_fcm_message' => $dt->firebase_fcm_token,
                ];
                $firebaseRealtime->insert_command('task_scheduling/audit/' . uniqid(), $record);
            }
    
            $firebaseService = new FirebaseStorage();
            foreach($screenshots as $idx => $capture){
                $firebaseUrl = $firebaseService->uploadFile($capture, "audit/stats", $fileNames[$idx]['file_name'].".pdf");
            }    
        });
    }
}
