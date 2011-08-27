tinyMCEPopup.requireLangPack();

var CsvUploadDialog = {
	init : function() {
		$(document).ready (function () {			
									 		
			$("#upl_file").livequery("change", function () {
				$("#loading").ajaxStart (function () {
					$("#upl_file").attr("value","");
					$(this).show();
				}).ajaxComplete (function () {
					$(this).hide();
				});
	
				$.ajaxFileUpload ({
					url:'/ajax/tinymce_csv_upl',		
					secureuri:false,
					fileElementId:'upl_file',
					dataType: 'text',
					success: function (data, status) {
						tinyMCEPopup.editor.execCommand('mceInsertContent', false, data);
						tinyMCEPopup.editor.addVisual()
						tinyMCEPopup.close();
					},
					error: function (data, status, e) {
						alert(e);
					}
				});
			});
			
		}); // end domready
		
	},

	insert : function() {
		// Insert image into the document
		//tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img src="'+$("#img_src").attr("value")+'" />');
	}
};

tinyMCEPopup.onInit.add(CsvUploadDialog.init, CsvUploadDialog);
