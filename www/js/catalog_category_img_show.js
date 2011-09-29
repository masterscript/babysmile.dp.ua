$(document).ready(function(){
        $('a.catalog_category').hover(
            function() {
                id = $(this).attr('id');
                num = id.replace(/img_(\d+)/gi, '$1');
                $('#src_img').attr('src', '/images/img_cat_eng/'+num+'.jpg');
            },
            function() { 
		 $('#src_img').attr('src', '/images/main_categories_image.jpg'); 
            }
        );    
});

