{{-- Tabs Navbar --}}
<ul class="nav pm_navbar" role="tablist">
    <li class="nav-item">
        <a href="{{ Route('cms.consignment.edit', ['id' => $id], true) }}"
            class="nav-link {{ isActive('cms.consignment.edit', $route_name) }} }}">寄倉單資訊</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.logistic.changeLogisticStatus', ['event' => \App\Enums\Delivery\Event::consignment()->value, 'eventId' => $id], true) }}"
            class="nav-link {{ isActive('cms.logistic.changeLogisticStatus', $route_name) }} }}">配送狀態</a>
    </li>
    <li class="nav-item">
        <a href="{{ Route('cms.logistic.create', ['event' => \App\Enums\Delivery\Event::consignment()->value, 'eventId' => $id], true) }}"
            class="nav-link {{ isActive('cms.logistic.create', $route_name) }}">物流設定</a>
    </li>
    @can('cms.delivery.edit')
    <li class="nav-item">
        <a href="{{ Route('cms.delivery.create', ['event' => \App\Enums\Delivery\Event::consignment()->value, 'eventId' => $id], true) }}"
            class="nav-link {{ isActive('cms.delivery.create', $route_name) }}">出貨審核</a>
    </li>
    @endcan
    <li class="nav-item">
        <a href="{{ Route('cms.consignment.inbound', ['id' => $id], true) }}"
           class="nav-link {{ isActive('cms.consignment.inbound', $route_name) }}">入庫審核</a>
    </li>
{{--    <li class="nav-item">--}}
{{--        <a href="{{ Route('cms.consignment.log', ['id' => $id], true) }}"--}}
{{--           class="nav-link {{ isActive('cms.consignment.log', $route_name) }}">變更紀錄</a>--}}
{{--    </li>--}}
</ul>
<hr class="narbarBottomLine mb-3">
