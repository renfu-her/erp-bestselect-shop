@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">個人獎金</h2>
    
    <ul class="nav nav-tabs border-bottom-0">
        <li class="nav-item">
            <button class="nav-link -page1 active" aria-current="page" type="button">獎金資訊</button>
        </li>
        <li class="nav-item">
            <button class="nav-link -page2" type="button">修改紀錄</button>
        </li>
    </ul>
    
    <div id="page1">
        <div class="card shadow p-4 mb-4">
            <div class="define-table table-light text-nowrap">
                <dl class="d-flex flex-column flex-sm-row">
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>訂單編號</dt>
                        <dd>-</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>品名規格</dt>
                        <dd>-</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>金額</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>數量</dt>
                        <dd>{{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>小計</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                </dl>
                <dl class="d-flex flex-column flex-sm-row">
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>當代獎金</dt>
                        <dd>$ {{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>出庫數量</dt>
                        <dd>{{ number_format(0) }}</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>倉庫</dt>
                        <dd>-</dd>
                    </div>
                    <div class="d-flex flex-row flex-sm-column">
                        <dt>產品人員</dt>
                        <dd>-</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
    
    <div id="page2" hidden>
        <div class="card shadow p-4 mb-4">
            <div class="table-responsive tableOverBox">
                <table class="table tableList table-hover table-striped">
                    <thead>
                        <tr>
                            <th scope="col">更改人員</th>
                            <th scope="col">修改時間</th>
                            <th scope="col" class="text-end">當代獎金</th>
                            <th scope="col" class="text-end">上代獎金</th>
                            <th scope="col">上代推薦人員</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Hans</td>
                            <td>{{ date('Y/m/d H:i:s', strtotime('2022/7/4 15:45:55')) }}</td>
                            <td class="text-end">$ {{ number_format(0) }}</td>
                            <td class="text-end">$ {{ number_format(0) }}</td>
                            <td>Hans2.0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        <a href="{{ Route('cms.order.detail', ['id' => $id]) }}" 
            class="btn btn-outline-primary px-4" role="button">返回明細</a>
    </div>
@endsection

@once
    @push('sub-scripts')
    <script>
        $('.nav-link').off('click').on('click', function () {
            const $this = $(this);
            const page = $this.hasClass('-page1') ? 'page1' : $this.hasClass('-page2') ? 'page2' : '';

            // tab
            $('.nav-link').removeClass('active').removeAttr('aria-current');
            $this.addClass('active').attr('aria-current', 'page');
            // page
            $('#page1, #page2').prop('hidden', true);
            $(`#${page}`).prop('hidden', false);
        });
    </script>
    @endpush
@endonce