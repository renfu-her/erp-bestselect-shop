@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">
        @if ($method === 'create') 新增 @else 編輯 @endif 商品群組
    </h2>
    <form class="card-body" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="title" title="名稱" required="true">
                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title"
                        value="{{ old('title', $data->title ?? '') }}" required aria-label="商品群組名稱" />
                    @error('title')
                        <div class="alert-danger"> {{ $message }} </div>
                    @enderror
                </x-b-form-group>
                <x-b-form-group name="has_child" title="子階層" required="true">
                    <div class="form-check">
                        <input class="form-check-input" value="0" @if (old('has_child', $data->has_child ?? '') == '0') checked @endif type="radio" name="has_child"
                            id="has_child1">
                        <label class="form-check-label" for="has_child1">
                            無
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" value="1" @if (old('has_child', $data->has_child ?? '') == '1') checked @endif type="radio" name="has_child"
                            id="has_child2">
                        <label class="form-check-label" for="has_child2">
                            有 </label>
                    </div>
                </x-b-form-group>
                <div id="hasChildArea">
                    <x-b-form-group name="type" title="連結類型" required="true">
                        <div class="form-check">
                            <input class="form-check-input" value="url" @if (old('type', $data->type ?? '') == 'url') checked @endif type="radio" name="type"
                                id="type1">
                            <label class="form-check-label" for="type1">
                                網址</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" value="group" @if (old('type', $data->type ?? '') == 'group') checked @endif type="radio" name="type"
                                id="type2">
                            <label class="form-check-label" for="type2">
                                群組</label>
                        </div>
                    </x-b-form-group>
                    <div id="urlArea">
                        <x-b-form-group name="url" title="網頁連結" required="false">
                            <input type="text" class="form-control @error('url') is-invalid @enderror" id="url" name="url"
                                value="{{ old('url', $data->url ?? '') }}" aria-label="網頁連結" />
                            @error('url')
                                <div class="alert-danger"> {{ $message }} </div>
                            @enderror
                        </x-b-form-group>
                    </div>
                    <div id="groupArea">
                        <x-b-form-group name="group_id" title="群組" required="false">
                            <select class="form-select" name="group_id" aria-label="Default select example">
                                @foreach ($collections as $key => $group)
                                    <option value="{{ $group['id'] }}" @if (old('group_id', $data->group_id ?? '') == $group['id']) selected @endif>
                                        {{ $group['name'] }}</option>
                                @endforeach
                            </select>
                        </x-b-form-group>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.collection.index', [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
        </div>
    </form>


@endsection
@once
    @push('sub-scripts')
        <script>
            let hasChildElem = $('input[name="has_child"]');
            let typeElem = $('input[name="type"]');
            let urlAreaElem = $('#urlArea');
            let groupAreaElem = $('#groupArea');
            let childAreaElem = $('#hasChildArea');

            let currentChild = @json(old('has_child', $data->has_child ?? '0'));
            let currentType = @json(old('type', $data->type ?? 'url'));

            hasChildAreaVisible(currentChild);
            changeTypeArea(currentType);

            hasChildElem.on('change', function() {
                hasChildAreaVisible($(this).val())
            });

            typeElem.on('change', function() {
                changeTypeArea($(this).val());
            });

            function hasChildAreaVisible(_status) {
                if (_status == 1) {
                    childAreaElem.show();
                } else {
                    childAreaElem.hide();
                }
            }

            function changeTypeArea(_type) {
                if (_type == 'url') {
                    urlAreaElem.show();
                    groupAreaElem.hide();
                } else {
                    urlAreaElem.hide();
                    groupAreaElem.show();
                }
            }
        </script>
    @endpush
@endonce
