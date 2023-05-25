@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">水果分類設定</h2>
<form action="" method="post">
    <div class="card shadow p-4 mb-4">
        <div class="row">
            <div class="col-12 mb-3">
                <h6 class="mb-2">春季水果（3至5月）</h6>
                <select name="tab1" multiple class="-select2 -multiple form-select"></select>
            </div>
            <div class="col-12 mb-3">
                <h6 class="mb-2">夏季水果（6至8月）</h6>
                <select name="tab2" multiple class="-select2 -multiple form-select"></select>
            </div>
            <div class="col-12 mb-3">
                <h6 class="mb-2">秋季水果（9至11月）</h6>
                <select name="tab3" multiple class="-select2 -multiple form-select"></select>
            </div>
            <div class="col-12 mb-3">
                <h6 class="mb-2">冬季水果（12至2月）</h6>
                <select name="tab4" multiple class="-select2 -multiple form-select"></select>
            </div>
            <div class="col-12 mb-3">
                <h6 class="mb-2">進口水果（1至12月）</h6>
                <select name="tab5" multiple class="-select2 -multiple form-select"></select>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4">儲存</button>
        <a href="{{ Route('cms.act-fruits.index') }}" class="btn btn-outline-primary px-4"
            role="button">返回列表</a>
    </div>
</form>
@endsection

@once
    @push('sub-scripts')
        <script>

        </script>
    @endpush
@endOnce
