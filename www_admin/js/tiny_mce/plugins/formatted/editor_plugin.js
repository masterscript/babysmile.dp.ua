/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * @author Andrey Garbuz
 * @copyright Copyright  2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
		 
	function insHeaderIE (ed,tag) {
		var range = ed.selection.getRng();
		if (range.boundingWidth == null) return;
		var parent = range.parentElement();
		var match = 0;

		while (parent.tagName != "HTML") {
			if (parent.tagName == tag) {
				parent.outerHTML = parent.innerHTML;
				match = 1;
				break;
			}
			parent = parent.parentElement;
		}
		if (match == 0) {
			if(range.boundingWidth != 0) {
				if(tag == "H3" || tag == "H2") {
					var text = '<'+tag+' >'+range.text+'</'+tag+'>';
					var parent = range.parentElement();
					while(parent.tagName != "HTML") {
						if(parent.tagName == "H3" || parent.tagName == "H2") {
							parent.outerHTML = text;
							match = 1;
							break;
						}
						parent = parent.parentElement;
					} //while
				if (match == 0) range.pasteHTML(text);
				} else {
					var text = '<'+tag+' >'+range.htmlText+'</'+tag+'>';
					var parent = range.parentElement();
					while(parent.tagName != "HTML") {
						if(parent.tagName == "H3" || parent.tagName == "H2") {
							return;
						}
						parent = parent.parentElement;
					} //while
					range.execCommand("RemoveFormat");
					range.pasteHTML(text);
				}
			}
		} //if
	}
	
	function insHeader (ed,h) {
		
		var text = ed.selection.getContent({format: 'raw'});
		var range = ed.selection.getRng();
		if (range.commonAncestorContainer) {
			if (range.commonAncestorContainer.tagName==h) {
				ed.dom.setOuterHTML(range.commonAncestorContainer,'<p>'+range.commonAncestorContainer.innerHTML+'</p>');
				//ed.execCommand('mceInsertContent', false, '<p>'+text+'</p>');
				return;
			}
			var parent = range.commonAncestorContainer.parentNode;
			parentTag = parent.nodeName;
			while (parentTag!='BODY') {
				if (parentTag==h) {
					//ed.execCommand('FormatBlock',false,'P');
					//ed.execCommand('mceInsertContent', false, '<p>'+text+'</p>');
					ed.dom.setOuterHTML(parent,'<p>'+parent.innerHTML+'</p>');
					return;
				}
				parent = parent.parentNode;
				parentTag = parent.nodeName;
			}
		}
		ed.execCommand('mceInsertContent', false, '<'+h+'>'+text+'</'+h+'>');
		
	}
	
	function setCellStyle (ed) {
		
		//  Range
		var range = ed.selection.getRng();
		//   
		var ancestor = range.commonAncestorContainer;
		if (ancestor) {
			//       
			if (ancestor.tagName=='TD') {
				if (ed.dom.getAttrib(ancestor, 'class')=='table-header') {
					ed.dom.removeClass(ancestor,'table-header');
				} else {
					ed.dom.setAttrib(ancestor, 'class', 'table-header');
				}
				return;
			}
			//         
			var td = ancestor.parentNode;
			parentTag = td.nodeName;
			while (parentTag!='BODY') {
				if (parentTag=='TD') {
					if (ed.dom.getAttrib(td, 'class')=='table-header') {
						ed.dom.removeClass(td,'table-header');
					} else {
						ed.dom.setAttrib(td, 'class', 'table-header');
					}
					return;
				}
				td = td.parentNode;
				parentTag = td.nodeName;
			}
		}
	
	}
	
	function findTag (ed,tag) {
		
		// range object
		var range = ed.selection.getRng();
		if (tinymce.isIE) {
			if (ed.selection.getNode().nodeName=='IMG') {
				return false;
			}
			var parent = range.parentElement();
			while (parent.tagName!='BODY')  {
				if (parent.tagName==tag) {
					return parent;
				}
				parent=parent.parentElement;
			} // end while
		} else {
			// common ancestor
			var ancestor = range.commonAncestorContainer;
			if (ancestor) {
				if (ancestor.tagName==tag) {
					return ancestor;
				}
				if (ancestor.tagName=='BODY') {
					return false;
				}
				// going up to tree
				var parent = ancestor.parentNode;
				parentTag = parent.nodeName;
				while (parentTag!='BODY') {
					if (parentTag==tag) {
						return parent;
					}
					parent = parent.parentNode;
					parentTag = parent.nodeName;
				} // end while
			} // end if
		} // end else
		
		return false;
	}
	
	function setHeadCellIE(ed) {
		
     	var td=findTDIE(ed);
     	if (td.className=='table-header') {
			td.className='';
		} else {
			td.className='table-header';
		}
		
	}


	function findTDIE(ed) {
		
		var td;
		var range = ed.selection.getRng();
		td = range.parentElement();
		while (td.tagName!='TD')  {
			if (td.tagName=='BODY') {
				return false;
			}
			td=td.parentElement;
     	}
     	return td;
		
	}

	
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('formatted');

	tinymce.create('tinymce.plugins.FormattedPlugin', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			
			// H2 header
			ed.addCommand('mceH2', function() {
											
				if (tinymce.isIE) {
					insHeaderIE(ed,'H2');
				} else {
					insHeader(ed,'H2');
				}
						
			});
			
			// H3 header
			ed.addCommand('mceH3', function() {
				if (tinymce.isIE) {
					insHeaderIE(ed,'H3');
				} else {
					insHeader(ed,'H3');
				}
			});
			
			// Warning style
			ed.addCommand('mceWarning', function() {
				ed.execCommand('mceSetCSSClass',false,'warning');
			});
			
			// Notice style
			ed.addCommand('mceNotice', function() {
				ed.execCommand('mceSetCSSClass',false,'notice');
			});
			
			// Table cell style
			ed.addCommand('mceCellStyle', function() {
				if (tinymce.isIE) {
					setHeadCellIE(ed);
				} else {
					setCellStyle(ed);
				}
			});

			// Register buttons
			ed.addButton('h2', {
				title : 'formatted.h2',
				cmd : 'mceH2',
				image : url + '/img/h2.gif'
			});
			
			ed.addButton('h3', {
				title : 'formatted.h3',
				cmd : 'mceH3',
				image : url + '/img/h3.gif'
			});
			
			ed.addButton('warning', {
				title : 'formatted.warning',
				cmd : 'mceWarning',
				image : url + '/img/warning.gif'
			});
			
			ed.addButton('notice', {
				title : 'formatted.notice',
				cmd : 'mceNotice',
				image : url + '/img/notice.gif'
			});
			
			ed.addButton('cellstyle', {
				title : 'formatted.cellstyle',
				cmd : 'mceCellStyle',
				image : url + '/img/cellcolor.gif'
			});

			// Add a node change handler
			ed.onNodeChange.add(function(ed, cm, n) {
				isTable = findTag(ed,'TABLE');
				cm.setActive('h2', n.nodeName == 'H2');
				cm.setActive('h3', n.nodeName == 'H3');
				cm.setActive('warning', n.className == 'warning');
				cm.setActive('notice', n.className == 'notice');
				cm.setActive('cellstyle', n.className == 'table-header');
				cm.setDisabled('cellstyle', isTable==false);
				//cm.setState('hide-button',true);
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Formatted buttons plugin',
				author : 'Andrey Garbuz',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('formatted', tinymce.plugins.FormattedPlugin);
})();