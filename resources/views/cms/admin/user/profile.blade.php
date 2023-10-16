@extends('layouts.main')
@section('sub-content')
<div class="pt-2 mb-3">
    <a href="{{ Route('cms.user.index', [], true) }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
    <button class="btn btn-success">編輯</button>
</div>

<form action="" method="post">
    <div class="card mb-4">
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>員工姓名</th>
                        <td>王烏梅</td>
                        <th>職　　號</th>
                        <td>08079</td>
                    </tr>
                    <tr>
                        <th>英文名字</th>
                        <td>May</td>
                        <td id="m_photo" colspan="2" rowspan="5" class="w-50">
                            <img src="{{ Asset('images/NoImg.png') }}" alt="">
                        </td>
                    </tr>
                    <tr>
                        <th>身分證號</th>
                        <td>A123456789</td>
                    </tr>
                    <tr>
                        <th>性　　別</th>
                        <td>女</td>
                    </tr>
                    <tr>
                        <th>家人同住</th>
                        <td>否</td>
                    </tr>
                    <tr>
                        <th>業績統計</th>
                        <td>否</td>
                    </tr>
                    <tr>
                        <th>部　　門</th>
                        <td>喜鴻-資訊部-資訊二組</td>
                        <th>職　　稱</th>
                        <td>軟體工程師</td>
                    </tr>
                    <tr>
                        <th>到 職 日</th>
                        <td>2020/09/10</td>
                        <th>離 職 日</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th>服務時間</th>
                        <td>3年1個月</td>
                        <th>
                            <a href="https://www.hsihung.com.tw/intranet/employee/vday2B.asp?n1=%B3\%A7%D3%AB%ED">特休天數</a>
                        </th>
                        <td>15</td>
                    </tr>
                    <tr>
                        <th>英文能力</th>
                        <td>無</td>
                        <th>英文證照</th>
                        <td>無</td>
                    </tr>
                    <tr>
                        <th>日文能力</th>
                        <td>無</td>
                        <th>日文證照</th>
                        <td>無</td>
                    </tr>
                    <tr>
                        <th>教育訓練</th>
                        <td colspan="3">
                            <span>完成初級</span>
                            <span class="ms-2 text-danger">[未進行中級]</span>
                            <span class="ms-2 text-danger">[未進行高級]</span>
                            <span class="ms-2 text-danger">[未進行專業級]</span>
                        </td>
                    </tr>
                    <tr>
                        <th>勞動契約</th>
                        <td>（已繳）</td>
                        <th>承攬契約</th>
                        <td>（未繳）</td>
                    </tr>
                    <tr>
                        <th>異動狀態</th>
                        <td>
                            <div>加入：2020/09/10</div>
                            <div>退出：</div>
                        </td>
                        <th>勞健保狀態</th>
                        <td>
                            <div>加入：2020/09/11</div>
                            <div>退出：</div>
                        </td>
                    </tr>
                    <tr>
                        <th>勞保金額</th>
                        <td>$XX,XXX</td>
                        <th>勞保自付額</th>
                        <td>$X,XXX</td>
                    </tr>
                    <tr>
                        <th>健保金額</th>
                        <td>$XX,XXX</td>
                        <th>健保自付額</th>
                        <td>$X,XXX</td>
                    </tr>
                    <tr>
                        <th class="small">勞退投保金額</th>
                        <td>$XX,XXX</td>
                        <th>健保眷屬</th>
                        <td>0人</td>
                    </tr>
                    <tr>
                        <th>公司提撥</th>
                        <td>6% ($X,XXX)</td>
                        <th>自行提撥</th>
                        <td>無</td>
                    </tr>
                    <tr>
                        <th>聯絡電話</th>
                        <td>(07)765-4321</td>
                        <th>戶籍電話</th>
                        <td>(07)765-4321</td>
                    </tr>
                    <tr>
                        <th>行動電話</th>
                        <td>0987-654-321</td>
                        <th>日本手機</th>
                        <td>無</td>
                    </tr>
                    <tr>
                        <th>公司電話</th>
                        <td>(02)2571-6101分機728</td>
                        <th>公司傳真</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th class="small">緊急聯絡人姓名</th>
                        <td>XXX</td>
                        <th class="small">緊急聯絡人電話</th>
                        <td>XXXX-XXX-XXX</td>
                    </tr>
                    <tr>
                        <th>生　　日</th>
                        <td>1999/01/01</td>
                        <th>血　　型</th>
                        <td>A</td>
                    </tr>
                    <tr>
                        <th>最高學歷</th>
                        <td>大學</td>
                        <th>科　　系</th>
                        <td>資訊系</td>
                    </tr>
                    <tr>
                        <th>是否打卡</th>
                        <td>是</td>
                        <th>服務地區</th>
                        <td>台北</td>
                    </tr>
                    <tr>
                        <th>公司地址</th>
                        <td colspan="3">10491 台北市中山區松江路148號7樓之2</td>
                    </tr>
                    <tr>
                        <th>聯絡地址</th>
                        <td colspan="3">台北市中山區松江路148號7樓之2</td>
                    </tr>
                    <tr>
                        <th>戶籍地址</th>
                        <td colspan="3">高雄市鳳山區忠義里7鄰中山西路</td>
                    </tr>
                    <tr>
                        <th>個人信箱</th>
                        <td colspan="3">besttour@besttour.com.tw</td>
                    </tr>
                    <tr>
                        <th>DISC類型</th>
                        <td colspan="3">DISC</td>
                    </tr>
                    <tr>
                        <th class="small">特殊證照專長</th>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <th>保險證照</th>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <th>經 理 證</th>
                        <td>無</td>
                        <th>領 隊 證</th>
                        <td>無</td>
                    </tr>
                    <tr>
                        <th class="small">領隊證領取日</th>
                        <td></td>
                        <th class="small">領隊證有效日</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th class="small">領隊證校正日</th>
                        <td></td>
                        <th>領隊語言別</th>
                        <td></td>
                    </tr>
                    <tr>
                        <th>其他項目</th>
                        <td colspan="3">
                            <ul class="mb-0">
                                <li>特殊人士：否</li>
                                <li>領有身心障礙手冊：無</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th>資歷簡介</th>
                        <td colspan="3">
                            <ul class="mb-0">
                                <li>XX科技工程師</li>
                                <li>OO國際工程師</li>
                                <li>QQ系統工程師</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th class="small">資歷服務時間</th>
                        <td colspan="3">
                            <ol class="mb-0">
                                <li>旅行社服務年資合計(年)：7</li>
                                <li>非旅行社服務年資料合計(年)：0</li>
                            </ol>
                        </td>
                    </tr>
                    <tr>
                        <th>備　　註</th>
                        <td colspan="3">喜鴻-健勞保</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>
@endsection

@once
    @push('sub-styles')
        <style>
            #m_photo {
                text-align: center;
                vertical-align: middle;
            }
            #m_photo img {
                max-width: 90%;
                max-height: 80%;
                width: auto;
                height: auto;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce