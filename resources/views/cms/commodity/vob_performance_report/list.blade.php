@extends('layouts.main')
@section('sub-content')

    @if (isset($search))
        <h2 class="mb-4">營業額目標</h2>
        <x-b-report-search>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">部門</label>
                <select class="form-select -select2 -multiple" multiple name="department[]" aria-label="部門"
                    data-placeholder="多選">
                    @foreach ($department as $value)
                        <option value="{{ $value->title }}" @if (in_array($value->title, $cond['department'])) selected @endif>
                            {{ $value->title }}</option>
                    @endforeach
                </select>
            </div>
        </x-b-report-search>
    @else
        <h2 class="mb-4">{{ $pageTitle }}</h2>
    @endif

    <div class="card shadow p-4 mb-4">
        <div class="col-auto">
            <a href="{{  route('cms.vob-performance-report.export-excel',$cond)  }}" 
                class="btn btn btn-success">輸出excel</a>
        </div>
        @if (isset($search))
            @can('cms.vob-performance-report.renew')
                <form id="form2" action="{{ route('cms.vob-performance-report.renew') }}" method="POST">
                    @csrf
                    <div class="d-flex justify-content-end align-items-center mb-3 flex-wrap">
                        <span class="text-muted me-1">重新計算</span>
                        <div class="col-auto me-1">
                            <select class="form-select form-select-sm" name="year" aria-label="年度">
                                <option value="" disabled>選擇年度</option>
                                @foreach ($year as $value)
                                    <option value="{{ $value }}" @if ($value == $cond['year']) selected @endif>
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto me-1">
                            <select class="form-select form-select-sm" name="month" aria-label="月份">
                                <option value="" disabled>選擇月份</option>
                                @for ($i = 1; $i < 13; $i++)
                                    <option value="{{ $i }}" @if ($i == $cond['month']) selected @endif>
                                        {{ $i }}月</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                立即統計
                                <div class="spinner-border spinner-border-sm" hidden role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            @endcan

            <h4>{{ $pageTitle }}</h4>
        @endif

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">
                            部門名稱
                        </th>
                        <th scope="col">
                            姓名
                        </th>
                        <th scope="col" class="text-center lh-1">營業額</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $price = 0;
                    @endphp
                    @foreach ($dataList as $key => $data)
                        @php
                            $price += $data->price;
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                {{ $data->department }}
                            </td>
                            <td>
                                {{ $data->name }}
                            </td>
                            <td class="text-center">
                                {{ $data->price }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">合計</th>
                        <th class="text-center">{{ $price }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>


@endsection
@once
    @push('sub-styles')
        <style>
            h4 {
                color: #415583;
            }

            .negative::before {
                content: '-';
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 立即統計
            $('#form2').submit(function(e) {
                $('#form2 .spinner-border').prop('hidden', false);
            });
        </script>
    @endpush
@endOnce
