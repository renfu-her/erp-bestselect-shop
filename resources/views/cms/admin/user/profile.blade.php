@extends('layouts.main')
@section('sub-content')
<div class="pt-2 mb-3">
    <a href="{{ Route('cms.user.index', [], true) }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
</div>

<form action="" method="post">
    <div class="card mb-4"></div>
</form>
@endsection