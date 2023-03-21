<?php

namespace App\Mail\EDM;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EDM20230320 extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'hrefToGo' => 'https://bit.ly/3ZVwtue',
            'image' => "https://images-besttour.cdn.hinet.net/product_intro/imgs/137/5nJefdygvssWLG6eLzKFpcSk9BRQOs8FE1gC8qLP.webp"
        ];
        return $this
            ->subject('挺在地 ❗阿里山茶、玉女小番茄 鮮送到府⚡最高再拿100元優惠👉')
            ->view('emails.edm.edm20230317')->with($data);
    }
}
