{{-- 功能按鈕群 --}}
<div class="btn-group pm_btnGroup" role="group">
    {{-- <a href="#" class="nav-link">
        <span class="icon"><i class="bi bi-printer"></i></span>
        <span class="label">列印</span>
    </a> --}}
    @if(\App\Enums\Consignment\AuditStatus::approved()->value != $purchaseData->audit_status)
        <a href="javascript:void(0)" data-href="{{ Route('cms.purchase.delete', ['id' => $id], true) }}"
           data-bs-toggle="modal" data-bs-target="#confirm-delete-purchase" class="nav-link">
            <span class="icon"><i class="bi bi-trash"></i></span>
            <span class="label">刪除採購單</span>
        </a>
    @endif
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
{{--    <li class="nav-item">--}}
{{--        <a href="#"--}}
{{--            class="nav-link disabled">物流資料</a>--}}
{{--    </li>--}}
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

<!-- Modal -->
<x-b-modal id="confirm-delete-purchase">
    <x-slot name="title">刪除確認</x-slot>
    <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
    <x-slot name="foot">
        <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
    </x-slot>
</x-b-modal>

@once
    @push('sub-scripts')
        <script>
            $('#confirm-delete-purchase').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
