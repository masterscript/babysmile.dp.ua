/**
 * $Id: editor_plugin_src.js 520 2008-01-07 16:30:32Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright  2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	tinymce.create('tinymce.plugins.MyAdvancedImagePlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceMyAdvImage', function() {
				var e = ed.selection.getNode();

				// Internal image object like a flash placeholder
				if (ed.dom.getAttrib(e, 'class').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : url + '/image.htm',
					width : 490 + parseInt(ed.getLang('advimage.delta_width', 0)),
					height : 395 + parseInt(ed.getLang('advimage.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('myimage', {
				title : 'advimage.image_desc',
				cmd : 'mceMyAdvImage',
				image : url + '/img/settings.gif'
			});
			
			ed.onNodeChange.add(function(ed, cm, n) {
				//cm.setActive('myimage', n.nodeName == 'IMG');
				cm.setDisabled('myimage', n.nodeName != 'IMG');
			});
			
		},

		getInfo : function() {
			return {
				longname : 'Advanced image with low functionality',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/myadvimage',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('myadvimage', tinymce.plugins.MyAdvancedImagePlugin);
})();