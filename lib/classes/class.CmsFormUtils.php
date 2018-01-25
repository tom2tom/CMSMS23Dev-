<?php
# A class for CMS Made Simple, to generate form tags.
# Copyright (C) 2016-2018 Robert Campbell <calguy1000@cmsmadesimple.org>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/licenses.html>.

/**
 * A static class providing functionality for building forms.
 *
 * @package CMS
 * @license GPL
 * @author  Robert Campbell
 * @since   2.0
 */
final class CmsFormUtils
{
    /**
     * @ignore
     */
    const NONE = '__none__';
    //untranslated error messages
    const ERRTPL = 'parameter "%s" is required for %s';
    const ERRTPL2 = 'a valid "%s" parameter is required for %s';

    /**
     * @ignore
     */
    private static $_activated_wysiwyg = [];

    /**
     * @ignore
     */
    private static $_activated_syntax = [];

    /* *
     * @ignore
     */
//    private function __construct() {}

    /**
     * Check existence of compulsory members of $parms, and they each have an acceptable value
     * @ignore
     * @since 2.3
     * @param array $parms tag parameters/attributes
     * @param array $must key(s) which must be set in $parms, each with a value-check code.
     * @return mixed Error-message string (template) or false if no error
     */
    protected static function must_attrs(array &$parms, array $must)
    {
        foreach ($must as $key=>$val) {
            if (!isset($parms[$key])) {
                return sprintf(self::ERRTPL, $key, '%s');
            }
            $tmp = $parms[$key];
            switch ($val) {
                case 'c': //acceptable/sanitized string
                case 'e': //false/null/empty is also acceptable
                    if ($tmp || (int)($tmp + 0) === 0) {
                        if (is_string($tmp)) {
                            $parms[$key] = $tmp = \sanitize($tmp);
                            if ($tmp) {
                                break;
                            }
                        }
                    }
                    if ($val == 'c') {
                        return sprintf(self::ERRTPL2, $key, '%s');
                    }
                    break;
                case 's': //any non-empty string
                    if (is_string($tmp) && $tmp !== '') {
                        return sprintf(self::ERRTPL2, $key, '%s');
                    }
                    break;
                case 'i': //int or string equivalent
                    $tmp = filter_var($tmp, FILTER_SANITIZE_NUMBER_INT);
                    if ($tmp) {
                        $parms[$key] = (int)$tmp;
                        break;
                    } else {
                        return sprintf(self::ERRTPL2, $key, '%s');
                    }
                    // no break
                case 'n': //any number or string equivalent
                    $tmp = filter_var($tmp, FILTER_SANITIZE_NUMBER_FLOAT);
                    if ($tmp) {
                        $parms[$key] = $tmp + 0;
                        break;
                    } else {
                        return sprintf(self::ERRTPL2, $key, '%s');
                    }
                    // no break
                case 'a': //any non-empty array
                    if (is_array($tmp) && $tmp) {
                        break;
                    } else {
                        return sprintf(self::ERRTPL2, $key, '%s');
                    }
            }
        }
        return false;
    }

