<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class Mail extends Mailable

{
    use Queueable, SerializesModels;

    public $details;


    /**
     * Create a new message instance.
     *
     * @return void

     */

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        $view = $this->details['view'];
        $subject = $this->details['subject'];
        $body = $this->details['body'];
        
        return $this->subject($subject)->view($view,["body"=> $body]);
    }

}