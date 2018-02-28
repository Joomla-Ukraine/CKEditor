/**
 * @version $Id: plugin.js 58 2011-02-18 12:40:41Z happy_noodle_boy $
 * @package JCE
 * @copyright Copyright (C) 2005 - 2011 Ryan Demmer. All rights reserved.
 * @author Ryan Demmer
 * @license GNU/GPL JCE is free software. This version may have been modified
 *          pursuant to the GNU General Public License, and as distributed it
 *          includes or is derivative of works licensed under the GNU General
 *          Public License or other free or open source software licenses.
 */
//if (typeof tinymce == 'undefined') document.location.href = 'index.php';

// String functions
(function ($) {
	var standalone = typeof tinyMCEPopup == 'undefined';

	$.Plugin = {

		i18n 		: {},
		language 	: '',

				options: {
						selectChange 	: $.noop,
						site 			: '',
						root			: '',
						help			: $.noop
				},

				getURI : function(absolute) {
					if (!standalone) {
						return tinyMCEPopup.editor.documentBaseURI.getURI(absolute);
					}

					return (absolute) ? this.options.root : this.options.site;
				},

				init: function (options) {
						var self = this;

					$.extend(this.options, options);

						// add button actions
						$('button#cancel').click(function(e) {
							if (!standalone) {
								tinyMCEPopup.close();
							}
							e.preventDefault();
						});

						// show body
						$('#jce').addClass('ui-widget-content').css('display', 'block');

						// activate tabs
						$('#tabs').tabs();

						// activate tooltips
						//$('.hastip, .tip, .tooltip').tips();

						// activate slider
						$('.slider').each(function() {
							var n = this;
							$('<span class="ui-slider-block"><span></span></span>').insertAfter(this).children('span').slider({
								min		: $(n).attr('min'),
								max		: $(n).attr('max'),
								step	: $(n).attr('step'),
								value 	: $(n).val() || 0,
								slide 	: function(event, ui) {
									$(n).val(ui.value);
								}
							});
						}).change(function() {
							$(this).next('span.ui-slider-block span.ui-slider').slider('value', $(this).val());
						});

						// create buttons
						$('button#insert, input#insert, button#update, input#update').button({
								icons: {
										primary: 'ui-icon-check'
								}
						});

						$('button#apply, input#apply').button({
								icons: {
										primary: 'ui-icon-plus'
								}
						});

						$('button#refresh, input#refresh').button({
								icons: {
										primary: 'ui-icon-refresh'
								}
						});

						$('button#cancel, input#cancel').button({
								icons: {
										primary: 'ui-icon-close'
								}
						});

						$('button#help, input#help').button({
								icons: {
										primary: 'ui-icon-help'
								}
						});

						// create colout picker widgets
						this.createColourPickers();
						// create browser widgets
						this.createBrowsers();

						// activate editable select lists
					/*	$('select.editable, select.mceEditableSelect').combobox({
								label	: self.translate('select_label', 'Add Value'),
								change	: this.options.change
						});
*/
						// set pattern value attribute actions
						$(':input[pattern]').change(function() {
							var pattern = $(this).attr('pattern'), v = $(this).val();
								if (!new RegExp('^(?:' + pattern + ')$').test(v)) {
									var n = new RegExp('(' + pattern + ')').exec(v);
									$(this).val(n[0]);
								}
						});
						// set max value attribute actions
						$(':input[max]').change( function() {
								var m = parseFloat($(this).attr('max')), v = parseFloat($(this).val());
								if (v > m) {
										$(this).val(m);
								}
						});
						// set min value attribute actions
						$(':input[min]').change( function() {
							var m = parseFloat($(this).attr('min')), v = parseFloat($(this).val());
								if (v < m) {
										$(this).val(m);
								}
						});
				},

				getPath: function (plugin) {
					if (!standalone) {
						return tinyMCEPopup.getParam('site_url') + 'components/com_jce/editor/tiny_mce/plugins/' + plugin;
					}

					return this.options.site + 'components/com_jce/editor/tiny_mce/plugins/' + plugin;
				},

				loadLanguage: function () {
					if (!standalone) {
							var ed = tinyMCEPopup.editor, u = ed.getParam('document_base_url') + 'components/com_jce/editor/tiny_mce';

							if (u && ed.settings.language && ed.settings.language_load !== false) {
						u += '/langs/' + ed.settings.language + '_dlg.js';

						if (!tinymce.ScriptLoader.isDone(u)) {
							document.write('<script type="text/javascript" src="' + tinymce._addVer(u) + '"></script>');
							tinymce.ScriptLoader.markDone(u);
						}
					}
					}
				},

				help: function (plugin, type) {
						type = type || 'standard';

						if (!standalone) {
							var ed = tinyMCEPopup.editor;

							ed.windowManager.open({
										url		: tinyMCEPopup.getParam('site_url') + 'index.php?option=com_jce&view=help&tmpl=component&lang=' + ed.settings.language + '&section=editor&category=' + plugin + '&type=' + type,
										width	: 780,
										height	: 560,
										resizable: 1,
										inline: 1,
										close_previous: 0
								});
						} else {
							this.options.help.call(this, plugin, type);
						}
				},

				setDimensions: function (wo, ho) {
						var w = $('#' + wo).val();
						var h = $('#' + ho).val();

						if (!w || !h) return;

						// Get tmp values
						var th = $('#tmp_' + ho).val();
						var tw = $('#tmp_' + wo).val();
						// tmp values must be set
						if (th && tw) {
								if (document.getElementById('constrain').checked) {
										var temp = (w / $('#tmp_' + wo).val()) * $('#tmp_' + ho).val();
										h = temp.toFixed(0);
										$('#' + ho).val(h);
								}
						}
						// set tmp values
						$('#tmp_' + ho).val(h);
						$('#tmp_' + wo).val(w);
				},

				setDefaults: function (s) {
					for (n in s) {
							v = s[n];

								if (v == 'default') {
									v = '';
								}

							if (n == 'border') {
										$('#border').attr('checked', parseFloat(v));
								} else {
										$('#' + n).val(v);
								}
						}
				},

				setClasses: function (v) {
						var c = $('#classes').val().split(' ');

						if (tinymce.inArray(c, v) == -1) {
								c.push(v);
						}

						$('#classes').val(tinymce.trim(c.join(' ')));
				},

				createColourPickers: function () {
					var self = this;
					$('input.color, input.colour').each(function () {
								var id = $(this).attr('id');

								var ev = $(this).get(0).onchange;

								$('<span class="pickcolor_icon" title="' + self.translate('browse') + '" id="' + id + '_pick"></span>').click(function (e) {
										if ($(this).hasClass('disabled'))
											return;

									return tinyMCEPopup.pickColor(e, id);
								}).insertAfter(this).css('background-color', $(this).val()).toggleClass('disabled', $(this).is(':disabled'));

								// need to use direct call to onchange event for tinyMCEPopup.pickColor callback
								$(this).get(0).onchange = function() {
									$(this).next('span.pickcolor_icon').css('background-color', $(this).val());
									if ($.isFunction(ev)) {
										ev.call(this);
									}
								};
						});
				},

				createBrowsers: function () {
						var self = this;
					$('input.browser').each(function () {
								var input = this, type = $(this).hasClass('image') ? 'image' : 'file';

								var ev = $(this).get(0).onchange;

								$('<span class="browser_icon" title="' + self.translate('browse') + '"></span>').click(function () {
										return TinyMCE_Utils.openBrowser(this, $(input).attr('id'), type, 'file_browser_callback');
								}).insertAfter(this);

								// need to use direct call to onchange event for tinyMCEPopup.pickColor callback
								$(this).get(0).onchange = function() {
									if ($.isFunction(ev)) {
										ev.call(this);
									}
								};
						});
				},

				getLanguage : function() {
					if (!this.language) {
						this.language = $('body').attr('lang') || 'en';
					}

					return this.language;
				},

				/**
		 * Adds a language pack, this gets called by the loaded language files like en.js.
		 *
		 * @method addI18n
		 * @param {String} p Prefix for the language items. For example en.myplugin
		 * @param {Object} o Name/Value collection with items to add to the language group.
		 * @source TinyMCE EditorManager.js
		 * @copyright Copyright 2009, Moxiecode Systems AB
		 * @licence GNU / LGPL 2 - http://www.gnu.org/copyleft/lesser.html
		 *
		 * Modified for JQuery
		 */
		addI18n : function(p, o) {
			var lo, i18n = this.i18n;

			if (!$.type(p) == 'string') {
				$.each(p, function(lc, o) {
					$.each(o, function(g, o) {
						$.each(o, function(k, o) {
							if (g === 'common')
								i18n[lc + '.' + k] = o;
							else
								i18n[lc + '.' + g + '.' + k] = o;
						});
					});
				});
			} else {
				$.each(o, function(k, o) {
					i18n[p + '.' + k] = o;
				});
			}
		},

				translate : function(s, ds) {
					if (!standalone) {
						return tinyMCEPopup.getLang('dlg.' + s, ds);
					}

					if (!$.isPlainObject(this.n))
						this.n = {};

					return this.i18n[this.getLanguage() + '.dlg.' + s] || ds;
				}
		};

	/**
	 * Cookie Functions
	 */
		$.Cookie = {
		/**
		 * Gets the raw data of a cookie by name.
		 *
		 * @method get
		 * @param {String} n Name of cookie to retrive.
		 * @return {String} Cookie data string.
		 * @copyright Copyright 2009, Moxiecode Systems AB
		 * @licence GNU / LGPL - http://www.gnu.org/copyleft/lesser.html
		 */
		get : function(n) {
			var c = document.cookie, e, p = n + "=", b;

			// Strict mode
			if (!c)
				return;

			b = c.indexOf("; " + p);

			if (b == -1) {
				b = c.indexOf(p);

				if (b != 0)
					return null;
			} else
				b += 2;

			e = c.indexOf(";", b);

			if (e == -1)
				e = c.length;

			return unescape(c.substring(b + p.length, e));
		},

		/**
		 * Sets a raw cookie string.
		 *
		 * @method set
		 * @param {String} n Name of the cookie.
		 * @param {String} v Raw cookie data.
		 * @param {Date} e Optional date object for the expiration of the cookie.
		 * @param {String} p Optional path to restrict the cookie to.
		 * @param {String} d Optional domain to restrict the cookie to.
		 * @param {String} s Is the cookie secure or not.
		 * @copyright Copyright 2009, Moxiecode Systems AB
		 * @licence GNU / LGPL - http://www.gnu.org/copyleft/lesser.html
		 */
		set : function(n, v, e, p, d, s) {
			document.cookie = n + "=" + escape(v) +
				((e) ? "; expires=" + e.toGMTString() : "") +
				((p) ? "; path=" + escape(p) : "") +
				((d) ? "; domain=" + d : "") +
				((s) ? "; secure" : "");
		}

		};

		/**
		 * JSON XHR
		 */
		$.JSON = {

				queue: function (o) {
						var _old = o.complete;

						o.complete = function () {
								if (_old) _old.apply(this, arguments);
						};

						$([$.JSON.queue]).queue("ajax", function () {
								window.setTimeout(function () {
										$.ajax(o);
								}, 500);

						});

						$.dequeue($.JSON.queue, "ajax");
				},

				/**
				 * Send JSON request
				 *
				 * @param func
				 *            Function name to execute by the server
				 * @param args
				 *            String, Array or Object containing arguments to
				 *            send
				 * @param callback
				 *            Callback function to execute
				 * @param scope
				 *            Scope to execute callback in
				 */
				request: function (func, data, callback, scope) {
						var self = this,
								json = {
										'fn': func
								};

						callback = callback || $.noop;

						// additonal POST data to add (will not be parsed by PHP json parser)
						var args = {};

						// get form input data (including token)
						var fields = $(':input', 'form').serializeArray();

						$.each(fields, function(i, field) {
							args[field.name] = field.value;
						});

						// if data is a string or array
						if ($.type(data) == 'string' || $.type(data) == 'array') {
								$.extend(json, {
										'args': data
								});
						} else {
								// if data is an object
								if (typeof data == 'object' && data.json) {
										$.extend(json, {
												'args': data.json
										});

										delete data.json;
								}

								$.extend(args, data);
						}

						$.JSON.queue({
								context: scope || this,
								dataType: 'json',
								type: 'POST',
								url: document.location.href,
								data: 'json=' + $.JSON.serialize(json) + '&' + $.param(args),
								success: function (o) {
										if (o.error) {
											$.Dialog.alert(o.text || o.error);
												return false;
										}

										r = o.result;

										if ($.isFunction(callback)) {
												callback.call(scope || this, r);
										} else {
												return r;
										}
								},

								error: function (e) {
										if (typeof e != 'undefined') {
												$.Dialog.alert(e);
										}
								}
						});
				},

				serialize: function (o) {
						var i, v, s = $.JSON.serialize,
								t;

						if (o == null) return 'null';

						t = typeof o;

						if (t == 'string') {
								v = '\bb\tt\nn\ff\rr\""\'\'\\\\';

								return '"' + o.replace(/([\u0080-\uFFFF\x00-\x1f\"])/g, function (a, b) {
										i = v.indexOf(b);

										if (i + 1) return '\\' + v.charAt(i + 1);

										a = b.charCodeAt().toString(16);

										return '\\u' + '0000'.substring(a.length) + a;
								}) + '"';

						}

						if (t == 'object') {
								if (o.hasOwnProperty && o instanceof Array) {
										for (i = 0, v = '['; i < o.length; i++)
										v += (i > 0 ? ',' : '') + s(o[i]);

										return v + ']';
								}

								v = '{';

								for (i in o)
								v += typeof o[i] != 'function' ? (v.length > 1 ? ',"' : '"') + i + '":' + s(o[i]) : '';

								return v + '}';
						}

						return '' + o;
				}

		},

		$.URL = {
			toAbsolute : function(url) {
				if (!standalone) {
					return tinyMCEPopup.editor.documentBaseURI.toAbsolute(url);
				}

				if (/http(s)?:\/\//.test(url)) {
					return url;
				}
				return $.Plugin.getURI(true) + url.substr(0, url.indexOf('/'));
			},

			toRelative : function(url) {
				if (!standalone) {
					return tinyMCEPopup.editor.documentBaseURI.toRelative(url);
				}

				if (/http(s)?:\/\//.test(url)) {
					return url.substr(url.indexOf('/'));
				}

				return url;
			}
		},

		/**
		 * Dialog Functions
		 */
		$.Dialog = {

			/**
			 * Basic Dialog
			 */
				dialog: function (title, data, options) {
						var div = document.createElement('div');

						$.extend(options, {
								minWidth: options.width,
								minHeight: options.height,
								modal: true,
								open: function () {
										if ($.isFunction(options.onOpen)) {
												options.onOpen.call();
										}
								},

								close: function () {
										$(this).dialog('destroy').remove();
								}

						});

						$(div).attr('title', title).append(data).dialog(options);

						return div;
				},

				/**
				 * Confirm Dialog
				 */
				confirm: function (s, cb, options) {
						var html = '<span>' + s + '</span>';

						options = $.extend(options, {
								resizable: false,
								height: 140,
								buttons: [{
										text: $.Plugin.translate('yes', 'Yes'),
										/*icons: {
												primary: 'ui-icon-check'
										},*/
										click: function () {
												cb.call(this, true);
												$(this).dialog("close");
										}

								}, {
										text: $.Plugin.translate('no', 'No'),
										/*icons: {
												primary: 'ui-icon-cancel'
										},*/
										click: function () {
												cb.call(this, false);
												$(this).dialog("close");
										}

								}]
						});

						return $.Dialog.dialog($.Plugin.translate('confirm', 'Confirm'), html, options);
				},

				/**
				 * Alert Dialog
				 */
				alert: function (s) {
						var html = '<span>' + s + '</span>';

						var options = {
								resizable: false,
								// height : 140,
								buttons: [{
										text: $.Plugin.translate('ok', 'OK'),
										click: function () {
												$(this).dialog("close");
										}

								}]
						};

						return $.Dialog.dialog($.Plugin.translate('alert', 'Alert'), html, options);
				},

				/**
				 * Prompt Dialog
				 */
				prompt: function (title, options) {
						var html = '<p>';

						var id = options.id || 'dialog-prompt',
								name = options.name || 'prompt',
								v = options.value || '';

						if (options.text) {
								html += '<label for="' + id + '">' + options.text + '</label>';
						}
						if (options.multiline) {
								html += '<textarea id="' + id + '" style="width:200px;height:75px;">' + v + '</textarea>';
						} else {
								html += '<input id="' + id + '" name="' + name + '" type="text" value="' + v + '" style="width:200px;" />';
						}

						html += '</p>';

						if (options.elements) {
								html += options.elements;
						}

						options = $.extend({
								resizable: false,
								width: 320,
								height: options.multiline ? 240 : 150,
								buttons: [{
										text: $.Plugin.translate('ok', 'Ok'),
										/*icons: {
												primary: 'ui-icon-check'
										},*/
										click: function () {
												if ($.isFunction(options.confirm)) {
														options.confirm.call(null, $('#' + id).val());
												}
												$(this).dialog("close");
										}

								}],
								onOpen: function () {
										$('#' + options.id).focus();
								}

						}, options);

						return $.Dialog.dialog(title, html, options);
				},

				/**
				 * Upload Dialog
				 */
				upload: function (options) {
						var div = document.createElement('div');

						$(div).attr('id', 'upload-body').append('<fieldset>' +
								'	<legend>' + $.Plugin.translate('browse', 'Browse') + '</legend>' +
								'	<input type="hidden" id="upload-dir" name="upload-dir" />' +
								'	<input type="file" name="file" size="40" style="position:relative;" />' +
								'</fieldset>' +
								'<fieldset>' +
								'	<legend>' + $.Plugin.translate('options', 'Options') + '</legend>' +
								'	<div id="upload-options">' +
								'		<label>' + $.Plugin.translate('upload_exists', 'If file exists: ') + '</label>' +
								'		<select id="upload-overwrite" name="upload-overwrite"></select>' +
								'	</div>' +
								'</fieldset>' +
								'<fieldset>' +
								'	<legend>' + $.Plugin.translate('queue', 'Queue') + '</legend>' +
								'	<div id="upload-options" style="position:absolute;"></div>' +
								'	<div id="upload-queue-block">' +
								'		<ul id="upload-queue"><li style="display:none;"></li></ul>' +
								'	</div>' +
								'</fieldset>');

						$(div).find('#upload-options').append(
						options.extended.body || '');

						$.extend(options, {
								width: 460,
								height: 380,
								buttons: [{
										text: $.Plugin.translate('upload', 'Upload'),
										/*icons: {
												primary: 'ui-icon-check'
										},*/
										click: function () {
												if ($.isFunction(options.upload)) {
														options.upload.call();
												}
										}

								}, {
										text: $.Plugin.translate('close', 'Close'),
										/*icons: {
												primary: 'ui-icon-check'
										},*/
										click: function () {
												$(this).dialog("close");
										}

								}]
						});

						return $.Dialog.dialog($.Plugin.translate('upload', 'Upload'), div, options);
				},

				/**
				 * IFrame Dialog
				 */
				iframe: function (name, url, options) {
						var div = document.createElement('div');

						$.extend(options, {
								width: $(window).width() - 100,
								height: $(window).height() - 50,
								onOpen: function () {
										var iframe = document.createElement('iframe');

										$(div).addClass('loading');

										$(iframe).attr({
												'src': url,
												'scrolling': 'auto',
												'frameborder': 0
										}).css({
												width: '99%',
												height: '95%'
										}).load(function () {
												if ($.isFunction(options.onFrameLoad)) {
														options.onFrameLoad.call();
												}

												$(div).removeClass('loading');
										});

										$(div).addClass('iframe-preview').append(iframe);

										$(div.parentNode).dialog("option", "position", 'center');
								}

						});

						return $.Dialog.dialog($.Plugin.translate('preview', 'Preview') + ' - ' + name, div, options);
				},

				/**
				 * Media Dialog
				 */
				media: function (name, url, options) {
					var self = this;
					options = options || {};

					/*
			 * Calculate dimensions to fit image to window
			*/

						function _calculateDimensions(w, h) {
								var x = Math.round($(window).width()) - 160;
								var y = Math.round($(window).height()) - 190;

								w = parseFloat(w), h = parseFloat(h);

								if (w > x) {
										h = h * (x / w);
										w = x;
										if (h > y) {
												w = w * (y / h);
												h = y;
										}
								} else if (h > y) {
										w = w * (y / h);
										h = y;
										if (w > x) {
												h = h * (x / w);
												w = x;
										}
								}

								return {
										width: Math.round(w),
										height: Math.round(h)
								};
						}

						var div = document.createElement('div');

						var ww = $(window).width(), wh = $(window).height();

						$.extend(options, {
								width		: ww - Math.round(ww / 100 * 10),
								height		: wh - Math.round(wh / 100 * 10),
								resizable	: false,
								close		: function() {
								$(div).innerHTML = '';
								$(this).dialog('destroy').remove();
							},
								onOpen		: function() {
										// image
										if (/\.(jpg|jpeg|gif|png)/i.test(url)) {
												$(div).addClass('image-preview loader');

												var img = new Image(), loaded = false;

												var dw = $('.ui-dialog-content').width(), dh = $('.ui-dialog-content').height();

												img.onload = function () {
														if (loaded) return false;

														if (img.width > dw || img.height > dh) {
																var dim = _calculateDimensions(img.width, img.height);

																$('div.image-preview').removeClass('loader').append('<img src="' + url + '" width="' + dim.width + '" height="' + dim.height + '" alt="' + $.Plugin.translate('preview', 'Preview') + '" />');
																$('div.image-preview').css('margin-top', ($('.ui-dialog-content').height() - dim.height) / 2);
														} else {
																$('div.image-preview').removeClass('loader').addClass('background').css({
																		'background-image': 'url(' + url + ')'
																});
														}

														$('.ui-dialog-content').click(function () {
																$(div.parentNode).dialog('close');
														});

														$(div.parentNode).dialog("option", "position", 'center');

														loaded = true;
												};

												img.src = url;
												// media element
										} else {
												$(div).addClass('media-preview loader').height($('.ui-dialog-content').height() - 20);

												var mediaTypes = {
														// Type, clsid, mime types,
														// codebase
														"flash": {
																classid: "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000",
																type: "application/x-shockwave-flash",
																codebase: "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"
														},

														"shockwave": {
																classid: "clsid:166b1bca-3f9c-11cf-8075-444553540000",
																type: "application/x-director",
																codebase: "http://download.macromedia.com/pub/shockwave/cabs/director/sw.cab#version=8,5,1,0"
														},

														"windowsmedia": {
																classid: "clsid:6bf52a52-394a-11d3-b153-00c04f79faa6",
																type: "application/x-mplayer2",
																codebase: "http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"
														},

														"quicktime": {
																classid: "clsid:02bf25d5-8c17-4b23-bc80-d3488abddc6b",
																type: "video/quicktime",
																codebase: "http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0"
														},

														"divx": {
																classid: "clsid:67dabfbf-d0ab-41fa-9c46-cc0f21721616",
																type: "video/divx",
																codebase: "http://go.divx.com/plugin/DivXBrowserPlugin.cab"
														},

														"realmedia": {
																classid: "clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa",
																type: "audio/x-pn-realaudio-plugin"
														},

														"java": {
																classid: "clsid:8ad9c840-044e-11d1-b3e9-00805f499d93",
																type: "application/x-java-applet",
																codebase: "http://java.sun.com/products/plugin/autodl/jinstall-1_5_0-windows-i586.cab#Version=1,5,0,0"
														},

														"silverlight": {
																classid: "clsid:dfeaf541-f3e1-4c24-acac-99c30715084a",
																type: "application/x-silverlight-2"
														},

														"video": {
																type: 'video/mp4'
														}
												};

												var mimes = {};

												// Parses the default mime types
												// string into a mimes lookup
												// map
												(function (data) {
														var items = data.split(/,/),
																i, y, ext;

														for (i = 0; i < items.length; i += 2) {
																ext = items[i + 1].split(/ /);

																for (y = 0; y < ext.length; y++)
																mimes[ext[y]] = items[i];
														}
												})("application/x-director,dcr," + "application/x-mplayer2,wmv wma avi," + "video/divx,divx," + "application/pdf,pdf," + "application/x-shockwave-flash,swf swfl," + "audio/mpeg,mpga mpega mp2 mp3," + "audio/ogg,ogg spx oga," + "audio/x-wav,wav," + "video/mpeg,mpeg mpg mpe," + "video/mp4,mp4 m4v," + "video/ogg,ogg ogv," + "video/webm,webm," + "video/quicktime,qt mov," + "video/x-flv,flv," + "video/vnd.rn-realvideo,rv," + "video/3gpp,3gp," + "video/x-matroska,mkv");

												var ext = $.String.getExt(url);
												var mt = mimes[ext];
												var type, props;

												$.each(
												mediaTypes, function (k, v) {
														if (v.type && v.type == mt) {
																type = k;
																props = v;
														}
												});

												// video types
												if (/^(mp4|m4v|og(g|v)|webm)$/i.test(ext)) {
														type = 'video';
														props = {
																type: mt
														};
												}

												// audio types
												if (/^(mp3|oga)$/i.test(ext)) {
														type = 'audio';
														props = {
																type: mt
														};
												}

												// flv
												if (/^(flv|f4v)$/i.test(ext)) {
														type = 'flv';
														props = {};
												}

												var swf = $.Plugin.getURI(true) + 'components/com_jce/editor/libraries/swf/mediaplayer.swf';

												if (type && props) {
														switch (type) {
														case 'audio':
														case 'video':
																if (type == 'video') {
																		$(div).append('<video autoplay="autoplay" controls="controls" type="' + props.type + '" style="width:100%;height:100%;" src="' + url + '"></video>');
																} else {
																		$(div).append('<audio autoplay="autoplay" controls="controls" src="' + url + '"></audio>');
																}
																var fb, ns = '<p style="margin-left:auto;">' + $.Plugin.translate('media_not_supported', 'Media type not supported by this browser') + '</p>';

																// add fallback
																if ((!$.browser.webkit || /Chrome/.test(navigator.appName)) && /(mp4|mp3|m4v)$/i.test(ext)) {
																		fb = true;
																}

																if (!/chrome/.test(navigator.userAgent) && /^(webm)$/i.test(ext)) {
																		$(div).html(ns).removeClass('loader');
																}

																if (!$.browser.mozilla && /^(og(g|v))$/i.test(ext)) {
																		$(div).html(ns).removeClass('loader');
																}

																// flowplayer fallback
																if (fb) {
																		url = $.URL.toAbsolute(url);

																		$(div).html('<object type="application/x-shockwave-flash" data="' + swf + '">' + '<param name="movie" value="' + swf + '" />' + '<param name="flashvars" value="src=' + url + '&autoPlay=true&controlBarAutoHide=false&playButtonOverlay=false" />' + '</object>');

																		if (ext == 'mp3') {
																				$('object', div).addClass('audio');
																		}
																}

																break;
														case 'flv':
																url = $.URL.toAbsolute(url);

																$(div).append('<object type="application/x-shockwave-flash" data="' + swf + '">' + '<param name="movie" value="' + swf + '" />' + '<param name="flashvars" value="src=' + url + '&autoPlay=true&controlBarAutoHide=false" />' + '</object>');
																break;
														case 'flash':
																$(div).append('<object type="' + props.type + '" data="' + url + '" style="width:100%;height:100%;"><param name="movie" value="' + url + '" /></object>');
																break;
														default:
																$(div).append('<object classid="' + props.classid + '" style="width:100%;height:100%;">' + '<param name="src" value="' + url + '" />' + '<embed src="' + url + '" style="width:100%;height:100%;" type="' + props.type + '"></embed>' + '</object>');
																break;
														}
														$(div).removeClass('loader');
												}
										}
								}

						});

						return $.Dialog.dialog($.Plugin.translate('preview', 'Preview') + ' - ' + name, div, options);
				}
		};

		/**
		 * String functions
		 */
		$.String = {

				basename: function (s) {
						s = s.replace(/\\/g, '/');
						return s.substring(s.length, s.lastIndexOf('/') + 1);
				},

				dirname: function (s) {
						return s.substring(0, s.lastIndexOf('/'));
				},

				filename: function (s) {
						return this.stripExt(this.basename(s));
				},

				getExt: function (s) {
						return s.substring(s.length, s.lastIndexOf('.') + 1).toLowerCase();
				},

				stripExt: function (s) {
						return s.replace(/\.[^.]+$/i, '');
				},

				pathinfo: function (s) {
						var info = {
								'basename': this.basename(s),
								'dirname': this.dirname(s),
								'extension': this.getExt(s),
								'filename': this.filename(s)
						};
						return info;
				},

				path: function (a, b) {
						a = this.clean(a);
						b = this.clean(b);

						if (a.substring(a.length - 1) != '/') a += '/';

						if (b.charAt(0) == '/') b = b.substring(1);

						return a + b;
				},

				clean: function (s) {
						if (!/:\/\//.test(s)) {
								return s.replace(/\/+/g, '/');
						}
						return s;
				},

				safe: function (s) {
						s = s.replace(/(\.){2,}/g, '').replace(/[^a-z0-9\.\_\-\s~]/gi, '').replace(/\s/gi, '_');
						return this.basename(s);
				},

				query: function (s) {
						var p = {};

						s = this.decode(s);

						if (/\?/.test(s)) {
							s = s.substring(s.indexOf('?') + 1);
						}
						$.each(s.replace(/&amp;/g, '&').split(/&/g), function() {
							var pair = this.split('=');
							p[pair[0]] = pair[1];
						});

						return p;
				},
				/**
				 * Encode basic entities
				 *
				 * Copyright 2010, Moxiecode Systems AB
				 */
				encode: function (s) {
						var baseEntities = {
						'"' : '&quot;',
						"'" : '&#39;',
						'<' : '&lt;',
						'>' : '&gt;',
						'&' : '&amp;'
					};
						return ('' + s).replace(/[<>&\"\']/g, function(chr) {
				return baseEntities[chr] || chr;
			});
				},
				/**
				 * Decode basic entities
				 *
				 * Copyright 2010, Moxiecode Systems AB
				 */
				decode: function (s) {
					var reverseEntities = {
					'&lt;' : '<',
					'&gt;' : '>',
					'&amp;' : '&',
					'&quot;' : '"',
					'&apos;' : "'"
				};
					return s.replace(/&(#)?([\w]+);/g, function(all, numeric, value) {
				if (numeric)
					return String.fromCharCode(value);

				return reverseEntities[all];
			});
				},

				escape: function (s) {
						return encodeURI(s);
				},

				unescape: function (s) {
						return decodeURI(s);
				},

				/*
		 * From TinyMCE form_utils.js function, slightly modified. @author
		 * Moxiecode @copyright Copyright � 2004-2008, Moxiecode Systems AB,
		 * All rights reserved.
		 */
				toHex: function (color) {
						var re = new RegExp("rgb\\s*\\(\\s*([0-9]+).*,\\s*([0-9]+).*,\\s*([0-9]+).*\\)", "gi");

						var rgb = color.replace(re, "$1,$2,$3").split(',');
						if (rgb.length == 3) {
								r = parseInt(rgb[0]).toString(16);
								g = parseInt(rgb[1]).toString(16);
								b = parseInt(rgb[2]).toString(16);

								r = r.length == 1 ? '0' + r : r;
								g = g.length == 1 ? '0' + g : g;
								b = b.length == 1 ? '0' + b : b;

								return "#" + r + g + b;
						}
						return color;
				},

				/*
		 * From TinyMCE form_utils.js function, slightly modified. @author
		 * Moxiecode @copyright Copyright � 2004-2008, Moxiecode Systems AB,
		 * All rights reserved.
		 */
				toRGB: function (color) {
						if (color.indexOf('#') != -1) {
								color = color.replace(new RegExp('[^0-9A-F]', 'gi'), '');

								r = parseInt(color.substring(0, 2), 16);
								g = parseInt(color.substring(2, 4), 16);
								b = parseInt(color.substring(4, 6), 16);

								return "rgb(" + r + "," + g + "," + b + ")";
						}
						return color;
				},

				ucfirst: function (s) {
						return s.substring(0, 1).toUpperCase() + s.substring(1);
				}

		};
		// load Language
		$.Plugin.loadLanguage();
})(jQuery);