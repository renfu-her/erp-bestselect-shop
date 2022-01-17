@extends('layouts.main')
@section('sub-content')
<h2>庫存管理</h2>

<form action="{{ Route('cms.combo-purchase.index') }}" method="GET">
    <div class="card shadow p-4 mb-4">
        <h6>搜尋條件</h6>
        <div class="row">
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">商品名稱</label>
                <input class="form-control" type="text" name="" placeholder="輸入商品名稱或SKU">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">廠商名稱</label>
                <select class="form-select -select2 -single" name="" aria-label="廠商名稱">
                    <option value="" selected disabled>請選擇</option>
                    <option value="1">廠商名稱（統編）</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">負責人</label>
                <select class="form-select -select2 -multiple" multiple name="" aria-label="負責人" data-placeholder="多選">
                    <option value="1">負責人1</option>
                    <option value="2">負責人2</option>
                </select>
            </div>
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">型態</legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="radio1" type="radio" checked>
                            不限
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="radio1" type="radio">
                            一般
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="radio1" type="radio">
                            組合包
                        </label>
                    </div>
                </div>
            </fieldset>
            <fieldset class="col-12 mb-3">
                <legend class="col-form-label p-0 mb-2">庫存狀態</legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="checkbox1" type="checkbox" checked>
                            不限
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="checkbox1" type="checkbox" checked>
                            達安全庫存
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <label class="form-check-label">
                            <input class="form-check-input" name="checkbox1" type="checkbox" checked>
                            無庫存
                        </label>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="col">
            <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
            <button type="submit" class="btn btn-primary px-4">搜尋</button>
        </div>
    </div>
</form>

<div class="card shadow p-4 mb-4">
    <div class="row justify-content-end mb-4">
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
        <table class="table table-striped tableList">
            <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">商品名稱</th>
                    <th scope="col">款式</th>
                    <th scope="col">類型</th>
                    <th scope="col">SKU</th>
                    <th scope="col">進貨數量</th>
                    <th scope="col">實際庫存</th>
                    <th scope="col">預扣庫存</th>
                    <th scope="col">安全庫存</th>
                    <th scope="col">廠商名稱</th>
                    <th scope="col">負責人</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>【喜鴻嚴選】咖啡候機室</td>
                    <td>綜合口味(10入)</td>
                    <td>一般商品</td>
                    <td>P22011300102</td>
                    <td>
                        <a href="簡易採購單清單">20</a>
                    </td>
                    <td>
                        <a href="銷售控管">100</a>
                    </td>
                    <td>
                        {{-- if (銷售控管 = 0) --}}
                        <a href="銷售控管">銷售控管</a>
                    </td>
                    <td>未達</td>
                    <td>
                        <a href="採購清單">喜鴻國際</a>
                    </td>
                    <td>負責人甲</td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>新年禮包</td>
                    <td>豪華版</td>
                    <td>組合包商品</td>
                    <td>P22011300103</td>
                    <td>
                        <a href="簡易採購單清單">10</a>
                    </td>
                    <td>
                        {{-- if (銷售控管 = 0) --}}
                        <a href="銷售控管">銷售控管</a>
                    </td>
                    <td>
                        <a href="銷售控管">100</a>
                    </td>
                    <td>
                        <a href="銷售控管" class="link-danger">已達</a>
                    </td>
                    <td>
                        {{-- if (無廠商) --}}
                        <a href="採購清單">採購清單</a>
                    </td>
                    <td>負責人乙</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="row flex-column-reverse flex-sm-row">
    <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
        {{-- 頁碼 --}}
        <div class="d-flex justify-content-center"></div>
    </div>
</div>
@endsection
@once
    @push('sub-styles')
    <style>
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
