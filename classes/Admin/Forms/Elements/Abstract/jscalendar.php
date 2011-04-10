<?php

/**
 * HTML Quickform element for jsCalendar
 *
 * jsCalendar is a dynamic JavaScript/HTML calendar which can be obtained from
 * http://www.dynarch.com/projects/calendar/
 *
 * The jsCalendar library itself was written by Mihai Bazon
 * (see the website above for licensing terms, and support)
 *
 * @author       Philippe Jausions <Philippe.Jausions@11abacus.com>
 * @copyright    2006-2007 11abacus
 * @license      New BSD License http://www.opensource.org/licenses/bsd-license.html
 * @link         http://www.dynarch.com/projects/calendar/
 * @link         http://pear.11abacus.com/package/HTML_QuickForm_jscalendar
 * @package      HTML_QuickForm_jscalendar
 * @category     HTML
 */
abstract class Admin_Forms_Elements_Abstract_jscalendar extends HTML_QuickForm_text {
    
    /**
     * The base URL for the jsCalendar JavaScript files
     *
     * The URL must ends with a trailing slash (i.e. "/javascript/cal/")
     *
     */
    const BASEPATH = '/js/calendar/';
    
    /**
     * Default language
     *
     * 2-letter language code supported by jsCalendar
     *
     */
    const LANG = 'ru';
    
	/**
     * The URL where to find the editor
     *
     * Use forward slashes / only.
     * The URL must end with a trailing slash /
     *
     * @var string
     */
    public $basePath;

    /**
     * Language for the calendar
     *
     * 2-letter language code supported by jsCalendar
     *
     * @var string
     */
    public $lang;

    /**
     * Calendar theme
     *
     * Setting this variable to a theme will generate the output of a <link>
     * tag, however, since <link> tags are not supposed to be in the <body>
     * of an HTML document, it is not recommended to do so.
     *
     * <code>
     * $calendar =& $form->addElement('jscalendar', 'date', 'Date');
     * $calendar->theme = 'calendar-win2k-1';
     * </code>
     *
     * @var string
     */
    public $theme = 'calendar-win2k';

    /**
     * Whether to use the jsCalendar version stripped of its comment
     *
     * @var boolean
     */
    public $stripped = true;
    
    /**
     * Use safe attribute names for input field
     *
     * @var unknown_type
     */
    public $safe_name = false;

    /**
     * Configuration settings for the calendar
     *
     * If you set the "daFormat", the field will be hidden instead of directly
     * editable. If JavaScript is disabled on the web browser, a regular text
     * field is displayed.
     *
     * @var array
     */
    private $config = array(
        'ifFormat'  => '%Y-%m-%d %H:%M:%S',
        'showsTime'  => 'true',
        'callbacks' => array()
        );
    
    /**
     * HTML code of trigger element
     *
     * @var string
     */
    public $trigger_html = '';

    /**
     * To avoid any namespace collision in JavaScript
     *
     * @var string
     */
    public $jsPrefix = '';

    /**
     * Value(s)
     *
     * @var mixed
     */
    private $values = null;

