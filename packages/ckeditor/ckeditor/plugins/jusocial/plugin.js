/*
*   Plugin developed by Joomla! Ukraine
*
*   LICENCE: GPL, LGPL, MPL
*   NON-COMMERCIAL PLUGIN.
*
*/

CKEDITOR.plugins.add( 'jusocial', {
    icons: 'jusocial',
    init: function( editor ) {

        editor.addCommand( 'jusocial', new CKEDITOR.dialogCommand( 'jusocialDialog' ) );
        editor.ui.addButton( 'jusocial', {
            label: 'Посты из соцсети',
            command: 'jusocial',
            icon: CKEDITOR.plugins.getPath('jusocial') + '/icons/icon.png'
        });

        CKEDITOR.dialog.add( 'jusocialDialog', this.path + 'dialogs/jusocialDialog.js' );
    }
});