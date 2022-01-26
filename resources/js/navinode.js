$(function () {
    // const testData = [
    // {"level":1,"title":"aaa","child":[{"level":2,"title":"bbb","child":[]},{"level":2,"title":"ccc","child":[]}]},
    // {"level":1,"title":"ddd","child":[{"level":2,"title":"eee","child":[{"level":3,"title":"fff"}]}]},
    // {"level":1,"title":"ggg","child":[]}];
    const testData = [
        {"id":1,"level":1,"title":"level1-a","child":[
            {"id":2,"level":2,"title":"level2-a","type":"2"},
            {"id":3,"level":2,"title":"level2-b","child":[
                {"id":4,"level":3,"title":"level3-a","type":"2"},
                {"id":5,"level":3,"title":"level3-b","type":"2"},
                {"id":6,"level":3,"title":"level3-c","type":"2"}]}]},
        {"id":7,"level":1,"title":"level1-b","type":"2"}];
    console.log('測試資料: ', testData);
    /** 定義 ************************************************************** */
    /**
     * @typedef JQuery
     * @type {object} $JQuery
     */
    
    // 階層。可動態更改最低集數
    // * LiClass 和 <style> ul.level_3>li 間距樣式 暫需手動更改 (我懶得寫了有興趣自己加:D)
    /**
     * 最高級數
     */
    const MAX_LV = 1;
    /**
     * 最低級數
     */
    const MIN_LV = 3;

    // 樣式 class
    /** ul component 基本樣式 */
    const UlBaseClass = 'd-flex align-items-end flex-column level ';
    /** ul component 階層樣式表 */
    const UlClass = defineUlClass();
    /** ul component 所有層級樣式 */
    const AllUlLvClass = Object.values(UlClass).join(' ');
    // console.log(UlClass);    // {1: 'level_1', 2: 'level_2', 3: 'level_3'}
    // console.log(AllUlLvClass);   // {'level_1 level_2 level_3'}

    /** li component 基本樣式 */
    const LiBaseClass = 'col-12 ';
    /** li component 階層樣式表 */
    const LiClass = {
        1: '',
        2: '',
        3: ''
    };
    /** li component 所有層級樣式 */
    const AllLiLvClass = Object.values(LiClass).join(' ');

    /** Template: one item component */
    const OneItemCopy = $('div.-cloneElem').clone().removeClass('-cloneElem d-none');
    $('div.-cloneElem').remove();
    /** Template: li component */
    const LiCopy = $(`<li class="${LiBaseClass}"></li>`).append([
        OneItemCopy,
        `<ul class="${UlBaseClass}level_2"></ul>`
    ]);

    /**
     * 自動生成 ul 階層樣式表
     * @returns {object} 樣式表
     */
    function defineUlClass() {
        let uc = {};
        for (let lv = MAX_LV; lv <= MIN_LV; lv++) {
            uc[lv] = 'level_' + lv;
        }
        return uc;
    }
    
    /* ************************************************************************ */
    
    /** 按鈕: 新增項目 */
    window.bindNewItemBtn = bindNewItemBtn;
    function bindNewItemBtn(parent = '') {
        let newLi = LiCopy.clone();
        bindDeleteBtn(newLi);
        
        $(`${parent} ul.${UlClass[MAX_LV]}`).append(newLi);
        resetAllLevelBtn();
        bindSortableBtn();
    }

    /**  按鈕: 儲存 */
    $('#menu_save').on('click', function () {
        let data = [];

        $('.level_1 > li').each(function () {
            data.push(getOneLiData($(this)));
        });

        console.log('***** Data:', data);

        /**
         * 取一組<li>項目資料
         * @param {JQuery} $li 一組 li 項目
         * @returns {object} 項目資料，含 {層級數, 選單名稱 [, 子項]}
         */
        function getOneLiData($li) {
            let level = getCurrentLevel($li.closest('ul.level'));
            let title = $li.children('.oneItem').find('.-title').text();
            // console.log(level, '--- ', title);

            if ($li.children('ul.level').length) {
                let child = $li.children('ul.level').children('li').map(function () {
                    return getOneLiData($(this));
                }).get();
                return { level, title, child };
            } else {
                return { level, title };
            }
        }
    });

    loadNaviNode(testData, '.-appendClone');
    /**
     * 依資料載入主選單
     * @param {Array} data 資料陣列
     */
    window.loadNaviNode = loadNaviNode;
    function loadNaviNode(data, parent = '') {
        data.forEach(liItem => {
            if (liItem.title && liItem.level) {
                $(`${parent} ul.${UlClass[MAX_LV]}`).append(setOneLiComponent(liItem));
            }
        });
        
        bindDeleteBtn(null, parent);
        resetAllLevelBtn();
        bindSortableBtn();

        /**
         * 依資料產生一組 li component
         * @param {object} liItem 資料
         * @returns {string} li 的 HTML
         */
        function setOneLiComponent(liItem) {
            let newLi = LiCopy.clone();
            // 改 title
            newLi.find('.oneItem .-title').text(liItem.title);
            if (liItem.level < MIN_LV) {
                // 改 子項的ul level class
                newLi.children('ul.level').removeClass(AllUlLvClass).addClass(UlClass[liItem.level + 1]);
                // 加入子項
                if (liItem.child) {
                    newLi.children('ul.level').append((liItem.child).map(function (title) {
                        return setOneLiComponent(title);
                    }));
                }
            } else {
                // 最後一階沒子項
                newLi.children('ul.level').remove();
            }
            
            return newLi;
        }
    }

    /** 功能 ************************************************************************ */

    /**
     * 綁定事件: click 刪除鈕
     * @param {JQuery} $li 一組 li 項目
     */
    function bindDeleteBtn($li, parent = '') {
        let $target = $li || $(`${parent} ul.${UlClass[MAX_LV]}`);
        $target.find('.oneItem .icon.-del').off('click').on('click', function () {
            $(this).closest('li').remove();
            resetAllLevelBtn();
        });
    }
    
    /**
     * 設定階層按鈕，每次有改變項目層級(排序、新增、刪除)都必須呼叫一次
     * 設定完綁定 click 調階層按鈕 [←] [→]
     */
    window.resetAllLevelBtn = resetAllLevelBtn;
    function resetAllLevelBtn() {
        $('.oneItem .icon').filter('.-upLv, .-downLv').removeClass('disabled');
        
        /**
         * 禁用進階鈕 [←]
         * 1. 最高級數項
         */
        $(`ul.level_${MAX_LV} > li > div .icon.-upLv`).addClass('disabled');

        /**
         * 禁用退階鈕 [→]
         * 1. 為該組同階的第一項
         * 2. 該組為滿階(孩子中有最低級數)
         * 3. 最低級數項
         */
        $(`ul.level > li:first-child > div .icon.-downLv,
            ul.level > li:has(ul.level_${MIN_LV} > li) > div .icon.-downLv,
            ul.level_${MIN_LV} > li > div .icon.-downLv`).addClass('disabled');

        // bind
        bindLevelBtn();
            

        /**
         * 綁定事件: click 調階層按鈕 [←] [→]
         */
        function bindLevelBtn() {
            /** [←] Left Btn 進階 */
            $('span.-upLv').off('click.level');
            $('span.-upLv:not(.disabled)').on('click.level', function () {
                let $thisLi = $(this).closest('li');
                let $parentLi = $thisLi.parent().closest('li');
                let thisLv = getCurrentLevel($thisLi);
                
                if (!thisLv || thisLv === MAX_LV) {
                    return false;
                } else {
                    // 重整(為新階層的)樣式
                    resetLevelClass(thisLv - 1, $thisLi);
                    
                    // 移動
                    $parentLi.after($thisLi);

                    // 重整階層按鈕
                    resetAllLevelBtn();
                    // 拖曳按鈕
                    bindSortableBtn();
                }
            });

            /** [→] Right Btn 退階 */
            $('span.-downLv').off('click.level');
            $('span.-downLv:not(.disabled)').on('click.level', function () {
                let $thisLi = $(this).closest('li');
                let $prevLi = $thisLi.prev('li');
                let thisLv = getCurrentLevel($thisLi);

                if (!thisLv || thisLv === MIN_LV) {
                    return false;
                } else {
                    // 重整(為新階層的)樣式
                    resetLevelClass(thisLv + 1, $thisLi);

                    // 移動
                    $prevLi.children('ul').append($thisLi);

                    // 重整階層按鈕
                    resetAllLevelBtn();
                    // 拖曳按鈕
                    bindSortableBtn();
                }
            });
        }
    }

    /**
     * 綁定事件: sortable 拖曳功能 [↔ ↕]。每次新增項目都必須呼叫一次
     */
    function bindSortableBtn() {
        $('ul.level.ui-sortable').sortable('destroy');

        for (const lv in UlClass) {
            if (UlClass[lv]) {
                $(`ul.${UlClass[lv]}`).sortable({
                    axis: 'y',
                    placeholder: "placeholder-highlight",
                    connectWith: `.${UlClass[lv]}`,
                    handle: '.icon.-move',
                    cursor: 'move',
                });
            }
        }

        // 給 .placeholder-highlight 高度
        $('ul.level').off('sortstart');
        $('ul.level').on('sortstart', function (event, ui) {
            (ui.helper).css('height', `${(ui.item).height()}px`);
            (ui.placeholder).css('height', `${(ui.item).height()}px`);
        });

        // 有更新順序時
        $('ul.level').off('sortupdate');
        $('ul.level').on('sortupdate', function () {
            resetAllLevelBtn();
        });
    }

    /* ************************************************************************ */

    /**
     * 抓層級
     * @param {JQuery} $current 要找的目標
     * @returns {number} 層級數
     */
    function getCurrentLevel($current) {
        let $Ul = $current.closest('ul');

        for (const lv in UlClass) {
            if (UlClass[lv] && $Ul.hasClass(UlClass[lv])) {
                // console.log('lv-', lv);
                return Number(lv) || 0;
            }
        }
        return 0;
    }

    /**
     * 重整層級
     * @param {number} newLv 更改後的新層級
     * @param {JQuery} $topLi 當前要改的 li 項目
     */
    function resetLevelClass(newLv, $topLi) {
        // 由高往低改層級
        for (let lv = newLv, $tempLi = $topLi, $tempUl = $(); 
            lv <= MIN_LV && ($tempLi.length || $tempUl.length); 
            lv++
        ) {
            if ($tempUl.length) {
                $tempUl.removeClass(AllUlLvClass).addClass(UlClass[lv]);
            }
            if ($tempLi.length) {
                $tempLi.removeClass(AllLiLvClass).addClass(LiClass[lv]);
                
                if (lv === MIN_LV) {
                    $tempLi.children('ul').remove();
                } else if (!$tempLi.children('ul').length) {
                    $tempLi.append($('<ul></ul>').addClass(UlBaseClass + UlClass[lv + 1]));
                }
            }

            // 下一階
            $tempUl = $tempLi.children('ul');
            $tempLi = $tempUl.children('li');
        }
    }

});