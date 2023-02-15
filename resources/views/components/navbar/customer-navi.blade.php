<h2>會員專區 - {{ $customer_data->name }}
    <span class="fs-5 text-secondary">（
        {{ $customer_data->sn }}<button type="button" data-bs-toggle="tooltip" title="複製" 
        data-mcode="{{ $customer_data->sn }}"
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
            class="nav-link {{ isActive('dividend', $route_name) }}">我的購物金</a>
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
                copyToClipboard(mcode, '已複製Mcode至剪貼簿', `請手動複製Mcode：${mcode}`);
            });
        </script>
    @endpush
@endonce