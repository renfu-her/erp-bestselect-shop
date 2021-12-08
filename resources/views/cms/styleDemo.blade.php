@extends('layouts.layout')
@section('content')
<x-b-topbar />
<div class="container-fluid">
    <div class="row">
        <main class="w-100">
            <!-- 主內容 -->
            <fieldset>
                <legend class="col-form-label">按鈕</legend>
                <div>
                    <div>
                        <button type="button" class="btn btn-primary">Primary</button>
                        <button type="button" class="btn btn-success">Success</button>
                        <button type="button" class="btn btn-danger">Danger</button>
                    
                        <button type="button" class="btn btn-primary" disabled>Disabled</button>
                        <button type="button" class="btn btn-primary px-4">Wide</button>
                        <button type="button" class="btn btn-outline-primary">Outline</button>
                        <a href="#" class="btn btn-primary" role="button">
                            <i class="bi bi-plus-lg"></i> 新增
                        </a>
                        <a href="#" class="btn btn-primary" role="button">
                            <i class="bi bi-arrow-left"></i> 返回
                        </a>
                    </div>
                    <div class="mt-2 tableList">
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                            <i class="bi bi-trash"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-paperclip"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-thermometer-snow"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-list-check"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-list-ul"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-card-list"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-check2-square"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-truck"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-truck-flatbed"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-arrow-left-right"></i>
                        </a>
                        <a href="#" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-file-earmark-lock2"></i>
                        </a>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class="col-form-label">文字</legend>
                <div>
                    <h1>h1. heading 標題</h1>
                    <h2>h2. heading 標題</h2>
                    <h3>h3. heading 標題</h3>
                    <h4>h4. heading 標題</h4>
                    <h5>h5. heading 標題</h5>
                    <h6>h6. heading 標題</h6>

                    <p><mark>[mark] tag - 代表被標記或是重點強調的文本，以供參考或標記</mark></p>
                    <p><del>[del] tag - 刪除線文字</del></p>
                    <p><u>[u] tag - 底線文字</u></p>
                    <p><em>[em] tag - 斜體文字</em></p>
                    <p><small>[small] tag - 精美印刷(小字)</small></p>
                    <p><strong>[strong] tag - 粗體文字</strong></p>

                    <p><s>[s] tag - 代表不再相關或不再準確的元素</s></p>
                    <p><ins>[ins] tag - 補充文字</ins></p>
                    <p><a href="#">超連結文字</a></p>
                    <p>[code] tag - 代表 <code>內行程式</code> 文字</p>
                </div>
            </fieldset>

            <fieldset>
                <legend class="col-form-label">內容</legend>
                <div>
                    <div class="card shadow p-4 mb-4">
                        <div class="row mb-4">Card</div>

                        <p>Table <code>.table-striped</code> 條紋行 / <code>.table-hover</code> 滑入行</p>
                        <div class="table-responsive tableOverBox">
                            <table class="table table-striped tableList mb-0 table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width:10%">#</th>
                                        <th scope="col">Left</th>
                                        <th scope="col" class="text-center">Center</th>
                                        <th scope="col" class="text-right">Right</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-success">
                                        <th scope="row">1</th>
                                        <td>Default</td>
                                        <td class="text-center">常溫</td>
                                        <td class="text-right">$99,999</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">2</th>
                                        <td>Default</td>
                                        <td class="text-center table-warning">常溫</td>
                                        <td class="text-right">$99,999</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">3</th>
                                        <td>Default</td>
                                        <td class="text-center">常溫</td>
                                        <td class="text-right">$99,999</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th scope="row"></th>
                                        <td>Default</td>
                                        <td class="text-center">常溫</td>
                                        <td class="text-right">$99,999</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend class="col-form-label">元件</legend>
                <div>
                    <div>
                        <span class="badge -step user">第一步</span>
                        <span class="badge -step admin">第一步</span>
                        <span class="badge -step deliveryman">第一步</span>
                    </div>
                    <div class="row mt-2">
                        <div class="col-4">
                            <label class="form-label" for="region">Region 多選</label>
                            <div class="input-group">
                                <select id="region" class="form-select">
                                    <option value="" selected>請選擇</option>
                                    <option value="1">item 1</option>
                                    <option value="2">item 2</option>
                                    <option value="3">item 3</option>
                                </select>
                                <button id="clear_region" class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="清空">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div id="chip-group-regions" class="d-flex flex-wrap bd-highlight chipGroup"></div>
                        </div>
                        <div class="col-4">
                            <label class="form-label" for="select2">Select2 搜尋多選</label>
                            <select name="select2[]" id="select2" multiple="multiple" class="w-100">
                                <option value="1">item 1</option>
                                <option value="2">item 2</option>
                                <option value="3">item 3</option>
                            </select>
                        </div>
                    </div>
                    
                </div>
            </fieldset>
            
            <fieldset>
                <legend class="col-form-label">表單</legend>
                <div>
                    <div class="row">
                        <div class="col-12 col-sm-4 mb-3">
                            <label class="form-label">Input</label>
                            <input class="form-control" type="text" placeholder="Placeholder" aria-label="Input">
                        </div>
                        <div class="col-12 col-sm-4 mb-3">
                            <label class="form-label">Select</label>
                            <select class="form-select" aria-label="Select">
                                <option value="1">item</option>
                                <option value="2">item</option>
                                <option value="3">item</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4 mb-3">
                            <label class="form-label">Textarea</label>
                            <textarea class="form-control" placeholder="Textarea" rows="1"></textarea>
                        </div>
                    </div>
                </div>
            </fieldset>

        </main>
    </div>
</div>
<x-b-toast />
@endsection

@once
    @push('styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/sub-content.css') }}">
        <link rel="stylesheet" href="{{ Asset('dist/css/component.css') }}">
        <style>
            fieldset legend.col-form-label {
                font-size: 1.5rem;
                font-weight: 500;
                position: relative;
                padding-left: 16px;
                display: flex;
                align-items: center;
            }
            fieldset legend.col-form-label::before {
                content: '';
                display: block;
                position: absolute;
                width: 8px;
                height: 56%;
                background-color: #c2185b;
                left: 0;
            }
        </style>
    @endpush
    @push('scripts')
        <script src="{{ Asset('dist/js/dashboard.js') }}"></script>
        <script src="{{ Asset('dist/js/helpers.js') }}"></script>
        <script src="{{ Asset('dist/js/components.js') }}"></script>
        <script>
            // window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + Laravel.apiToken;
            window.axios.defaults.headers.common['Accept'] = 'application/json';
        </script>
        <script>
            // region
            let selectRegion = [];
            let Chips_regions = new ChipElem($('#chip-group-regions'));
            $('#region').off('change.chips').on('change.chips', function(e) {
                let region = { val: $(this).val(), title: $(this).children(':selected').text()};
                if (selectRegion.indexOf(region.val) === -1) {
                    selectRegion.push(region.val);
                    Chips_regions.add(region.val, region.title);
                }
                
                $(this).val('');
            });
            // X btn
            Chips_regions.onDelete = function(id) {
                selectRegion.splice(selectRegion.indexOf(id), 1);
            };
            // 清空
            $('#clear_region').on('click', function(e) {
                selectRegion = [];
                Chips_regions.clear();
                e.preventDefault();
            });

            // select2
            $('#select2').select2();
        </script>
    @endpush
@endonce