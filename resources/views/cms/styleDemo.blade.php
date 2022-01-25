@extends('layouts.layout')
@section('content')
<x-b-topbar />
<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="d-md-block sidebar collapse">
            <ul class="list-unstyled ps-0 btn-toggle-nav">
                <li>
                    <div class="d-flex justify-content-center p-2">
                        <a href="/" class="btn btn-outline-dark px-4"><i class="bi bi-arrow-left"></i> Back Dashboard</a>
                    </div>
                </li>
            </ul>
        </nav>

        <main class="ms-sm-auto px-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="px-4">
                    <!-- 主內容 -->
                    <fieldset id="1_buttons">
                        <legend class="col-form-label">按鈕</legend>
                        <div>
                            <div>
                                <button type="button" class="btn btn-primary">Primary</button>
                                <button type="button" class="btn btn-success">Success</button>
                                <button type="button" class="btn btn-danger">Danger</button>
                            
                                <button type="button" class="btn btn-primary" disabled>Disabled</button>
                                <button type="button" class="btn btn-primary px-4">Wide</button>
                                <button type="button" class="btn btn-outline-primary">Outline</button>
                                <button type="button" class="btn btn-outline-primary border-dashed" style="font-weight: 500;"><i class="bi bi-plus-circle"></i> 新增</button>
                                <a href="#" class="btn btn-primary" role="button">
                                    <i class="bi bi-plus-lg"></i> 新增
                                </a>
                                <a href="#" class="btn btn-primary" role="button">
                                    <i class="bi bi-arrow-left"></i> 返回
                                </a>
                                <div class="d-flex align-items-center mt-3">
                                    <h2 class="flex-grow-1 mb-0">標題</h2>
                                    <a href="#" class="btn btn-outline-primary -in-header">
                                        <i class="bi bi-plus-circle"></i> 新增1
                                    </a>
                                    <a href="#" class="btn btn-outline-primary -in-header">
                                        <i class="bi bi-plus-lg"></i> 新增2
                                    </a>
                                </div>
                            </div>
                            <div class="mt-2 tableList">
                                <a href="#" data-bs-toggle="tooltip" title="編輯" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="刪除" class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="附件/檔案" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-paperclip"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="溫層" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-thermometer-snow"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="歷程" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-list-check"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="總表查詢" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-list-ul"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="詳細記錄" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-card-list"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="確認報表" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-check2-square"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="派車" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="取貨" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-truck-flatbed"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="點對點" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-arrow-left-right"></i>
                                </a>
                                <a href="#" data-bs-toggle="tooltip" title="權限設定" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-file-earmark-lock2"></i>
                                </a>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset id="2_text">
                        <legend class="col-form-label">文字</legend>
                        <div>
                            <h1>h1. heading 標題</h1>
                            <h2>h2. heading 標題</h2>
                            <h3>h3. heading 標題</h3>
                            <h4>h4. heading 標題</h4>
                            <h5>h5. heading 標題</h5>
                            <h6>h6. heading 標題</h6>

                            <label class="col-form-label">
                                必填欄位 <span class="text-danger">*</span>
                            </label>
                            <p><mark>[mark] tag - 代表被標記或是重點強調的文本，以供參考或標記</mark></p>
                            <p><del>[del] tag - 刪除線文字</del></p>
                            <p><u>[u] tag - 底線文字</u></p>
                            <p><em>[em] tag - 斜體文字</em></p>
                            <p><small>[small] tag - 精美印刷(小字)</small></p>
                            <p><strong>[strong] tag - 粗體文字</strong></p>
                            <p class="text-muted">text-muted 文字</p>

                            <p><s>[s] tag - 代表不再相關或不再準確的元素</s></p>
                            <p><ins>[ins] tag - 補充文字</ins></p>
                            <p><code>a.text</code> - <a href="#" class="-text">超連結文字</a></p>
                            <p>[code] tag - 代表 <code>內行程式</code> 文字</p>
                        </div>
                    </fieldset>

                    <fieldset id="3_content">
                        <legend class="col-form-label">內容</legend>
                        <div>
                            <div class="card shadow p-4 mb-4">
                                <h6>Card</h6>

                                <p>Table <code>.table-striped</code> 條紋行 / <code>.table-hover</code> 滑入行</p>
                                <div class="table-responsive tableOverBox">
                                    <table class="table tableList table-hover table-striped mb-1">
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

                    <fieldset id="4_components">
                        <legend class="col-form-label">元件</legend>
                        <div>
                            <div>
                                {{-- Toast --}}
                                <button type="button" class="btn btn-primary" title="AAA" id="liveToastBtn">
                                    Show live toast
                                </button>

                                {{-- Button trigger modal --}}
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    Launch demo modal
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                ...
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Control --}}
                                <a href="#" data-href="#" data-bs-toggle="modal" data-bs-target="#confirm-delete" type="button" class="btn btn-danger btn-sm">
                                    123
                                </a>
                                <x-b-modal id="confirm-delete">
                                    <x-slot name="title">是否要刪除此人員？</x-slot>
                                    <x-slot name="body">123</x-slot>
                                    <x-slot name="foot">
                                        <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
                                    </x-slot>
                                </x-b-modal>

                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="工具提示框"></i>

                                {{-- 格式化地址 --}}
                                <div calss="form-group">
                                    <label class="col-form-label">
                                        地址 <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group has-validation">
                                        <select class="form-select @error('city_id') is-invalid @enderror" style="max-width:20%" id="city_id" name="city_id">
                                            <option>請選擇</option>
                                            @foreach ($citys as $city)
                                                <option value="{{ $city['city_id'] }}" >{{ $city['city_title'] }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select @error('region_id') is-invalid @enderror" style="max-width:20%" id="region_id" name="region_id">
                                            <option>請選擇</option>
                                            @foreach ($regions as $region)
                                                <option value="{{ $region['region_id'] }}" >{{ $region['region_title'] }}</option>
                                            @endforeach
                                        </select>
                                        <input name="addr" type="text" class="form-control @error('addr') is-invalid @enderror"
                                            value="">
                                        <button class="btn btn-outline-success" type="button" id="format_btn">格式化</button>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge -step">第一步</span>
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
                                    <input type="hidden" name="regions_value">
                                    <div id="chip-group-regions" class="d-flex flex-wrap bd-highlight chipGroup"></div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label" for="select2-multiple">Select2 搜尋多選</label>
                                    <select name="select2[]" id="select2-multiple" multiple class="-select2 -multiple form-select" data-placeholder="可多選">
                                        <option value="1">item 1</option>
                                        <option value="2">item 2</option>
                                        <option value="3">item 3</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label" for="select2">Select2 搜尋單選</label>
                                    <select name="select2[]" id="select2" class="-select2 -single form-select" data-placeholder="請單選">
                                        <option value="" selected disabled>請選擇</option>
                                        <option value="1">item 1</option>
                                        <option value="2">item 2</option>
                                        <option value="3">item 3</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <x-b-editor id="editor" classes="my-3"></x-b-editor>
                            </div>
                            <div>
                                <x-b-calendar id="calendar" readOnly="false" create="true" classes="my-3" ></x-b-calendar>
                            </div>
                        </div>
                    </fieldset>
                    
                    <fieldset id="5_forms">
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
                                        <option value="1">item 1</option>
                                        <option value="2" disabled>item 2</option>
                                        <option value="3">item 3</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-4 mb-3">
                                    <label class="form-label">Textarea</label>
                                    <textarea class="form-control" placeholder="Textarea" rows="1"></textarea>
                                </div>
                                <div class="col-12 col-sm-4 mb-3">
                                    <label class="form-label">Date</label>
                                    <input class="form-control" type="date">
                                </div>
                                <div class="col-12 col-sm-8 mb-3">
                                    <label class="form-label">Files</label>
                                    <input class="form-control" type="file" multiple>
                                </div>
                                <fieldset class="col-12 mb-3">
                                    <legend class="col-form-label p-0 mb-2">Checkbox</legend>
                                    <div class="px-1 pt-1">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="checkbox" type="checkbox" >
                                                Default
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="checkbox" type="checkbox" checked>
                                                Checked
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="checkbox" type="checkbox" disabled>
                                                Disabled
                                            </label>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="col-12 mb-3">
                                    <legend class="col-form-label p-0 mb-2">Radio</legend>
                                    <div class="px-1 pt-1">
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="radio" type="radio" >
                                                Default
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="radio" type="radio" checked>
                                                Checked
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="radio" type="radio" disabled>
                                                Disabled
                                            </label>
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Range</label>
                                    <input class="form-range" type="range" min="0" max="50" step="5">
                                </div>
                                
                                <form class="row">
                                    <h6>Validation</h6>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <input type="text" class="form-control is-valid" value="Valid" required>
                                        <div class="valid-feedback">Good!</div>
                                    </div>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <select class="form-select is-valid" aria-label="Select">
                                            <option value="1">item 1</option>
                                            <option value="2">item 2</option>
                                        </select>
                                        <div class="valid-feedback">Good!</div>
                                    </div>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <textarea class="form-control is-valid" placeholder="Textarea" rows="1"></textarea>
                                        <div class="valid-feedback">Good!</div>
                                    </div>

                                    <div class="col-12 col-sm-4 mb-3">
                                        <input type="text" class="form-control is-invalid" value="" required>
                                        <div class="invalid-feedback">Bad!</div>
                                    </div>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <select class="form-select is-invalid" aria-label="Select">
                                            <option value="1">item 1</option>
                                            <option value="2">item 2</option>
                                        </select>
                                        <div class="invalid-feedback">Bad!</div>
                                    </div>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <textarea class="form-control is-invalid" placeholder="Textarea" rows="1"></textarea>
                                        <div class="invalid-feedback">Bad!</div>
                                    </div>

                                    <div class="col-12 col-sm-4 mb-3">
                                        <div class="input-group has-validation">
                                            <span class="input-group-text">@</span>
                                            <input type="text" class="form-control is-valid" value="abc" required>
                                            <div class="valid-feedback">Good!</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <div class="input-group has-validation">
                                            <input type="text" class="form-control is-invalid" value="" required>
                                            <button class="btn btn-outline-secondary" type="button"><i class="bi bi-x-lg"></i></button>
                                            <div class="invalid-feedback">Bad!</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <div class="input-group has-validation">
                                            <span class="input-group-text">$</span>
                                            <input type="text" class="form-control is-valid" value="9" required>
                                            <span class="input-group-text">.00</span>
                                            <div class="valid-feedback">Good!</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4 mb-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input is-invalid" name="checkbox" type="checkbox" >
                                            <label class="form-check-label">Ban to terms</label>
                                            <div class="invalid-feedback">Bad!</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-8 mb-3">
                                        <div class="form-check form-check-inline is-valid">
                                            <input class="form-check-input is-valid" name="radioValid" type="radio" >
                                            <label class="form-check-label">Agree to terms</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input is-valid" name="radioValid" type="radio" >
                                            <label class="form-check-label">Agree to terms</label>
                                        </div>
                                        <div class="valid-feedback">Good!</div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </fieldset>

                </div>
            </div>
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
            main > div > div > fieldset[id]:target::before,
            main > div > div > fieldset[id]:not(:target)::before {
                content: "";
                display: block;
                /* height: 60px; fixed header height*/
                margin: 60px 0 0; /* negative fixed header height */
            }
            main > div > div {
                margin-top: -60px;
            }
            main > div > div > fieldset[id]:not(:last-child) {
                margin-bottom: -50px;
            }
            main > div > div > fieldset[id] > legend.col-form-label {
                font-size: 1.5rem;
                font-weight: 500;
                position: relative;
                padding-left: 16px;
                display: flex;
                align-items: center;
            }
            main > div > div > fieldset[id] > legend.col-form-label::before {
                content: '';
                display: block;
                position: absolute;
                width: 8px;
                height: 56%;
                background-color: #c2185b;
                left: 0;
            }

            /*********/
        </style>
        @stack('sub-styles')
    @endpush
    @push('scripts')
        <script src="{{ Asset('dist/js/dashboard.js') }}"></script>
        <script src="{{ Asset('dist/js/helpers.js') }}"></script>
        <script src="{{ Asset('dist/js/components.js') }}"></script>
        <script>
            window.axios.defaults.headers.common['Accept'] = 'application/json';

            // menu
            $('main > div > div > fieldset[id]').each(function (index, element) {
                // element == this
                let $li = $('<li></li>');
                let $a = $('<a></a>').addClass('link-dark nav-link');
                $a.text($(element).children('legend').text());
                $a.attr('href', '#' + element.id);
                $li.append($a);
                $('#sidebarMenu > ul').append($li);
            });
            $('#sidebarMenu.sidebar ul.btn-toggle-nav li').on('click', function () {
                $('#sidebarMenu.sidebar ul.btn-toggle-nav li').removeClass('active');
                $(this).addClass('active');
            });
        </script>
        <script>
            // Toast Trigger
            var toastTrigger = $('#liveToastBtn');
            if (toastTrigger) {
                toastTrigger.on('click', function() {
                    toast.show('測試測試測試測試測試測試測試測試測試', { title: '錯誤錯誤!', type: 'danger' });
                });
            }

            // Modal Control
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            // 格式化地址
            let cityElem = $('#city_id');
            let regionElem = $('#region_id')
            let addrInputElem = $('input[name=addr]');
            cityElem.on('change', function(e) {
                getRegionsAction($(this).val());
            });
            function getRegionsAction(city_id, region_id) {
                Addr.getRegions(city_id)
                    .then(re => {
                        Elem.renderSelect(regionElem, re.datas, {
                            default: region_id,
                            key: 'region_id',
                            value: 'region_title'
                        });
                    });
            }
            $('#format_btn').on('click', function(e) {
                let addr = addrInputElem.val();

                if (addr) {
                    Addr.addrFormating(addr).then(re => {
                        addrInputElem.val(re.data.addr);
                        if (re.data.city_id) {
                            cityElem.val(re.data.city_id);
                            getRegionsAction(re.data.city_id, re.data.region_id);

                        }
                    });
                }
            });

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
                $('input[name="regions_value"]').val(selectRegion);
            });
            // X btn
            Chips_regions.onDelete = function(id) {
                selectRegion.splice(selectRegion.indexOf(id), 1);
                $('input[name="regions_value"]').val(selectRegion);
            };
            // 清空
            $('#clear_region').on('click', function(e) {
                selectRegion = [];
                Chips_regions.clear();
                $('input[name="regions_value"]').val(selectRegion);
                e.preventDefault();
            });

            // select2
            // $('.-select2').select2();

            Editor.createEditor('editor', {
                initialValue: '<h2>Header</h2><p>iewo reiu8ud jijh3 dsl</p>'
            });
        </script>
        @stack('sub-scripts')
    @endpush
@endonce