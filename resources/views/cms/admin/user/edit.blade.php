@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.user.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create') 新增 @else 編輯 @endif 帳號
            </div>
            <div class="card-body">
                <x-b-form-group name="name" title="姓名" required="true">
                    <input class="form-control @error('name') is-invalid @enderror" name="name"
                           value="{{ old('name', $data->name ?? '') }}"/>
                </x-b-form-group>
                <x-b-form-group name="account" title="帳號" required="true">
                    @if ($method === 'create')
                        <input class="form-control @error('account') is-invalid @enderror" name="account"
                               value="{{ old('account', $data->account ?? '') }}"/>
                    @else
                        <div class="col-form-label">{{ $data->account ?? '' }}</div>
                    @endif
                </x-b-form-group>
                <x-b-form-group name="password" title="密碼" required="{{ ($method === 'create') ? 'true' : 'false' }}">
                    <input class="form-control @error('password') is-invalid @enderror" type="password"
                           name="password" value=""/>
                </x-b-form-group>
                <x-b-form-group name="password_confirmation" title="密碼確認">
                    <input class="form-control @error('password_confirmation') is-invalid @enderror" type="password"
                           name="password_confirmation" value=""/>
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
            <div class="card-header">物流權限</div>
            <div class="card-body">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">託運人員</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline @error('lgt_user')is-invalid @enderror">
                            <label class="form-check-label">
                                <input class="form-check-input @error('lgt_user')is-invalid @enderror" name="lgt_user"
                                       value="0" type="radio"
                                       @if (old('lgt_user', $user_lgt->user ?? '') == "0") checked @endif>否</label>
                        </div>
                        <div class="form-check form-check-inline @error('lgt_user')is-invalid @enderror">
                            <label class="form-check-label">
                                <input class="form-check-input @error('lgt_user')is-invalid @enderror" name="lgt_user"
                                       value="1" type="radio"
                                       @if (old('lgt_user', $user_lgt->user ?? '') == "1") checked @endif>是</label>
                        </div>
                        @error('lgt_user')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">帳號權限</div>
            <div class="card-body">
                <h6 class="mb-3"><span class="badge rounded-pill -step admin">角色</span></h6>
                @foreach ($roles as $key => $role)
                    @if($is_super_admin || $role['title'] != '超級管理員')
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="role_id[]"
                                   id="role{{ $key }}"
                                   @if (in_array($role['id'], old('role_id', $role_ids ?? [])))
                                       checked
                                   @elseif($role['id'] == ($employeeRoleId ?? null))
                                        {{--  員工角色預設勾選--}}
                                       checked
                                   @endif
                                   value="{{ $role['id'] }}">
                            <label class="form-check-label" for="role{{ $key }}">
                                {{ $role['title'] }}
                            </label>
                        </div>
                    @endif
                @endforeach

                @can('cms.user.permit')
                <h6><span class="badge rounded-pill -step admin">各單元權限</span></h6>
                @foreach ($permissions as $key => $permission)
                    @if($is_super_admin || $permission->title != '頁面權限管理')
                        <fieldset class="col mb-3 -permis-set">
                            <legend class="col-form-label p-0 mb-2">{{ $permission->title }}</legend>
                            <div class="px-1">
                                <div class="form-check form-check-inline pe-3 border-end border-3">
                                    <input class="form-check-input -permisAll" type="checkbox"
                                           id="permisAll_{{ $permission->id }}">
                                    <label for="permisAll_{{ $permission->id }}">全選</label>
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
                        <hr>
                    @endif
                @endforeach
                @endcan
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
            // 全選
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
