@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">水果分類設定</h2>

<ul class="nav nav-tabs border-bottom-0">
    <li class="nav-item">
        <button class="nav-link active" data-page="tab1" aria-current="page" type="button">
            春季水果<br><span class="small">(3至5月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab2" type="button">
            夏季水果<br><span class="small">(6至8月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab3" type="button">
            秋季水果<br><span class="small">(9至11月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab4" type="button">
            冬季水果<br><span class="small">(12至2月)</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-page="tab5" type="button">
            進口水果<br><span class="small">(1至12月)</span>
        </button>
    </li>
</ul>

<div id="tab1" class="-page">
    <form action="" method="post">
        <div class="card shadow p-4 mb-4">
            <h6>春季水果 (3至5月)</h6>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>
</div>
<div id="tab2" class="-page" hidden>
    <form action="" method="post">
        <div class="card shadow p-4 mb-4">
            <h6>夏季水果 (6至8月)</h6>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>
</div>
<div id="tab3" class="-page" hidden>
    <form action="" method="post">
        <div class="card shadow p-4 mb-4">
            <h6>秋季水果 (9至11月)</h6>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>
</div>
<div id="tab4" class="-page" hidden>
    <form action="" method="post">
        <div class="card shadow p-4 mb-4">
            <h6>冬季水果 (12至2月)</h6>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>
</div>
<div id="tab5" class="-page" hidden>
    <form action="" method="post">
        <div class="card shadow p-4 mb-4">
            <h6>進口水果 (1至12月)</h6>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>
</div>

<div class="col-auto">
    <a href="{{ Route('cms.act-fruits.index') }}" class="btn btn-outline-primary px-4"
        role="button">返回列表</a>
</div>
@endsection

@once
    @push('sub-styles')
        <style>
            /* 拖曳預覽框 */
            .-appendClone.--selectedP tr.placeholder-highlight {
                width: 100%;
                height: 60px;
                margin-bottom: .5rem;
                display: table-row;
            }
            tr.placeholder-highlight > td {
                border: none;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('.nav-link').off('click').on('click', function() {
                const $this = $(this);
                const page = $this.data('page');

                // tab
                $('.nav-link').removeClass('active').removeAttr('aria-current');
                $this.addClass('active').attr('aria-current', 'page');
                // page
                $('.-page').prop('hidden', true);
                $(`#${page}`).prop('hidden', false);
            });
        </script>
    @endpush
@endOnce
