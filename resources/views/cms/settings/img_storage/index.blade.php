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
                <form action="" method="post">
                    <div style="padding: 0 5px;">
                        <table style="width:700px; max-width:100%;margin:auto;" border="1" cellspacing="0">
                            <tr>
                                <td style="" class="title">上傳說明</td>
                                <td style="padding: 2px;">檔案格式：JPG / JPEG / PNG / GIF</td>
                            </tr>
                            <tr>
                                <td style="" class="title">選擇檔案</td>
                                <td>
                                    <input type="file" name="" accept=".jpg,.jpeg,.png,.gif" style="width:100%;">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="margin-top: 10px;text-align: center;">
                        <button type="submit" style="padding:1px 15px;">上傳</button>
                    </div>
                </form>
            </div>
            <div style="flex: 50%;min-width:390px;">
                <h2>圖片檢視</h2>
                <form action="" method="post">
                    <div style="padding: 0 5px;">
                        <table style="width:700px; max-width:100%;margin:auto;" border="1" cellspacing="0">
                            <tr>
                                <td style="" class="title">上傳人員</td>
                                <td>
                                    <input type="text" name="" value="{{ $user }}" style="width:calc(100% - 8px);">
                                </td>
                            </tr>
                            <tr>
                                <td style="" class="title">上傳日期</td>
                                <td>
                                    <input type="date" name="" value="">～<input type="date" name="" value="">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="margin-top: 10px;text-align: center;">
                        {{-- @if (按查詢後) --}}
                            <span style="margin-right: 20px;">
                                總筆數：9（共 3 頁）
                                <select name="">
                                    <option value="1">第 1 頁</option>
                                    <option value="2">第 2 頁</option>
                                    <option value="3">第 3 頁</option>
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
            @for ($i = 0; $i < 9; $i++)
                <div class="item">
                    <table>
                        <tr>
                            <td>{{ date('Y/m/d H:i:s', strtotime('2023-02-21 10:44:49')) }}</td>
                            <td style="width: 70px;">上傳人員</td>
                        </tr>
                        <tr>
                            <td>
                                <input type="text" id="url_{{ $i }}" value="https://img.bestselection.com.tw/2022PG01/20220914114543293.jpg" style="width:100%">
                            </td>
                            <td>
                                <button type="button" onclick="copyUrl('url_{{ $i }}');">複製</button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">圖庫 (點圖預覽)</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="https://img.bestselection.com.tw/2022PG01/20220914114543293.jpg" target="_blank">
                                    <img src="https://img.bestselection.com.tw/2022PG01/20220914114543293.jpg" alt="">
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
            @endfor
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