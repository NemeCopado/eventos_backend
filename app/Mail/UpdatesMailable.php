<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpdatesMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $files;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body, $files)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->files = $files;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->view('emails.updates')->subject($this->subject);
        if ($this->files){
            foreach ($this->files as $file){
                $this->attach($file->getRealPath(), [
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ]);
            }
        }
        return $this;
    }
}
