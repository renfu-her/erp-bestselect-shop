<ul class="nav pm_navbar" role="tablist">
    <li class="nav-item">
        <a href="#" class="nav-link ">我的訂單</a>
    </li>
    <li class="nav-item">
        <a href="#" class="nav-link ">我的優惠卷</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.customer.dividend', ['id' => $customer], true) }}" class="nav-link ">我的鴻利</a>
    </li>
    <li class="nav-item">
        <a href="#" class="nav-link ">個人資料</a>
    </li>
    <li class="nav-item">
        <a href="#" class="nav-link ">推薦註冊</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.customer.edit', ['id' => $customer], true) }}"
           class="nav-link {{ isActive('address', $route_name) }}">收件地址管理</a>
    </li>
    <li class="nav-item">
        <a href="#" class="nav-link ">身份驗證</a>
    </li>
</ul>
<hr class="narbarBottomLine mb-3">
