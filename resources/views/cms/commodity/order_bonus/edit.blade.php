@extends('layouts.main')
@section('sub-content')
    <form method="post" action="{{ Route('cms.order-bonus.create') }}">
        @csrf
        <h2 class="mb-4">新增月報表</h2>
        <div class="card shadow p-4 mb-4">
            <div class="col-12">

                <x-b-form-group name="title" title="報表名稱" required="true">
                    <input class="form-control @error('title') is-invalid @enderror" name="title"
                        value="{{ old('title', $data['title'] ?? '') }}" required />
                </x-b-form-group>

                <x-b-form-group name="month" title="月份" required="true">
                    <input class="form-control @error('month') is-invalid @enderror" name="month"
                        value="{{ old('month', $data['month'] ?? '') }}"  type="month" required/>
                </x-b-form-group>

                <x-b-form-group name="transfer_at" title="匯款日期" required="true">
                    <input class="form-control @error('transfer_at') is-invalid @enderror" name="transfer_at"
                        value="{{ old('transfer_at', $data['transfer_at'] ?? '') }}" type="date"  required/>
                </x-b-form-group>

            </div>
        </div>

        <div class="justify-content-start mt-3">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
            <a href="{{ Route('cms.order-bonus.index', [], true) }}">
                <button type="button" class="btn btn-outline-primary px-4" id="cancelBtn">取消</button>
            </a>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')
        <script>
            $('input[name="month"]').on('change', function() {
                const month = moment($(this).val()).format('YYYY.MM分潤報表');
                let oldTitle = $('input[name="title"]').val();
                oldTitle = oldTitle.replace(/^[0-9]{4}.[0-9]{2}分潤報表(-?)/, '');
                $('input[name="title"]').val(month + (oldTitle ? '-' + oldTitle : ''));
            });
        </script>
    @endpush
@endonce