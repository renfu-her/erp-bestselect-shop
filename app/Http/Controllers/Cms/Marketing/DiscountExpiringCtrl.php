<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Enums\Discount\DisMethod;
use App\Enums\Discount\DisStatus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use App\Models\Customer;
use App\Models\CustomerCoupon;
use App\Models\Order;


class DiscountExpiringCtrl extends Controller
{
    public function index(Request $request)
    {
        $cond = [];
        $query = $request->query();

        $data_per_page = getPageCount(Arr::get($query, 'data_per_page', 100));

        $cond['title'] = Arr::get($query, 'title');
        $cond['method_code'] = Arr::get($query, 'method_code');
        $cond['status_code'] = Arr::get($query, 'status_code', '');
        $cond['is_global'] = Arr::get($query, 'is_global');
        $cond['mail_sended'] = Arr::get($query, 'mail_sended', 'all');
        $cond['start_date'] = Arr::get($query, 'start_date', date('Y-m-d'));
        $cond['end_date'] = Arr::get($query, 'end_date');

        $status_code = $cond['status_code'] ? explode(',', $cond['status_code']) : null;

        $data_list = CustomerCoupon::discount_expiring(
                null,
                $cond['title'],
                $cond['method_code'],
                $status_code,
                $cond['is_global'],
                $cond['mail_sended'],
                $cond['start_date'],
                $cond['end_date'],
            )->paginate($data_per_page)->appends($query);

        $cond['method_code'] = $cond['method_code'] ? $cond['method_code'] : [];

        return view('cms.marketing.discount_expiring.list', [
            'form_action' => route('cms.discount_expiring.mail_send'),
            'dis_methods' => DisMethod::getValueWithDesc(),
            'dis_status' => DisStatus::getValueWithDesc(),
            'data_per_page' => $data_per_page,
            'cond' => $cond,
            'data_list' => $data_list,
        ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->isMethod('post')) {
            $request->merge([
                'id' => $id,
            ]);

            $request->validate([
                'id' => 'required|exists:usr_customer_coupon,id',
                'mail_subject' => 'required|string',
                'mail_content' => 'required|string',
            ]);

            DB::beginTransaction();

            try {
                CustomerCoupon::where('id', $id)->update([
                    'mail_subject' => request('mail_subject'),
                    'mail_content' => request('mail_content'),
                ]);

                DB::commit();

                wToast('到期通知信更新成功');
                return redirect(request('back_url'));

            } catch (\Exception $e) {
                DB::rollback();
                wToast(__('到期通知信更新失敗'), ['type' => 'danger']);

                return redirect()->back()->withInput();
            }

        } else {
            $customer_coupon = CustomerCoupon::findOrFail($id);

            return view('cms.marketing.discount_expiring.edit', [
                'form_action' => route('cms.discount_expiring.edit', ['id' => $id]),
                'back_url'=>url()->previous(),
                'customer_coupon' => $customer_coupon,
            ]);
        }
    }

    public function mail_send(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'required|exists:usr_customer_coupon,id',
            'id' => 'required|array',
            'id.*' => 'required|exists:usr_customer_coupon,id',
        ]);

        $compare = array_diff(request('selected'), request('id'));
        if(count($compare) == 0){
            try {
                // if(env('APP_ENV') == 'rel'){
                    foreach (request('id') as $key => $value) {
                        $customer_coupon = CustomerCoupon::find($value);
                        $customer = Customer::find($customer_coupon->customer_id);
                        $order_sn = $customer_coupon->from_order_id ? Order::find($customer_coupon->from_order_id)->sn : '';

                        $mail_content = $customer_coupon->mail_content;

                        $replace = [
                            '{$active_edate}' => $customer_coupon->active_edate,
                            '{$name}' => $customer->name,
                            '{$email}' => $customer->email,
                            '{$sn}' => $order_sn,
                        ];
                        foreach($replace as $key => $value){
                            $mail_content = str_replace($key, $value, $mail_content);
                        }

                        Mail::send('emails.discount_expiring.notice', [
                            'mail_content' => $mail_content,
                        ], function($mail) use ($customer_coupon, $customer) {
                            $mail->to($customer->email);
                            $mail->subject($customer_coupon->mail_subject);
                        });

                        $customer_coupon->update([
                            'mail_sended_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                // }

                wToast(__('到期通知信寄送成功'));

            } catch (\Exception $e) {
                wToast(__('到期通知信寄送失敗'), ['type' => 'danger']);
            }

            return redirect()->back();
        }

        wToast(__('到期通知信寄送失敗'), ['type' => 'danger']);
        return redirect()->back();
    }
}
