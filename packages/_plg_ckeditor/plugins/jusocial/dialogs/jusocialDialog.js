/*
*   Plugin developed by Joomla! Ukraine
*
*   LICENCE: GPL, LGPL, MPL
*   NON-COMMERCIAL PLUGIN.
*
*/

CKEDITOR.dialog.add( 'jusocialDialog', function( editor ) {
    return {
        title: 'Посты из соцсети',
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
                        label: 'Вставте embed-код',
                        validate: CKEDITOR.dialog.validate.notEmpty( "Пусто!" )
                    }
                ]
            }
        ],
        onOk: function()
        {
            var editor = this.getParentEditor();
            var content = this.getValueOf( 'jusocPlugin', 'jusoc_code' );

            if ( content.length>0 )
            {
                var realElement = CKEDITOR.dom.element.createFromHtml('<p><p>');

                realElement.setHtml('[socpost]' + convert(content) + '[/socpost]');
                editor.insertElement(realElement);
            }
        }
    };
});

function convert(str)
{
    str = str.replace(/&/g, "&amp;");
    str = str.replace(/>/g, "&gt;");
    str = str.replace(/</g, "&lt;");
    return str;
}
