module.exports = class Toast {
    constructor() {
        if (!$('#liveToast').length || !$('#liveToast_h').length) {
            console.error('Toast need dom Element!');
            return;
        }
    }

    /**
     * 吐司訊息
     * @param String content 內容
     * @param Object options {type 類型, title 標題, subTitle 副標題, Toast選項}
     * 
     * @example
     * toast.show('內容', { 
     *  title: '標題', 
     *  subTitle: '副標題', 
     *  type: 'danger', 
     *  animation: true,
     *  autohide: true, 
     *  delay: 5000 
     * })
     */

    show(content, options = {}) {
        let $elem = $('#liveToast').clone();

        if (options.title || options.subTitle) {
            $elem = $('#liveToast_h').clone();
            $('.t-title', $elem).html(options.title);
            $('.t-subTitle', $elem).html(options.subTitle);
        }

        if (options.type) {
            $elem.addClass('toast-' + options.type);
            
            switch (options.type) {
                case 'danger':
                case 'warning':
                    $('.toast-header i.bi', $elem).addClass('bi-exclamation-triangle-fill');
                    break;
                case 'success':
                    $('.toast-header i.bi', $elem).addClass('bi-check-circle-fill');
                    break;
                case 'primary':
                default:
                    $('.toast-header i.bi', $elem).addClass('bi-info-circle-fill');
                    break;
            }
        }

        $('.t-content', $elem).html(content);
        $elem.attr('id', null);
        $('.toast-container').append($elem);
        var toast = new bootstrap.Toast($elem, options);
        toast.show();
    }
};
