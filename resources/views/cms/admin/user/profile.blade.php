@extends('layouts.main')
@section('sub-content')
    @php
        $editMode = $method == 'edit';
        $empty = '';
    @endphp
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.user.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
        @if ($editMode)
            <a href="{{ Route('cms.user.profile', ['id' => $id], true) }}" class="btn btn-outline-danger">取消編輯</a>
        @else
            <a href="{{ Route('cms.user.editProfile', ['id' => $id], true) }}" class="btn btn-success">編輯</a>
        @endif
    </div>

    <form action="" method="post">
        <div class="card mb-4">
            <div class="card-body">
                <table id="profile_table" class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th>員工姓名</th>
                            <td>{{ $data->name }}</td>
                            <th>職　　號</th>
                            <td>{{ $data->account }}</td>
                        </tr>
                        <tr>
                            <th>英文名字</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="en_name"
                                        value="{{ $data->en_name }}" placeholder="{{ $data->en_name }}">
                                </td>
                            @else
                                <td>{{ $data->en_name }}</td>
                            @endif
                            <td id="m_photo" colspan="2" rowspan="5" class="w-50">
                                <img src="{{ $data->img ?? Asset('images/NoImg.png') }}" alt="大頭照">
                                @if ($editMode)
                                    <input class="form-control form-control-sm" type="file" name="img">
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>身分證號</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="identity"
                                        value="{{ $data->identity }}" placeholder="{{ $data->identity }}">
                                </td>
                            @else
                                <td>{{ $data->identity }}</td>
                            @endif
                        </tr>
                        
                        <tr>
                            <th>性　　別</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="gender" type="radio" value="男"
                                                @if ('男' == '男') checked @endif>
                                            男
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="gender" type="radio" value="女"
                                                @if (false == '女') checked @endif>
                                            女
                                        </label>
                                    </div>
                                @else
                                     男
                                @endif
                            </td>
                        </tr>
                        
                        <tr>
                            <th>家人同住</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="live_with_family" type="radio"
                                                value="是" @if ($data->live_with_family == '是') checked @endif>
                                            是
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="live_with_family" type="radio"
                                                value="否" @if ($data->live_with_family == '否') checked @endif>
                                            否
                                        </label>
                                    </div>
                                @else
                                    {{ $data->live_with_family }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>業績統計</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="performance_statistics" type="radio"
                                                value="是" @if ($data->performance_statistics == '是') checked @endif>
                                            是
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="performance_statistics" type="radio"
                                                value="否" @if ($data->performance_statistics == '否') checked @endif>
                                            否
                                        </label>
                                    </div>
                                @else
                                    {{ $data->performance_statistics }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>部　　門</th>
                            <td>資訊部-資訊二組</td>
                            <th>職　　稱</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="job_title"
                                        value="{{ $data->job_title }}" placeholder="{{ $data->job_title }}">
                                </td>
                            @else
                                <td>{{ $data->job_title }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>到 職 日</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="date" name="date_of_job_entry"
                                        value="{{ $data->date_of_job_entry }}">
                                </td>
                            @else
                                <td>
                                    {{ $data->date_of_job_entry ? date('Y/m/d', strtotime($data->date_of_job_entry)) : '' }}
                                </td>
                            @endif
                            <th>離 職 日</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="date" name="date_of_job_leave"
                                        value="{{ $data->date_of_job_leave }}">
                                </td>
                            @else
                                <td>
                                    {{ $data->date_of_job_leave ? date('Y/m/d', strtotime($data->date_of_job_leave)) : '' }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>服務時間</th>
                            <td>3年1個月</td>
                            <th>特休天數</th>
                            {{-- https://www.hsihung.com.tw/intranet/employee/vday2B.asp?n1=%A4%FD%AF%F8%AC%DC --}}
                            <td>15</td>
                        </tr>
                        <tr>
                            <th>英文能力</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="ability_english"
                                        value="{{ $data->ability_english }}" placeholder="{{ $data->ability_english }}">
                                </td>
                            @else
                                <td>
                                    {{ $data->ability_english ? $data->ability_english : '無' }}
                                </td>
                            @endif
                            <th>英文證照</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="english_certification"
                                        value="{{ $data->english_certification }}" placeholder="{{ $data->english_certification }}">
                                </td>
                            @else
                                <td>
                                    {{ $data->english_certification ? $data->english_certification : '無' }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>日文能力</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="ability_japanese"
                                        value="{{ $data->ability_japanese }}" placeholder="{{ $data->ability_japanese }}">
                                </td>
                            @else
                                <td>
                                    {{  $data->ability_japanese ?  $data->ability_japanese : '無' }}
                                </td>
                            @endif
                            <th>日文證照</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="japanese_certification"
                                        value="{{ $data->japanese_certification }}" placeholder="{{ $data->japanese_certification }}">
                                </td>
                            @else
                                <td>
                                    {{  $data->japanese_certification ?  $data->japanese_certification : '無' }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>教育訓練</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="教育訓練"
                                        value="完成初級 [未進行中級] [未進行高級] [未進行專業級]" placeholder="完成初級 [未進行中級] [未進行高級] [未進行專業級]">
                                </td>
                            @else
                                <td colspan="3">
                                    完成初級 [未進行中級] [未進行高級] [未進行專業級]
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>勞動契約</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="勞動契約" type="radio" value="1"
                                                @if (true) checked @endif>
                                            已繳
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="勞動契約" type="radio" value="0"
                                                @if (false) checked @endif>
                                            未繳
                                        </label>
                                    </div>
                                @else
                                    @if (true)
                                        已繳
                                    @else
                                        未繳
                                    @endif
                                @endif
                            </td>
                            <th>承攬契約</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="承攬契約" type="radio" value="1"
                                                @if (false) checked @endif>
                                            已繳
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="承攬契約" type="radio" value="0"
                                                @if (true) checked @endif>
                                            未繳
                                        </label>
                                    </div>
                                @else
                                    @if (false)
                                        已繳
                                    @else
                                        未繳
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>異動狀態</th>
                            <td colspan="3">
                                <div class="d-flex">
                                    <div>加入：{{ $data->date_of_job_entry ? date('Y/m/d', strtotime($data->date_of_job_entry)) : '' }}</div>
                                    <div class="ms-5">退出：{{ $data->date_of_job_leave ? date('Y/m/d', strtotime($data->date_of_job_leave)) : '' }}</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="small">勞健保狀態</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1 ps-2">
                                    <div class="d-flex">
                                        <label class="text-nowrap">加入：</label>
                                        <input class="form-control form-control-sm" type="date"
                                            name="date_of_insurance_entry" value="{{ $data->date_of_insurance_entry }}">
                                        <label class="text-nowrap ms-2">退出：</label>
                                        <input class="form-control form-control-sm" type="date"
                                            name="date_of_insurance_leave" value="{{ $data->date_of_insurance_leave }}">
                                    </div>
                                </td>
                            @else
                                <td colspan="3">
                                    <div class="d-flex">
                                        <div>加入：{{ $data->date_of_insurance_entry ? date('Y/m/d', strtotime($data->date_of_insurance_entry)) : '' }}</div>
                                        <div class="ms-5">退出：{{ $data->date_of_insurance_leave ? date('Y/m/d', strtotime($data->date_of_insurance_leave)) : '' }}</div>
                                    </div>
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>勞保金額</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="number" name="labor_insurance"
                                        value="{{ $data->labor_insurance }}" placeholder="{{ $data->labor_insurance }}">
                                </td>
                            @else
                                <td>
                                    ${{ number_format($data->labor_insurance) }}
                                </td>
                            @endif
                            <th class="small">勞保自付額</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="number" name="labor_insurance_oop"
                                        value="{{ $data->labor_insurance_oop }}" placeholder="{{ $data->labor_insurance_oop }}">
                                </td>
                            @else
                                <td>
                                    ${{ number_format($data->labor_insurance_oop) }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>健保金額</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="number" name="health_insurance"
                                        value="{{ $data->health_insurance }}" placeholder="{{ $data->health_insurance }}">
                                </td>
                            @else
                                <td>
                                    ${{ number_format($data->health_insurance) }}
                                </td>
                            @endif
                            <th class="small">健保自付額</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="number" name="health_insurance_oop" 
                                        value="{{ $data->health_insurance_oop }}" placeholder="{{ $data->health_insurance_oop }}">
                                </td>
                            @else
                                <td>
                                    ${{ number_format($data->health_insurance_oop) }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th class="small">勞退投保金額</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="number" name="勞退投保金額"
                                        value="99999" placeholder="99999">
                                </td>
                            @else
                                <td>
                                    ${{ number_format(99999) }}
                                </td>
                            @endif
                            <th>健保眷屬</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <div class="d-flex">
                                        <input class="form-control form-control-sm" type="number" name="health_insurance_dependents"
                                            value="{{ $data->health_insurance_dependents }}" placeholder="{{ $data->health_insurance_dependents }}">
                                        <span class="ms-1">人</span>
                                    </div>
                                </td>
                            @else
                                <td>
                                    {{ $data->health_insurance_dependents }} 人
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>公司提撥</th>
                            <td>6% (${{ number_format(9999) }})</td>
                            <th>自行提撥</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="number" name="自行提撥"
                                        value="" placeholder="">
                                </td>
                            @else
                                <td>
                                    {{ $empty ? $empty : '無' }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>聯絡電話</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="tel" name="tel"
                                        value="{{ $data->tel }}" placeholder="{{ $data->tel }}">
                                </td>
                            @else
                                <td>{{ $data->tel }}</td>
                            @endif
                            <th>戶籍電話</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="tel" name="household_tel"
                                        value="{{ $data->household_tel }}" placeholder="{{ $data->household_tel }}">
                                </td>
                            @else
                                <td>{{ $data->household_tel }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>行動電話</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="tel" name="phone"
                                        value="{{ $data->phone }}" placeholder="{{ $data->phone }}">
                                </td>
                            @else
                                <td>{{ $data->phone }}</td>
                            @endif
                            <th>日本手機</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="tel" name="日本手機"
                                        value="" placeholder="">
                                </td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                        <tr>
                            <th>公司電話</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <div class="d-flex">
                                        <input class="form-control form-control-sm" type="tel" name="office_tel"
                                            value="{{ $data->office_tel }}" placeholder="{{ $data->office_tel }}">
                                        <label class="ms-1">#</label>
                                        <input class="form-control form-control-sm" type="tel"
                                            style="max-width: 50px;" name="office_tel_ext" value="{{ $data->office_tel_ext }}"
                                            placeholder="{{ $data->office_tel_ext }}">
                                    </div>
                                </td>
                            @else
                                <td>
                                    {{ $data->office_tel }} {{ $data->office_tel_ext ? '分機'.$data->office_tel_ext : '' }}
                                </td>
                            @endif
                            <th>公司傳真</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="tel" name="office_fax"
                                        value="{{ $data->office_fax }}" placeholder="{{ $data->office_fax }}">
                                </td>
                            @else
                                <td>{{ $data->office_fax }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th class="small">緊急聯絡人姓名</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="contact_person"
                                        value="{{ $data->contact_person }}" placeholder="{{ $data->contact_person }}">
                                </td>
                            @else
                                <td>{{ $data->contact_person }}</td>
                            @endif
                            <th class="small">緊急聯絡人電話</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="tel" name="contact_person_tel"
                                        value="{{ $data->contact_person_tel }}" placeholder="{{ $data->contact_person_tel }}">
                                </td>
                            @else
                                <td>{{ $data->contact_person_tel }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>生　　日</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="date" name="birthday"
                                        value="{{ $data->birthday }}">
                                </td>
                            @else
                                <td>
                                    {{ $data->birthday ? date('Y/m/d', strtotime($data->birthday)) : '' }}
                                </td>
                            @endif
                            <th>血　　型</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <select class="form-select form-select-sm" name="blood_type">
                                        <option value="A" @if ($data->blood_type == 'A')selected @endif>A</option>
                                        <option value="B" @if ($data->blood_type == 'B')selected @endif>B</option>
                                        <option value="O" @if ($data->blood_type == 'O')selected @endif>O</option>
                                        <option value="AB" @if ($data->blood_type == 'AB')selected @endif>AB</option>
                                    </select>
                                </td>
                            @else
                                <td>{{ $data->blood_type }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>最高學歷</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="education"
                                        value="{{ $data->education }}" placeholder="{{ $data->education }}">
                                </td>
                            @else
                                <td>{{ $data->education }}</td>
                            @endif
                            <th>科　　系</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="education_department" 
                                        value="{{ $data->education_department }}" placeholder="{{ $data->education_department }}">
                                </td>
                            @else
                                <td>{{ $data->education_department }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>是否打卡</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="punch_in" type="radio"
                                                value="是" @if ($data->punch_in == '是') checked @endif>
                                            是
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="punch_in" type="radio"
                                                value="否" @if ($data->punch_in == '否') checked @endif>
                                            否
                                        </label>
                                    </div>
                                @else
                                    {{ $data->punch_in }}
                                @endif
                            </td>
                            <th>服務地區</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <select class="form-select form-select-sm" name="service_area">
                                        <option value=""></option>
                                        <option value="企業專案" @if ($data->service_area == '企業專案')selected @endif>企業專案</option>
                                        <option value="國內旅遊" @if ($data->service_area == '國內旅遊')selected @endif>國內旅遊</option>
                                        <option value="台北" @if ($data->service_area == '台北')selected @endif>台北</option>
                                        <option value="中壢" @if ($data->service_area == '中壢')selected @endif>中壢</option>
                                        <option value="桃園" @if ($data->service_area == '桃園')selected @endif>桃園</option>
                                        <option value="苗栗" @if ($data->service_area == '苗栗')selected @endif>苗栗</option>
                                        <option value="新竹" @if ($data->service_area == '新竹')selected @endif>新竹</option>
                                        <option value="豐原" @if ($data->service_area == '豐原')selected @endif>豐原</option>
                                        <option value="台中" @if ($data->service_area == '台中')selected @endif>台中</option>
                                        <option value="台南" @if ($data->service_area == '台南')selected @endif>台南</option>
                                        <option value="嘉義" @if ($data->service_area == '嘉義')selected @endif>嘉義</option>
                                        <option value="高雄" @if ($data->service_area == '高雄')selected @endif>高雄</option>
                                        <option value="獎勵旅遊" @if ($data->service_area == '獎勵旅遊')selected @endif>獎勵旅遊</option>
                                    </select>
                                </td>
                            @else
                                <td>{{ $data->service_area }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>公司地址</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="office_address"
                                        value="{{ $data->office_address }}" placeholder="{{ $data->office_address }}">
                                </td>
                            @else
                                <td colspan="3">{{ $data->office_address }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>聯絡地址</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="address"
                                        value="{{ $data->address }}" placeholder="{{ $data->address }}">
                                </td>
                            @else
                                <td colspan="3">{{ $data->address }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>戶籍地址</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="household_address"
                                        value="{{ $data->household_address }}" placeholder="{{ $data->household_address }}">
                                </td>
                            @else
                                <td colspan="3">{{ $data->household_address }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>個人信箱</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="email" name="email"
                                        value="{{ $data->email }}" placeholder="{{ $data->email }}">
                                </td>
                            @else
                                <td colspan="3">{{ $data->email }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>DISC類型</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="disc_category"
                                        value="{{ $data->disc_category }}" placeholder="{{ $data->disc_category }}">
                                </td>
                            @else
                                <td colspan="3">{{ $data->disc_category }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th class="small">特殊證照專長</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="certificates"
                                        value="{{ $data->certificates }}" placeholder="{{ $data->certificates }}">
                                </td>
                            @else
                                <td colspan="3">{{ $data->certificates }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>保險證照</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="insurance_certification" 
                                        value="{{ $data->insurance_certification }}" placeholder="{{ $data->insurance_certification }}">
                                </td>
                            @else
                                <td colspan="3">{{ $data->insurance_certification }}</td>
                            @endif
                        </tr>
                        <tr>
                            <th>經 理 證</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="經理證" type="radio" value="1"
                                                @if (false) checked @endif>
                                            有
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="經理證" type="radio" value="0"
                                                @if (true) checked @endif>
                                            無
                                        </label>
                                    </div>
                                @else
                                    無
                                @endif
                            </td>
                            <th>領 隊 證</th>
                            <td>
                                @if ($editMode)
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="領隊證" type="radio" value="1"
                                                @if (false) checked @endif>
                                            有
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input class="form-check-input" name="領隊證" type="radio" value="0"
                                                @if (true) checked @endif>
                                            無
                                        </label>
                                    </div>
                                @else
                                    無
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="small">領隊證領取日</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="date" name="領隊證領取日"
                                        value="">
                                </td>
                            @else
                                <td>
                                    {{ '' ? date('Y/m/d', strtotime('')) : '' }}
                                </td>
                            @endif
                            <th class="small">領隊證有效日</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="date" name="領隊證有效日"
                                        value="">
                                </td>
                            @else
                                <td>
                                    {{ '' ? date('Y/m/d', strtotime('')) : '' }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th class="small">領隊證校正日</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="date" name="領隊證校正日"
                                        value="">
                                </td>
                            @else
                                <td>
                                    {{ '' ? date('Y/m/d', strtotime('')) : '' }}
                                </td>
                            @endif
                            <th class="small">領隊語言別</th>
                            @if ($editMode)
                                <td class="p-1">
                                    <input class="form-control form-control-sm" type="text" name="領隊語言別"
                                        value="" placeholder="">
                                </td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                        <tr>
                            <th>其他項目</th>
                            @if ($editMode)
                                <td colspan="3" class="py-1">
                                    <div>
                                        <label>特殊人士：</label>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="特殊人士" type="radio"
                                                    value="1" @if (false) checked @endif>
                                                是
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="特殊人士" type="radio"
                                                    value="0" @if (true) checked @endif>
                                                否
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label>領有身心障礙手冊：</label>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="領有身心障礙手冊" type="radio"
                                                    value="1" @if (false) checked @endif>
                                                是
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input class="form-check-input" name="領有身心障礙手冊" type="radio"
                                                    value="0" @if (true) checked @endif>
                                                否
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td colspan="3">
                                    <ul class="mb-0">
                                        <li>特殊人士：否</li>
                                        <li>領有身心障礙手冊：否</li>
                                    </ul>
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>資歷簡介</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <textarea name="history" class="form-control form-control-sm">{{ $data->history }}</textarea>
                                </td>
                            @else
                                <td colspan="3">
                                    {{ $data->history }}
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th class="small">資歷服務時間</th>
                            @if ($editMode)
                                <td colspan="3" class="py-1">
                                    <div class="d-flex">
                                        <label class="text-nowrap">旅行社服務年資合計：</label>
                                        <input class="form-control form-control-sm" type="number" name="旅行社服務年資"
                                            value="7" placeholder="7">
                                        <span class="ms-1">年</span>
                                    </div>
                                    <div class="d-flex">
                                        <label class="text-nowrap">非旅行社服務年資合計：</label>
                                        <input class="form-control form-control-sm" type="number" name="非旅行社服務年資"
                                            value="0" placeholder="0">
                                        <span class="ms-1">年</span>
                                    </div>
                                </td>
                            @else
                                <td colspan="3">
                                    <ul class="mb-0">
                                        <li>旅行社服務年資合計：7年</li>
                                        <li>非旅行社服務年資合計：0年</li>
                                    </ul>
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th>備　　註</th>
                            @if ($editMode)
                                <td colspan="3" class="p-1">
                                    <textarea name="note" class="form-control form-control-sm">{{ $data->note }}</textarea>
                                </td>
                            @else
                                <td colspan="3">
                                    {{ $data->note }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection

@once
    @push('sub-styles')
        <style>
            #profile_table {
                vertical-align: middle;
            }

            #m_photo {
                text-align: center;
            }

            #m_photo img {
                max-width: 90%;
                max-height: 80%;
                width: 135px;
                height: 180px;
            }

            #profile_table .d-flex {
                align-items: center;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script></script>
    @endpush
@endonce
