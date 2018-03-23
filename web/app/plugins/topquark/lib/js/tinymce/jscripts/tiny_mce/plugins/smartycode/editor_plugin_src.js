/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.SmartyCodePlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('tqpSmartyCode', function() {
				ed.windowManager.open({
					file : url + '/smartycode.php',
					width : 600 + parseInt(ed.getLang('emotions.delta_width', 0)),
					height : 500 + parseInt(ed.getLang('emotions.delta_height', 0)),
					scrollbars : true,
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('smartycode', {title : 'smartycode.smartycode_desc', cmd : 'tqpSmartyCode', image : url + '/images/smartycode.gif'});
		},

		getInfo : function() {
			return {
				longname : 'SmartyCode',
				author : 'Top Quark Productions',
				authorurl : 'http://www.topquarkproductions.ca',
				infourl : 'http://www.topquarkproductions.ca',
				version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('smartycode', tinymce.plugins.SmartyCodePlugin);
})();
