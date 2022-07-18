@extends('layouts.main')
@section('sub-content')
    <form method="post" action="{{ Route('cms.order-bonus.create') }}">
        @csrf
        <h2 class="mb-4">新增月報表</h2>
        <div class="card shadow p-4 mb-4">
            <div class="col-12">

                <x-b-form-group name="title" title="標題" required="true">
                    <input class="form-control @error('title') is-invalid @enderror" name="title"
                        value="{{ old('title', $data['title'] ?? '') }}" required />
                </x-b-form-group>

                <x-b-form-group name="month" title="月份" required="true">
                    <input class="form-control @error('month') is-invalid @enderror" name="month"
                        value="{{ old('month', $data['month'] ?? '') }}"  type="date" required/>
                </x-b-form-group>

            </div>
            <div class="col-12">
                <div class="justify-content-start mt-3">
                    <button type="submit" class="btn btn-primary px-4">儲存</button>
                    <a href="{{ Route('cms.google_marketing.index', [], true) }}">
                        <button type="button" class="btn btn-outline-primary px-4" id="cancelBtn">取消</button>
                    </a>
                </div>
            </div>
            <div>
            </div>
        </div>
    </form>
@endsection
