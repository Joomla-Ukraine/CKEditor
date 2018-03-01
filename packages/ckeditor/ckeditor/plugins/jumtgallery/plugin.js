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

CKEDITOR.plugins.add('jumtgallery', {
    requires: ['iframedialog'],
    icons: 'jumtgallery',
    init: function (editor) {
        var height = 540,
            width = 750;

        var editarea = editor_name;

        CKEDITOR.dialog.addIframe(
            'myiframedialogDialog',
            'Gallery',
            this.path + 'gallery/form.php?editor=' + editarea,
            width,
            height,
            function () {
            },
            {
                onOk: function () {
                }
            }
        );

        editor.addCommand('myiframedialog', new CKEDITOR.dialogCommand('myiframedialogDialog'));

        editor.ui.addButton('jumtgallery', {
            label: 'Insert Facebook and Twitter post',
            command: 'myiframedialog',
            icon: CKEDITOR.plugins.getPath('jumtgallery') + '/icons/icon.png'
        });
    }
});

var toolbar = CKEDITOR.config.toolbar_Full;

toolbar[toolbar.length - 1].items.push('Myiframedialog');