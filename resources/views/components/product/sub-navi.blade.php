<div id="productManage">
    <!-- 標題 -->
    <h3 class="mb-3">商品名稱</h3>

    <!-- 功能按鈕群 -->
    <div class="btn-group pm_btnGroup" role="group">
        <a href="" class="nav-link pt-0">
            <span class="icon -open_eye"><span class="bi bi-eye-fill"></span></span>
            <!-- 不公開改成下面 -->
            <!-- <span class="icon -close_eye"><span class="bi bi-eye-slash-fill"></span></span> -->
            <span class="label">公開</span>
        </a>
        <a href="" class="nav-link pt-0">
            <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
            <span class="label">前往該商品</span>
        </a>
        <a href="" class="nav-link pt-0">
            <span class="icon"><i class="bi bi-files"></i></span>
            <span class="label">複製</span>
        </a>
        <a href="" class="nav-link pt-0">
            <span class="icon"><i class="bi bi-trash"></i></span>
            <span class="label">刪除商品</span>
        </a>
    </div>

    <!-- Navbar -->
    <ul class="nav nav-tabs pm_navbar" role="tablist">
        <li class="nav-item">
            <a class="nav-link @if ('ProductController@spus_edit'==$ctrl) active @endif" href="{{ wRoute('backend.product.spus_edit', ['id' => 1]) }}">商品資訊</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if ('ProductInfoController@index'==$ctrl) active @endif" href="{{ wRoute('backend.product.info', ['id' => 1]) }}">商品介紹</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if ('ProductInfoController@spec'==$ctrl) active @endif" href="{{ wRoute('backend.product.spec', ['id' => 1]) }}">規格說明</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if ('ProductInfoController@productReturn'==$ctrl) active @endif"
                href="{{ wRoute('backend.product.return', ['id' => 1]) }}">退換貨須知</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if ('ProductInfoController@ship'==$ctrl) active @endif" href="{{ wRoute('backend.product.ship', ['id' => 1]) }}">物流</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if ('ProductInfoController@setting'==$ctrl) active @endif" href="{{ wRoute('backend.product.setting', ['id' => 1]) }}">設定</a>
        </li>
    </ul>
    <hr class="narbarBottomLine">

    <!-- 以下放入子頁內容 -->

</div>
@once
    @push('styles')
        <style>
            /* 功能按鈕群 */
            .pm_btnGroup a span.icon {
                margin-right: -5px;
            }

            .icon.-close_eye+span.label::before {
                content: '不';
            }

            /* Navbar */
            .pm_navbar {
                border-bottom: 0px;
            }

            .pm_navbar.nav-tabs .nav-link.active,
            .pm_navbar.nav-tabs .nav-link.active:focus,
            .pm_navbar.nav-tabs .nav-link.active:hover {
                background-color: transparent;
                border-width: 0;
                border-bottom: 3px solid #999999;
                margin-bottom: -2px;
                color: #007bff;
            }

            .pm_navbar.nav-tabs .nav-link:focus,
            .pm_navbar.nav-tabs .nav-link:hover {
                border-color: transparent;
            }

            .pm_navbar a.nav-link {
                color: #999999;
            }

            .pm_navbar+.narbarBottomLine {
                margin: 0 -30px;
            }

        </style>
    @endpush
    @push('scripts')
        <script>
            $(function() {

                // 公開/不公開
                $('.pm_btnGroup > a:first-child').on('click', function() {
                    if ($(this).children('.icon').hasClass('-open_eye')) {
                        $(this).children('.icon').removeClass('-open_eye').addClass('-close_eye');
                        $(this).find('.bi ').removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
                    } else {
                        $(this).children('.icon').removeClass('-close_eye').addClass('-open_eye');
                        $(this).find('.bi ').removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
                    }
                });
            });

        </script>
    @endpush
@endonce
