const TEditor = require('@toast-ui/editor');
require('@toast-ui/editor/dist/i18n/zh-tw');
const colorPlugin = require('@toast-ui/editor-plugin-color-syntax');


module.exports = class Editor {
    
    static createEditor(elem, options) {
        options = {
            toolbarItems: [
                ['heading', 'bold', 'italic', 'strike'],
                ['hr', 'quote'],
                ['ul', 'ol', 'indent', 'outdent'],
                ['table', 'image', 'link']
            ],
            colorTool: true,
            ...options
        };

        const editor = new TEditor({
            el: document.querySelector(elem),
            initialEditType: 'wysiwyg',
            hideModeSwitch: true,
            language: 'zh-TW',
            height: options.height || 'auto',
            toolbarItems: options.toolbarItems,
            plugins: (options.colorTool) ? [colorPlugin] : []
        });
        // auto height
        document.querySelector(elem + ' .toastui-editor-main-container').addEventListener('click', function () {
            editor.focus();
        });

        return editor;
    }
}