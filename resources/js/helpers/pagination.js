/**
 * 產生分頁
 * @param $elem baseElem 產生分頁的容器
 * @param Object options {hasPageSum: 是否顯示總計}
 * 
 * @example
 * let myPages = new Pagination($('pageDiv'));
 * myPages.create(currentPage當前頁面, {
 *  totalData: 總筆數,
 *  totalPages: 總頁數,
 *  changePageFn: 換頁fn()
 * })
 */
module.exports = class Pagination {
    constructor(baseElem, options = {}) {
        if (!baseElem || !baseElem.length) {
            console.error('The first argument is required and must be an element.');
            return;
        }
        this.baseElem = baseElem;
        this.opts = Object.assign({}, {
            Max_num: 13,    // 最多顯示數
            Buffer_num: 3,   // active 鄰居數
            Edge_num: 2,     // 邊界頁數
            Ellipsis_num: 1, // ...數
            Active_num: 1,   // active數

            hasPageSum: true,   // 是否顯示總計
        }, options);
        this.init();
    }

    init() {
        const { baseElem } = this;
        const { hasPageSum } = this.opts;

        baseElem.empty();
        if (hasPageSum) {
            baseElem.append('<div id="pageSum" class="me-1">共 0 頁（共 0 筆資料）</div>');
        }
        baseElem.append(`<div class="d-flex justify-content-center">
                <nav>
                    <ul class="pagination">
                    <li class="page-item disabled">
                            <button type="button" class="page-link" aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                    </li>
                    <li class="page-item disabled">
                            <button type="button" class="page-link" aria-label="Next">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                    </li>
                    </ul>
                </nav>
            </div>
        `);
    }

    create(currentPage, {
        totalData = 0,   // 總筆數
        totalPages = 0,    // 總頁數
        changePageFn = null    // 換頁fn
    } = {}) {
        this.currentPage = currentPage;
        const { baseElem } = this;
        const { hasPageSum,
            Max_num,
            Buffer_num,
            Edge_num,
            Ellipsis_num,
            Active_num
        } = this.opts;

        if (hasPageSum) {
            baseElem.find('#pageSum').text(`共 ${totalPages} 頁（共 ${totalData} 筆資料）`);
        }
        // init
        baseElem.find('.page-item').removeClass('disabled').attr('tabindex', null);
        baseElem.find('.page-item:not(:first-child, :last-child)').remove();
        baseElem.find('nav').show();
        // 分頁
        for (let index = 1; index <= totalPages; index++) {
            let $li = $('<li class="page-item"></li>');

            /*** 顯示數字條件
             * 1. 總頁數 >= Max_num
             * 2. 邊界頁數
             * 3. 當前頁數及前後緩衝鄰居頁
             * 4. 當 當前頁在離頭尾邊界差距最大連續邊界頁(=邊界頁數+省略頁+緩衝頁+active頁)以內時，最大連續邊界頁以內的頁數
             * */
            let maxContinuousPage = Edge_num + Ellipsis_num + Buffer_num + Active_num;
            if (totalPages <= Max_num || 
                index <= Edge_num || index > totalPages - Edge_num ||
                Math.abs(currentPage - index) <= Buffer_num ||
                (maxContinuousPage >= currentPage && maxContinuousPage + Buffer_num >= index) ||
                (totalPages - maxContinuousPage < currentPage && totalPages - (maxContinuousPage + Buffer_num) < index)
            ) {
                $li = PageLink_N(index, $li);
            } else {
                $li = PageLink_Es(index, $li);
            }
            
            if ($li) baseElem.find('.page-item:last-child').before($li);
        }

        // disabled Previous Next
        baseElem.find('.page-item.active:nth-child(2)').prev('.page-item').addClass('disabled');
        baseElem.find('.page-item.active:nth-last-child(2) + .page-item').addClass('disabled');
        baseElem.find('.page-item.disabled').attr('tabindex', -1);
        // bind click event
        baseElem.find('.page-item button.page-link').off('click').on('click', function () {
            const btn = $(this).attr('aria-label');
            let page = baseElem.find('.page-item.active span.page-link').data('page');
            switch (btn) {
                case 'Previous':
                    page--;
                    break;
                case 'Next':
                    page++;
                    break;
                default:
                    page = $(this).data('page');
                    break;
            }
            if (typeof changePageFn === 'function') {
                changePageFn(page);
            }
        });

        // 產生 數字鈕
        function PageLink_N(index, $li) {
            let $page_link = '';
            if (index == currentPage) {
                $page_link = $(`<span class="page-link">${index}</span>`);
                $li.addClass('active');
            } else {
                $page_link = $(`<button class="page-link" type="button">${index}</button>`);
            }
            $page_link.data('page', index);
            $li.append($page_link);
            return $li;
        }
        // 產生 省略符號
        function PageLink_Es(index, $li) {
            if (baseElem.find('.page-item:nth-last-child(2) span').text() === '...') {
                return false;
            } else {
                $li.addClass('disabled');
                $li.append('<span class="page-link">...</span>');
                return $li;
            }
        }
    }
};