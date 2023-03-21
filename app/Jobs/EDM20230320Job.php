<?php

namespace App\Jobs;

use App\Enums\Globals\Status;
use App\Mail\EDM\EDM20230320;
use App\Models\MailSendRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EDM20230320Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new EDM20230320());
        MailSendRecord::updateOrCreateData($this->email, EDM20230320::class, Status::success());
    }

    public function failed($exception)
    {
        MailSendRecord::updateOrCreateData($this->email, EDM20230320::class, Status::fail(), $exception->getMessage());
    }
}
