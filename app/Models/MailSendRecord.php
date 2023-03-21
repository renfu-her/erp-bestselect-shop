<?php

namespace App\Models;

use App\Enums\Globals\Status;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailSendRecord extends Model
{
    use HasFactory;
    protected $table = 'mail_send_record';
    protected $guarded = [];

    public static function updateOrCreateData($email, $event, $status, $msg = null) {
        if (!$status instanceof Status) {
            return ['success' => 0, 'error_msg' => 'status error'];
        }
        return IttmsDBB::transaction(function () use ($email, $event, $status, $msg) {
            $id = self::updateOrCreate([
                "email" => $email,
                'event' => $event,
            ], [
                'status' => $status->value,
                'msg' => $msg,
            ])->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }
}
