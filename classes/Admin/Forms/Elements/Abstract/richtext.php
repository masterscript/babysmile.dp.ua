<?php

/**
 * HTML Quickform element for tinyMCE
 *
 */
abstract class Admin_Forms_Elements_Abstract_richtext extends HTML_QuickForm_textarea {
    
    /**
     * The base URL for the JavaScript files
     *
     */
    const BASEPATH = '/js/';
        
    /**
     * Configuration settings
     *
     * @var array
     */
    private $config = array(
		'debug' => false,
        'mode'  => 'exact',
        'theme' => 'advanced',
        'language' => 'ru',
        'verify_html' => false,
	    'convert_urls' => false,
        'document_base_url' => '/',
        'relative_urls' => false,
        'remove_script_host' => false,
	    'width' => '640',
	    'height' => '480',
        'skin' => 'o2k7',
        'plugins' => 'inlinepopups,mytable,contextmenu,imgcontent,myadvimage,insertdatetime,paste,preview,searchreplace,formatted,csvupload',
        'content_css' => '/styles/tinymce.css',
        'theme_advanced_buttons1' => 'bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,sub,sup,|,bullist,numlist,|,hr,charmap',
	    'theme_advanced_buttons2' => 'code,|,undo,redo,|,outdent,indent,|,link,unlink,anchor,|,removeformat,|,imgcontent,myimage,|,insertdate,inserttime,|,pastetext,pasteword,selectall,|,preview,|,search,|,h2,h3,warning,notice',
	    'theme_advanced_buttons3' => 'table,delete_col,delete_row,col_after,col_before,row_after,row_before,row_after,row_before,split_cells,merge_cells ,|,cellstyle,|,csvupload'
    );
    
    /**
     * Class constructor
     *
     */
    public function __construct ($elementName = null,$elementLabel = null,$attributes = null,$options = array()) {
        
        $this->updateAttributes(array('type' => 'text'));
        $this->HTML_QuickForm_textarea($elementName, $elementLabel, $attributes);
        $this->_type = 'richtext';

        if (is_array($options)) {
            $this->setConfig($options);
        }
        
    }

    /**
     * Sets configuration
     *
     * @param array $options
     */
    public function setConfig ($options) {
        
        $this->config = array_merge($this->config,$options);
        
    }

    /**
     * Returns the richtext in HTML
     *
     * @return string
     */
    public function toHtml() {
        
        // load needle javascript files
        $html = '<script type="text/javascript" src="'.self::BASEPATH.'tiny_mce/tiny_mce.js"></script>'
                .PHP_EOL;
        
        // load jQuery library
        if (!defined('JQUERY_LIBRARY_LOADED')) {
        $html .= '<script type="text/javascript" src="'.self::BASEPATH.'jquery.js"></script>'
                 .PHP_EOL; 
            define('JQUERY_LIBRARY_LOADED',true);
        }
        
        // load configuration
        $html .= '<script type="text/javascript">'
        		 .PHP_EOL.
        			'tinyMCE.init({'.PHP_EOL;
        $config = array();
        foreach ($this->config as $param=>$value) {
        	//$config[] = $param.' : "'.$value.'"';
			$config[] = $param.' : '.var_export($value,true);
        }
        $html .= implode(','.PHP_EOL,$config);
        $html .= PHP_EOL.'});'.PHP_EOL.
        		'</script>'.PHP_EOL;
        
        // create hidden field
        $hidden = new HTML_QuickForm_hidden('param');
        $hidden->setValue('content_id='.Admin_Core::getItemId());
        $hidden->updateAttributes(array('id'=>'param'));
        
        return $html.$hidden->toHtml().parent::toHtml();

    }        
	
}

?>