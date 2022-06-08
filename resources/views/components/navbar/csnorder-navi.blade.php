{{-- Tabs Navbar --}}
<ul class="nav pm_navbar" role="tablist">
    <li class="nav-item">
        <a href="{{ Route('cms.consignment-order.edit', ['id' => $id], true) }}"
            class="nav-link {{ isActive('cms.consignment-order.edit', $route_name) }} }}">寄倉訂購單資訊</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.logistic.changeLogisticStatus', ['event' => \App\Enums\Delivery\Event::csn_order()->value, 'eventId' => $id], true) }}"
            class="nav-link {{ isActive('cms.logistic.changeLogisticStatus', $route_name) }} }}">配送狀態</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.logistic.create', ['event' => \App\Enums\Delivery\Event::csn_order()->value, 'eventId' => $id], true) }}"
            class="nav-link {{ isActive('cms.logistic.create', $route_name) }}">物流設定</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.delivery.create', ['event' => \App\Enums\Delivery\Event::csn_order()->value, 'eventId' => $id], true) }}"
           class="nav-link {{ isActive('cms.delivery.create', $route_name) }}">出貨審核</a>
    </li>
{{--    <li class="nav-item">--}}
{{--        <a href="{{ Route('cms.consignment-order.log', ['id' => $id], true) }}"--}}
{{--           class="nav-link {{ isActive('cms.consignment-order.log', $route_name) }}">變更紀錄</a>--}}
{{--    </li>--}}
</ul>
<hr class="narbarBottomLine mb-3">
