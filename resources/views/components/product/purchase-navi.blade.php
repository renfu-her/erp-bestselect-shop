{{-- 功能按鈕群 --}}
<div class="btn-group pm_btnGroup" role="group">
    <a href="#" class="nav-link">
        <span class="icon"><i class="bi bi-printer"></i></span>
        <span class="label">列印</span>
    </a>
    <a href="#" class="nav-link">
        <span class="icon"><i class="bi bi-trash"></i></span>
        <span class="label">刪除採購單</span>
    </a>
</div>

{{-- Tabs Navbar --}}
<ul class="nav pm_navbar" role="tablist">
    <li class="nav-item">
        <a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}"
            class="nav-link {{ isActive('edit', $route_name) }} {{ isActive('pay-deposit', $route_name) }} {{ isActive('pay-final', $route_name) }}">採購單資訊</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.purchase.log', ['id' => $id], true) }}"
            class="nav-link {{ isActive('log', $route_name) }}">變更紀錄</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.purchase.inbound', ['id' => $id], true) }}"
            class="nav-link {{ isActive('inbound', $route_name) }}">入庫審核</a>
    </li>
    <li class="nav-item">
        <a href="#"
            class="nav-link disabled">物流資料</a>
    </li>
    <li class="nav-item">
        <a href="#"
            class="nav-link disabled">結單管理</a>
    </li>
    <li class="nav-item">
        <a href="#"
            class="nav-link disabled">退貨</a>
    </li>
</ul>
<hr class="narbarBottomLine mb-3">
