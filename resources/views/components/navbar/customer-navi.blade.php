<h2>會員專區 - {{ '烏梅' }}
    <span class="fs-5 text-secondary">（
        {{ 'M00000123' }}<button type="button" data-bs-toggle="tooltip" title="複製" 
        data-mcode="{{ 'M00000123' }}"
        class="-copy icon icon-btn fs-5 text-primary rounded-circle border-0 p-2">
            <i class="bi bi-clipboard2-check"></i>
        </button>）
    </span>
</h2>

<ul class="nav pm_navbar" role="tablist">
    <li class="nav-item">
        <a href="{{ Route('cms.customer.order', ['id' => $customer], true) }}"
            class="nav-link {{ isActive('order', $route_name) }}">我的訂單</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.customer.coupon', ['id' => $customer], true) }}"
            class="nav-link {{ isActive('coupon', $route_name) }}">我的優惠卷</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.customer.dividend', ['id' => $customer], true) }}"
            class="nav-link {{ isActive('dividend', $route_name) }}">我的鴻利</a>
    </li>
    {{-- <li class="nav-item"> --}}
    {{-- <a href="#" class="nav-link ">個人資料</a> --}}
    {{-- </li> --}}
    <li class="nav-item">
        <a href="#" class="nav-link ">推薦註冊</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.customer.address', ['id' => $customer], true) }}"
            class="nav-link {{ isActive('address', $route_name) }}">地址管理</a>
    </li>
    <li class="nav-item">
        <a href="#" class="nav-link ">身份驗證</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.customer.bonus', ['id' => $customer], true) }}"
            class="nav-link {{ isActive('bonus', $route_name) }}">分潤</a>
    </li>
</ul>
<hr class="narbarBottomLine mb-3">


@once
    @push('sub-scripts')
        <script>
            $('button.-copy').off('click').on('click', function() {
                const mcode = $(this).data('mcode');
                if (navigator && navigator.clipboard) {
                    navigator.clipboard.writeText(mcode)
                        .then(() => {
                            toast.show('已複製頁面連結至剪貼簿', {
                                type: 'success'
                            });
                        }).catch((err) => {
                            console.error('剪貼簿錯誤', err);
                            toast.show('請手動複製連結：<br>' + mcode, {
                                title: '發生錯誤',
                                type: 'danger'
                            });
                        });
                } else {
                    toast.show('請手動複製連結：<br>' + mcode, {
                        title: '不支援剪貼簿功能',
                        type: 'danger'
                    });
                }
            });
        </script>
    @endpush
@endonce