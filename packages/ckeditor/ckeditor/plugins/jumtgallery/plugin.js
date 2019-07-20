/**
 * CKEditor for Joomla!
 *
 * @version       5.x
 * @package       CKEditor
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2014-2019 by Denys D. Nosov (https://joomla-ua.org)
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

CKEDITOR.plugins.add('jumtgallery', {
    requires: ['iframedialog'],
    lang: ['en', 'ru', 'uk'],
    icons: 'jumtgallery',
    init: function (editor) {

        var height = 540,
            width = 750,
            editarea = editor_name;

        CKEDITOR.dialog.addIframe(
            'myiframedialogDialog',
            editor.lang.jumtgallery.title,
            this.path + 'gallery/form.php?editor=' + editarea,
            width,
            height,
            function () {
            }, {
                onShow: function () {
                    document.getElementById(this.getButton('ok').domId).style.display = 'none';
                }
            }
        );

        editor.addCommand('myiframedialog', new CKEDITOR.dialogCommand('myiframedialogDialog'));

        editor.ui.addButton('jumtgallery', {
            label: editor.lang.jumtgallery.button,
            command: 'myiframedialog',
            icon: CKEDITOR.plugins.getPath('jumtgallery') + '/icons/icon.png'
        });
    }
});