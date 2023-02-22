<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>上傳圖片</title>
    <style>
        * {
            font-family: "Microsoft JhengHei", sans-serif;
            position: relative;
        }
        html, body {
            margin-top: 0;
        }
        #content {
            width: 100%;
            position: -webkit-sticky;
            position: sticky;
            padding: 8px 0 1px;
            top: 0;
            z-index: 1;
            background-color: #ffffff;
        }
        #main {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        h2 {
            text-align: center;
            margin-top: 10px;
        }
        button {
            font-size: 1rem;
        }
        .title {
            text-align:center;
            background-color: #0099ff;
            color: #ffffff;
            width: 100px;
        }
        .item {
            text-align: center;
        }
        .item > table {
            margin: 0 10px;
        }
        .item img {
            width: 300px;
            height: 200px;
        }
    </style>
</head>
<body>
    <div id="content">
        <div id="main">
            <div style="flex: 50%;min-width:390px;background-color: #fffde7;">
                <h2>圖片上傳</h2>
                <form action="{{ route("cms.img-storage.create") }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div style="padding: 0 5px;">
                        <table style="width:700px; max-width:100%;margin:auto;" border="1" cellspacing="0">
                            <tr>
                                <td style="" class="title">上傳說明</td>
                                <td style="padding: 2px;">檔案格式：JPG / JPEG / PNG / GIF</td>
                            </tr>
                            <tr>
                                <td style="" class="title">選擇檔案</td>
                                <td>
                                    <input type="file" name="file" accept=".jpg,.jpeg,.png,.gif" style="width:100%;">
                                </td>
                            </tr>
                        </table>
                        @error('file')
                            {{ $message }}
                        @enderror
                    </div>
                    <div style="margin-top: 10px;text-align: center;">
                        <button type="submit" style="padding:1px 15px;">上傳</button>
                    </div>
                </form>
            </div>
            <div style="flex: 50%;min-width:390px;">
                <h2>圖片檢視</h2>
                <form action="" method="get">
                    <div style="padding: 0 5px;">
                        <table style="width:700px; max-width:100%;margin:auto;" border="1" cellspacing="0">
                            <tr>
                                <td style="" class="title">上傳人員</td>
                                <td>
                                    <input type="text" name="user_name" value="{{ $user }}" style="width:calc(100% - 8px);">
                                </td>
                            </tr>
                            <tr>
                                <td style="" class="title">上傳日期</td>
                                <td>
                                    <input type="date" name="sDate" value="">～<input type="date" name="eDate" value="">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="margin-top: 10px;text-align: center;">
                        {{-- @if (按查詢後) --}}
                            <span style="margin-right: 20px;">
                                總筆數：{{ $dataList->count() }}（共 {{ $dataList->lastPage() }}  頁）
                                <select name="page">
                                    @for($i=0;$i< $dataList->lastPage();$i++)
                                    <option value="{{ $i+1 }}">第 {{ $i+1 }} 頁</option>
                                    @endfor
                                </select>
                            </span>
                        {{-- @endif --}}
                        <button type="submit" style="padding:1px 15px;">查詢</button>
                    </div>
                </form>
            </div>
        </div>
        <hr>
    </div>
    <div id="search">
        <div style="display: flex;flex-wrap: wrap;justify-content: center;">
         
            @foreach($dataList as $key => $value)
                @php
                    $url = getImageUrl($value->url,true);

                    
                @endphp
                <div class="item">
                    <table>
                        <tr>
                            <td>{{ date('Y/m/d H:i:s', strtotime($value->created_at)) }}</td>
                            <td style="width: 70px;">{{ $value->user_name }}</td>
                        </tr>
                        <tr>
                            <td>
                                <input type="text" id="url_{{ $key }}" value="{{  $url }}" style="width:100%">
                            </td>
                            <td>
                                <button type="button" onclick="copyUrl('url_{{ $key }}');">複製</button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">圖庫 (點圖預覽)</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="{{  $url }}" target="_blank">
                                    <img src="{{  $url }}" alt="">
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">CDN同步</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <img src="https://img.bestselection.com.tw/2022PG01/20220914114543293.jpg" alt="">
                            </td>
                        </tr>
                    </table>
                    <hr>
                </div>
            @endforeach
        </div>
    </div>

</body>
<script>
    function copyUrl(id) {
        let input = document.getElementById(id);
        input.select();
        document.execCommand('Copy');
    }
</script>
</html>