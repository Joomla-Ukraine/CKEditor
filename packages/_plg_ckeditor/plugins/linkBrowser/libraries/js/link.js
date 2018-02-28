(function () {
	var LinkDialog = {
		settings: {},
		init: function () {

			var self = this, n, el, action = 'insert';

			// Create Browser Tree
			this.createTree();
			$.Plugin.init();

		},

		insertLink: function (v) {
			if (window.parent.linkBrowserUrl == 'relative') {
				link = v;
			}
			else {
				v = v.replace('index.php', '');
				link = (document.location.protocol + '//' + document.location.hostname + document.location.pathname).replace('administrator/index.php', '') + v;
			}
			window.parent.CKEDITOR.tools.callFunction(window.parent.FuncDialogNr, link);
		},

		createTree: function () {
			// Tree
			jQuery('#link-options').tree({

				collapseTree: true,
				charLength: 50,
				onInit: function (e, callback) {
					if ($.isFunction(callback)) {
						callback.apply();
					}
				},

				// When a node is clicked
				onNodeClick: function (e, node) {
					var v, link = $(node).find('a');

					if (!$('span.nolink', $(node)).length) {
						v = $('a', node).attr('href');

						if (v == 'javascript:;')
							v = $(node).attr('id');

						LinkDialog.insertLink($.String.decode(v));
					}

					if ($('span.folder', $(node)).length) {
						$(this).tree('toggleNode', e, node);
					}

					e.preventDefault();
				},

				// When a node is toggled and loaded
				onNodeLoad: function (e, node) {
					var self = this;

					$(this).tree('toggleLoader', node);

					var query = $.String.query($.String.unescape($(node).attr('id')));

					$.JSON.request('getLinks', {'json': query}, function (o) {
						if (o) {
							if (!o.error) {
								var ul = $('ul:first', node);

								if (ul) {
									$(ul).remove();
								}

								$(self).tree('createNode', o.folders, node);
								$(self).tree('toggleNodeState', node, true);
							} else {
								$.Dialog.alert(o.error);
							}
						}
						$(self).tree('toggleLoader', node);
					}, self);
				}
			});
		}
	};
	LinkDialog.init();
})();