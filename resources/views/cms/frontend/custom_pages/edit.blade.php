@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">
        @if ($method === 'create') 新增 @else 編輯 @endif 自訂頁面
    </h2>
    <form id="form1" class="card-body" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="page_name" title="頁面名稱" required="true">
                    <input type="text"
                        class="form-control @error('page_name') is-invalid @enderror"
                        name="page_name"
                        value="{{ old('page_name', $dataList->page_name ?? '')}}"
                        required
                        aria-label="頁面名稱"/>
                </x-b-form-group>
                <p class="mark m-0"><i class="bi bi-exclamation-diamond-fill mx-2 text-warning"></i>
                    系統會自動將「頁面名稱」代入網頁連結、標題、描述中
                    ，如需調整搜尋引擎SEO成效，可自行修改
                </p>
                <x-b-form-group name="url" title="網頁連結名稱" required="false">
                    <input type="text"
                        class="form-control @error('url') is-invalid @enderror"
                        name="url"
                        value="{{ old('url', $dataList->url ?? '')}}"
                        aria-label="網頁連結名稱"/>
                </x-b-form-group>
                <x-b-form-group name="title" title="網頁標題" required="false">
                    <input type="text"
                        class="form-control @error('title') is-invalid @enderror"
                        name="meta_title"
                        value="{{ old('title', $dataList->title ?? '')}}"
                        aria-label="網頁標題"/>
                </x-b-form-group>
                <x-b-form-group name="desc" title="網頁描述" required="false">
                    <input type="text"
                        class="form-control @error('desc') is-invalid @enderror"
                        name="desc"
                        value="{{ old('desc', $dataList->desc ?? '')}}"
                        aria-label="網頁描述"/>
                </x-b-form-group>
                <x-b-form-group name="sale_channel" title="通路選擇" required="false">
                    <select class="form-select @error('sale_channel') is-invalid @enderror" name="sale_channel" aria-label="通路選擇" >
                        @foreach($salesChannels as $salesChannel)
                                <option value="{{ $salesChannel->id }}"
                                    @if(old('sale_channel', $dataList->prd_sale_channels_id_fk ?? '') === $salesChannel->id)
                                        selected
                                    @endif
                                >
                                    {{  $salesChannel->title  }}
                                </option>
                        @endforeach
                    </select>
                </x-b-form-group>
                <x-b-form-group name="type" title="自訂類型" required="true">
                    <div class="px-1">
                        @foreach($customPagesType as $key => $type)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label @error('type') is-invalid @enderror">
                                    {{ $type }}
                                    <input class="form-check-input"
                                           value="{{ $key }}"
                                           @if(old('type', $dataList->type ?? '') === $key)
                                               checked
                                           @endif
                                           name="type"
                                           required
                                           type="radio">
                                </label>
                            </div>
                        @endforeach
                    </div>
                </x-b-form-group>
            </div>
        </div>

        <div id="content_1" class="card shadow p-4 mb-4 -content">
            <div class="d-flex align-items-center mb-4">
                <h6 class="mb-0">【一般】自訂內容</h6>
                <a href="https://img.bestselection.com.tw/fadd1.asp?name="
                    class="btn btn-outline-primary -in-header ms-4" target="_blank">
                    <i class="bi bi-upload"></i> 上傳圖片
                </a>
            </div>
            <textarea id="editor" name="content" hidden class="@error('content') is-invalid @enderror">
            </textarea>
        </div>

        <div id="content_2" class="card shadow p-4 mb-4 -content" hidden>
            <h6>【活動頁】自訂內容</h6>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Head 資訊（例：<code>&lt;meta&gt;</code>、<code>&lt;script src=""&gt;</code> JS引用連結）</label>
                    <textarea class="form-control @error('head') is-invalid @enderror" rows="5" name="head"
                    placeholder="<meta charset=&quot;utf-8&quot;>
<script src=&quot;https://.../jquery.min.js&quot;></script>">
                        {{ old('head', $dataList->head ?? '')}}
                    </textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">網頁內容程式碼（例：<code>&lt;body&gt;</code>）</label>
                    <textarea class="form-control @error('body') is-invalid @enderror" rows="5" name="body"
                    placeholder="<body>
...
</body>">
                        {{ old('body', $dataList->body ?? '')}}
                    </textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">網頁內嵌 javascript 程式碼（勿含<code>&lt;script&gt;</code> tag）</label>
                    <div class="textarea-group">
                        <span class="input-group-text">&lt;script&gt;</span>
                        <textarea class="form-control @error('script') is-invalid @enderror" rows="5" name="script" placeholder="new WOW().init();">
                            {{ old('script', $dataList->script ?? '')}}
                        </textarea>
                        <span class="input-group-text">&lt;/script&gt;</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.custom-pages.index', [], true) }}" class="btn btn-outline-primary px-4"
               role="button">返回列表</a>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <style>
            .textarea-group > .form-control {
                position: relative;
            }
            .textarea-group:not(.has-validation) > :not(:last-child) {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }
            .textarea-group > :not(:first-child) {
                margin-top: -1px;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script src="{{ Asset("plug-in/tinymce/tinymce.min.js") }}"></script>
        <script src="{{ Asset("plug-in/tinymce/myTinymce.js") }}"></script>
        <script>
            // 自訂類型 切換
            $('input[name="type"]').off('change').on('change', function () {
                const type = $(this).val();
                const id = 'div#content_' + type;

                $('div.-content').prop('hidden', true);
                $('div.-content').find('textarea').prop('disabled', true);

                $(id).prop('hidden', false);
                $(id).find('textarea').prop('disabled', false);
            });

            //自訂類型為「活動頁」時，顯示UI
            if ({{ $dataList->type ?? 1 }} == 2) {
                $('div#content_1').prop('hidden', true);
                $('div#content_1').find('textarea').prop('disabled', true);

                $('div#content_2').prop('hidden', false);
                $('div#content_2').find('textarea').prop('disabled', false);
            }

            // 一般 文字編輯器
            let content = @json($dataList->content ?? '');
            content = content ? content : '';
            tinymce.init({
                selector: '#editor',
                ...TINY_OPTION
            }).then((editors) => {
                editors[0].setContent(content);
            });

            $('#form1').submit(function(e) {
                if ($('input[name="type"]').val() === '1') {
                    $('textarea#editor').val(tinymce.get('editor').getContent());
                }
            });
        </script>
    @endpush
@endonce
