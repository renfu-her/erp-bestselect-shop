<div id="navBreadcrumb" class="d-flex align-items-center border-bottom">
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        @php
            //csn_order的ctrl是consignment-order
            if (isset($value) and is_array($value) and isset($value['parent']) and \App\Enums\Delivery\Event::csn_order()->value == $value['parent']) {
                $value['parent'] = 'consignment-order';
            }
        @endphp
        {{ Breadcrumbs::render($routeName, $value) }}
    </nav>
</div>
