@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.shipment.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form id="form1" method="post" action="{{ Route('cms.shipment.method-edit', [], true) }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header"> 編輯 出貨方式</div>
            @foreach ($datas as $key => $value)
                <input type="hidden" name="id[]" value="{{ $value->id }}">
                <div class="card-body">
                    <div class="row">
                        <x-b-form-group name="category" title="出貨方式">
                            <div class="px-1">
                                {{ $value->method }}
                            </div>
                        </x-b-form-group>


                        <x-b-form-group name="note" title="說明" required="false">
                            <textarea id="editor" name="note[]" hidden>{{ $value->note }}</textarea>
                        </x-b-form-group>
                    </div>
                </div>
            @endforeach
        </div>



        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.shipment.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script src="{{ Asset('plug-in/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ Asset('plug-in/tinymce/myTinymce.js') }}"></script>
        <script>
            let content = @json(old('note', $note ?? ''));
            content = content ? content : '';

            tinymce.init({
                selector: '#editor',
                ...TINY_OPTION
            }).then((editors) => {
                editors[0].setContent(content);
            });

            $('#form1').submit(function(e) {
                $('textarea[name="note"]').val(tinymce.get('editor').getContent());
            });
        </script>
    @endpush
@endonce