    /**
     * Check and update tag-properties
     * @ignore
     * @since 2.3
     * @param array $parms tag parameters/attributes
     * @param array $alts optional extra renames for keys in #parms, each member like 'oldname'=>'newname'
     * @return mixed Error-message string, or false if no error
     */
    protected static function clean_attrs(array &$parms, array $alts = [])
    {
        //aliases
        $alts += ['classname'=>'class'];
        foreach ($alts as $key => $val) {
            if (isset($parms[$key])) {
                $parms[$val] = $parms[$key];
                unset($parms[$key]);
            }
        }

        extract($parms, EXTR_SKIP);

        //identifiers
        if (!empty($htmlid)) {
            $tmp = $htmlid;
            if (empty($modid)) {
                if (!empty($id)) {
                    $modid = $id;
                } else {
                    return sprintf(self::ERRTPL, 'id', '%s');
                }
            }
        } elseif (!empty($modid)) {
            $tmp = $modid.$name;
        } elseif (!empty($id)) {
            $tmp = $id.$name;
            $modid = $id;
        } else {
            return sprintf(self::ERRTPL, 'id', '%s');
        }
        unset($parms['htmlid']);

        $parms['modid'] = \sanitize($modid);
        $parms['name'] = \sanitize($modid.$name);
        $tmp = \sanitize($tmp);
        $parms['id'] = ($tmp) ? $tmp : $parms['name'];

        //expectable bools
        foreach (['disabled', 'readonly', 'required'] as $key) {
            if (isset($$key)) {
                if (\cms_to_bool($$key)) {
                    $parms[$key] = $key;
                } elseif ($$key !== $key) {
                    unset($parms[$key]);
                }
            }
        }
        //expectable ints
        foreach (['maxlength', 'size', 'step', 'cols', 'rows', 'width', 'height'] as $key) {
            if (isset($$key)) {
                $tmp = filter_var($$key, FILTER_SANITIZE_NUMBER_INT);
                if ($tmp || $tmp === 0) {
                    $parms[$key] = $tmp;
                } else {
                    return sprintf(self::ERRTPL2, $key, '%s');
                }
            }
        }
        //numbers generally
        foreach (['min', 'max'] as $key) {
            if (isset($$key)) {
                $tmp = filter_var($$key, FILTER_SANITIZE_NUMBER_FLOAT);
                if ($tmp || (int)$tmp === 0) {
                    $parms[$key] = $tmp;
                } else {
                    return sprintf(self::ERRTPL2, $key, '%s');
                }
            }
        }
        return false;
    }

    /**
     * Generate output representing members of $parms that are not in $excludes
     * @ignore
     * @since 2.3
     * @param array $parms tag parameters/attributes
     * @param array $excludes key(s) which must be ignored in $parms
     * @return string
     */
    protected static function join_attrs(array &$parms, array $excludes) : string
    {
        $out = '';
        foreach ($parms as $key=>$val) {
            if (!in_array($key, $excludes)) {
                if (!is_numeric($key)) {
                    $out .= ' '.$key.'='.'"'.$val.'"';
                } else {
                    $out .= ' '.$val;
                }
            }
        }
        return $out;
    }

    /**
     * A simple recursive utility function to create an option, or a set of options for a select list or multiselect list.
     *
     * Accepts an associative 'option' array with at least two populated keys: 'label' and 'value'.
     * If 'value' is not an array then a single '<option>' is created.  However, if 'value' is itself
     * an array then an 'optgroup' will be created with it's values.
     *
     * i.e: $tmp = array('label'=>'myoptgroup','value'=>array( array('label'=>'opt1','value'=>'value1'), array('label'=>'opt2','value'=>'value2') ) );
     *
     * The 'option' array can have additional keys for 'title' and 'class'
     *
     * i.e: $tmp = array('label'=>'opt1','value'=>'value1','title'=>'My title','class'=>'foo');
     *
     * @param array $data The option data
     * @param string[]|string $selected  The selected elements
     * @return string The generated <option> element(s).
     * @see self::create_options()
     */
    public static function create_option($data, $selected = null) : string
    {
        if (!is_array($data)) {
            return '';
        }

        $out = '';
        if (isset($data['label']) && isset($data['value'])) {
            if (!is_array($data['value'])) {
                $out .= '<option value="'.trim($data['value']).'"';
                if ($selected == $data['value'] || is_array($selected) && in_array($data['value'], $selected)) {
                    $out .= ' selected="selected"';
                }
                if (!empty($data['title'])) {
                    $out .= ' title="'.trim($data['title']).'"';
                }
                if (!empty($data['class'])) {
                    $out .= ' class="'.trim($data['class']).'"';
                }
                $out .= '>'.$data['label'].'</option>';
            } else {
                $out .= '<optgroup label="'.$data['label'].'">';
                foreach ($data['value'] as $one) {
                    $out .= self::create_option($one, $selected);
                }
                $out .= '</optgroup>';
            }
        } else {
            foreach ($data as $rec) {
                $out .= self::create_option($rec, $selected);
            }
        }
        return $out;
    }

