@extends('layouts.main')
@section('sub-content')
    <div class="card p-4 mb-4">
        <h6 class="mb-3">
            主旨：{{ $data->title ?? '' }}
        </h6>
        <h6 class="mb-3">
            公告期限：{{ date('Y/m/d', strtotime($data->expire_time ?? '')) }}
        </h6>
        <h6 class="mb-3">
            重要性：{{ $weight_title ?? '' }}
        </h6>
        <h6 class="mb-3">
            內容：
        </h6>
        {!! $data->content ?? '' !!}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="col-auto">
        <a href="{{ Route('cms.bulletin_board.index', [], true) }}" class="btn btn-outline-primary px-4"
           role="button">返回列表</a>
    </div>
@endsection
