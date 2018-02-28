(function () {
    var a = {
            exec: function (editor) {
                var format = {
                    element: 'h4'
                };
                var style = new CKEDITOR.style(format);
                style.apply(editor.document);
            }
        },

        b = "heading-h4";
    CKEDITOR.plugins.add(b, {
        init: function (editor) {
            editor.addCommand(b, a);
        }
    });
})();