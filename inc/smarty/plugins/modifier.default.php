<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     default
 * Purpose:  designate default value for empty variables
 * -------------------------------------------------------------
 */
function smarty_modifier_default($string, $default = '')
{
    if (empty($string))
        return $default;
    else
        return $string;
}

/* vim: set expandtab: */

?>
