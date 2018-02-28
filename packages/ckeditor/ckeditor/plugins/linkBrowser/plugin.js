/*
 * This plugin uses parts of JCE extension by Ryan Demmer.
 * @copyright	Copyright (C) 2005 - 2011 Ryan Demmer. All rights reserved.
 * @copyright	Copyright (C) 2003 - 2011, CKSource - Frederico Knabben. All rights reserved.
 * @license		GNU/GPL
 * CKEditor extension is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
(function () {
	var pluginName = 'linkBrowser';
	var dialog;
	CKEDITOR.plugins.add('linkBrowser',
		{
			requires: ['iframedialog', 'link'],

			init: function (editor) {
				dialog = this;

				//plugin code goes here

				window.FuncDialogNr = editor._.FuncDialogNr = CKEDITOR.tools.addFunction(dialogFunction, editor)
				CKEDITOR.on('dialogDefinition', function (ev) {
					// Take the dialog name and its definition from the event
					// data.
					var dialogName = ev.data.name;
					var dialogDefinition = ev.data.definition;
					if (dialogName == "link" || dialogName == "image") {
						dialogDefinition.minWidth = 500;
						dialogDefinition.minHeight = 350;
					}

					// Check if the definition is from the dialog we're
					// interested on (the 'Link' dialog).
					if (dialogName == 'link' || dialogName == 'image') {
						if (document.location.pathname.indexOf('administrator') > -1) {
							path = '../';
						} else {
							path = '';
						}
						dialog = dialogDefinition;
						// Add a new tab to the 'Link' dialog.
						dialogDefinition.addContents({
							id: 'linkBrowserTab',
							label: 'Link Browser',
							accessKey: 'B',
							elements: [
								{
									id: 'dummy',
									type: 'html',
									html: ''
								},
								{
									id: 'linkBrowser',
									type: 'html',
									html: '<iframe src="' + path + 'index.php?option=com_ckeditor&task=plugin&plugin=linkBrowser" id="iframeNextgen" allowtransparency="1" style="width:500px;height:340px;margin:0;padding:0;vertical-align: auto;"></iframe>'
								}
							]
						});

					}
				});
			}
		});

	function dialogFunction(string) {
		if (dialog.dialog.getName() == 'link') {
			dialog.dialog.setValueOf('info', 'url', string);
			dialog.dialog.selectPage('info');
		}
		if (dialog.dialog.getName() == 'image') {
			dialog.dialog.selectPage('Link');
			dialog.dialog.setValueOf('Link', 'txtUrl', string);
		}
		if (linkBrowserUrl == 'relative') {
			dialog.dialog.setValueOf('info', 'protocol', '');
		}

	}
})();
