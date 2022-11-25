<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrdCreditCard extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'crd_credit_cards';
    protected $guarded = [];
    public $timestamps = false;


    public static function get_credit_card_info(string $card_number)
    {
        $card_number = trim($card_number);
        $info = (object)[
            'card_type_code' => null,
            'card_type' => null,
        ];
        if(in_array(strlen($card_number), [15, 16])) {
            $card = null;
            if(strlen($card_number) == 16) {
                $substr = substr($card_number, 0, 1);
                if($substr == 4){
                    $card = CrdCreditCard::where('title', 'VISA')->orderBy('id', 'asc')->first();

                } else if($substr == 5) {
                    $s_substr = substr($card_number, 0, 2);

                    if ( in_array($s_substr, range(51, 55)) ) {
                        $card = CrdCreditCard::where('title', 'MASTER')->orderBy('id', 'asc')->first();
                    }

                } else if($substr == 3) {
                    $s_substr = substr($card_number, 0, 4);

                    if ( in_array($s_substr, range(3528, 3589)) ) {
                        $card = CrdCreditCard::where('title', 'JCB')->orderBy('id', 'asc')->first();
                    }

                } else if($substr == 6) {
                    $s_substr = substr($card_number, 0, 2);

                    if ($s_substr == 62) {
                        $card = CrdCreditCard::where('title', 'UnionPay')->orderBy('id', 'asc')->first();
                    }
                }

            } else if(strlen($card_number) == 15) {
                $substr = substr($card_number, 0, 1);
                if($substr == 3) {
                    $s_substr = substr($card_number, 0, 3);

                    if ( in_array($s_substr, range(340, 379)) ) {
                        $card = CrdCreditCard::where('title', 'AMEX')->orderBy('id', 'asc')->first();
                    }
                }
            }

            if($card){
                $info = (object)[
                    'card_type_code' => $card->id,
                    'card_type' => $card->title,
                ];
            }
        }

        return $info;
    }
}
