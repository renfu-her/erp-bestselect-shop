@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">UTM網址產生器</h2>
    <div class="card mb-4">
        <form id="form">
            <div class="card-body">
                <label class="col-form-label">
                    發佈平台
                </label>
                <select name="select_platform" id="select_source" class="mb-4 form-select" required>
                    <option value="" selected disabled>選擇發佈平台</option>
                    <option value="Bestselect-Page">喜鴻購物-臉書粉絲團</option>
                    <option value="Facebook-Bestselection-Group">喜鴻購物-臉書社團</option>
                    <option value="Bestselection-Line-Text">喜鴻購物-Line文字</option>
                    <option value="Bestselection-Line-Photo">喜鴻購物-Line圖片</option>
                    <option value="Manual">喜鴻購物-車上購物手冊</option>
                    <option value="QrCode">喜鴻購物-QR Code封條</option>
                    <option value="Bestselect-Internal-Link">喜鴻購物-內部連結</option>
                    <option value="Besttour-Banner">喜鴻假期-官網Banner</option>
                    <option value="Besttour-Page">喜鴻假期-臉書粉絲團</option>
                    <option value="Besttour-Youtube">喜鴻假期-YouTube</option>
                    <option value="Birdsflyaway-Youtube">鳥事少一點-YouTube</option>
                    <option value="Besttour-Youtube-CG">誠貫-喜鴻假期YouTube廣告</option>
                    <option value="FB-CG">誠貫-FB廣告</option>
                </select>
                <label class="col-form-label">原始網址</label>
                <div>
                    <input name="input_url" type="text" id="ori_url" class="form-control" placeholder="">
                </div>
                <br>
                <!-- 浮動控制項-->
                <div class="mb-4">
                    <!-- 按鈕：確認 -->
                    <input type="submit" name="B1" id="generate_url" value="產生短網址" class="Enter_ok enterBtn">
                </div>
            </div>
        </form>
    </div>

    <div class="card mb-4">
        <p id="wait">短網址正在產生中。。 請等待。。</p>
        <br><strong class="url_description">1.產生的短網址是:</strong>
        <p id=short_url></p>
        <div class="copyBtn" name="short_url">點我複製短網址</div>
        <br>
        <br>
        <br>
        <br><strong class="url_description">2.產生的長網址是:</strong>
        <p id=long_url></p>
        <div class="copyBtn" name="long_url">點我複製長網址</div>
    </div>
