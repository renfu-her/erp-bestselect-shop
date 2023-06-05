@extends('layouts.main')
@section('sub-content')
    <div class="card p-4 mb-4">
        <h6 class="mb-3">
            操作者：{{ $data->user_name ?? '' }}
        </h6>
        <h6 class="mb-3">
            日期：{{ $data->created_at }}
        </h6>
        <h6 class="mb-3">
            會計科目：{{ $data->category_title ?? '' }}
        </h6>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">帳號</th>
                        <th scope="col">點數</th>
                        <th scope="col">狀態</th>
                        <th scope="col">備註</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($log ?? [] as $key => $value)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                {{ $value->account }}
                            </td>
                            <td>{{ $value->dividend }}</td>
                            <td>
                                @if ($value->status == '1')
                                    成功
                                @else
                                    失敗
                                @endif
                            </td>
                            <td>{{ $value->note }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-auto">
        <a href="{{ Route('cms.manual-dividend.index', [], true) }}" class="btn btn-outline-primary px-4"
            role="button">返回列表</a>
    </div>
@endsection
