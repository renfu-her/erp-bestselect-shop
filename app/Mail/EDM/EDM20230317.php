<?php

namespace App\Mail\EDM;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EDM20230317 extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('挺在地 ❗阿里山茶、玉女小番茄 鮮送到府⚡最高再拿100元優惠👉')
            ->view('emails.edm.edm20230317')->with($this->data);
    }
}
