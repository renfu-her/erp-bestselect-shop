@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">個人獎金</h2>

    <div>
        <a href="{{ Route('cms.order.detail', ['id' => $id]) }}" 
            class="btn btn-outline-primary px-4" role="button">返回明細</a>
    </div>
@endsection