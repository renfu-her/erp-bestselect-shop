<?php

namespace Database\Seeders;

use App\Models\Fruit;
use App\Models\FruitCollection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class FruitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('fru_collection_fruit')->truncate();
        DB::table('fru_fruits')->truncate();
        DB::table('fru_collections')->truncate();



        $collections = [
            ['春季', '(3至5月)'],
            ['夏季', '(6至8月)'],
            ['秋季', '(9至11月)'],
            ['冬季', '(12至2月)'],
            ['進口水果', '(1至12月)'],
        ];

        FruitCollection::insert(array_map(function ($n) {
            return [
                'title' => $n[0],
                'sub_title' => $n[1],
            ];
        }, $collections));

        $fruits = [
            [
                'title' => '茂谷柑',
                'sub_title' => '果肉Q彈、多汁',
                'place' => '台灣台中',
                'season' => '春、冬季 (12月-隔年3月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_02.jpg',
                'text' => '擁有濃郁爽口風味、果肉柔軟、多汁、果皮薄柔質細緻帶有蜂蜜的香氣，充滿豐富的維生素C及纖維、類黃酮、有機酸礦物質，讓每日元氣滿分！',
                'link' => '',
                'status' => '12月開放預購'
            ],
            [
                'title' => '玉女小番茄',
                'sub_title' => '皮薄汁多、甜度高',
                'place' => '台灣高雄/嘉義',
                'season' => '春、冬季 (12月-隔年3月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_03.jpg',
                'text' => '玉女小番茄採溫室離地無毒栽種，全程無使用農藥，顆顆鮮豔欲滴，皮薄、多汁、果肉細緻，吃得健康又安心',
                'link' => '',
                'status' => '12月開放預購'
            ],
            [
                'title' => '橙蜜香小番茄',
                'sub_title' => '黃中帶橘的圓形，酸中帶甜',
                'place' => '台灣高雄',
                'season' => '春、冬季 (12月-隔年3月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_04.jpg',
                'text' => '橙蜜香的風味獨特又狂野，果皮較厚反而更有口感，果型尾端較圓潤，最特別的，還是那股誰也比不上的香橙風味。反之金瑩吃起來皮薄又多汁，足夠的甜味搭配些許番茄清酸，果型尾端較尖，讓它奪得了橘黃色的玉女的稱號。',
                'link' => '',
                'status' => '12月開放預購'
            ],
            [
                'title' => '鳳梨釋迦',
                'sub_title' => '果肉Ｑ，甜中帶酸',
                'place' => '台灣台東',
                'season' => '春、冬季 (12月-隔年3月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_05.jpg',
                'text' => '鳳梨釋迦表面的鱗目是尖形突起，且因為表皮是一整片、沒有裂開，必須先用刀子去皮、切塊才能食用。鳳梨釋迦的果實大、籽粒少，果肉口感較紮實、帶有微微脆度，還有著淡淡的鳳梨香氣，酸甜又爽口！',
                'link' => '',
                'status' => '12月開放預購'
            ],
            [
                'title' => '大目釋迦',
                'sub_title' => '口感濃郁，甜度高',
                'place' => '台灣台東',
                'season' => '春、冬季 (12月-隔年3月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_06.jpg',
                'text' => '主要產地為台東縣、屏東縣和台南縣等，其中以台東釋迦最為有名！大目釋迦的麟目呈圓形，熟度足夠後可以直接剝下，最簡單的吃法就是用湯匙挖果肉食用，口感香甜而軟綿，冰過之後就像冰淇淋。',
                'link' => '',
                'status' => '12月開放預購'
            ],
            [
                'title' => '牛奶蜜棗',
                'sub_title' => '香甜多汁，不澀口',
                'place' => '台灣台南',
                'season' => '春、冬季 (12月-隔年3月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_07.jpg',
                'text' => '使用發酵乳或牛奶澆灌的農法而得其名。牛奶蜜棗通常呈現水滴型的外觀，圓頭尖尖較瘦長，表皮帶有翠綠光澤，口感上相當爽脆涮嘴，可是冬季必吃水果之一。',
                'link' => '',
                'status' => '12月開放預購'
            ],
            [
                'title' => '貴妃枇杷',
                'sub_title' => '水果界貴妃皮薄易破特別嬌嫩',
                'place' => '台灣南投',
                'season' => '春季 (2-4月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_08.jpg',
                'text' => '有別於一般裝盒的枇杷，因為夠新鮮才能夠帶枝出貨！如同貴妃般，嬌貴的枇杷顆顆飽滿圓潤，汁多味美，口口清甜Q彈，皮也非常好剝！',
                'link' => '',
                'status' => '今年產季已過'
            ],
            [
                'title' => '玉荷包',
                'sub_title' => '果肉鮮脆、香甜爆汁',
                'place' => '台灣高雄',
                'season' => '春季 (5月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_09.jpg',
                'text' => '台灣荔枝的品種當中，相當受到歡迎的是玉荷包，果核小果肉豐厚，汁多酸甜比例佳，容易讓人一口接一口。',
                'link' => 'https://bit.ly/3LQyljp',
                'status' => '販售中'
            ],
            [
                'title' => '百香果',
                'sub_title' => '甜中帶酸，顆顆飽滿的抗氧化王者',
                'place' => '台灣南投',
                'season' => '春、夏、秋季 (3-4月、7-9月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_12.jpg',
                'text' => '百香果被譽為水果界的「果汁之王」在炎炎夏日，百香果獨特清爽的滋味無論是加入果汁還是做成甜點都很棒，酸酸甜甜又開胃，使人暑氣消去不少。',
                'link' => '',
                'status' => '7月開放預購'
            ],
            [
                'title' => '愛文芒果',
                'sub_title' => '香甜多汁超級好吃',
                'place' => '台灣屏東',
                'season' => '春、夏季 (4-8月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_11.jpg',
                'text' => '顏色色澤鮮艷紅通通的，果型外表飽滿，聞起來有熱帶水果獨有的香甜香氣，散發出濃郁的果香味，口感吃起來果肉細嫩，香甜多汁，因為甜度高所以雖帶點微酸還是很順口甜而不膩，夏季配上冰一起吃最消暑了。',
                'link' => 'https://www.bestselection.com.tw/product/P220928003?openExternalBrowser=1',
                'status' => '販售中'
            ],
            [
                'title' => '烏香芒果',
                'sub_title' => '具獨特龍眼香甜滋味',
                'place' => '台灣屏東',
                'season' => '春、夏季 (3-8月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_10.jpg',
                'text' => '獨特少見的黑香芒果，又稱烏香芒果，目前在台灣只有少量種植，常常產季一到就會被搶購一空。黑香芒果具有特別的「龍眼香味」，肉質Q纖維少，甜度高又多汁',
                'link' => '',
                'status' => '今年產季已過'
            ],
            [
                'title' => '夏雪芒果',
                'sub_title' => '芒果界的LV',
                'place' => '台灣台東',
                'season' => '春、夏季 (5-7月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_13.jpg',
                'text' => '「夏雪」果型碩大，果核卻薄小，一口咬下，滿滿的果肉，滿滿的果汁，不但帶著土芒果、愛文芒果的香氣、口感，果肉細緻；甜度媲美金煌芒果。',
                'link' => 'https://bit.ly/3Vi3qiT',
                'status' => '販售中'
            ],
            [
                'title' => '蜜雪芒果',
                'sub_title' => '芒果界香奈兒!!',
                'place' => '台灣台東',
                'season' => '春、夏季 (5-7月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_14.jpg',
                'text' => '果實相貌為「金黃帶桃紅」的中型果，體型比金黃、夏雪小，但比愛文略大。和愛文相比，蜜雪主要有「果實色澤更美、可溶性固形物高、果肉品質優、香氣表現特別、櫥架壽命更長」的優點。',
                'link' => 'https://www.bestselection.com.tw/product/P230508001?openExternalBrowser=1',
                'status' => '販售中'
            ],
            [
                'title' => '珍翠芭樂',
                'sub_title' => '果肉厚、口感脆，四季品質穩定',
                'place' => '台灣屏東',
                'season' => '(6-9月、12月至隔年4月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_06_02.jpg',
                'text' => '珍翠芭樂不僅果肉厚、口感清脆細緻、糖酸比適中、果形賣相佳，更因為氣候適應力強，可大幅降低過去珍珠芭樂夏季品質落差的困擾，一舉扭轉民眾認為夏天芭樂較難吃的印象，被視為是刺激夏季芭樂市場的戰鬥型品種、芭樂界的夏季天后！',
                'link' => '',
                'status' => '6月開放預購'
            ],
            [
                'title' => '巨鑽蓮霧',
                'sub_title' => '純淨環境無毒栽培',
                'place' => '台灣高雄',
                'season' => '全年 (1-3月、5-12月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_06_03.jpg',
                'text' => '巨鑽蓮霧為新研發出來的獨家品種，甜度雖然不像黑糖芭比或是黑珍珠那麼甜，但巨鑽蓮霧不易裂果、顏色鮮紅、多汁清脆，並帶有淡淡的清甜味！',
                'link' => 'https://bit.ly/3ALyHBp',
                'status' => '已售罄'
            ],
            [
                'title' => '拉拉山水蜜桃',
                'sub_title' => '入口即化、甜蜜多汁',
                'place' => '台灣桃園',
                'season' => '春、夏季 (6-8月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_02_03.jpg',
                'text' => '拉拉山水蜜桃特色是底部渾圓，柄部有道溝，果形大、多汁、甜度高，色澤風味均佳，不像一般水蜜桃的尾部呈尖凹狀。',
                'link' => '',
                'status' => '6月開放預購'
            ],
            [
                'title' => '溫室美濃瓜',
                'sub_title' => '溫室直立式栽培',
                'place' => '台灣嘉義',
                'season' => '夏、秋季 (6-9月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_02_04.jpg',
                'text' => '美濃瓜又稱香瓜，多以露天種植，但這幾年美濃瓜逐漸轉型成為高經濟價值的水果，改採以溫室及立藤、一株只留一顆的高規栽培，使得美濃瓜不但香氣濃郁，顏色白霧美麗，甜度口感更佳！',
                'link' => '',
                'status' => '6月開放預購'
            ],
            [
                'title' => '寶島甘露梨',
                'sub_title' => '細緻清甜、蘊含沛然水分',
                'place' => '台灣苗栗',
                'season' => '夏、秋季 (7-9月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_02_06.jpg',
                'text' => '果型較巨大、扁圓，表面有點凹凸。看似粗曠，卻有著與外型截然不同的細緻果肉。其品種特色，越大顆的果實反而肉質越細膩。風味帶有淡淡甘蔗香氣，口感清脆可口、清甜多汁，飽滿水分。',
                'link' => '',
                'status' => '7月開放預購'
            ],
            [
                'title' => '金鑽鳳梨',
                'sub_title' => '鳳梨品種維生素C最高!',
                'place' => '台灣屏東',
                'season' => '夏季 (4-8月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_02_09.jpg',
                'text' => '果實結實飽滿，有重量感，果實無裂縫、靠傷，果目明顯突起，果實要新鮮聞起來有一股濃郁特殊香味，其果肉深黃色，且汁多，甜度高，酸味低，吃起來爽口芳香，品質佳。',
                'link' => 'https://bit.ly/3mduWkq',
                'status' => '販售中'
            ],
            [
                'title' => '白玉蓮霧',
                'sub_title' => '口感綿密、香甜、有特殊花香味',
                'place' => '台灣屏東',
                'season' => '夏季 (5-7月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_01_01.jpg',
                'text' => '白色的蓮霧口感比較綿密，水份比較沒有那麼多，但是相對本產的黑珍珠蓮霧，水份比較多，比較脆。草生栽培、不使用化學除草劑、藥檢合格，讓消費者吃得安心又開心。',
                'link' => 'https://www.bestselection.com.tw/product/P221019001?openExternalBrowser=1',
                'status' => '販售中'
            ],
            [
                'title' => '牛奶芭樂',
                'sub_title' => '喝牛奶長大的芭樂',
                'place' => '台灣高雄',
                'season' => '(8-9月、11月-隔年2月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_06_01.jpg',
                'text' => '因有農民使用牛奶發酵製成的液肥來灌溉，又被稱為「牛奶芭樂」。',
                'link' => '',
                'status' => '8月開放預購'
            ],
            [
                'title' => '黑糖芭比',
                'sub_title' => '號稱蓮霧界的\'夢幻逸品\'',
                'place' => '台灣高雄',
                'season' => '全年 (1-3月、5-12月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_06_04.jpg',
                'text' => '黑糖芭比為蓮霧界的LV，其果色深紅、果實碩大、口感爽脆多汁，甜度高。搭配六龜特有的高山氣候，孕育出自然甜蜜的黑糖芭比蓮霧。',
                'link' => 'https://bit.ly/44e7UeB',
                'status' => '販售中'
            ],
            [
                'title' => '文旦',
                'sub_title' => '香甜細嫩 皮薄多汁',
                'place' => '台灣台南',
                'season' => '秋季 (9-10月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_03_01.jpg',
                'text' => '台灣柚子品種如麻豆文旦、白柚、西施柚等多達14種，其中又以台南「麻豆文旦」最為出名。文旦皮薄，果肉吃起來柔軟多汁，清爽香甜，甜中帶酸，散發出獨特的香氣。',
                'link' => '',
                'status' => '9月開放預購'
            ],
            [
                'title' => '摩天嶺甜柿',
                'sub_title' => '日夜溫差，淬鍊出高甜度的果實',
                'place' => '台灣台中',
                'season' => '秋季 (10月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_03_02.jpg',
                'text' => '摩天嶺海拔900~1000公尺的地理優勢，不但害蟲數量相對平地少，連每天的朝暮都變化萬千，日夜溫差可達9至10℃，淬鍊出高甜度的果實，種出的甜柿顏色亮麗，且飽含光澤。',
                'link' => '',
                'status' => '10月開放預購'
            ],
            [
                'title' => '梨山蜜蘋果',
                'sub_title' => '清脆甜美，口感風味超級滿足',
                'place' => '台灣台中',
                'season' => '秋季 (11月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_03_03.jpg',
                'text' => '蜜蘋果，源自於日本的高貴品種，賦予它美麗優雅的名字，又稱「惠」、「光榮」，果型飽滿，口感清脆，風味甜美。在海拔2000公尺的高山恣意接受陽光與土地的滋養，引用雪霸山脈純淨的水源灌溉，經過日夜極大的溫差才凝結下蘋果的甜美。',
                'link' => '',
                'status' => '11月開放預購'
            ],
            [
                'title' => '極光哈密瓜',
                'sub_title' => '水果界愛馬仕！嘉義極光哈密瓜',
                'place' => '台灣嘉義',
                'season' => '秋、冬季 (11-12月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_03_07.jpg',
                'text' => '鮮嫩多汁的極光哈密瓜，外型橢圓，橘黃果肉口感清脆、皮薄肉厚，甜度可高達18度！不像一般哈密瓜軟綿的口感，反而似水梨般清脆爽口，在夏季品嘗沁涼脆口的極光哈密瓜絕對是一大享受。',
                'link' => '',
                'status' => '11月開放預購'
            ],
            [
                'title' => '日本水蜜桃',
                'sub_title' => '日本的代表性水果',
                'place' => '日本山梨/和歌山',
                'season' => '夏季 (7-8月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_05_01.jpg',
                'text' => '吃起來鮮甜多汁，肉質細嫩，口感豐富。',
                'link' => '',
                'status' => '7月開放預購'
            ],
            [
                'title' => '日本麝香葡萄',
                'sub_title' => '夏秋之際最受歡迎的代表水果之一',
                'place' => '日本岡山',
                'season' => '夏、秋季 (8-9月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_05_02.jpg',
                'text' => '清洗過後連皮直接吃，當清脆頗具彈力的皮一咬開時，豐郁多汁的芳香果肉瞬間滿足了大家的味蕾，充滿著清新夢幻的風味，吃過後保證令人久久難以忘懷。',
                'link' => '',
                'status' => '8月開放預購'
            ],
            [
                'title' => '日本水蜜桃蘋果',
                'sub_title' => '數量稀少、多汁爽脆的最佳口味',
                'place' => '日本青森',
                'season' => '秋季 (10月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_05_03.jpg',
                'text' => '外表黃綠外帶有一抹腮紅，因此又被暱稱為『水蜜桃蘋果』。',
                'link' => '',
                'status' => '10月開放預購'
            ],
            [
                'title' => '日本陽光蜜富士',
                'sub_title' => '蘋果中的極品',
                'place' => '日本青森',
                'season' => '全年 (1-3月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_05_04.jpg',
                'text' => '生長在陽光充足、晝夜溫差大的天氣，每一顆密貧果斑點明顯，甜度及口感極佳。經常會在果核周圍生產蜜芯及蜜炙，切開為透明霜狀，相當美味。',
                'link' => '',
                'status' => '今年產季已過'
            ],
            [
                'title' => '秘魯無籽葡萄',
                'sub_title' => '果實碩大、口感紮實脆口',
                'place' => '南美洲秘魯',
                'season' => '春季 (2-4月)',
                'pic' => 'https://www.bestselection.com.tw/activity/fruits/image/Fru_sea_05_05.jpg',
                'text' => '位於南半球，可以在北半球缺乏葡萄的產季，提供新鮮採收的葡萄，空運來台，保存新鮮以及風味，秋脆屬於無籽葡萄系列的青皮葡萄品種，果肉細緻而多汁、皮薄肉Q，甜度高，帶有淡淡麝香味，冷藏後食用，風味怡人。',
                'link' => '',
                'status' => '今年產季已過'
            ]
        ];

        Fruit::insert($fruits);
    }
}
