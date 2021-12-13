@extends('layouts.main')
@section('sub-content')
<div class="mb-3">
    <h2>主商品名稱</h2>
</div>
{{-- 功能按鈕群 --}}
<div class="btn-group pm_btnGroup" role="group">
    <a href="" class="nav-link pt-0">
        <span class="icon -open_eye"><span class="bi bi-eye-fill"></span></span>
        <!-- 不公開改成下面 -->
        <!-- <span class="icon -close_eye"><span class="bi bi-eye-slash-fill"></span></span> -->
        <span class="label">公開</span>
    </a>
    <a href="" class="nav-link pt-0">
        <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
        <span class="label">前往該商品</span>
    </a>
    <a href="" class="nav-link pt-0">
        <span class="icon"><i class="bi bi-files"></i></span>
        <span class="label">複製</span>
    </a>
    <a href="" class="nav-link pt-0">
        <span class="icon"><i class="bi bi-trash"></i></span>
        <span class="label">刪除商品</span>
    </a>
</div>
@endsection
@once
    @push('sub-styles')
    <style>
        .icon.-close_eye + span.label::before {
            content: '不';
        }
    </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