    /**
     * Create a series of options suitable for use in a select input element.
     *
     * This method is intended to provide a simple way of creating options from a simple associative array
     * but can accept multiple arrays of options as specified for the CmsFormUtils::create_option method
     *
     * i.e: $tmp = array('value1'=>'label1','value2'=>'label2');
     * $options = CmsFormUtils::create_options($tmp);
     *
     * i.e: $tmp = array( array('label'=>'label1','value'=>'value1','title'=>'title1'),
     *                    array('label'=>'label2','value'=>'value2','class'=>'class2') );
     * $options = CmsFormUtils::create_options($tmp)
     *
     * @param array $options
     * @param mixed $selected string value or array of them
     * @return string
     * @see CmsFormUtils::create_options()
     */
    public static function create_options($options, $selected = '') : string
    {
        if (!is_array($options) || !$options) {
            return '';
        }

        $out = '';
        foreach ($options as $key => $value) {
            if (!is_array($value)) {
                $out .= self::create_option(['label'=>$value,'value'=>$key], $selected);
            } else {
                $out .= self::create_option($value, $selected);
            }
        }
        return $out;
    }

    /**
     * Get xhtml for a dropdown selector
     * @see also CmsFormUtils::create_select()
     *
     * @param string $name The name attribute for the select name
     * @param array  $list_options  Options as per the CmsFormUtils::create_options method
     * @param mixed string|string[] $selected Selected value as per the CmsFormUtils::create_option method
     * @param array  $params Array of additional options including: multiple,class,title,id,size
     * @deprecated use create_select() instead
     * @return string The HTML content for the <select> element.
     */
    public static function create_dropdown(string $name, array $list_options, $selected, array $params = []) : string
    {
        $parms = ['type'=>'drop', 'name'=>$name, 'options'=>$list_options, 'selected'=>$selected] + $params;
        return self::create_select($parms);
    }

