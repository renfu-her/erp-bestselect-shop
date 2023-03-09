@extends('layouts.main')
@section('sub-content')
    @if ($method === 'edit')
        <h2 class="mb-3"># 報廢單</h2>
    @else
        <h2 class="mb-3">新增報廢單</h2>
    @endif

    <form id="form1" method="post" action="{{ $formAction }}" class="-banRedo">
        @method('POST')
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>報廢單內容</h6>
            <div class="col-12">
                <label class="form-label">報廢單備註</label>
                <input class="form-control" type="text" value="{{$bacPapa->memo ?? ''}}" name="scrap_memo" placeholder="報廢單備註">
            </div>
            <div class="table-responsive tableOverBox mb-3">
                <table id="Pord_list" class="table table-striped tableList">
                    <thead class="small">
                    <tr>
                        <th style="width:3rem;">#</th>
                        <th class="text-center">採購單號</th>
                        <th>商品名稱</th>
                        <th>SKU</th>
                        <th>效期</th>
                        <th>倉庫</th>
                        <th>事件</th>
                        <th>現有數量</th>
                        <th class="text-center" style="width: 10%">報廢數量</th>
                        <th>目前可售數量</th>
                        <th>備註</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="d-grid mt-3">
                    <button id="addProductBtn" type="button"
                            class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                        <i class="bi bi-plus-circle bold"></i> 加入商品
                    </button>
                </div>
            </div>

            <h6 class="mb-1">其他項目</h6>

            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList mb-1">
                    <thead class="small">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">項目</th>
                        <th scope="col">金額（單價）</th>
                        <th scope="col">數量</th>
                        <th scope="col">備註</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php
                        $items = $dlv_other_items;
                    @endphp

                    @for ($i = 0; $i < 5; $i++)
                        <tr>
                            <td>{{ $i + 1 }}<input type="hidden" name="back_item_id[{{ $i }}]" value="{{ $items[$i]->id ?? '' }}"></td>

                            <td>
                                <select class="select-check form-select form-select-sm -select2 -single @error('bgrade_id.' . $i) is-invalid @enderror" name="bgrade_id[{{ $i }}]" data-placeholder="請選擇會計科目">
                                    <option value="" selected disabled>請選擇會計科目</option>
                                    @foreach($total_grades as $g_value)
                                        <option value="{{ $g_value['primary_id'] }}" {{ $g_value['primary_id'] == old('bgrade_id.' . $i, $items[$i]->grade_id ?? '') ? 'selected' : '' }}
                                        @if($g_value['grade_num'] === 1)
                                            class="grade_1"
                                                @elseif($g_value['grade_num'] === 2)
                                                    class="grade_2"
                                                @elseif($g_value['grade_num'] === 3)
                                                    class="grade_3"
                                                @elseif($g_value['grade_num'] === 4)
                                                    class="grade_4"
                                            @endif
                                        >{{ $g_value['code'] . ' ' . $g_value['name'] }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <input type="text" name="btitle[{{ $i }}]"
                                       value="{{ old('btitle.' . $i, $items[$i]->product_title ?? '') }}"
                                       class="d-target form-control form-control-sm @error('btitle.' . $i) is-invalid @enderror"
                                       aria-label="項目" placeholder="請輸入項目" disabled>
                            </td>

                            <td>
                                <input type="number" name="bprice[{{ $i }}]"
                                       value="{{ old('bprice.' . $i, $items[$i]->price ?? '') }}"
                                       class="d-target r-target form-control form-control-sm @error('bprice.' . $i) is-invalid @enderror"
                                       aria-label="金額" placeholder="請輸入金額" disabled>
                            </td>

                            <td>
                                <input type="number" name="bqty[{{ $i }}]"
                                       value="{{ old('bqty.' . $i, $items[$i]->qty ?? '') }}" min="0"
                                       class="d-target r-target form-control form-control-sm @error('bqty.' . $i) is-invalid @enderror"
                                       aria-label="數量" placeholder="請輸入數量" disabled>
                            </td>

                            <td>
                                <input type="text" name="bmemo[{{ $i }}]"
                                       value="{{ old('bmemo.' . $i, $items[$i]->memo ?? '') }}"
                                       class="d-target form-control form-control-sm @error('bmemo.' . $i) is-invalid @enderror"
                                       aria-label="備註" placeholder="請輸入備註" disabled>
                            </td>
                        </tr>
                    @endfor
                    </tbody>
                </table>
            </div>
            @error('error_msg')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="method" value="{{ $method }}" />
                <button type="submit" class="btn btn-primary px-4" >送出</button>
                <a href="{{ Route('cms.scrap.index', []) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            // +/- btn
            $('button.-minus, button.-plus').on('click', function() {
                const $input = $(this).siblings('input[type="number"]');
                const max = $input.attr('max') !== '' ? Number($input.attr('max')) : null;
                const min = $input.attr('min') !== '' ? Number($input.attr('min')) : null;
                const m_qty = Number($input.val());
                if ($(this).hasClass('-minus') && (min !== null && m_qty > min)) {
                    $input.val(m_qty - 1);
                }
                if ($(this).hasClass('-plus') && (max != null && m_qty < max)) {
                    $input.val(m_qty + 1);
                }
            });
            $(document).on('change', 'select.select-check', function() {
                if(this.value){
                    $(this).parents('tr').find('.d-target').prop('disabled', false);
                    $(this).parents('tr').find('.r-target').prop('required', true);
                } else {
                    $(this).parents('tr').find('.d-target').prop('disabled', true);
                    $(this).parents('tr').find('.r-target').prop('required', false);
                }
            });

            $.each($('select.select-check'), function(i, ele) {
                if(ele.value){
                    $(ele).parents('tr').find('.d-target').prop('disabled', false);
                    $(ele).parents('tr').find('.r-target').prop('required', true);
                } else {
                    $(ele).parents('tr').find('.d-target').prop('disabled', true);
                    $(ele).parents('tr').find('.r-target').prop('required', false);
                }
            });
        </script>
    @endpush
@endonce

