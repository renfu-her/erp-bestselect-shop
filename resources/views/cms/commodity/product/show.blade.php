@extends('layouts.main')
@section('sub-content')
    <div class="d-flex align-items-center mb-1 tableList">
        <h2 class="flex-grow-1 mb-0">{{ $product->title }}</h2>
        <button id="share" type="button" data-bs-toggle="tooltip" title="分享"
            data-link="{{ frontendUrl() }}product/{{ $product->sku }}" data-title="{{ $product->title }}"
            class="icon icon-btn fs-5 text-dark rounded-circle border border-dark me-1 col-auto">
            <i class="bi bi-share-fill"></i>
        </button>
        <a href="{{ frontendUrl() }}product/{{ $product->sku }}" data-bs-toggle="tooltip" title="前往官網商品頁"
            class="icon icon-btn fs-5 text-primary rounded-circle border border-primary me-1 col-auto" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <a href="{{ Route('cms.order.create') }}" data-bs-toggle="tooltip" title="內網訂購"
            class="icon icon-btn fs-5 text-success rounded-circle border border-success col-auto">
            <i class="bi bi-bag-fill"></i>
        </a>
    </div>
    <div class="mb-1 fw-light text-secondary small">
        @php
            echo $product->feature;
        @endphp
    </div>
    <p class="text-primary small">
        @php
            echo $product->slogan;
        @endphp
    </p>
    {{-- {{ dd([
        '商品資訊 product' => $product,
        '商品圖片 images' => $images,
        '規格說明 specs' => $specs,
        '款式>通路價格 styles' => $styles,
        '運送方式 shipment' => $shipment
    ]) }} --}}

    <div class="accordion open">
        {{-- 圖片 --}}
        <div class="accordion-item">
            <h3 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-target="#collapse1" data-bs-toggle="collapse"
                    aria-expanded="true" aria-controls="collapse1">
                    商品圖片
                </button>
            </h3>
            <div id="collapse1" class="accordion-collapse collapse show">
                <div class="accordion-body pb-0 pe-0" style="overflow-x: auto">
                    <div class="upload_image_block" style="width: max-content">
                        @foreach ($images as $img)
                            <a href="{{ \App\Enums\Globals\ImageDomain::CDN . $img['url'] }}" target="_blank">
                                <span class="browser_box rounded-0 border">
                                    <img src="{{ \App\Enums\Globals\ImageDomain::CDN . $img['url'] }}" alt="{{ '圖片' . $img['id'] }}">
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- 款式通路 --}}
        <div class="accordion-item">
            <h3 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-target="#collapse2" data-bs-toggle="collapse"
                    aria-expanded="true" aria-controls="collapse2">
                    銷售通路
                </button>
            </h3>
            <div id="collapse2" class="accordion-collapse collapse show">
                <div class="accordion-body pb-0">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-secondary">
                            <th class="text-center">款式</th>
                            <th>銷售通路</th>
                            <th class="text-end">售價</th>
                            <th class="text-end">經銷價</th>
                            <th class="text-end">定價</th>
                            <th class="text-end">獎金
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                    title="預設：(售價-經銷價) × {{ App\Enums\Customer\Bonus::bonus()->value }}"></i>
                            </th>
                            <th class="text-end">鴻利抵扣
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip"
                                    title="此設定顯示於顧客購買結帳頁面商品可使用之鴻利上限。預設：售價 × 各通路可抵扣上限"></i>
                            </th>
                        </thead>
                        <tbody>
                            @foreach ($styles as $style)
                                @foreach ($style->prices as $i => $price)
                                    <tr>
                                        @if ($i === 0)
                                            <th rowspan="{{ count($style->prices) }}" class="text-center table-warning">
                                                {{ $style->title }}</th>
                                        @endif
                                        <td>{{ $price->salechannel_title }}</td>
                                        <td class="text-end">${{ number_format($price->price) }}</td>
                                        <td class="text-end">${{ number_format($price->dealer_price) }}</td>
                                        <td class="text-end">${{ number_format($price->origin_price) }}</td>
                                        <td class="text-end">${{ number_format($price->bonus) }}</td>
                                        <td class="text-end">${{ number_format($price->dividend) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            <tr></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 商品介紹 --}}
        <div class="accordion-item">
            <h3 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-target="#collapse3" data-bs-toggle="collapse"
                    aria-expanded="true" aria-controls="collapse3">
                    商品介紹
                </button>
            </h3>
            <div id="collapse3" class="accordion-collapse collapse show">
                <div class="accordion-body">
                    @php
                        echo $product->desc;
                    @endphp
                </div>
            </div>
        </div>

        {{-- 規格說明 --}}
        <div class="accordion-item">
            <h3 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-target="#collapse4" data-bs-toggle="collapse"
                    aria-expanded="true" aria-controls="collapse4">
                    規格說明
                </button>
            </h3>
            <div id="collapse4" class="accordion-collapse collapse show">
                <div class="accordion-body pb-0">
                    <table class="table table-bordered">
                        @foreach ($specs as $spec)
                            <tr>
                                <td>{{ $spec->title }}</td>
                                <td>{{ $spec->content }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        {{-- 運送方式 --}}
        <div class="accordion-item">
            <h3 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-target="#collapse5" data-bs-toggle="collapse"
                    aria-expanded="true" aria-controls="collapse5">
                    運送方式
                </button>
            </h3>
            <div id="collapse5" class="accordion-collapse collapse show">
                <div class="accordion-body">
                    @if (isset($shipment))
                        {!! $shipment->note !!}
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection
@once
    @push('sub-styles')
        <style>
            #collapse2 .table>tbody>tr:hover>td {
                --bs-table-accent-bg: var(--bs-table-hover-bg);
                color: var(--bs-table-hover-color);
            }

            #collapse3 img {
                max-width: 100%;
                height: auto;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 分享btn
            $('#share').on('click', function() {
                const link = $(this).data('link');
                const title = $(this).data('title');

                if (navigator.share) {
                    navigator.share({
                            title: title,
                            url: link
                        })
                        .then(() => {
                            console.log('分享連結成功');
                        }).catch((err) => {
                            console.error('分享連結錯誤', err);
                            if (!err.toString().includes('AbortError')) {
                                copyToClipboard(link);
                            }
                        });
                } else {
                    copyToClipboard(link);
                }
            });

            // 複製剪貼簿
            function copyToClipboard(link) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(link)
                        .then(() => {
                            toast.show('已複製連結網址。');
                        }).catch((err) => {
                            toast.show(`不支援剪貼簿功能，請手動複製網頁連結：<br>${link}`, {
                                type: 'danger'
                            });
                        });
                }
            }
        </script>
    @endpush
@endOnce
