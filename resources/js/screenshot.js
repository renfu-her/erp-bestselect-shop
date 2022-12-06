(function () {
    'use strict'

    window.Screenshot = (url = '', { pages = 1, start, process, error }) => {
        if (!window.EventSource) {
            toast.show('該瀏覽器不支援圖片下載功能', { type: 'danger' });
        }
        url = encodeURIComponent(url);
        const source = new EventSource(`http://localhost:3003/edm-render?url=${url}&total_page=${pages}`);
        if (typeof start !== 'function') start = () => {};
        if (typeof process !== 'function') process = () => {};
        if (typeof error !== 'function') error = () => {};

        // 監聽過程
        source.addEventListener(
            'message',
            function (e) {
                // e.data = {"totalTask":總任務數,"task":任務進度,"rate":完成率,"name":"說明"}
                try {
                    const result = JSON.parse(e.data);
                    process(result);
                    console.log(result);
                } catch (err) {
                    error(err);
                }
            },
            false
        );

        // 開始連接
        source.addEventListener(
            'open',
            function (e) {
                start(e);
                console.log(e);
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
                    case EventSource.CLOSED:
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