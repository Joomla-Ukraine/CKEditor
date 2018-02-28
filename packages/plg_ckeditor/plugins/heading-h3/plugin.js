(function () {
    var a = {
            exec: function (editor) {
                var format = {
                    element: 'h3'
                };
                var style = new CKEDITOR.style(format);
                style.apply(editor.document);
            }
        },

        b = "heading-h3";
    CKEDITOR.plugins.add(b, {
        init: function (editor) {
            editor.addCommand(b, a);
        }
    });
})();