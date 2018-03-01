/**
 * CKEditor for Joomla!
 *
 * @version       5.x
 * @package       CKEditor
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2014-2018 by Denys D. Nosov (https://joomla-ua.org)
 * @license       LICENSE.md
 *
 **/

/*
 * Plugin developed by Joomla! Ukraine
 *
 * LICENCE: GPL, LGPL, MPL
 * NON-COMMERCIAL PLUGIN.
 *
 **/

(function () {
    CKEDITOR.plugins.add('jusocial', {
        lang: ['en', 'ru', 'uk'],
        icons: 'jusocial',
        init: function (editor) {
            console.log(editor.lang);
            editor.addCommand('jusocial', new CKEDITOR.dialogCommand('jusocialDialog'));
            editor.ui.addButton('jusocial', {
                label: editor.lang.jusocial.button,
                command: 'jusocial',
                icon: CKEDITOR.plugins.getPath('jusocial') + '/icons/icon.png'
            });

            CKEDITOR.dialog.add('jusocialDialog', function (editor) {
                return {
                    title: editor.lang.jusocial.title,
                    minWidth: 400,
                    minHeight: 100,
                    contents: [
                        {
                            id: 'jusocPlugin',
                            label: 'Basic Settings',
                            elements: [
                                {
                                    type: 'textarea',
                                    id: 'jusoc_code',
                                    label: editor.lang.jusocial.jusadd,
                                    validate: CKEDITOR.dialog.validate.notEmpty(editor.lang.jusocial.empty)
                                }
                            ]
                        }
                    ],
                    onOk: function () {
                        var editor = this.getParentEditor();
                        var content = this.getValueOf('jusocPlugin', 'jusoc_code');

                        if (content.length > 0) {
                            var realElement = CKEDITOR.dom.element.createFromHtml('<p><p>');

                            realElement.setHtml('[socpost]' + convert(content) + '[/socpost]');
                            editor.insertElement(realElement);
                        }
                    }
                };
            });
        }
    });
})();

function convert(str) {
    str = str.replace(/&/g, "&amp;");
    str = str.replace(/>/g, "&gt;");
    str = str.replace(/</g, "&lt;");
    return str;
}