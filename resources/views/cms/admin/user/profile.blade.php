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
                        <td>王烏梅</td>
                        <th>職　　號</th>
                        <td>08079</td>
                    </tr>
                    <tr>
                        <th>英文名字</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text" 
                                    name="en_name" value="May" placeholder="May">
                            </td>
                        @else
                            <td>
                                May
                            </td>
                        @endif
                        <td id="m_photo" colspan="2" rowspan="5" class="w-50">
                            <img src="{{ Asset('images/NoImg.png') }}" alt="">
                            @if ($editMode)
                                <input class="form-control form-control-sm" type="file" name="img">
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>身分證號</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text" 
                                    name="identity" value="A123456789" placeholder="A123456789">
                            </td>
                        @else
                            <td>
                                A123456789
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>性　　別</th>
                        <td>
                            @if ($editMode)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="gender" type="radio" 
                                            value="男" @if (false) checked @endif>
                                        男
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="gender" type="radio" 
                                            value="女" @if (true) checked @endif>
                                        女
                                    </label>
                                </div>
                            @else
                                女
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
                                            value="1" @if (false) checked @endif>
                                        是
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="live_with_family" type="radio" 
                                            value="0" @if (true) checked @endif>
                                        否
                                    </label>
                                </div>
                            @else
                                否
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
                                            value="1" @if (false) checked @endif>
                                        是
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="performance_statistics" type="radio" 
                                            value="0" @if (true) checked @endif>
                                        否
                                    </label>
                                </div>
                            @else
                                否
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>部　　門</th>
                        <td>資訊部-資訊二組</td>
                        <th>職　　稱</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="job_title" value="軟體工程師" placeholder="軟體工程師" >
                            </td>
                        @else
                            <td>
                                軟體工程師
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>到 職 日</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="date"
                                    name="date_of_job_entry" value="2020-09-10" >
                            </td>
                        @else
                            <td>
                                {{ true ? date('Y/m/d', strtotime('2020-09-10')) : '' }}
                            </td>
                        @endif
                        <th>離 職 日</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="date"
                                    name="date_of_job_leave" value="XXX" >
                            </td>
                        @else
                            <td>
                                {{ $empty ? date('Y/m/d', strtotime('')) : '' }}
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>服務時間</th>
                        <td>3年1個月</td>
                        <th>
                            <a href="https://www.hsihung.com.tw/intranet/employee/vday2B.asp?n1=%A4%FD%AF%F8%AC%DC">特休天數</a>
                        </th>
                        <td>15</td>
                    </tr>
                    <tr>
                        <th>英文能力</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="ability_english" value="" placeholder="" >
                            </td>
                        @else
                            <td>
                                {{ $empty ? $empty : '無' }}
                            </td>
                        @endif
                        <th>英文證照</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="english_certification" value="" placeholder="" >
                            </td>
                        @else
                            <td>
                                {{ $empty ? $empty : '無' }}
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>日文能力</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="ability_japanese" value="" placeholder="" >
                            </td>
                        @else
                            <td>
                                {{ $empty ? $empty : '無' }}
                            </td>
                        @endif
                        <th>日文證照</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="japanese_certification" value="" placeholder="" >
                            </td>
                        @else
                            <td>
                                {{ $empty ? $empty : '無' }}
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>教育訓練</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="教育訓練" value="完成初級 [未進行中級] [未進行高級] [未進行專業級]" placeholder="完成初級 [未進行中級] [未進行高級] [未進行專業級]" >
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
                                        <input class="form-check-input" name="勞動契約" type="radio" 
                                            value="1" @if (true) checked @endif>
                                        已繳
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="勞動契約" type="radio" 
                                            value="0" @if (false) checked @endif>
                                        未繳
                                    </label>
                                </div>
                            @else
                                @if (true) 已繳 @else 未繳 @endif
                            @endif
                        </td>
                        <th>承攬契約</th>
                        <td>
                            @if ($editMode)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="承攬契約" type="radio" 
                                            value="1" @if (false) checked @endif>
                                        已繳
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="承攬契約" type="radio" 
                                            value="0" @if (true) checked @endif>
                                        未繳
                                    </label>
                                </div>
                            @else
                                @if (false) 已繳 @else 未繳 @endif
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>異動狀態</th>
                        <td colspan="3">
                            <div class="d-flex">
                                <div>加入：2020/09/10</div>
                                <div class="ms-4">退出：</div>
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
                                        name="date_of_insurance_entry" value="2020-09-11" >
                                    <label class="text-nowrap ms-2">退出：</label>
                                    <input class="form-control form-control-sm" type="date"
                                        name="date_of_insurance_leave" value="" >
                                </div>
                            </td>
                        @else
                            <td colspan="3">
                                <div class="d-flex">
                                    <div>加入：{{ true ? date('Y/m/d', strtotime('2020-09-11')) : '' }}</div>
                                    <div class="ms-4">退出：{{ '' ? date('Y/m/d', strtotime('')) : '' }}</div>
                                </div>
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>勞保金額</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="number"
                                    name="labor_insurance" value="99999" placeholder="99999" >
                            </td>
                        @else
                            <td>
                                {{ number_format(99999) }}
                            </td>
                        @endif
                        <th class="small">勞保自付額</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="number"
                                    name="labor_insurance_oop" value="9999" placeholder="9999" >
                            </td>
                        @else
                            <td>
                                ${{ number_format(9999) }}
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>健保金額</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="number"
                                    name="health_insurance" value="99999" placeholder="99999" >
                            </td>
                        @else
                            <td>
                                ${{ number_format(99999) }}
                            </td>
                        @endif
                        <th class="small">健保自付額</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="number"
                                    name="health_insurance_oop" value="9999" placeholder="9999" >
                            </td>
                        @else
                            <td>
                                ${{ number_format(9999) }}
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th class="small">勞退投保金額</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="number"
                                    name="勞退投保金額" value="99999" placeholder="99999" >
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
                                    <input class="form-control form-control-sm" type="number"
                                    name="health_insurance_dependents" value="0" placeholder="0" >
                                    <span class="ms-1">人</span>
                                </div>
                            </td>
                        @else
                            <td>
                                0人
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>公司提撥</th>
                        <td>6% ($X,XXX)</td>
                        <th>自行提撥</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="number"
                                    name="自行提撥" value="" placeholder="" >
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
                                <input class="form-control form-control-sm" type="tel"
                                    name="tel" value="(07)765-4321" placeholder="(07)765-4321" >
                            </td>
                        @else
                            <td>
                                (07)765-4321
                            </td>
                        @endif
                        <th>戶籍電話</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="tel"
                                    name="household_tel" value="(07)765-4321" placeholder="(07)765-4321" >
                            </td>
                        @else
                            <td>
                                (07)765-4321
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>行動電話</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="tel"
                                    name="phone" value="0987-654-321" placeholder="0987-654-321" >
                            </td>
                        @else
                            <td>
                                0987-654-321
                            </td>
                        @endif
                        <th>日本手機</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="tel"
                                    name="日本手機" value="" placeholder="" >
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
                                    <input class="form-control form-control-sm" type="tel"
                                    name="office_tel" value="(02)2571-6101" placeholder="(02)2571-6101" >
                                    <label class="ms-1">#</label>
                                    <input class="form-control form-control-sm" type="tel"
                                    style="max-width: 50px;"
                                    name="office_tel_ext" value="728" placeholder="728" >
                                </div>
                            </td>
                        @else
                            <td>
                                (02)2571-6101 分機728
                            </td>
                        @endif
                        <th>公司傳真</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="tel"
                                    name="office_fax" value="" placeholder="" >
                            </td>
                        @else
                            <td></td>
                        @endif
                    </tr>
                    <tr>
                        <th class="small">緊急聯絡人姓名</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="contact_person" value="XXX" placeholder="XXX" >
                            </td>
                        @else
                            <td>
                                XXX
                            </td>
                        @endif
                        <th class="small">緊急聯絡人電話</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="tel"
                                    name="contact_person_tel" value="0987-654-321" placeholder="0987-654-321" >
                            </td>
                        @else
                            <td>0987-654-321</td>
                        @endif
                    </tr>
                    <tr>
                        <th>生　　日</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="date"
                                    name="birthday" value="1999-01-01" >
                            </td>
                        @else
                            <td>
                                {{ true ? date('Y/m/d', strtotime('1999-01-01')) : '' }}
                            </td>
                        @endif
                        <th>血　　型</th>
                        @if ($editMode)
                            <td class="p-1">
                                <select class="form-select form-select-sm" name="blood_type">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="O">O</option>
                                    <option value="AB">AB</option>
                                </select>
                            </td>
                        @else
                            <td>
                                A
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>最高學歷</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="education" value="XX大學" placeholder="XX大學" >
                            </td>
                        @else
                            <td>
                                XX大學
                            </td>
                        @endif
                        <th>科　　系</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="education_department" value="資訊系" placeholder="資訊系" >
                            </td>
                        @else
                            <td>
                                資訊系
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>是否打卡</th>
                        <td>
                            @if ($editMode)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="punch_in" type="radio" 
                                            value="1" @if (true) checked @endif>
                                        是
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="punch_in" type="radio" 
                                            value="0" @if (false) checked @endif>
                                        否
                                    </label>
                                </div>
                            @else
                                @if (true) 是 @else 否 @endif
                            @endif
                        </td>
                        <th>服務地區</th>
                        @if ($editMode)
                            <td class="p-1">
                                <select class="form-select form-select-sm" name="service_area">
                                    <option value=""></option>
                                    <option value="企業專案">企業專案</option>
                                    <option value="國內旅遊">國內旅遊</option>
                                    <option value="台北" selected>台北</option>
                                    <option value="中壢">中壢</option>
                                    <option value="桃園">桃園</option>
                                    <option value="苗栗">苗栗</option>
                                    <option value="新竹">新竹</option>
                                    <option value="豐原">豐原</option>
                                    <option value="台中">台中</option>
                                    <option value="台南">台南</option>
                                    <option value="嘉義">嘉義</option>
                                    <option value="高雄">高雄</option>
                                    <option value="獎勵旅遊">獎勵旅遊</option>
                                </select>
                            </td>
                        @else
                            <td>
                                台北
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>公司地址</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="office_address" value="10491 台北市中山區松江路148號7樓之2"
                                    placeholder="10491 台北市中山區松江路148號7樓之2" >
                            </td>
                        @else
                            <td colspan="3">
                                10491 台北市中山區松江路148號7樓之2
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>聯絡地址</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="address" value="10491 台北市中山區松江路148號7樓之2"
                                    placeholder="10491 台北市中山區松江路148號7樓之2" >
                            </td>
                        @else
                            <td colspan="3">
                                10491 台北市中山區松江路148號7樓之2
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>戶籍地址</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="household_address" value="83042 高雄市鳳山區忠義里7鄰中山西路666號"
                                    placeholder="83042 高雄市鳳山區忠義里7鄰中山西路666號" >
                            </td>
                        @else
                            <td colspan="3">
                                83042 高雄市鳳山區忠義里7鄰中山西路666號
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>個人信箱</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="email"
                                    name="email" value="besttour@besttour.com.tw"
                                    placeholder="besttour@besttour.com.tw" >
                            </td>
                        @else
                            <td colspan="3">
                                besttour@besttour.com.tw
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>DISC類型</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="disc_category" value="DISC" placeholder="DISC" >
                            </td>
                        @else
                            <td colspan="3">
                                DISC
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th class="small">特殊證照專長</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="certificates" value="" placeholder="" >
                            </td>
                        @else
                            <td colspan="3"></td>
                        @endif
                    </tr>
                    <tr>
                        <th>保險證照</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="insurance_certification" value="" placeholder="" >
                            </td>
                        @else
                            <td colspan="3"></td>
                        @endif
                    </tr>
                    <tr>
                        <th>經 理 證</th>
                        <td>
                            @if ($editMode)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="經理證" type="radio" 
                                            value="1" @if (false) checked @endif>
                                        有
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="經理證" type="radio" 
                                            value="0" @if (true) checked @endif>
                                        無
                                    </label>
                                </div>
                            @else
                                @if (false) 有 @else 無 @endif
                            @endif
                        </td>
                        <th>領 隊 證</th>
                        <td>
                            @if ($editMode)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="領隊證" type="radio" 
                                            value="1" @if (false) checked @endif>
                                        有
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="領隊證" type="radio" 
                                            value="0" @if (true) checked @endif>
                                        無
                                    </label>
                                </div>
                            @else
                                @if (false) 有 @else 無 @endif
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="small">領隊證領取日</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="date"
                                    name="領隊證領取日" value="" >
                            </td>
                        @else
                            <td>
                                {{ '' ? date('Y/m/d', strtotime('')) : '' }}
                            </td>
                        @endif
                        <th class="small">領隊證有效日</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="date"
                                    name="領隊證有效日" value="" >
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
                                <input class="form-control form-control-sm" type="date"
                                    name="領隊證校正日" value="" >
                            </td>
                        @else
                            <td>
                                {{ '' ? date('Y/m/d', strtotime('')) : '' }}
                            </td>
                        @endif
                        <th class="small">領隊語言別</th>
                        @if ($editMode)
                            <td class="p-1">
                                <input class="form-control form-control-sm" type="text"
                                    name="領隊語言別" value="" placeholder="" >
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
                                    <li>領有身心障礙手冊：無</li>
                                </ul>
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>資歷簡介</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <textarea name="history" class="form-control form-control-sm"
                                >XX科技工程師 / OO國際工程師 / QQ系統工程師</textarea>
                            </td>
                        @else
                            <td colspan="3">
                                XX科技工程師 / OO國際工程師 / QQ系統工程師
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th class="small">資歷服務時間</th>
                        @if ($editMode)
                            <td colspan="3" class="py-1">
                                <div class="d-flex">
                                    <label class="text-nowrap">旅行社服務年資合計：</label>
                                    <input class="form-control form-control-sm" type="number"
                                        name="旅行社服務年資" value="7" placeholder="7" >
                                    <span class="ms-1">年</span>
                                </div>
                                <div class="d-flex">
                                    <label class="text-nowrap">非旅行社服務年資料合計：</label>
                                    <input class="form-control form-control-sm" type="number"
                                        name="非旅行社服務年資" value="0" placeholder="0" >
                                    <span class="ms-1">年</span>
                                </div>
                            </td>
                        @else
                            <td colspan="3">
                                <ul class="mb-0">
                                    <li>旅行社服務年資合計：7年</li>
                                    <li>非旅行社服務年資料合計：0年</li>
                                </ul>
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <th>備　　註</th>
                        @if ($editMode)
                            <td colspan="3" class="p-1">
                                <textarea name="note" class="form-control form-control-sm"
                                >喜鴻-健勞保</textarea>
                            </td>
                        @else
                            <td colspan="3">
                                喜鴻-健勞保
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
        <script>
        </script>
    @endpush
@endonce