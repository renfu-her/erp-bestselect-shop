@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">出貨方式</h2>
    <form id="form1" method="post" action="{{ Route('cms.shipment.method-edit', [], true) }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            @foreach ($datas as $key => $value)
                <h6>{{ $value->method }}</h6>
                <input type="hidden" name="id[]" value="{{ $value->id }}">

                <textarea name="note[]" hidden>{{ $value->note }}</textarea>

                <hr class="mb-0 mt-4">
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
    @push('sub-styles')
        <style>
            .card > hr:last-child {
                display: none;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script src="{{ Asset('plug-in/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ Asset('plug-in/tinymce/myTinymce.js') }}"></script>
        <script>
            $('textarea[name="note[]"]').each(function (index, element) {
                // element == this
                tinymce.init({
                    target: element,
                    ...TINY_OPTION
                }).then((editors) => {
                    editors[0].setContent(element.value);
                });
            });

            $('#form1').submit(function(e) {
                $('textarea[name="note"]').val(tinymce.get('editor').getContent());
            });
        </script>
    @endpush
@endonce
