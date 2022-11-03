@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if ($method === 'create')
            新增
        @else
            編輯
        @endif 申議書
    </h2>
    <form id="form1" class="card-body" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="title" title="主旨" required="true" class="mb-2">
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                        name="title" value="{{ old('title', $data->title ?? '') }}" required aria-label="主旨" />
                </x-b-form-group>
            </div>
            <div class="row">
                <x-b-form-group name="content" title="內容" required="true" class="mb-2">
                    <textarea type="text" class="form-control @error('content') is-invalid @enderror" id="content" name="content"
                        required aria-label="內容">{{ old('content', $data->content ?? '') }}</textarea>
                </x-b-form-group>
            </div>
            <div class="row">
                <x-b-form-group name="orders" title="主旨" required="false" class="mb-2">
                    <input type="text" class="form-control @error('orders') is-invalid @enderror" id="orders"
                        name="orders" value="{{ old('orders', $data->orders ?? '') }}" required aria-label="主旨" />
                </x-b-form-group>
            </div>
        </div>


        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.petition.index', [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
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
    </form>

@endsection
@once
    @push('sub-scripts')
        <script src="{{ Asset('plug-in/tinymce/tinymce.min.js') }}"></script>
        <script src="{{ Asset('plug-in/tinymce/myTinymce.js') }}"></script>
        <script>
            //如果預設時間有修改，請改下面常數
            //選一般預設7天 重要預設15天 極重要預設30天
            const ONE_STAR = 7;
            const TWO_STAR = 15;
            const THREE_STAR = 30;

            const ONE_STAR_DATE = moment().add(ONE_STAR, 'days').format('YYYY-MM-DD');
            const TWO_STAR_DATE = moment().add(TWO_STAR, 'days').format('YYYY-MM-DD');
            const THREE_STAR_DATE = moment().add(THREE_STAR, 'days').format('YYYY-MM-DD');

            //只有在「新增」時， 選一般預設7天 重要預設15天 極重要預設30天
            @if ($method === 'create')
                //預設初始值
                $('#datePicker').val(ONE_STAR_DATE);
                $('input[name="weight"]').off('change').on('change', function() {
                    const weight = parseInt($(this).val());
                    switch (weight) {
                        case 1:
                            $('#datePicker').val(ONE_STAR_DATE);
                            break;
                        case 2:
                            $('#datePicker').val(TWO_STAR_DATE);
                            break;
                        case 3:
                            $('#datePicker').val(THREE_STAR_DATE);
                            break;
                        default:
                            $('#datePicker').val(ONE_STAR_DATE);
                    }
                });
            @endif

            // 一般 文字編輯器
            let content = @json($data->content ?? '');
            content = content ? content : '';
            tinymce.init({
                selector: '#editor',
                ...TINY_OPTION
            }).then((editors) => {
                editors[0].setContent(content);
            });

            $('#form1').submit(function(e) {
                $('textarea#editor').val(tinymce.get('editor').getContent());
            });
        </script>
    @endpush
@endonce
