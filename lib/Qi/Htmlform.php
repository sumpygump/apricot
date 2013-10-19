<?php
/**
 * Class file for Htmlform
 *
 * @package Qi
 */

/**
 * Qi_Htmlform
 * Provides functions for making html form elements.
 *
 * @package Qi
 * @author Jansen Price <jansen.price@gmail.com>
 * @version 1.0
 */
class Qi_Htmlform {

    /**
     * Create form label
     *
     * @param mixed $id
     * @param mixed $caption
     * @return string
     */
    public static function label($id, $caption) {
        $result = "<label for=\"$id\">$caption</label>\n";
        return $result;
    }

    /**
     * Create text input field
     *
     * @param mixed $name
     * @param string $id
     * @param string $value
     * @param mixed $size
     * @param mixed $maxlength
     * @param mixed $readonly
     * @param string $additional
     * @return string
     */
    public static function text($name, $id='', $value='', $size=false, $maxlength=false, $readonly=false, $additional = '') {
        $result = "<input type=\"text\" name=\"$name\"";
        if ($id) $result .= " id=\"$id\"";
        if ($value) $result .= " value=\"$value\"";
        if ($size) $result .= " size=\"$size\"";
        if ($maxlength) $result .= " maxlength=\"$maxlength\"";
        if ($readonly) $result .= " readonly=\"readonly\"";
        if ($additional) $result .= " $additional";
        $result .= " />\n";

        return $result;
    }

    /**
     * Create select input
     *
     * @param mixed $name
     * @param string $id
     * @param mixed $names_values
     * @param mixed $first_blank
     * @param mixed $selected
     * @param string $additional
     * @return string
     */
    public static function select($name, $id='', $names_values, $first_blank=false, $selected=false, $additional="") {
        if (isset($names_values[0]) && is_array($names_values[0])) {
            $array_type = 'assoc';
        } else {
            $array_type = 'key';
        }

        $result = "<select name=\"$name\"";

        if ($id) {
            $result .= " id=\"$id\"";
        }

        $result .= " $additional>\n";

        if (!$selected) {
            $firstselected = " selected=\"selected\"";
        } else {
            $firstselected = "";
        }

        if ($first_blank !== false) {
            $result .= "\t<option value=\"$first_blank\"$firstselected>&nbsp;</option>\n";
        }

        if ($array_type == 'assoc') {
            for($i = 0; $i < count($names_values); $i++) {
                $name  = $names_values[$i][0];
                $value = $names_values[$i][1];

                $result .= "\t<option value=\"$name\"";

                if ($selected == $name) {
                    $result .= " selected=\"selected\"";
                }

                $result .= ">$value</option>\n";
            }
        } else {
            foreach($names_values as $name=>$value) {
                $result .= "\t<option value=\"$name\"";

                if ($selected == $name) {
                    $result .= " selected=\"selected\"";
                }

                $result .= ">$value</option>\n";
            }
        }

        $result .= "</select>\n";

        return $result;
    }

    /**
     * Create a textarea input
     *
     * @param mixed $id
     * @param int $cols
     * @param int $rows
     * @param string $text
     * @param string $additional
     * @return string
     */
    public static function textarea($id, $cols=30, $rows=5, $text='', $additional='') {
        $result = "<textarea name=\"$id\" id=\"$id\" rows=\"$rows\" cols=\"$cols\">\n";
        $result .= $text;
        $result .= "</textarea>\n";

        return $result;
    }

    /**
     * Create a submit button
     *
     * @param mixed $id
     * @param string $value
     * @param string $additional
     * @return string
     */
    public static function submit($id, $value='Submit', $additional='') {
        $result = "<input type=\"submit\" id=\"$id\" name=\"$id\" value=\"$value\" $additional />\n";
        return $result;
    }

    /**
     * Create a checkbox input
     *
     * @param mixed $id
     * @param mixed $curval
     * @param mixed $readonly
     * @param string $true
     * @param string $addl
     * @return string
     */
    public static function checkbox($id, $curval, $readonly=false, $true='Y', $addl="") {
        $result = "<input type=\"checkbox\" id=\"$id\" name=\"$id\" value=\"$true\"";
        if ($addl) $result .= " $addl";
        if($curval == $true) {
            $result .= " checked=\"checked\"";
        }
        if($readonly == true) {
            $result .= " readonly=\"readonly\"";
        }
        $result .= " />";
        return $result;
    }

    /**
     * Create a hidden input
     *
     * @param mixed $name
     * @param string $id
     * @param string $value
     * @param string $additional
     * @return string
     */
    public static function hidden($name, $id='', $value='',$additional = '') {
        $result = "<input type=\"hidden\" name=\"$name\"";
        if ($id) $result .= " id=\"$id\"";
        if ($value) $result .= " value=\"$value\"";
        if ($additional) $result .= " $additional";
        $result .= " />\n";

        return $result;
    }

    /**
     * Create a table layout
     *
     * @param mixed $elements
     * @param mixed $class
     * @param string $id
     * @param string $lbl
     * @param string $ctl
     * @return string
     */
    public static function table($elements, $class, $id='', $lbl="lbl", $ctl="ctl") {
        $result = "<table class=\"$class\"";
        if($id) $result .= " id=\"$id\"";
        $result .= ">\n";
        if(is_array($elements)) {
            foreach($elements as $key=>$value) {
                $result .= "\t<tr><td class=\"$lbl\" valign=\"top\">$key</td><td class=\"$ctl\">$value</td></tr>\n";
            }
        }
        $result .= "</table>\n";
        return $result;
    }
}
