<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\App;

class LocaleTest extends TestCase
{
    public function test_default_locale_is_tw()
    {
        $this->assertEquals('zh_TW', App::currentLocale());
    }

    public function test_fallback_locale_is_en()
    {
        $this->assertEquals('en', $this->app->getFallbackLocale());
    }

    public function test_trans_string_by_key()
    {
        $this->assertEquals('頁面不存在' ,__('Not Found'));
    }

    public function test_404_not_found_page_is_zh_TW()
    {
        $response = $this->get('aaaaaaa');
        $this->followRedirects($response)
            ->assertSeeText('頁面不存在');
    }
}
