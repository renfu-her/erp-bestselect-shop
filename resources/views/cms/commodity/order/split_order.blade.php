@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#sn 分割訂單</h2>

    <form action="" method="post">
        {{-- @foreach ($collection as $item) --}}
            <div @class([
                'card shadow mb-4 -detail',
                '-detail-primary' => 1,
                '-detail-warning' => 0,
            ])>
                <div class="card-header px-4 d-flex align-items-center bg-white flex-wrap justify-content-end border-bottom-0">
                    <strong class="flex-grow-1 mb-0">BEST-宅配</strong>
                    <span class="badge -badge fs-6">宅配</span>
                </div>
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm table-hover mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col" style="width:10%;">選取</th>
                                    <th scope="col">商品名稱</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col" style="width:10%;" class="text-center">訂購數量</th>
                                    <th scope="col" style="width:10%;" class="text-center">分出數量</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @foreach ($collection as $item) --}}
                                    <tr>
                                        <th>
                                            <input class="form-check-input ms-1" name="style_id[]" type="checkbox">
                                        </th>
                                        <td>物流測試-R</td>
                                        <td>P22062700101</td>
                                        <td class="text-center">3</td>
                                        <td class="text-center">
                                            <select name="qty[]" class="form-select form-select-sm" disabled aria-label="分出數量">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3" selected>3</option>
                                            </select>
                                        </td>
                                    </tr>
                                {{-- @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {{-- @endforeach --}}

        {{-- demo --}}
            <div @class([
                'card shadow mb-4 -detail',
                '-detail-primary' => 0,
                '-detail-warning' => 1,
            ])>
                <div class="card-header px-4 d-flex align-items-center bg-white flex-wrap justify-content-end border-bottom-0">
                    <strong class="flex-grow-1 mb-0">咖啡．候機室-新竹2號店</strong>
                    <span class="badge -badge fs-6">自取</span>
                </div>
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm table-hover mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col" style="width:10%;">選取</th>
                                    <th scope="col">商品名稱</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col" style="width:10%;" class="text-center">訂購數量</th>
                                    <th scope="col" style="width:10%;" class="text-center">分出數量</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>
                                        <input class="form-check-input ms-1" name="style_id[]" type="checkbox">
                                    </th>
                                    <td>組合包商品-三包組</td>
                                    <td>C22062100201</td>
                                    <td class="text-center">1</td>
                                    <td class="text-center">
                                        <select name="qty[]" class="form-select form-select-sm" disabled aria-label="分出數量">
                                            <option value="1" selected>1</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <input class="form-check-input ms-1" name="style_id[]" type="checkbox">
                                    </th>
                                    <td>Group-GGGG</td>
                                    <td>C22071900101</td>
                                    <td class="text-center">2</td>
                                    <td class="text-center">
                                        <select name="qty[]" class="form-select form-select-sm" disabled aria-label="分出數量">
                                            <option value="1">1</option>
                                            <option value="2" selected>2</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        {{-- end demo --}}

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">送出</button>
            <a href="#" class="btn btn-outline-primary px-4" role="button">返回明細</a>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
        <style>
            .table.table-bordered:not(.table-sm) tr:not(.table-light) {
                height: 70px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('input[name="style_id[]"]').on('change', function () {
                const check = $(this).prop('checked');
                $(this).closest('tr').find('select[name="qty[]"]').prop('disabled', !check);
            });
        </script>
    @endpush
@endonce