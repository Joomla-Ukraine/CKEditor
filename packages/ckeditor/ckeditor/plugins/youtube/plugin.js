/*
* Youtube Embed Plugin
*
* @author Jonnas Fonini <jonnasfonini@gmail.com>
* @version 2.1.9
*/
(function () {
    CKEDITOR.plugins.add('youtube', {
        lang: ['en', 'bg', 'pt', 'pt-br', 'ja', 'hu', 'it', 'fr', 'tr', 'ru', 'de', 'ar', 'nl', 'pl', 'vi', 'zh', 'el', 'he', 'es', 'nb', 'nn', 'fi', 'et', 'sk', 'cs', 'ko', 'eu'],
        init: function (editor) {
            editor.addCommand('youtube', new CKEDITOR.dialogCommand('youtube', {
                allowedContent: 'div{*}(*); iframe{*}[!width,!height,!src,!frameborder,!allowfullscreen]; object param[*]; a[*]; img[*]'
            }));

            editor.ui.addButton('Youtube', {
                label: editor.lang.youtube.button,
                toolbar: 'insert',
                command: 'youtube',
                icon: this.path + 'images/icon.png'
            });

            CKEDITOR.dialog.add('youtube', function (instance) {
                var video,
                    disabled = editor.config.youtube_disabled_fields || [];

                return {
                    title: editor.lang.youtube.title,
                    minWidth: 510,
                    minHeight: 200,
                    onShow: function () {
                        for (var i = 0; i < disabled.length; i++) {
                            this.getContentElement('youtubePlugin', disabled[i]).disable();
                        }
                    },
                    contents:
                        [{
                            id: 'youtubePlugin',
                            expand: true,
                            elements:
                                [
                                    {
                                        type: 'hbox',
                                        widths: ['70%', '15%', '15%'],
                                        children:
                                            [
                                                {
                                                    id: 'txtUrl',
                                                    type: 'text',
                                                    label: editor.lang.youtube.txtUrl,
                                                    validate: function () {
                                                        if (this.isEnabled()) {
                                                            if (!this.getValue()) {
                                                                alert(editor.lang.youtube.noCode);
                                                                return false;
                                                            } else {
                                                                video = ytVidId(this.getValue());

                                                                if (this.getValue().length === 0 || video === false) {
                                                                    alert(editor.lang.youtube.invalidUrl);
                                                                    return false;
                                                                }
                                                            }
                                                        }
                                                    }
                                                },
                                                {
                                                    type: 'text',
                                                    id: 'txtWidth',
                                                    width: '60px',
                                                    label: editor.lang.youtube.txtWidth,
                                                    'default': editor.config.youtube_width != null ? editor.config.youtube_width : '640',
                                                    validate: function () {
                                                        if (this.getValue()) {
                                                            var width = parseInt(this.getValue()) || 0;

                                                            if (width === 0) {
                                                                alert(editor.lang.youtube.invalidWidth);
                                                                return false;
                                                            }
                                                        } else {
                                                            alert(editor.lang.youtube.noWidth);
                                                            return false;
                                                        }
                                                    }
                                                },
                                                {
                                                    type: 'text',
                                                    id: 'txtHeight',
                                                    width: '60px',
                                                    label: editor.lang.youtube.txtHeight,
                                                    'default': editor.config.youtube_height != null ? editor.config.youtube_height : '360',
                                                    validate: function () {
                                                        if (this.getValue()) {
                                                            var height = parseInt(this.getValue()) || 0;

                                                            if (height === 0) {
                                                                alert(editor.lang.youtube.invalidHeight);
                                                                return false;
                                                            }
                                                        } else {
                                                            alert(editor.lang.youtube.noHeight);
                                                            return false;
                                                        }
                                                    }
                                                }
                                            ]
                                    }
                                ]
                        }
                        ],
                    onOk: function () {
                        var content = '';

                        var url = 'https://', params = [], startSecs;
                        var width = this.getValueOf('youtubePlugin', 'txtWidth');
                        var height = this.getValueOf('youtubePlugin', 'txtHeight');

                        url += 'www.youtube.com/';
                        url += 'embed/' + video;


                        if (params.length > 0) {
                            url = url + '?' + params.join('&');
                        }

                        content += '<iframe width="' + width + '" height="' + height + '" src="' + url + '"';
                        content += 'frameborder="0" allowfullscreen loading="lazy"></iframe>';

                        var element = CKEDITOR.dom.element.createFromHtml(content);
                        var instance = this.getParentEditor();
                        instance.insertElement(element);
                    }
                };
            });
        }
    });
})();

function handleLinkChange(el, api) {
    var video = ytVidId(el.getValue());
    var time = ytVidTime(el.getValue());

    if (el.getValue().length > 0) {
        el.getDialog().getContentElement('youtubePlugin', 'txtEmbed').disable();
    } else {
        el.getDialog().getContentElement('youtubePlugin', 'txtEmbed').enable();
    }
}

function handleEmbedChange(el, api) {

}


/**
 * JavaScript function to match (and return) the video Id
 * of any valid Youtube Url, given as input string.
 * @author: Stephan Schmitz <eyecatchup@gmail.com>
 * @url: http://stackoverflow.com/a/10315969/624466
 */
function ytVidId(url) {
    var p = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
    return (url.match(p)) ? RegExp.$1 : false;
}

/**
 * Matches and returns time param in YouTube Urls.
 */
function ytVidTime(url) {
    var p = /t=([0-9hms]+)/;
    return (url.match(p)) ? RegExp.$1 : false;
}