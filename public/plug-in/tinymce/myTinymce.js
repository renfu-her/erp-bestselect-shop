const TINY_OPTION = {
    language: 'zh_TW',
    skin: 'oxide',
    plugins: `code fullscreen lists link image media charmap preview anchor table`,
    menubar: false,  // 'edit insert format table tools'
    toolbar_mode: 'wrap',
    toolbar: 
        `code preview fullscreen | undo redo cut copy paste pastetext | 
        alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | 
        styles | fontfamily | fontsize | bold italic underline strikethrough forecolor backcolor codeformat | removeformat |
        link unlink anchor | image media table hr blockquote charmap`,
    iframe_template_callback: (data) => (
        `<div class="embed-iframe-video">
            <iframe src="${data.source}" frameborder="0" allowfullscreen></iframe>
        </div>`
    ),
    // allow_script_urls: true,
    // valid_elements: '*[*]',
    // extended_valid_elements: 'script[language|type|src|async|defer|charset]'
};
