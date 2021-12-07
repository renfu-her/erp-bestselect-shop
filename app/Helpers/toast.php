<?php

if (!function_exists('wToast')) {
    /**
     * 吐司訊息
     * @param String $content 內容
     * @param array $options [type 類型, title 標題, subTitle 副標題, Toast選項]
     */
    function wToast(String $content, array $options = [] )
    {
        $merged = (object) array_merge((array) ['content'=>$content], (array) $options);
        if(!isset($merged->delay)){
            $merged->delay = 3000;
        }
        if (!isset($merged->type)) {
            $merged->type = 'primary';
            // 警告錯誤用 danger
        }
        request()->session()->flash('toast_status', $merged);
    }
}
