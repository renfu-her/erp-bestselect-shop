@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.organize.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">編輯 組織架構</div>
            <div class="card-body">

                <div class="row">
                    <x-b-form-group name="" title="部門名稱" required="true">
                        <input class="form-control" type="text" disabled value="{{ $data->title }}">
                    </x-b-form-group>
                    <x-b-form-group name="customer_id" title="姓名" required="true">

                        <select name="user_id" class="form-select -select2 -single" data-placeholder="請單選">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @if(old('user_id',$data->user_id ?? '')==$user->id) selected @endif>{{ $user->name }}</option>
                            @endforeach
                        </select>

                    </x-b-form-group>

                </div>
            </div>
        </div>



        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.organize.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