    /**
     * Class constructor
     *
     * @param   string  jsCalendar instance name
     * @param   string  jsCalendar instance label
     * @param   array   Config settings for jsCalendar
     *                  (see "Calendar.setup in details" in "DHTML Calendar
     *                   Widget" online documentation)
     * @param   string  Attributes for the text input form field
     */
    public function __construct ($elementName = null,$elementLabel = null,$attributes = null,$options = array()) {
        
        $this->updateAttributes(array('type' => 'text'));
        $this->HTML_QuickForm_text($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'jscalendar';

        if (is_array($options)) {
            $this->setConfig($options);
        }
        
    }

    /**
     * Sets configuration for jsCalendar
     *
     * @param mixed an associated array of configuration value
     *              or the name of key of config setting
     * @param mixed Value of config setting (only used when $key is a scalar)
     * @param boolean $callback whether the other parameters should be
     *                          considered as settings for callbacks.
     */
    public function setConfig ($key,$value = null,$callback = false) {
        
        if ($callback) {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    $this->config['callbacks'][$k] = $v;
                }
            } else {
                $this->config['callbacks'][$key] = $value;
            }
        } else {
            if (is_array($key)) {
                // Catch the "button" special option
                $button = array_search('button', $key);
                if ($button !== false && is_numeric($button)) {
                    unset($key[$button]);
                    $key['button'] = '';
                }

                // Catch the "multiple" special option
                $multiple = array_search('multiple', $key);
                if ($multiple !== false && is_numeric($multiple)) {
                    unset($key[$multiple]);
                    $key['multiple'] = true;
                }
                if (array_key_exists('multiple', $key)) {
                    $this->setMultiple($key['multiple']);
                    unset($key['multiple']);
                }

                $this->config = array_merge($this->config, $key);

            } elseif ($key == 'multiple') {
                if (is_null($value)) {
                    $value = true;
                }
                $this->setMultiple($value);

            } else {
                $this->config[$key] = $value;
            }
        }
    }

    /**
     * Sets the "multiple" attribute for the calendar
     *
     * @param boolean $multiple Whether the calendar supports multi-selections
     *
     * @return void
     * @see getMultiple();
     */
    public function setMultiple ($multiple = true) {
        
        if ($multiple) {
            $this->updateAttributes(array('multiple' => true));
        } else {
            $this->removeAttribute('multiple');
        }
        
    }

    /**
     * Returns the "multiple" attribute for the calendar
     *
     * @return boolean Returns TRUE if the calendar supports multi-selections,
     *                 FALSE otherwise
     * @see setMultiple();
     */
    public function getMultiple () {
        
        return (boolean)$this->getAttribute('multiple');
        
    }

    /**
     * Returns the jsCalendar in HTML
     *
     * @return string
     */
    public function toHtml() {
        
        $html = '';
        
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }
        $name     = $this->getAttribute('name');
        if ($this->safe_name) {
            $safeName = preg_replace('/[^a-zA-Z0-9-_]/', '___',$this->jsPrefix.$name).'___calendar_';
        } else {
            $safeName = $name;
        }
        $id       = $this->getAttribute('id');
        if (is_null($id)) {
            $id = $safeName.'field';
            $this->updateAttributes(array('id' => $id));
        }
        if ($this->safe_name) {
            $id      = preg_replace('/[^a-zA-Z0-9-_]/', '___', $id);
        }
        $cname   = $safeName.'trigger';
        $jscript = '';

        $options = $this->config;

        if (!defined('HTML_QUICKFORM_JSCALENDAR_LOADED')) {
            // load jsCalendar
            $basePath = (empty($this->basePath))
                        ? SITE_SUBDIR.self::BASEPATH
                        : $this->basePath;

            $lang = (empty($this->lang))
                    ? self::LANG
                    : $this->lang;

            if ($this->stripped) {
                $calendarFile = 'calendar_stripped.js';
                $calendarSetupFile = 'calendar-setup_stripped.js';
            } else {
                $calendarFile = 'calendar.js';
                $calendarSetupFile = 'calendar-setup.js';
            }

            $calendarLangFile = 'lang/calendar-'.$lang.'.js';

            $html = '<script type="text/javascript" src="'.$basePath.$calendarFile.'"></script>'
                   .PHP_EOL
                   .'<script type="text/javascript" src="'.$basePath.$calendarLangFile.'"></script>'
                   .PHP_EOL
                   .'<script type="text/javascript" src="'.$basePath.$calendarSetupFile.'"></script>'
                   .PHP_EOL;

            if (isset($this->theme)) {
                $html .= '<link rel="stylesheet" type="text/css" href="'.$basePath.$this->theme.'.css" />';
            }
            define('HTML_QUICKFORM_JSCALENDAR_LOADED', true);
        }

        // Add binding to input field
        $options['inputField'] = $id;

        // Handle multiple selections
        $multiple = $this->getMultiple();

        $attr = $this->getAttributes();
        unset($attr['multiple']);

        $value = $this->getValue();
        if (!$multiple) {
            // Render the text field
            $htmlField = parent::toHtml();
            $htmlValue = htmlspecialchars($value);

        } else {
            // We may use a textarea element to list the multiple dates
            // instead of a type="text" one
            unset($attr['type']);

            settype($value, 'array');
            $htmlValue = implode(PHP_EOL, array_map('htmlspecialchars',
                                                    $value));
            $tag = new HTML_Common($attr);
            $tag->removeAttribute('value');

            if (empty($options['daFormat'])) {
                $tag->setAttribute('onchange', $safeName.'onUserChange(this);');
            }

            $htmlField = '<textarea '.$tag->getAttributes(true).'>'
                         .$htmlValue
                         .'</textarea>';
        }

        // Should a button be automatically added?
        $generateButton = false;
        if (array_key_exists('button', $options)
            && empty($options['button'])) {

            $generateButton = true;
            $options['button'] = $cname;
        }

        // Add the element that will contain the actual data
        if (isset($options['daFormat'])) {
            if (empty($options['displayArea'])) {
                $options['displayArea'] = $safeName.'displayArea';
            }
            $html .= '<noscript>'.$htmlField.'</noscript>'.PHP_EOL;
        } else {
            $html .= $htmlField;
        }

        if ($multiple) {
            // Setup the "multiple" option of the calendar
//            $jsDate = array();

            foreach ($value as $v) {
                $date = ($v) ? strtotime($v) : false;
                if ($date > 0) {
                    $jsDates[] = 'new Date('.$date.'000)';
                }
            }

            $dateVarName = $safeName.'multiple';
            $jscript .= 'var '.$dateVarName.' = ['.implode(', ', $jsDates).'];'
                        .PHP_EOL;
            $options['callbacks']['multiple'] = $dateVarName;

            // Create our own onClose callback to handle multiple dates
            // selection
            if (!isset($options['daFormat'])) {
                $jscript .= 'function '.$safeName.'onUserChange(element)'
                            .PHP_EOL
                            .'{'.PHP_EOL
                            .'    var j = 0;'.PHP_EOL
                            .'    var d = 0;'.PHP_EOL
                            .'    var value = "";'.PHP_EOL
                            .'    var values = element.value.split(/[\n\r]/);'
                            .PHP_EOL
                            .'    '.$dateVarName.'.length = 0;'.PHP_EOL
                            .'    for (var i = 0; i < values.length; ++i) {'
                            .PHP_EOL
                            .'        d = Date.parseDate(values[i], "'
                                                        .$options['ifFormat']
                                                        .'");'.PHP_EOL
                            .'        if (values[i] == d.print("'
                                                        .$options['ifFormat']
                                                        .'")) {'.PHP_EOL
                            .'            '.$dateVarName.'[j++] = d;'.PHP_EOL
                            .'        }'.PHP_EOL
                            .'    }'.PHP_EOL
                            .'    '.$dateVarName
                                   .'.sort(function (a, b) {return a.getTime() - b.getTime();});'
                                   .PHP_EOL
                            .'    for (i = 0; i < j; ++i) {'.PHP_EOL
                            .'        value += '.$dateVarName.'[i].print("'
                                                .$options['ifFormat']
                                                .'") + "\n";'.PHP_EOL
                            .'    }'.PHP_EOL
                            .'    element.value = value;'.PHP_EOL
                            .'}'.PHP_EOL;
            }

            $onClose = 'function (cal) {'.PHP_EOL
                       .'    var html = "";'.PHP_EOL
                       .'    var d;'.PHP_EOL
                       .'    var j = 0;'.PHP_EOL
                       .'    var value = "";'.PHP_EOL
                       .'    '.$dateVarName.'.length = 0;'.PHP_EOL
                       .'    for (var i in cal.multiple) {'.PHP_EOL
                       .'        d = cal.multiple[i];'.PHP_EOL
                       .'        if (d) {'.PHP_EOL
                       .'            '.$dateVarName.'[j++] = d;'.PHP_EOL
                       .'        }'.PHP_EOL
                       .'    }'.PHP_EOL
                       .'    '.$dateVarName.'.sort(function (a, b) {return a.getTime() - b.getTime();});'.PHP_EOL
                       .'    for (i = 0; i < j; ++i) {'.PHP_EOL
                       .'        d = '.$dateVarName.'[i];'.PHP_EOL
                       .'        value += d.print("'.$options['ifFormat']
                                                .'") + "\n";'
                                                  .PHP_EOL;
            if (!empty($options['daFormat'])) {
                $onClose .= '        html += "<span>" + d.print("'
                                                 .$options['daFormat'].'")'
                                                 .' + "<\/span>";'.PHP_EOL;
            }
            $onClose .=  '    }'.PHP_EOL
                        .'    var field = document.getElementById("'.$id.'");'
                              .PHP_EOL
                        .'    field.value = value;'.PHP_EOL;

            if (!empty($options['daFormat'])) {
                $onClose .= '    var display = document.getElementById("'
                                .$options['displayArea'].'");'.PHP_EOL
                            .'    display.innerHTML = html;'.PHP_EOL;
            }

            // Any user-registered onClose callback?
            if (!empty($options['callbacks']['onClose'])) {
                $callback = $options['callbacks']['onClose'];
                if (preg_match('/^function[ (]/', $callback)) {
                    $jscript .= 'var '.$safeName.'callback_onClose = '
                                .$callback.';'.PHP_EOL;
                    $callback = $safeName.'callback_onClose';
                }

                $onClose .= '    if (typeof '.$callback.' == "function") {'
                            .PHP_EOL
                            .'        return '.$callback.'(cal);'.PHP_EOL
                            .'    }'.PHP_EOL;
            }
            $onClose .=  '    cal.hide();'.PHP_EOL
                        .'    return true;'.PHP_EOL
                        .'}';

            $options['callbacks']['onClose'] = $onClose;
        }

        // Enhanced experience for JavaScript-enabled web browsers

        // If an area is used to display a formatted value:
        if (isset($options['daFormat'])) {

            if ($multiple) {
                $htmlValue = str_replace(PHP_EOL, '\n', $htmlValue);

                $htmlDisplayValue = '';
                foreach ($value as $v) {
                    $date = ($v) ? strtotime($v) : false;
                    if ($date > 0) {
                        $displayValue = strftime($options['daFormat'], $date);
                    } else {
                        $displayValue = $v;
                    }
                    $htmlDisplayValue .= '<span>'
                                         .htmlspecialchars($displayValue)
                                         .'<\/span>';
                }
            } else {
                $date = ($value) ? strtotime($value) : false;
                if ($date > 0) {
                    $displayValue = strftime($options['daFormat'], $date);
                } else {
                    $displayValue = $value;
                }
                $htmlDisplayValue = htmlspecialchars($displayValue);
            }

            $htmlField = '<input type="hidden" name="'.htmlspecialchars($name)
                         .'" id="'.htmlspecialchars($id).'"'
                         .' value="'.$htmlValue.'" />';

            unset($attr['name']);
            unset($attr['value']);
            unset($attr['type']);
            unset($attr['rows']);
            unset($attr['cols']);
            $attr['id'] = $options['displayArea'];

            $tag = new HTML_Common($attr);

            $jscript .= 'document.write(\''.$htmlField
                                     .'<span '.$tag->getAttributes(true).'>'
                                     .$htmlDisplayValue
                                     .'<\\/span>\');'
                     .PHP_EOL;
        }

        if ($generateButton) {
            $jscript .= 'document.write(\'<input type="button" value="..." name="'
                     .$cname.'" id="'.$cname.'" />\');'.PHP_EOL;

        } elseif (empty($options['button']) && empty($options['displayArea'])) {
            $options['eventName'] = 'focus';
        }

        $jscript .= 'Calendar.setup({'.$this->jsSerialize($options).'});'
                 .PHP_EOL;

        return $html.$this->trigger_html
               .'<script type="text/javascript">'.PHP_EOL
               .'// <![CDATA['.PHP_EOL
               .$jscript
               .'// ]]>'.PHP_EOL
               .'</script>'.PHP_EOL;
    }

    /**
     * Returns the jsCalendar content in HTML
     *
     * @return string
     */
    public function getFrozenHtml () {
        
        $value = $this->getValue();
        if (!isset($this->config['daFormat'])) {
            return (is_array($value))
                   ? implode('<br />'.PHP_EOL, $value)
                   : $value;
        }
        $data = array();
        settype($value, 'array');
        foreach ($value as $v) {
            $date = ($v) ? strtotime($v) : false;
            $data[] = ($date > 0)
                      ? strftime($this->config['daFormat'], $date)
                      : $v;
        }
        return implode('<br />'.PHP_EOL, $data);
        
    }

    /**
     * Serializes into JavaScript
     *
     * @param  array  $data
     * @return string
     */
    protected function jsSerialize ($data,$function = false) {
        
        $jstr = '';
        foreach ($data as $key => $val) {
            if (is_bool($val)) {
                $val = ($val) ? 'true' : 'false';

            } elseif ($function) {
                // We're good like this...

            } elseif ($key == 'callbacks') {
                if (empty($val)) {
                    continue;
                }
                $callbacks = $this->_jsSerialize($val, true);
                if ($jstr) {
                    $jstr .= ', ';
                }
                $jstr .= $callbacks;
                continue;

            } elseif (is_array($val)) {
                // Used for multiple dates - NOT a generic array Javascript
                // serializer!
                $val = '['.implode(', ', $val).']';

            } elseif (!is_numeric($val)) {
                $val = '"'.$val.'"';
            }
            if ($jstr) {
                $jstr .= ', ';
            }
            $jstr .= $key.':'.$val;
        }
        return $jstr;
        
    }

    /**
     * Sets the element's value
     *
     * @param     string|array  $value  Dates (in ifFormat)
     * @return    void
     * @access    public
     */
    public function setValue ($value) {
        
        if ($this->getMultiple()) {
            if (is_string($value)) {
                $value = preg_split('/[\r\n]/', $value);
            }
            settype($value, 'array');
            foreach ($value as $i => $v) {
                $v = trim($v);
                if (strlen($v)) {
                    $value[$i] = $v;
                } else {
                    unset($value[$i]);
                }
            }
        } else {
            parent::setValue($value);
        }
        $this->values = $value;
        
    }

    /**
     * Returns element value(s)
     *
     * @return    string|array
     */
    public function getValue() {
        
        if ($this->getMultiple()) {
            return (array)$this->values;
        }
        if (!is_array($this->values) || !$this->values) {
            return $this->values;
        }
        reset($this->values);
        return current($this->values);
        
    }
	
}

?>