    /**
     * Get xhtml for a selector (checkbox, radiogroup, list, dropdown)
     *
     * @since 2.3
     *
     * @param array  $parms   Attribute(s)/definition(s) to be
     *  included in the element, each member like name=>value. The
     *  name may be numeric, in which case only the value is used.
     *  Must include at least 'type' and 'name' and at least 2 of
     *  'htmlid', 'modid', 'id', the latter being an alias for either
     *  'htmlid' or 'modid'. Recognized types are 'check','radio','list',
     *  'drop'
     *
     * @return string
     */
    public static function create_select(array $parms) : string
    {
        //must have these $parms, each with a usable value
        $err = self::must_attrs($parms, ['type'=>'c', 'name'=>'c']);
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }
        //common checks
        $err = self::clean_attrs($parms, ['items'=>'options']);
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }
        extract($parms);
        //custom checks & setup
        switch ($type) {
            case 'check':
                $err = self::must_attrs($parms, ['value'=>'s']);
                if ($err) {
                    break;
                }

                if (empty($class)) {
                    $parms['class'] = 'cms_checkbox';
                } else {
                    $parms['class'] .= ' cms_checkbox';
                }

                if (isset($selectedvalue) && $selectedvalue == $value) {
                    $parms['selected'] = 'selected';
                }

                $out = '<input type="checkbox"';
                $out .= self::join_attrs($parms, ['type', 'selectedvalue']);
                $out .= ' />'."\n";
                break;
            case 'radio':
                $err = self::must_attrs($parms, ['items'=>'a', 'selectedvalue'=>'s']);
                if ($err) {
                    break;
                }

                if (empty($class)) {
                    $parms['class'] = 'cms_radio';
                } else {
                    $parms['class'] .= ' cms_radio';
                }

                $each = '<input' . self::join_attrs($parms, [
                 'id',
                 'options',
                 'selectedvalue',
                 'delimiter',
                ]);
                $i = 1;
                $count = count($options);
                $out = '';
                foreach ($options as $key=>$val) {
                    $out .= $each . ' id="'.$modid.$name.$i.'" value="'.$val.'"';
                    if ($val == $selectedvalue) {
                        $out .= ' checked="checked"';
                    }
                    $out .= ' /><label class="cms_label" for="'.$modid.$name.$i.'">'.$key .'</label>';
                    if ($i < $count && $delimiter) {
                        $out .= $delimiter;
                    }
                    $out .= "\n";
                    ++$i;
                }
                break;
            case 'drop':
                unset($parms['multiple']);
                $tmp = 'cms_dropdown';
                //no break here
            case 'list':
                $err = self::must_attrs($parms, ['items'=>'a']);
                if ($err) {
                    break;
                }

                if ($type == 'list') {
                    $tmp = 'cms_select';
                    if ($multiple) {
                        $parms['multiple'] = 'multiple';
                        // adjust name if element allows multiple-selection
                        if (!\endswith($name, '[]')) {
                            $parms['name'] = $name . '[]';
                        }
                    } else {
                        unset($parms['multiple']);
                    }
                }

                if (empty($class)) {
                    $parms['class'] = $tmp;
                } else {
                    $parms['class'] .= ' '.$tmp;
                }

                $selected = '';
                if (!empty($selectedvalue)) {
                    $selected = $selectedvalue; //maybe array
                } elseif (isset($selectedindex)) {
                    $keys = array_keys($options);
                    if (array_key_exists($selectedindex, $keys)) {
                        $selected = $options[$keys[$selectedindex]];
                    }
                }

                $out = '<select' . self::join_attrs($parms, [
                 'type',
                 'options',
                 'selectedindex',
                 'selectedvalue',
                ]);
                $contents = self::create_options($options, $selected);
                $out .= '>'.$contents.'</select>'."\n";
                break;
            default:
                $err = sprintf(self::ERRTPL2, 'type', '%s');
                break;
        }
        if (!$err) {
            return $out;
        }
        $tmp = sprintf($err, __METHOD__);
        assert(!$err, $tmp);
        return '<!-- ERROR: '.$tmp.' -->';
    }

    /**
     * Get xhtml for a single-element input (text, textarea, button, submit etc)
     *
     * @since 2.3
     *
     * @param array  $parms   Attribute(s)/definition(s) to be
     *  included in the element, each member like name=>value. Any
     *  name may be numeric, in which case only the value is used.
     *  Must include at least 'type' and 'name' and at least 2 of
     *  'htmlid', 'modid', 'id', the latter being an alias for either
     *  'htmlid' or 'modid'
     *
     * @return string
     */
    public static function create_input(array $parms) : string
    {
        //must have these $parms, each with a usable value
        $err = self::must_attrs($parms, ['type'=>'c', 'name'=>'c']);
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }

        if ($parms['type'] != 'textarea') {
            //common checks
            $err = self::clean_attrs($parms, ['text'=>'value', 'contents'=>'value']);
            if ($err) {
                $tmp = sprintf($err, __METHOD__);
                assert(!$err, $tmp);
                return '<!-- ERROR: '.$tmp.' -->';
            }

            extract($parms);
            //custom checks
            if (empty($class)) {
                $parms['class'] = 'cms_'.$type;
            } else {
                $parms['class'] .= ' cms_'.$type;
            }
            $value = $parms['value'] ?? '';
            $parms['value'] = ($value && $type != 'password') ? \entitize($value) : $value;

            $out = '<input';
            $out .= self::join_attrs($parms, ['TODO']);
            return $out.' />'."\n";
        }
        unset($parms['type']); //don't confuse with 'wantedsyntax'
        return self::create_textarea($parms);
    }

    /**
     * Record a syntax module
     * @internal
     * @ignore
     */
    private static function add_syntax($module_name)
    {
        if ($module_name) {
            if (!in_array($module_name, self::$_activated_syntax)) {
                self::$_activated_syntax[] = $module_name;
            }
        }
    }

    /**
     * Used externally
     */
    public static function get_requested_syntax_modules()
    {
        return self::$_activated_syntax;
    }

    /**
     * Record a wysiwyg module (which will ensure that the headers and initialization is done, later.
     * In the frontend the {cms_init_editor} plugin must be included in the head part of the page template.
     *
     * @internal
     * @ignore
     * @param string module_name (required)
     * @param string id (optional) the id of the textarea element)
     * @param string stylesheet_name (optional) the name of a stylesheet to include with this area (some WYSIWYG editors may not support this)
     */
    private static function add_wysiwyg($module_name, $id = self::NONE, $stylesheet_name = self::NONE)
    {
        if ($module_name) {
            if (!isset(self::$_activated_wysiwyg[$module_name])) {
                self::$_activated_wysiwyg[$module_name] = [];
            }
            self::$_activated_wysiwyg[$module_name][] = ['id' => $id, 'stylesheet' => $stylesheet_name];
        }
    }

    /**
     * Used externally
     */
    public static function get_requested_wysiwyg_modules()
    {
        return self::$_activated_wysiwyg;
    }

    /**
     * Get xhtml for a text area input
     * parameters:
     *   name          = (required string) name attribute for the text area element.
     *   modid         = (optional string) id given to the module on execution.  If not specified, '' will be used.
     *   id/htmlid     = (optional string) id attribute for the text area element.  If not specified, name is used.
     *   class/classname = (optional string) class attribute for the text area element.  Some values will be added to this string.
     *                   default is cms_textarea
     *   forcemodule/forcewysiwyg = (optional string) used to specify the module to enable.  If specified, the module name will be added to the
     *                   class attribute.
     *   enablewysiwyg = (optional boolean) used to specify wether a wysiwyg textarea is required.  sets the language to html.
     *   wantedsyntax  = (optional string) used to specify the language (html,css,php,smarty) to use.  If non empty indicates that a
     *                   syntax hilighter module is requested.
     *   cols/width    = (optional integer) columns of the text area (css or the syntax/wysiwyg module may override this)
     *   rows/height   = (optional integer) rows of the text area (css or the syntax/wysiwyg module may override this)
     *   maxlength     = (optional integer) maxlength attribute of the text area (syntax/wysiwyg module may ignore this)
     *   required      = (optional boolean) indicates a required field.
     *   placeholder   = (optional string) placeholder attribute of the text area (syntax/wysiwyg module may ignore this)
     *   value/text    = (optional string) default text for the text area, will undergo entity conversion.
     *   encoding      = (optional string) default utf-8 encoding for entity conversion.
     *   addtext       = (optional string) additional text to add to the textarea tag.
     *   cssname/stylesheet = (optional string) Pass this stylesheet name to the WYSIWYG area if any.
     *
     * note: if wantedsyntax is empty, AND enablewysiwyg is false, then just a plain text area is created.
     *
     * @param array $parms An associative array with parameters.
     * @return string
     */
    public static function create_textarea($parms) : string
    {
        $err = self::must_attrs($parms, ['name'=>'c']);
        if (!$err) {
            //common checks
            $err = self::clean_attrs($parms, [
             'height'=>'rows',
             'width'=>'cols',
             'text'=>'value',
             'type'=>'wantedsyntax',
             'forcewysiwyg'=>'forcemodule',
             'stylesheet'=>'cssname',
            ]);
        }
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }

        extract($parms);

        if (empty($class)) {
            $parms['class'] = 'cms_textarea';
        } else {
            $parms['class'] .= ' cms_textarea';
        }
        if (empty($cols) || $cols <= 0) {
            $parms['cols'] = 20;
        }
        if (empty($rows) || $rows <= 0) {
            $parms['rows'] = 5;
        }
        if (!empty($maxlength) && $maxlength <= 0) {
            unset($parms['maxlength']);
        }

        $value = $value ?? '';

        $module = null;
        // do we want a wysiwyg area ?
        $enablewysiwyg = !empty($enablewysiwyg) && \cms_to_bool($enablewysiwyg);
        if ($enablewysiwyg) {
            // we want a wysiwyg
            $parms['class'] .= ' cmsms_wysiwyg';
            $module = \ModuleOperations::get_instance()->GetWYSIWYGModule($forcemodule);
            if ($module && $module->HasCapability(\CmsCoreCapabilities::WYSIWYG_MODULE)) {
                $parms['data-cms-lang'] = 'html'; //park badly-named variable TODO config['?']
                $module_name = $module->GetName();
                $parms['class'] .= ' '.$module_name;
                if (empty($cssname)) {
                    $cssname = self::NONE;
                }
                self::add_wysiwyg($module_name, $id, $cssname);
            }
        }

        $wantedsyntax = $wantedsyntax ?? '';
        if (!$module && $wantedsyntax) {
            $parms['data-cms-lang'] = $wantedsyntax; //park
            $module = \ModuleOperations::get_instance()->GetSyntaxHighlighter($forcemodule);
            if ($module && $module->HasCapability(\CmsCoreCapabilities::SYNTAX_MODULE)) {
                $module_name = $module->GetName();
                $parms['class'] .= ' '.$module_name;
                self::add_syntax($module_name);
            }
        }

        if ($value && $enablewysiwyg && !$wantedsyntax) {
//           if( empty($encoding) ) $encoding = CmsNlsOperations::get_encoding();
            if (!isset($encoding)) {
                $encoding = '';
            } //use the system-default
            $value = cms_htmlentities($value, ENT_NOQUOTES, $encoding); //TODO syntax flag
        }

        $out = '<textarea';
        $out .= self::join_attrs($parms, [
         'type',
         'modid',
         'value',
         'enablewysiwyg',
         'forcemodule',
         'wantedsyntax',
         'encoding',
         'cssname',
        ]);
        $out .= '>'.$value.'</textarea>'."\n";
        return $out;
    }

    /**
     * Get xhtml for a label for another element
     *
     * @since 2.3
     *
     * @param array  $parms   Attribute(s)/definition(s) to be
     *  included in the element, each member like name=>value. Any
     *  name may be numeric, in which case only the value is used.
     *  Must include at least 'name' and 'labeltext'
     *
     * @return string
     */
    public static function create_label(array $parms) : string
    {
        //must have these $parms, each with a usable value
        $err = self::must_attrs($parms, ['name'=>'c', 'labeltext'=>'c']);
        if (!$err) {
            $err = self::clean_attrs($parms);
        }
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }

        if (empty($parms['class'])) {
            $parms['class'] = 'cms_label';
        } else {
            $parms['class'] .= ' cms_label';
        }

        $out = '<label for="'.$parms['name'].'"';
        $out .= self::join_attrs($parms, ['name', 'labeltext']);
        $contents = \cms_htmlentities($parms['labeltext']);
        $out .= '>'.$content.'</label>'."\n";
        return $out;
    }

    /**
     * Get xhtml for the start of a module form
     *
     * @since 2.3
     *
     * @param array  $parms   Attribute(s)/definition(s) to be
     *  included in the element, each member like name=>value. Any
     *  name may be numeric, in which case only the value is used.
     *  Must include at least 'action'
     *
     * @return string
     */
    public static function create_form_start(&$modinstance, array $parms) : string
    {
        static $_formcount = 1;
        //must have these $parms, each with a usable value
        $err = self::must_attrs($parms, ['action'=>'c']);
        if (!$err) {
            $err = self::clean_attrs($parms);
        }
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }
        extract($parms);

        $idsuffix = (!empty($idsuffix)) ? \sanitize($idsuffix) : '';
        if ($idsuffix === '') {
            $idsuffix = $_formcount++;
        }

        $method = (!empty($method)) ? \sanitize($method) : 'POST';

        if (!empty($returnid) || $returnid === 0) {
            $returnid = (int)$returnid; //OR filter_var() ?
            $content_obj = \cms_utils::get_current_content(); //CHECKME ever relevant when CREATING a form?
            $goto = ($content_obj) ? $content_obj->GetURL() : 'index.php';
            if (strpos($goto, ':') !== false && \CmsApp::get_instance()->is_https_request()) {
                $goto = str_replace('http:', 'https:', $goto);
            }
        } else {
            $goto = 'moduleinterface.php';
        }

        $out = '<form id="'.$modid.'moduleform_'.$idsuffix.'" method="'.$method.'" action="'.$goto.'"';
        $out .= self::join_attrs($parms, [
         'name',
         'id',
         'modid',
         'idsuffix',
         'returnid',
         'action',
         'method',
         'params',
         'inline',
        ]);
        $out .= '>'."\n".
        '<div class="hidden">'."\n".
        '<input type="hidden" name="mact" value="'.$modinstance->GetName().','.$modid.','.$action.','.($inline?1:0).'" />'."\n";
        if ($returnid !== '') {
            $out .= '<input type="hidden" name="'.$modid.'returnid" value="'.$returnid.'" />'."\n";
            if ($inline) {
                $config = \cms_config::get_instance();
                $out .= '<input type="hidden" name="'.$config['query_var'].'" value="'.$returnid.'" />'."\n";
            }
        } else {
            $out .= '<input type="hidden" name="'.CMS_SECURE_PARAM_NAME.'" value="'.$_SESSION[CMS_USER_KEY].'" />'."\n";
        }
        $excludes = ['module','action','id'];
        foreach ($params as $key=>$val) {
            if (!in_array($key, $excludes)) {
//TODO          $val = TODOfunc($val); //urlencode ? serialize?
                $out .= '<input type="hidden" name="'.$modid.$key.'" value="'.$val.'" />'."\n";
            }
        }
        $out .= '</div>'."\n";
        return $out;
    }

    /**
     * Get xhtml for the end of a module form
     *
     * @since 2.3
     *
     * This is basically just a wrapper around </form>, but might be
     * extended in the future. It's here mainly for consistency.
     *
     * @return string
     */
    public static function create_form_end() : string
    {
        return '</form>'."\n";
    }

    /**
     * Get xhtml for a link to run a module action, or just the URL for that
     * action
     *
     * @since 2.3
     *
     * @param object $modinstance
     * @param array  $parms   Attribute(s)/definition(s) to be
     *  included in the element, each member like name=>value. Any
     *  name may be numeric, in which case only the value is used.
     *  Must include at least 'action'
     *
     * @return string
     */
    public static function create_action_link(&$modinstance, array $parms) : string
    {
        $err = self::clean_attrs($parms);
        if (!$err) {
            //must have these $parms, each with a usable value
            $err = self::must_attrs($parms, ['action'=>'c', 'modid'=>'c']);
        }
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }

        extract($parms);

        //optional
        if (!empty($returnid) || $returnid === 0) {
            $returnid = (int)$returnid;
        } else {
            $returnid = '';
        }

        if (empty($params) || !is_array($params)) {
            $params = [];
        }

        $prettyurl = (!$empty($prettyurl)) ? filter_var($prettyurl, FILTER_SANITIZE_URL) : '';

        // create the url
        $out = $modinstance->create_url($modid, $action, $returnid, $params, !empty($inline), !empty($targetcontentonly), $prettyurl);

        if (!$onlyhref) {
            $out = '<a href="' . $out . '"';
            $out .= self::join_attrs($parms, [
            'modid', 'action', 'returnid', 'params', 'inline', 'targetcontentonly', 'prettyurl',
            'warn_message', 'contents', 'onlyhref']);
            if ($warn_message) {
                $out .= ' onclick="return confirm(\''.$warn_message.'\');"';
            }
            $contents = \cms_htmlentities($contents);
            $out .= '>'.$contents.'</a>';
        }
        return $out;
    }

    /**
     * Get xhtml for a link to a site page, essentially a go-back facilitator. Or only the url
     *
     * @param object $modinstance
     * @param array  $parms each member like 'name'=>'value', may include:
     *  string $htmlid The id-attribute to be applied to the created tag
     *  string $modid The id given to the module on execution
     *  string $id An alternate for either of the above id's
     *  mixed  $returnid The id to eventually return to, '' or int > 0
     *  string $contents The activatable text for the displayed link
     *  string TODO support activatable image too
     *  array  $params An array of paramters to be included in the URL of the link. Each member like $key=>$value.
     *  bool   $onlyhref A flag to determine if only the href section should be returned
     *  others deemed relevant and provided by the caller
     *
     * @return string
     */
    public static function create_return_link(&$modinstance, array $parms) : string
    {
        $err = self::clean_attrs($parms);
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }

        extract($parms);

        if (!empty($returnid) || $returnid === 0) {
            $returnid = (int)$returnid; //'' or int > 0
        } else {
            $returnid = '';
        }

        if (empty($params) || !is_array($params)) {
            $params = [];
        }
        // create the url
        $out = $modinstance->create_pageurl($modid, $returnid, $params, false); //i.e. not $for_display

        if ($out) {
            if (!$onlyhref) {
                $out = '<a href="'.$out.'"';
                $out .= self::join_attrs($parms, [
                 'modid',
                 'returnid',
                 'contents',
                 'params',
                 'onlyhref',
                ]);
                $contents = \cms_htmlentities($contents);
                $out .= '>'.$contents.'</a>';
            }
        }
        return $out;
    }

    /**
     * Get xhtml for a link to show a site page
     *
     * @param array  $parms each member like 'name'=>'value', may include:
     *  int $pageid the page id of the page we want to direct to
     *  string $contents The activatable text for the displayed link
     *  string TODO support activatable image too
     *  array $attrs extra tag content, each member like $key=>$value
     *
     * @return string
     */
    public static function create_content_link(array $parms) : string
    {
        //must have these $parms, each with a usable value
        $err = self::must_attrs($parms, ['pageid'=>'i']);
        if (!$err) {
            $err = self::clean_attrs($parms);
        }
        if ($err) {
            $tmp = sprintf($err, __METHOD__);
            assert(!$err, $tmp);
            return '<!-- ERROR: '.$tmp.' -->';
        }

        extract($parms);

        $out = '<a href="';
        $config = \cms_config::get_instance();
        if ($config['url_rewriting'] == 'mod_rewrite') {
            // mod_rewrite
            $contentops = \CmsApp::get_instance()->GetContentOperations();
            $alias = $contentops->GetPageAliasFromID($pageid);
            if ($alias) {
                $out .= CMS_ROOT_URL.'/'.$alias.(isset($config['page_extension']) ? $config['page_extension'] : '.shtml');
            } else {
                $tmp = 'no alias for page with id='.$pageid;
                assert(!$alias, $tmp);
                return '<!-- ERROR: '.$tmp.' -->';
            }
        } else {
            // not mod rewrite
            $out .= CMS_ROOT_URL.'/index.php?'.$config['query_var'].'='.$pageid;
        }
        $out .= '"';
        $out .= self::join_attrs($parms, [
         'pageid',
         'modid',
         'contents',
        ]);
        $contents = \cms_htmlentities($contents);
        $out .= '>'.$contents.'</a>';
        return $out;
    }
} // end of class