@endsection
@once
    @push('sub-scripts')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="http://malsup.github.com/jquery.form.js"></script>
        <script>
            const BESTSELECTION_REGEX = /(^https:\/\/.*\.bestselection\.com\.tw)/;

            const TEST = false;
            const BITLY_TOKEN = TEST ? "a55bd2eca919950d842173e393eccd2e1e8300f8" : "7d23e99d11d91b52bd3915bf809fe9bfd7e306bf";
            const PICSEE_TOKEN = TEST ? "20f07f91f3303b2f66ab6f61698d977d69b83d64" : "32e5b4c2031799dc40841114844a3aa35343a923";
            $(document).ready(function(){
                $(".copyBtn").hide();
                $('#wait').hide();
                $(".url_description").hide();

                $.validator.addMethod("checkBestselectionUrl", function(value, element) {
                        return this.optional(element) || BESTSELECTION_REGEX.test(value);
                        ;},
                    "請輸入正確的網址");

                $("#form").validate({
                    // ignore: ".ignore",
                    rules: {
                        "select_platform": {
                            required: true
                        },
                        "input_url": {
                            required: true,
                            checkBestselectionUrl: true
                        }
                    },
                    messages: {
                        "select_platform": {
                            required: "請選擇平台"
                        },
                        "input_url": {
                            required: "請輸入喜鴻購物網址",
                            checkBestselectionUrl: "輸入的網址並不是喜鴻購物網址！！"
                        }
                    },
                    submitHandler: function(form) {
                        var source = $("#select_source").val();
                        var oriUrl = $("#ori_url").val();
                        var long_url = toUtmUrl(oriUrl, source);
                        // console.log("long_url:" + long_url);

                        // use Picsee instead
                        if (true) {
                            // console.log("pics Supported");
                            $.ajax({
                                url: "https://api.pics.ee/v1/links/?access_token=" + PICSEE_TOKEN,
                                type: 'POST',
                                dataType: 'json',
                                data: JSON.stringify({
                                    'url': long_url,
                                    'externalId': source //for short url tags
                                }),
                                contentType: "application/json",
                                success: function (data){
                                    $("#short_url").text(data["data"]["picseeUrl"]);
                                    $("#long_url").text(data["meta"]["request"]["query"]["url"]);
                                    $(".copyBtn").show(1000);
                                    $(".url_description").show(1000);
                                    console.log(data);
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown){
                                    console.info(XMLHttpRequest.responseJSON);
                                },
                                beforeSend: function() {
                                    $('#wait').show(1000);
                                    $(".copyBtn").hide();
                                    $(".url_description").hide();
                                    $("#short_url").empty();
                                    $("#long_url").empty();
                                },
                                complete: function() {
                                    $('#wait').hide(1000);
                                    $(".copyBtn").show();
                                    $(".url_description").show();
                                }
                            });
                        } else {
                            // console.log("isBitlySupported");
                            $.ajax({
                                url: "https://api-ssl.bitly.com/v4/bitlinks",
                                type: 'POST',
                                headers: {"Authorization" : 'Bearer ' + BITLY_TOKEN},
                                dataType: 'json',
                                data: JSON.stringify({
                                    'long_url': long_url,
                                    'tags': [source]
                                }),
                                contentType: "application/json",
                                success: function (data){
                                    $("#short_url").text(data.link);
                                    $("#long_url").text(data.long_url);
                                    $(".copyBtn").show(1000);
                                    $(".url_description").show(1000);
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown){
                                    console.info(XMLHttpRequest.responseJSON);
                                },
                                beforeSend: function() {
                                    $('#wait').show(1000);
                                    $(".copyBtn").hide();
                                    $(".url_description").hide();
                                    $("#short_url").empty();
                                    $("#long_url").empty();
                                },
                                complete: function() {
                                    $('#wait').hide(1000);
                                    $(".copyBtn").show();
                                    $(".url_description").show();
                                }
                            });
                        }

                        return false;
                    },
                    invalidHandler: function(form) {
                    }
                });

                function toUtmUrl(url, platform) {
                    var currentDate = new Date();
                    var today = "".concat(currentDate.getFullYear(), "-",currentDate.getMonth() + 1, "-",currentDate.getDate())
                    var dat = generate_random_string(5)

                    var utmPath = "";
                    var utmUrl = "";

                    switch(platform){
                        case "QrCode":
                            utmPath = toUtmPath(url, "offline", "qrcode", "qrcode-" + dat, today, "photo");
                            break;
                        case "Bestselect-Internal-Link":
                            utmPath = deletePara(url);
                            break;
                        case "Bestselection-Line-Text":
                            utmPath = toUtmPath(url, "line", "human", "bestselect_line-" + dat, today, "text");
                            break;
                        case "Bestselection-Line-Photo":
                            utmPath = toUtmPath(url, "line", "human", "bestselect_line-" + dat, today, "photo");
                            break;
                        case "Birdsflyaway-Youtube":
                            utmPath = toUtmPath(url, "youtube", "video", "birdsflyaway_youtube-" + dat, today, "text");
                            break;
                        case "Besttour-Youtube":
                            utmPath = toUtmPath(url, "youtube", "video", "besttour_youtube-" + dat, today, "text");
                            break;
                        case "Besttour-Youtube-CG":
                            utmPath = toUtmPath(url, "youtube", "paid", "cg_besttour_youtube-" + dat, today, "all");
                            break;
                        case "Bestselect-Page":
                            utmPath = toUtmPath(url, "fb", "page", "bestselect_page-" + dat, today, "text");
                            break;
                        case "FB-CG":
                            utmPath = toUtmPath(url, "fb", "paid", "cg_bestselect-" + dat, today, "all");
                            break;
                        case "Manual":
                            utmPath = toUtmPath(url, "offline", "dm", "manual-" + dat, today, "photo");
                            break;
                        case "Besttour-Page":
                            utmPath = toUtmPath(url, "fb", "page", "besttour_page-" + dat, today, "text");
                            break;
                        case "Facebook-Bestselection-Group":
                            utmPath = toUtmPath(url, "fb", "group", "bestselect_group-" + dat, today, "text");
                            break;
                        case "Besttour-Banner":
                            utmPath = toUtmPath(url, "besttour", "banner", "banner-" + dat, today, "photo");
                            break;
                        default:
                            console.log('Sorry, the platform setting is wrong:' + platform );
                    }

                    return utmPath;
                }

                function toUtmPath(url, utmSource, utmMedium, utmCampaign, utmTerm, utmContent) {
                    let newUrl = new URL(url);

                    newUrl.searchParams.set('utm_source', utmSource);
                    newUrl.searchParams.set('utm_medium', utmMedium);
                    newUrl.searchParams.set('utm_campaign', utmCampaign);
                    newUrl.searchParams.set('utm_term', utmTerm);
                    newUrl.searchParams.set('utm_content', utmContent);
                    newUrl.searchParams.delete('mcode');

                    return newUrl;
                }

                function deletePara(url) {
                    let delUtmUrl = new URL(url);

                    delUtmUrl.searchParams.delete('utm_source');
                    delUtmUrl.searchParams.delete('utm_medium');
                    delUtmUrl.searchParams.delete('utm_campaign');
                    delUtmUrl.searchParams.delete('utm_term');
                    delUtmUrl.searchParams.delete('utm_content');
                    delUtmUrl.searchParams.delete('mcode');
                    delUtmUrl.searchParams.delete('fc');

                    return delUtmUrl;
                }

                function generate_random_string(string_length){
                    let random_string = '';
                    let random_ascii;
                    for(let i = 0; i < string_length; i++) {
                        random_ascii = Math.floor((Math.random() * 25) + 97);
                        random_string += String.fromCharCode(random_ascii)
                    }
                    return random_string
                }

                $(".copyBtn").click(function() {
                    var name = $(this).attr('name');
                    var el = document.getElementById(name);
                    var range = document.createRange();
                    range.selectNodeContents(el);
                    var sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                    document.execCommand('copy');
                    alert("網址：" + sel + " 複製完成");
                    return false;
                });
            });
            //get method
            /*
            $(document).ready(function(){
                $("button").click(function(){
                    $.ajax({
                      url: "https://api-ssl.bitly.com/v4/bitlinks/bit.ly/2FsJsOK/clicks?unit=day&units=-1",
                      type: 'GET',
                      // Fetch the stored token from localStorage and set in the header
                      headers: {"Authorization" : 'Bearer a55bd2eca919950d842173e393eccd2e1e8300f8'},
                      dataType: 'json',
                      success: function (data){
                        console.info(data.unit_reference);
                      }
                    });
                });
            });
            */
        </script>
    @endpush
@endOnce
