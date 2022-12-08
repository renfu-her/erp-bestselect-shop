(function () {
    'use strict'

    window.Screenshot = (url = '', { pages = 1, start, process, error }) => {
        if (!window.EventSource) {
            toast.show('該瀏覽器不支援圖片下載功能', { type: 'danger' });
            return;
        }

        const SERVICE_ORIGIN = 'https://render-edm.bestselection.com.tw';
        url = encodeURIComponent(url);
        const source = new EventSource(`${SERVICE_ORIGIN}/edm-render?url=${url}&total_page=${pages}`);
        if (typeof start !== 'function') start = () => {};
        if (typeof process !== 'function') process = () => {};
        if (typeof error !== 'function') error = () => {};

        // 監聽過程
        source.addEventListener(
            'message',
            function (e) {
                if (e.origin !== SERVICE_ORIGIN) {
                    console.log('Unknown origin: ', e.origin);
                    return;
                }
                // e.data = {"totalTask":總任務數,"task":任務進度,"rate":完成率,"name":"說明"}
                try {
                    const result = JSON.parse(e.data);
                    process(result);
                    console.log(result);
                } catch (err) {
                    error(e.data);
                }
            },
            false
        );

        // 連線已建立
        source.addEventListener(
            'open',
            function (e) {
                start(e);
                console.log('start', e);
            },
            false
        );

        // 發生錯誤
        source.addEventListener(
            'error',
            function (e) {
                if (e.eventPhase === EventSource.CLOSED) {
                    source.close();
                }
                switch (e.target.readyState) {
                    case EventSource.CLOSED:   //連線已關閉
                        error('closed');
                        break;
                    case EventSource.CONNECTING:
                        error('connecting');
                        break;
                
                    default:
                        error('error');
                        break;
                }
            },
            false
        );
    };
})();