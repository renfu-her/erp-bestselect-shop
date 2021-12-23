@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.role.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create') 新增 @else 編輯 @endif 角色
            </div>
            <div class="card-body">
                <x-b-form-group name="title" title="名稱" required="true" required="true">
                    <input class="form-control @error('title') is-invalid @enderror" name="title"
                           value="{{ old('name', $data->title ?? '') }}"/>
                </x-b-form-group>

                @if ($method === 'edit')
                    <input type='hidden' name='id' value="{{ old('id', $id) }}"/>
                @endif
                @error('id')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">管理人員權限</div>
            <div class="card-body">
                <h6><span class="badge rounded-pill -step admin">各單元權限</span></h6>
                @foreach ($permissions as $key => $permission)
                    <fieldset class="col mb-3 -permis-set">
                        <legend class="col-form-label p-0 mb-2">{{ $permission->title }}</legend>
                        <div class="px-1">
                            <div class="form-check form-check-inline pe-3 border-end border-3">
                                <input class="form-check-input -permisAll" type="checkbox"
                                       id="permisAll_{{ $permission->id }}">
                                <label class="form-check-label" for="permisAll_{{ $permission->id }}">全選</label>
                            </div>
                            @foreach ($permission->permissions as $key2 => $pe)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input -permis" type="checkbox" name="permission_id[]"
                                           id="checboxP{{ $key }}_{{ $key2 }}"
                                           @if (in_array($pe->id, old('permission_id', $permission_id ?? []))) checked
                                           @endif
                                           value="{{ $pe->id }}">
                                    <label class="form-check-label" for="checboxP{{ $key }}_{{ $key2 }}">
                                        {{ $pe->title }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>

@endsection
@once
    @push('sub-scripts')
        <script>
            // init 全選
            $('fieldset.-permis-set').each(function (i, elem) {
                let check = $(elem).find('input[type="checkbox"].-permis:checked').length,
                    uncheck = $(elem).find('input[type="checkbox"].-permis:not(:checked)').length;
                $(elem).find('input[type="checkbox"].-permisAll').prop({
                    indeterminate: check > 0 && uncheck > 0,
                    checked: check > 0 && uncheck === 0
                });
            });
            // 單元全選
            $('.-permisAll').off('change.permisAll').on('change.permisAll', function () {
                const checked = $(this).prop('checked');
                $(this).closest('fieldset.-permis-set').find('input[type="checkbox"].-permis').prop({
                    indeterminate: false,
                    checked: checked
                });
            });
            // 個別選擇
            $('input[type="checkbox"].-permis').off('change.permis').on('change.permis', function () {
                const thisFieldset = $(this).closest('fieldset.-permis-set');
                let check = thisFieldset.find('input[type="checkbox"].-permis:checked').length,
                    uncheck = thisFieldset.find('input[type="checkbox"].-permis:not(:checked)').length;
                // console.log('選擇:', check > 0);
                // console.log('不選擇:', uncheck > 0);

                thisFieldset.find('input[type="checkbox"].-permisAll').prop({
                    indeterminate: check > 0 && uncheck > 0,
                    checked: check > 0 && uncheck === 0
                });
            });
        </script>
    @endpush
@endonce
