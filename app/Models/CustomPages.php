<?php

namespace App\Models;

use App\Enums\FrontEnd\CustomPageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * 自訂頁面
 */
class CustomPages extends Model
{
    use HasFactory;

    protected $table = 'csp_custom_pages';
    protected $fillable = [
        'id',
        'page_name',
        'url',
        'title',
        'desc',
        'prd_sale_channels_id_fk',
        'usr_users_id_fk',
        'type',
        'csp_html_type_fk',
    ];

    public $timestamps = true;

    public static function storeCustomPages(?array $input)
    {
        $page_name = Arr::get($input, 'page_name');
        $url = Arr::get($input, 'url');
        $title = Arr::get($input, 'title');
        $desc = Arr::get($input, 'desc');
        $prd_sale_channels_id_fk = Arr::get($input, 'sale_channel', '1');
        $type = Arr::get($input, 'type');
        $content = Arr::get($input, 'content');
        $head = Arr::get($input, 'head');
        $body = Arr::get($input, 'body');
        $script = Arr::get($input, 'script');
        $usr_users_id_fk = Arr::get($input, 'usr_users_id_fk');

        if ($type === CustomPageType::General) {
            $html_id = DB::table('csp_general_html')
                            ->insertGetId([
                                'content' => $content,
                            ]);

        } elseif ($type === CustomPageType::Activity) {
            $html_id = DB::table('csp_activity_html')
                            ->insertGetId([
                                'head' => $head,
                                'body' => $body,
                                'script' => $script,
                            ]);
        }

        self::create([
            'page_name' => $page_name,
            'url' => $url ? trim($url) : trim($page_name),
            'title' => $title ? trim($title) : trim($page_name),
            'desc' => $desc ?? trim($page_name),
            'prd_sale_channels_id_fk' => $prd_sale_channels_id_fk,
            'usr_users_id_fk' => $usr_users_id_fk,
            'type' => $type,
            'csp_html_type_fk' => $html_id,
//                'link' => $link,
        ]);
    }

    public static function getDataListById(int $id)
    {
        $query = DB::table('csp_custom_pages')->where('csp_custom_pages.id', '=', $id);
        if (DB::table('csp_custom_pages')
            ->where([
                ['type', '=', CustomPageType::General],
                ['csp_custom_pages.id', '=', $id],
            ])->exists()) {
            $query->leftJoin('csp_general_html', 'csp_custom_pages.csp_html_type_fk', '=', 'csp_general_html.id')
                ->select([
                    'content',
                ]);
        } else {
            $query->leftJoin('csp_activity_html', 'csp_custom_pages.csp_html_type_fk', '=', 'csp_activity_html.id')
                ->select([
                    'head',
                    'body',
                    'script',
                ]);
        }

        $dataList = $query->addSelect([
                                    'csp_custom_pages.id',
                                    'page_name',
                                    'url',
                                    'title',
                                    'desc',
                                    'prd_sale_channels_id_fk',
                                    'type',
                                    'csp_html_type_fk',
                                ])
                                ->get()
                                ->first();

        return $dataList;
    }

    public static function updateCustomPages(array $input)
    {
        $originalCustomPages = self::find($input['id']);
        $page_name = Arr::get($input, 'page_name');
        $url = Arr::get($input, 'url');
        $title = Arr::get($input, 'title');
        $desc = Arr::get($input, 'desc');
        $prd_sale_channels_id_fk = Arr::get($input, 'sale_channel', '1');
        $type = Arr::get($input, 'type');
        $content = Arr::get($input, 'content');
        $head = Arr::get($input, 'head');
        $body = Arr::get($input, 'body');
        $script = Arr::get($input, 'script');
        $usr_users_id_fk = Arr::get($input, 'usr_users_id_fk');

        if ($type === $originalCustomPages->getOriginal('type')) {
            if ($type === CustomPageType::General) {
                DB::table('csp_general_html')
                    ->where('id', '=', $originalCustomPages->csp_html_type_fk)
                    ->update([
                        'content' => $content,
                    ]);
                $html_id = $originalCustomPages->csp_html_type_fk;
            } elseif ($type === CustomPageType::Activity) {
                DB::table('csp_activity_html')
                    ->where('id', '=', $originalCustomPages->csp_html_type_fk)
                    ->update([
                        'head'   => $head,
                        'body'   => $body,
                        'script' => $script,
                    ]);
                $html_id = $originalCustomPages->csp_html_type_fk;
            }
        } else {
            if ($type === CustomPageType::General) {
                DB::table('csp_activity_html')
                    ->where('id', '=', $originalCustomPages->csp_html_type_fk)
                    ->delete();
                $html_id = DB::table('csp_general_html')
                            ->insertGetId([
                                'content' => $content,
                            ]);
            } elseif ($type === CustomPageType::Activity) {
                DB::table('csp_general_html')
                    ->where('id', '=', $originalCustomPages->csp_html_type_fk)
                    ->delete();
                $html_id = DB::table('csp_activity_html')
                            ->insertGetId([
                                'head'   => $head,
                                'body'   => $body,
                                'script' => $script,
                            ]);
            }
        }

        $originalCustomPages->update([
            'page_name' => $page_name,
            'url' => $url ? trim($url) : trim($page_name),
            'title' => $title ? trim($title) : trim($page_name),
            'desc' => $desc ?? trim($page_name),
            'prd_sale_channels_id_fk' => $prd_sale_channels_id_fk,
            'usr_users_id_fk' => $usr_users_id_fk,
            'type' => $type,
            'csp_html_type_fk' => $html_id,
            //                'link' => $link,
        ]);
    }

    /**
     * @param string $pathName csp_custom_pages' column url
     * @param  string  $id table csp_custom_pages的 primary ID
     * 回傳自訂頁面的URL完整路徑
     * @return string
     */
    public static function getFullUrlPath(string $pathName, string $id)
    {
        return frontendUrl() . 'event/' . $id . '/' . $pathName;
    }
}
