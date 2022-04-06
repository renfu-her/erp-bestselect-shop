@extends('layouts.main')

@section('sub-content')
    @if($errors->any())

    @endif
    <h2 class="mb-4">{{ $method == 'create' ? '選品' : '修改' }}</h2>

    <div class="card shadow p-4 mb-4">
        <form id="search" method="GET" action="{{ $form_action }}">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品名稱或SKU</label>
                    <input class="form-control" type="text" name="keyword" id="keyword" placeholder="請輸入商品名稱或SKU" value="{{request('keyword')}}">
                </div>
            </div>
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            @if($errors->any())
            <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
            @endif

            <div class="row justify-content-end mb-4">
                @if($method == 'edit')
                    <div class="col">
                        <div class="row">
                            <div class="col-12 col-sm-4">
                                <label class="form-label">加減價%
                                    <mark class="fw-light small">
                                        <i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>寄倉售價 = 官網售價 + (官網售價 * 加減價%)
                                    </mark>
                                </label>

                                <div class="input-group input-group-sm flex-nowrap">
                                    <span class="input-group-text"><i class="bi bi-percent"></i></span>
                                    <input class="form-control form-control-sm" type="text" id="pmp" placeholder="請輸入加減價%" value="3">
                                </div>
                            </div>
                        </div>
                        <div class="col mt-3">
                            <button type="button" class="btn btn-primary px-4 cal_price">計算</button>
                        </div>
                    </div>
                @endif

                <div class="col-auto">
                    顯示
                    <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                        @foreach (config('global.dataPerPage') as $value)
                            <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
                        @endforeach
                    </select>
                    筆
                </div>
            </div>

            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            @if($method == 'edit')
                                <th scope="col">寄倉商品編號</th>
                            @endif
                            <th scope="col">商品名稱</th>
                            <th scope="col">款式</th>
                            {{-- <th scope="col">類型</th> --}}
                            <th scope="col">SKU</th>
                            @if($method == 'edit')
                                <th scope="col">官網售價</th>
                                <th scope="col">寄倉售價</th>
                                <th scope="col" class="text-center">刪除</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="product_list">
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th class="text-center">
                                    <input type="hidden" name="selected[{{$key}}]" value="0">
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{$key}}]" value="{{ $data->id }}" aria-label="選取商品">
                                    <input type="hidden" name="product_style_id[]" value="{{ $data->id }}">
                                </th>
                                @if($method == 'edit')
                                    <td>
                                        <input class="form-control select_input" type="text" name="depot_product_no[]" placeholder="請輸入寄倉商品編號" value="{{ $data->depot_product_no }}" disabled="disabled">
                                    </td>
                                @endif
                                <td>{{ $data->product_title }}</td>
                                <td>{{ $data->spec }}</td>
                                {{-- <td>{{ $data->type_title }}</td> --}}
                                <td>{{ $data->sku }}</td>
                                @if($method == 'edit')
                                    <td><span class="o_price">{{-- number_format(intval($data->ost_price ?: 0)) --}}{{ $data->ost_price }}<span></td>
                                    <td>
                                        <input class="form-control select_input d_price" type="number" step="0.01" min="0" name="depot_price[]" placeholder="請輸入寄倉售價" value="{{ $data->depot_price }}" disabled="disabled">
                                    </td>
                                    <td class="text-center">
                                        @can('cms.depot.product-delete')
                                            <a href="javascript:void(0)" data-href="{{ Route('cms.depot.product-delete', ['id' => $data->select_id]) }}" data-bs-toggle="modal" data-bs-target="#confirm-delete" class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        @endcan
                                        <input type="hidden" name="select_id[]" value="{{ $data->select_id }}">
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row flex-column-reverse flex-sm-row">
            <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
                @if($dataList)
                    <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                    {{-- 頁碼 --}}
                    <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4 submit" disabled="disabled">{{ $method == 'create' ? '儲存' : '更新' }}</button>
            <a href="{{ Route('cms.depot.product-index', ['id' => $depot->id], true) }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
        </div>
    </form>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>
            $(function() {
                // 顯示筆數選擇
                $('#dataPerPageElem').on('change', function(e) {
                    $('input[name=data_per_page]').val($(this).val());
                    $('#search').submit();
                });

                $('#checkAll').change(function(){
                    $all = $(this)[0];
                    $('.product_list tr').each(function( index ) {
                        if($(this).is(':visible')){
                            $(this).find('th input.single_select').prop('checked', $all.checked);

                            $('.submit').prop('disabled', $('input.single_select:checked').length == 0);

                            $(this).find('input.select_input').prop('disabled', $(this).find('th input.single_select:checked').length == 0);
                        }
                    });

                    // $('.single_select').prop('checked', this.checked);
                });


                $('.single_select').click(function(){
                    $('.submit').prop('disabled', $('input.single_select:checked').length == 0);
                    $(this).parents('tr').find('input.select_input').prop('disabled', !this.checked);
                });

                // $('#keyword').on('keyup', function () {
                //     let keyword = $(this).val().toLowerCase();
                //     $('.product_list tr').filter(function () {
                //         $(this).toggle($(this).children('td:eq(0)').text().toLowerCase().indexOf(keyword) > -1 || $(this).children('td:eq(2)').text().toLowerCase().indexOf(keyword) > -1)
                //     });
                // });

                // $('.reset').on('click', function(){
                //     $('#keyword').val('');
                //     $('.product_list tr').css('display', '');
                // });


                $('.cal_price').click(function(){
                    let pmp = parseFloat($('#pmp').val()) || 3;

                    $('.product_list tr').each(function( index ) {
                        if($(this).find('th input.single_select:checked').length > 0){
                            let target = $(this).find('input.d_price');
                            if(! target.prop('disabled')){
                                let o_price = parseInt($(this).find('td span.o_price').text(), 10);

                                // target.val(+(Math.round((o_price + o_price * pmp / 100) + "e+2")  + "e-2"));
                                target.val( +(Math.round(o_price + o_price * pmp / 100)) );
                            }
                        }
                    });
                });


                // 刪除
                $('#confirm-delete').on('show.bs.modal', function(e) {
                    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
                });
            });
        </script>
    @endpush
@endOnce
