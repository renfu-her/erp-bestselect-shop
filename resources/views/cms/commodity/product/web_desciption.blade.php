@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>
    <form action="{{ route('cms.product.edit-web-desc', ['id' => $product->id]) }}" method="post">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>商品介紹（網頁）</h6>
            <x-b-editor id="editor"></x-b-editor>
        </div>
        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.product.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            let desc = @json($desc);
            desc = desc ? desc : '';
            Editor.createEditor('editor', {
                initialValue: desc
            });
            /*
            $('form').submit(function(e) {
                e.preventDefault();
                console.log(editor.getHTML());
            });
            */
        </script>
    @endpush
@endOnce
