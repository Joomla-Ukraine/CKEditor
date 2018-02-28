(function () {
    var a = {
            exec: function (editor) {
                var format = {
                    element: 'h6'
                };
                var style = new CKEDITOR.style(format);
                style.apply(editor.document);
            }
        },

        b = "heading-h6";
    CKEDITOR.plugins.add(b, {
        init: function (editor) {
            editor.addCommand(b, a);
        }
    });
})();