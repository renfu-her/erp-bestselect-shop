{{-- 功能按鈕群 --}}
<div class="btn-group pm_btnGroup" role="group">
    <a href="#" class="nav-link disabled">
        <span class="icon -open_eye"><span class="bi bi-eye-fill"></span></span>
        <!-- 不公開改成下面 -->
        <!-- <span class="icon -close_eye"><span class="bi bi-eye-slash-fill"></span></span> -->
        <span class="label">公開</span>
    </a>
    <a href="{{ frontendUrl() }}product/{{ $sku }}" target="_blank" class="nav-link">
        <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
        <span class="label">前往該商品</span>
    </a>
    <a href="#" class="nav-link disabled">
        <span class="icon"><i class="bi bi-files"></i></span>
        <span class="label">複製</span>
    </a>
    <a href="#" class="nav-link disabled">
        <span class="icon"><i class="bi bi-trash"></i></span>
        <span class="label">刪除商品</span>
    </a>
</div>

{{-- Tabs Navbar --}}
<ul class="nav pm_navbar" role="tablist">
    @can('cms.product.edit')
        <li class="nav-item">
            <a href="{{ Route('cms.product.edit', ['id' => $id], true) }}"
                class="nav-link {{ isActive('edit', $route_name) }}">商品資訊</a>
        </li>
    @endcan
    @can('cms.product.edit-style')
        @if ($type == 'p')
            <li class="nav-item">
                <a href="{{ Route('cms.product.edit-style', ['id' => $id], true) }}"
                    class="nav-link {{ isActive('edit-style', $route_name) }}{{ isActive('edit-spec', $route_name) }}">規格款式</a>
            </li>
        @endif
    @endcan
    @can('cms.product.edit-combo')
        @if ($type == 'c')
            <li class="nav-item">
                <a href="{{ Route('cms.product.edit-combo', ['id' => $id], true) }}"
                    class="nav-link {{ isActive('edit-combo', $route_name) }}{{ isActive('edit-combo-prod', $route_name) }}{{ isActive('create-combo-prod', $route_name) }}">組合包款式</a>
            </li>
        @endif
    @endcan

    <li class="nav-item">
        <a href="{{ Route('cms.product.edit-sale', ['id' => $id], true) }}"
            class="nav-link {{ isActive('edit-sale', $route_name) }}{{ isActive('edit-stock', $route_name) }}{{ isActive('edit-price', $route_name) }}">銷售控管</a>
    </li>
    @can('cms.product.edit-web-desc')
        <li class="nav-item">
            <a href="{{ Route('cms.product.edit-web-desc', ['id' => $id], true) }}"
                class="nav-link {{ isActive('edit-web-desc', $route_name) }}">[網頁]商品介紹</a>
        </li>
    @endcan
    @can('cms.product.edit-web-spec')
        <li class="nav-item">
            <a href="{{ Route('cms.product.edit-web-spec', ['id' => $id], true) }}"
                class="nav-link {{ isActive('edit-web-spec', $route_name) }}">[網頁]規格說明</a>
        </li>
    @endcan
    {{-- <li class="nav-item">
        <a href="{{ Route('cms.product.edit-web-logis', ['id' => $id], true) }}"
            class="nav-link {{ isActive('edit-web-logis', $route_name) }}">[網頁]運送方式</a>
    </li> --}}
    @can('cms.product.edit-setting')
        <li class="nav-item">
            <a href="{{ Route('cms.product.edit-setting', ['id' => $id], true) }}"
                class="nav-link {{ isActive('edit-setting', $route_name) }}">設定</a>
        </li>
    @endcan
</ul>
<hr class="narbarBottomLine mb-3">
