@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">請款單查詢</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">客戶</label>
                    <select class="form-select -select2 -single" name="client_key" aria-label="客戶" data-placeholder="請選擇客戶">
                        <option value="" selected>不限</option>
                        @foreach ($client as $value)
                            <option value="{{ $value['id'] . '|' . $value['name'] }}" {{ $value['id'] . '|' . $value['name'] == $cond['client_key'] ? 'selected' : '' }}>{{ $value['name'] . ' - ' . ($value['email'] ?? $value['id']) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">請款單號</label>
                    <input class="form-control" type="text" name="request_sn" value="{{ $cond['request_sn'] }}" placeholder="請輸入請款單號">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">單據編號</label>
                    <input class="form-control" type="text" name="source_sn" value="{{ $cond['source_sn'] }}" placeholder="請輸入單據編號">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">請款金額</label>
                    <div class="input-group has-validation">
                        <input type="number" step="1" min="0" class="form-control @error('request_min_price') is-invalid @enderror" 
                        name="request_min_price" value="{{ $cond['request_min_price'] }}" placeholder="起始金額" aria-label="請款起始金額">
                        <input type="number" step="1" min="0" class="form-control @error('request_max_price') is-invalid @enderror" 
                        name="request_max_price" value="{{ $cond['request_max_price'] }}" placeholder="結束金額" aria-label="請款結束金額">
                        <div class="invalid-feedback">
                            @error('request_min_price')
                                {{ $message }}
                            @enderror
                            @error('request_max_price')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">請款日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('request_sdate') is-invalid @enderror" name="request_sdate" value="{{ $cond['request_sdate'] }}" aria-label="請款起始日期">
                        <input type="date" class="form-control -endDate @error('request_edate') is-invalid @enderror" name="request_edate" value="{{ $cond['request_edate'] }}" aria-label="請款結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('request_sdate')
                                {{ $message }}
                            @enderror
                            @error('request_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">請款狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach ($check_posting_status as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="check_posting" type="radio" value="{{ $key }}" {{ (string)$key == $cond['check_posting'] ? 'checked' : '' }}>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            @can('cms.request.create')
            <div class="col">
                <a href="{{ Route('cms.request.create') }}" class="btn btn-primary" role="button">
                    <i class="bi bi-plus-lg"></i> 新增請款單
                </a>
            </div>
            @endcan
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">請款單號</th>
                        <th scope="col">收款單號</th>
                        <th scope="col">請款對象</th>
                        <th scope="col">科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col" class="text-end">收款金額</th>
                        <th scope="col">業務員</th>
                        <th scope="col">部門</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                    @php
                        $data->accounting = null;
                        $data->summary = null;

                        if($data->request_o_items){
                            $request_o_items = json_decode($data->request_o_items);
                            $str = '';
                            foreach ($request_o_items as $i_value){
                                $str .= $i_value->grade_code . ' ' . $i_value->grade_name . '<br>';
                            }

                            $data->accounting = rtrim($str, '<br>');
                            $data->summary = rtrim(implode('<br>', collect($request_o_items)->pluck('summary')->toArray()), '<br>');
                        }
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td><a href="{{ route('cms.request.show', ['id' => $data->request_o_id]) }}">{{ $data->request_o_sn }}</a></td>
                        <td><a href="{{ route('cms.request.ro-receipt', ['id' => $data->request_o_id]) }}">{{ $data->ro_sn }}</a></td>
                        <td>{{ $data->request_o_client_name }}</td>

                        <td>{!! $data->accounting !!}</td>
                        <td>{!! $data->summary !!}</td>

                        <td class="text-end">${{ number_format($data->request_o_price) }}</td>
                        <td>{{ $data->creator_name }}</td>
                        <td>{{ $data->creator_department }}</td>
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

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此報表？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });

            // Modal Control
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            localStorage.setItem('request_url', location.pathname + location.search);
        </script>
    @endpush
@endOnce
