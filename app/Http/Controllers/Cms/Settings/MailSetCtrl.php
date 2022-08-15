<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Enums\Globals\SharedPreference\Category;
use App\Http\Controllers\Controller;
use App\Models\SharedPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//通知信管理
class MailSetCtrl extends Controller
{
    public function index(Request $request)
    {
        $mail_set_list = DB::table(app(SharedPreference::class)->getTable(). ' as sp')
            ->where('sp.category', '=', Category::mail()->value)
            ->orderBy('sp.order')
            ->orderBy('sp.id')
            ->get();

        return view('cms.settings.mail_set.index', [
            'mail_set_list' => $mail_set_list,
            'formAction' => Route('cms.mail_set.edit', []),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'category.*' => 'required|string',
            'event.*' => 'required|string',
            'feature.*' => 'required|string',
            'status.*' => 'required|int',
        ]);

        $d = $request->all();
        if (0 < count($d['category'])) {
            for($i = 0; $i < count($d['category']); $i++) {
                SharedPreference::updateOrCreate([
                    'category' => $d['category'][$i]
                    , 'event' => $d['event'][$i]
                    , 'feature' => $d['feature'][$i]
                ], [
                    'status' => $d['status'][$i]
                ]);
            }
        }
        wToast('儲存成功');
        return redirect(Route('cms.mail_set.index', [], true));
    }
}
