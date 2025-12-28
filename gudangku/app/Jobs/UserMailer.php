<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

// Helper
use App\Helpers\Generator;
// Model
use App\Models\FailedJob;
// Mailer
use App\Mail\UserMail;

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
                'message' => Generator::getMessageTemplate("unknown_error", null), 
                'stack_trace' => $e->getTraceAsString(), 
                'file' => $e->getFile(), 
                'line' => $e->getLine(), 
            ];
            FailedJob::createFailedJob("token", $obj);
        }
    }
}
