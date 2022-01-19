@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">首頁設定</h2>
<a href="{{ Route('cms.homepage.banner.index') }}">橫幅廣告列表</a>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
