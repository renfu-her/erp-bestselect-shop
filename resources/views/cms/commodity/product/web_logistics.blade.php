@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">{{ $product->title }}</h2>
        <x-b-prd-navi :product="$product"></x-b-prd-navi>
    </div>
    <form id="form1" method="POST" action="{{ route('cms.product.edit-web-logis', ['id' => $product->id]) }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>運送方式（網頁）</h6>
            <x-b-editor id="editor"></x-b-editor>
            <textarea name="logistic_desc" hidden></textarea>
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
            let content = @json($product->logistic_desc);

            Editor.createEditor('editor', {
                initialValue: content
            });

            $('#form1').submit(function(e) {
                $('textarea[name="logistic_desc"]').val(editor.getHTML());
            });
        </script>
    @endpush
@endOnce
