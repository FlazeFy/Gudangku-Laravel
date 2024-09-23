<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\UserMail;
use Illuminate\Support\Facades\Mail;

use App\Helpers\Generator;
use App\Models\FailedJob;

class UserMailer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $context;
    protected $body;
    protected $username;
    protected $receiver;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($context, $body, $username, $receiver)
    {
        $this->context = $context;
        $this->body = $body;
        $this->username = $username;
        $this->receiver = $receiver;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $email = new UserMail($this->context, $this->body, $this->username);
            Mail::to($this->receiver)->send($email);
        } catch (\Exception $e) {
            $obj = [
                'message' => 'something wrong. please contact admin', 
                'stack_trace' => $e->getTraceAsString(), 
                'file' => $e->getFile(), 
                'line' => $e->getLine(), 
            ];
            FailedJob::create([
                'id' => Generator::getUUID(), 
                'type' => "token", 
                'status' => "failed",  
                'payload' => json_encode($obj),
                'created_at' => date("Y-m-d H:i:s"), 
            ]);
        }
    }
}
