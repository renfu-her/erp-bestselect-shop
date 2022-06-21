@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.credit_manager.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>
    <h2 class="mb-4">請款比例列表</h2>

    <form id="search" action="{{ Route('cms.credit_percent.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="col-12 col-md-6 mb-3">
                <label class="form-label">銀行名稱</label>
                <input class="form-control" name="keyword_bank" type="text" placeholder="銀行名稱" value="{{$keyword_bank ?? ''}}"
                       aria-label="銀行名稱">
            </div>
            @if(isset($cards) && 0 < count($cards))
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">信用卡別</label>
                        <select class="form-select" name="keyword_credit_id" aria-label="信用卡別">
                            <option value="" @if ('' == $keyword_credit_id ?? '') selected @endif disabled>請選擇</option>
                            @foreach ($cards as $key => $card)
                                <option value="{{ $card->id }}"
                                        @if ($card->id == $keyword_credit_id ?? '') selected @endif>{{ $card->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="col">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">請款銀行</th>
                    <th scope="col">信用卡別</th>
                    <th scope="col">會計科目</th>
                    <th scope="col">會計科目代碼</th>
                    <th scope="col">請款比例</th>
                    @can('cms.credit_percent.edit')
                        <th scope="col" class="text-center">編輯</th>
                    @endcan
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data['bank']['title'] }}</td>
                        <td>{{ $data['credit_card']['title'] }}</td>
                        <td>{{ $data['bank']['name'] }}</td>
                        <td>{{ $data['bank']['code'] }}</td>
                        <td>{{ $data['percent'] }}</td>
                        <td class="text-center">
                            @can('cms.credit_percent.edit')
                                <a href="{{ Route('cms.credit_percent.edit', ['bank_id' => $data['bank']['id'], 'credit_id' => $data['credit_card']['id']], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
