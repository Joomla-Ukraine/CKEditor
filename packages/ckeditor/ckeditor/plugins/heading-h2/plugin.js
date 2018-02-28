(function () {
    var a = {
            exec: function (editor) {
                var format = {
                    element: 'h2'
                };
                var style = new CKEDITOR.style(format);
                style.apply(editor.document);
            }
        },

        b = "heading-h2";
    CKEDITOR.plugins.add(b, {
        init: function (editor) {
            editor.addCommand(b, a);
        }
    });
})();