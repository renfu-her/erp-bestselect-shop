{{-- 功能按鈕群 --}}
<div class="btn-group pm_btnGroup" role="group">
    <a href="#" class="nav-link disabled" hidden>
        <span class="icon -open_eye"><span class="bi bi-eye-fill"></span></span>
        <!-- 不公開改成下面 -->
        <!-- <span class="icon -close_eye"><span class="bi bi-eye-slash-fill"></span></span> -->
        <span class="label">公開</span>
    </a>
    <a href="{{ frontendUrl() }}product/{{ $sku }}" target="_blank" class="nav-link">
        <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
        <span class="label">前往該商品</span>
    </a>
    <a href="#" data-bs-toggle="modal" data-bs-target="#copyProduct" class="nav-link">
        <span class="icon"><i class="bi bi-files"></i></span>
        <span class="label">複製來源</span>
    </a>
    <a href="#" class="nav-link disabled" hidden>
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

<div id="copyProduct" class="modal fade" data-bs-backdrop="static" tabindex="-1" aria-labelledby="change-mcodeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="#" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="change-mcodeLabel">選擇複製來源商品</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-12 mb-3">
                        <label class="form-label">1. 請先搜尋</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control -search" placeholder="請輸入名稱或SKU"
                                aria-label="搜尋條件" aria-describedby="搜尋條件">
                            <button class="btn btn-outline-primary px-4 -search" type="button">搜尋</button>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">2. 選擇商品</label>
                        <select class="form-select" name="">
                            <option>請先搜尋</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary px-4">複製</button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('sub-scripts')
<script>
    (() => {
        // 複製商品資訊
        $('#copyProduct button.-search').off('click').on('click', function() {
            const _URL = `${Laravel.apiUrl.productList}`;
            const keyword = $('#copyProduct input.-search').val();
            if (!keyword) {
                return false;
            }
            const $select = $('#copyProduct select');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.empty();

            axios.post(_URL, {
                title: keyword,
                options: {sku: keyword}
            }).then((result) => {
                console.log(result.data);
                const res = result.data;
                if (res.status === '0' && res.data && res.data.length) {
                    $select.append('<option>請選擇</option>');
                    (res.data).forEach(prod => {
                        $select.append(`<option value="${prod.id}">
                            ${prod.sku}｜${prod.title}
                        </option>`);
                    });
                    $select.select2({
                        dropdownParent: $('#copyProduct')
                    });
                } else {
                    $select.append('<option>查無資料</option>');
                }
            }).catch((err) => {
                console.error(err);
                toast.show('發生錯誤', {
                    type: 'danger'
                });
            });
        });

        $('#copyProduct').on('shown.bs.modal', function (e) {
            // 禁用鍵盤 Enter submit
            $('form').on('keydown.ban', ':input:not(textarea)', function(e) {
                return e.key !== 'Enter';
            });
        });
        $('#copyProduct').on('hide.bs.modal', function (e) {
            const $select = $('#copyProduct select');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.empty();
            $select.append('<option>請先搜尋</option>');
            $('#copyProduct input.-search').val('');
            // 禁用鍵盤 Enter submit
            $('form').off('keydown.ban');
        });
    })();
</script>
@endpush
@endonce