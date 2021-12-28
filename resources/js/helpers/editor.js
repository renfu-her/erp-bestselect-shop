const TEditor = require('@toast-ui/editor');
require('@toast-ui/editor/dist/i18n/zh-tw');
const colorPlugin = require('@toast-ui/editor-plugin-color-syntax');


module.exports = class Editor {
    
    static createEditor(elemId, options) {
        options = {
            height: 'auto',
            toolbarItems: [
                ['heading', 'bold', 'italic', 'strike'],
                ['hr', 'quote'],
                ['ul', 'ol', 'indent', 'outdent'],
                ['table', 'link']
            ],
            imageTool: true,
            colorTool: true,
            ...options
        };

        const editor = new TEditor({
            el: document.querySelector('#' + elemId),
            initialEditType: 'wysiwyg',
            hideModeSwitch: true,
            language: 'zh-TW',
            height: options.height || 'auto',
            toolbarItems: options.toolbarItems,
            plugins: (options.colorTool) ? [colorPlugin] : []
        });
        // auto height
        document.querySelector('#' + elemId + ' .toastui-editor-main-container').addEventListener('click', function () {
            editor.focus();
        }, true);

        // image only URL
        if (options.imageTool) {
            let insertBodyHtml = document.createElement('div');
            insertBodyHtml.innerHTML = `
                <label for="tuiImgUrl_` + elemId + `">圖片網址</label>
                <input id="tuiImgUrl_` + elemId + `" type="text">
                <label for="tuiAlt_` + elemId + `">描述</label>
                <input id="tuiAlt_` + elemId + `" type="text">
                
                <div class="toastui-editor-button-container">
                    <button type="button" class="toastui-editor-close-button"
                        onClick="javascript:window['` + elemId + `'].eventEmitter.emit('closePopup'); window['` + elemId + `'].focus();"
                    >取消</button>
                    <button type="button" class="toastui-editor-ok-button"
                        onClick="javascript:window['` + elemId + `'].exec('addImage', 
                        { imageUrl: document.getElementById('tuiImgUrl_` + elemId + `').value, 
                        altText: document.getElementById('tuiAlt_` + elemId + `').value }); 
                        window['` + elemId + `'].eventEmitter.emit('closePopup'); window['` + elemId + `'].focus();"
                    >確認</button>
                </div>`;
            editor.insertToolbarItem({ groupIndex: 3, itemIndex: 1 }, {
                name: 'imageUrl',
                tooltip: '插入圖片',
                popup: {
                    body: insertBodyHtml
                },
                text: '',
                className: 'image toastui-editor-toolbar-icons'
            });
        }

        window[elemId] = editor;
    }
}