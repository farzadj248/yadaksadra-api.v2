<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendQueueEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    public $timeout = 7200; // 2 hours

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $subject = $this->details['subject'];
        $body = $this->details['body'];
        $users = $this->details['users'];

        foreach($users as $value){
            $email = $value['email'];
            $name = $value['name'];
            $view = $value['view'];
            
            \Mail::send($view, ["body"=>$body], function($message) use($email,$name,$subject){
                $message->to($email, $name)
                    ->subject($subject);
            });
        }
    }
}