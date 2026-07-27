<?php

namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewInventoryMail extends Mailable
{
    use Queueable, SerializesModels;
    public $context;
    public $body;
    public $username;

    public function __construct($context, $body, $username)
    {
        $this->context = $context;
        $this->body = $body;
        $this->username = $username;
    }

    public function envelope()
    {
        return new Envelope(
            subject: '[Inventory] New Item has added',
        );
    }

    public function build()
    {
        return $this->view('components.email.new_inventory')
            ->with([
                'context' => $this->context,
                'body' => $this->body,
                'username' => $this->username,
            ]);
    }

    public function attachments()
    {
        return [];
    }
}
