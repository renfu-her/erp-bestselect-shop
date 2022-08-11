@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">
        @if ($method === 'create')
            新增
        @else
            編輯
        @endif 公佈欄
    </h2>
    <form id="form1" class="card-body" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="title" title="主旨" required="true" class="mb-2">
                    <input type="text"
                           class="form-control @error('title') is-invalid @enderror"
                           id="title"
                           name="title"
                           value="{{ old('title', $data->title ?? '')}}"
                           required
                           aria-label="主旨"/>
                </x-b-form-group>

                <x-b-form-group name="weight" title="重要性" required="true" class="mb-2">
                    <div class="px-1 pt-1">
                        @foreach($weights as $value => $weight)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    {{ $weight }}
                                    <input class="form-check-input @error('weight') is-invalid @enderror"
                                            value="{{ $value }}"
                                            name="weight"
                                            id="weight"
                                            type="radio"
                                            @if ($method === 'create' &&
                                                $value == \App\Enums\AdminManagement\Weight::OneStar
                                            )
                                                checked
                                            @elseif (
                                                $value == old('weight', $data->weight ?? '')
                                            )
                                                checked
                                        @endif
                                    >
                                </label>
                            </div>
                        @endforeach
                    </div>
                </x-b-form-group>

                <div class="col-12 mb-2">
                    <label class="form-label">公告期限</label>
                    <div class="input-group">
                        <input type="date"
                               name="expire_time"
                               value="{{ old('expire_time', date('Y-m-d', strtotime($data->expire_time ?? ''))) }}"
                               class="form-control @error('expire_time') is-invalid @enderror"
                               id="datePicker"
                               aria-label=""/>
                        <button class="btn btn-outline-secondary icon" type="button" data-clear
                                data-bs-toggle="tooltip" title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
                    </div>
                </div>

                <x-b-form-group name="type" title="公告對象" required="true">
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                全公司
                                <input class="form-check-input"
                                        value=""
                                        name="type"
                                        type="radio"
                                        checked
                                        disabled
                                >
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                個人
                                <input class="form-check-input"
                                        value=""
                                        name="type"
                                        type="radio"
                                        disabled
                                >
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                部門
                                <input class="form-check-input"
                                        value=""
                                        name="type"
                                        type="radio"
                                        disabled
                                >
                            </label>
                        </div>
                    </div>
                </x-b-form-group>
            </div>
        </div>
        <div id="content" class="card shadow p-4 mb-4 -content">
            <div class="d-flex align-items-center mb-4">
                <h6 class="mb-0">公告內容</h6>
                <a href="https://img.bestselection.com.tw/fadd1.asp?name="
                   class="btn btn-outline-primary -in-header ms-4" target="_blank">
                    <i class="bi bi-upload"></i> 上傳圖片
                </a>
            </div>
            <textarea id="editor"
                      name="content"
                      class="@error('content') is-invalid @enderror">
            </textarea>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.bulletin_board.index', [], true) }}" class="btn btn-outline-primary px-4"
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
        <script src="{{ Asset("plug-in/tinymce/tinymce.min.js") }}"></script>
        <script src="{{ Asset("plug-in/tinymce/myTinymce.js") }}"></script>
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
            @if($method === 'create')
                //預設初始值
                $('#datePicker').val(ONE_STAR_DATE);
                $('input[name="weight"]').off('change').on('change', function () {
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

            $('#form1').submit(function (e) {
                $('textarea#editor').val(tinymce.get('editor').getContent());
            });
        </script>
    @endpush
@endonce
