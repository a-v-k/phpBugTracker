<?php

// functions.php - Set up global functions
// ------------------------------------------------------------------------
// Copyright (c) 2001 - 2004 The phpBugTracker Group
// ------------------------------------------------------------------------
// This file is part of phpBugTracker
//
// phpBugTracker is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// phpBugTracker is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with phpBugTracker; if not, write to the Free Software Foundation,
// Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
// ------------------------------------------------------------------------
// $Id: functions.php,v 1.2 2007-02-08 23:25:00 avk Exp $
// Set the domain if gettext is available
if (false && is_callable('gettext')) {
    define('USE_GETTEXT', true);
    setlocale(LC_ALL, LOCALE);
    bindtextdomain('phpbt', './locale');
    textdomain('phpbt');
} else {
    define('USE_GETTEXT', false);
}

///
/// Show text to the browser - escape hatch
function show_text($text, $iserror = false) {
    global $t;

    if (!is_object($t)) {
        echo "<div class=\"error\">$text</div>";
        exit;
    }

    $t->assign(array(
        'text' => $text,
        'iserror' => $iserror
    ));
    $t->render('error.html', '');
}

///
/// Build a select box with the item matching $value selected
function build_select($box, $selected = '', $project = 0, $limit = false) {
    global $db, $select, $perm, $restricted_projects, $viewable_projects, $QUERY, $u;

    // create hash to map tablenames
    $cfgDatabase = array(
        'group' => TBL_AUTH_GROUP,
        'project' => TBL_PROJECT,
        'component' => TBL_COMPONENT,
        'status' => TBL_STATUS,
        'resolution' => TBL_RESOLUTION,
        'severity' => TBL_SEVERITY,
        'priority' => TBL_PRIORITY,
        'version' => TBL_VERSION,
        'database' => TBL_DATABASE,
        'site' => TBL_SITE
    );

    $text = '';

    if (isset($cfgDatabase[$box])) {
        $querystart = "select {$box}_id, {$box}_name from $cfgDatabase[$box]";
        $querymid = ' where sort_order > 0 order by sort_order';
        $queries = array(
            'group' => $querystart . ' where is_role = 0 order by group_name',
            'severity' => $querystart . $querymid,
            'priority' => $querystart . $querymid,
            'site' => $querystart . $querymid,
            'status' => (!$limit || ($perm->have_perm('CloseBug', $project) or $perm->have_perm('ManageBug', $project)) ? $querystart . $querymid : $querystart . " where sort_order > 0 and (bug_open = 1 or status_id = " . (!empty($selected) ? $selected : 0) . ") order by sort_order"),
            'resolution' => $querystart . $querymid,
            'project' => $perm->have_perm('Admin') ? $querystart . " where " .
                    ($selected ? "(active > 0 or project_id in (" . $db->quote($selected) . "))" : 'active > 0') .
                    " order by {$box}_name" :
                    $querystart . " where project_id in (" . $viewable_projects . ")" .
                    " and " .
                    ($selected ? " (active > 0 or project_id in (" . $db->quote($selected) . "))" : 'active > 0') .
                    " order by {$box}_name",
            'component' => $querystart . " where project_id = " . $db->quote($project) . " and active = 1 order by sort_order, {$box}_name",
            'version' => $querystart . " where project_id = " . $db->quote($project) . " and active = 1 order by sort_order, {$box}_id desc",
            'database' => $querystart . $querymid
        );
    }

    switch ($box) {
        case 'user_filter':
            $options = array(
                0 => translate("All Users"),
                1 => translate("Active Users"),
                2 => translate("Inactive Users"));
            foreach ($options as $k => $v) {
                $text .= sprintf("<option value=\"%d\"%s>%s</option>", $k, ($k == $selected ? ' selected' : ''), $v);
            }
            break;
        case 'group':
            if ($project) { // If we are building for project admin page
                if (!is_array($selected) or ! count($selected) or ( count($selected) && in_array(0, $selected))) {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text = "<option value=\"all\"$sel>All Groups</option>";
            }
            $rs = $db->query($queries[$box]);
            while ($rs->fetchInto($row)) {
                if (is_array($selected) && count($selected) && in_array($row[$box . '_id'], $selected) or
                        $selected == $row[$box . '_id'] and $selected != '') {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= '<option value="' .
                        $row[$box . '_id'] . "\"$sel>" . $row[$box . '_name'] . '</option>';
            }
            break;
        case 'database': $text = '<option value="0">None</option>';
        case 'severity':
        case 'priority':
        case 'status':
        case 'resolution':
        case 'project':
        case 'site':
        case 'component':
        case 'version':
            $rs = $db->query($queries[$box]);
            while ($rs->fetchInto($row)) {
                if (is_array($selected) && count($selected) && in_array($row[$box . '_id'], $selected)) {
                    $sel = ' selected';
                } elseif ($selected == $row[$box . '_id'] and $selected != '') {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= '<option value="' .
                        $row[$box . '_id'] . "\"$sel>" . $row[$box . '_name'] . '</option>';
            }
            break;
        case 'os':
            $rs = $db->query("select {$box}_id, {$box}_name, regex from " . TBL_OS . " where sort_order > 0 order by sort_order");
            while ($rs->fetchInto($row)) {
                if ($selected == '' and isset($row['Regex']) and
                        preg_match($row['Regex'], $GLOBALS['HTTP_USER_AGENT'])) {
                    $sel = ' selected';
                } elseif (is_array($selected) && count($selected) && in_array($row[$box . '_id'], $selected)) {
                    $sel = ' selected';
                } elseif ($selected == $row[$box . '_id']) {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= '<option value="' . $row[$box . '_id'] . "\"$sel>" . $row[$box . '_name'] . "</option>";
            }
            break;
        case 'owner':
            // Added the DISTINCT SQL modifier so we don't get duplicated users in the list. (Because of being in multiple groups with assignable rights.)
            $rs = $db->query("select DISTINCT u.user_id, login from " . TBL_AUTH_USER . " u, " . TBL_USER_GROUP . " ug, " . TBL_GROUP_PERM . " gp, " . TBL_AUTH_PERM . " p where u.active > 0 and u.user_id = ug.user_id and ug.group_id = gp.group_id and gp.perm_id = p.perm_id and p.perm_name = 'Assignable' order by login");
            while ($rs->fetchInto($row)) {
                // either singular matches, or array matches are acceptable
                if (($selected == $row['user_id']) || (is_array($selected) && count($selected) && in_array($row['user_id'], $selected))) {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= "<option value=\"{$row['user_id']}\"$sel>" .
                        maskemail($row['login']) . "</option>";
            }
            break;
        case 'reporter':
            global $u;
            $selected = $selected ? $selected : $u;
            $rs = $db->query("select u.user_id, login from " . TBL_AUTH_USER . " u where u.active > 0  order by login");
            while ($rs->fetchInto($row)) {
                // either singular matches, or array matches are acceptable
                if ($selected == $row['user_id']) {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= "<option value=\"{$row['user_id']}\"$sel>" .
                        maskemail($row['login']) . "</option>";
            }
            break;
        case 'bug_cc':
            $may_edit = (isset($perm) && $perm->have_perm('EditBug', $project));
            $rs = $db->query(sprintf($QUERY['functions-bug-cc'], $db->quote($selected)));
            while (list($uid, $user) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
                if ($may_edit or $uid == $u) {
                    $text .= "<option value=\"$uid\">" . maskemail($user) . '</option>';
                }
            }
            // Pad the sucker
            $text .= '<option value="" disabled>';
            for ($i = 0; $i < 30; $i++) {
                $text .= '&nbsp;';
            }
            $text .= '</option>';
            break;
        case 'LANGUAGE' :
            $dir = opendir('languages');
            while (false !== ($file = readdir($dir))) {
                if ($file != '.' && $file != '..' && $file != 'CVS' && substr($file, -3) == 'php') {
                    $filelist[] = str_replace('.php', '', $file);
                }
            }
            closedir($dir);
            sort($filelist);
            foreach ($filelist as $file) {
                if ($file == $selected) {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= "<option value=\"$file\"$sel>$file</option>";
            }
            break;
        case 'THEME' :
            $dir = opendir('templates');
            while (false !== ($file = readdir($dir))) {
                if ($file != '.' && $file != '..' && $file != 'CVS') {
                    $filelist[] = str_replace('.php', '', $file);
                }
            }
            closedir($dir);
            sort($filelist);
            foreach ($filelist as $file) {
                if ($file == $selected) {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= "<option value=\"$file\"$sel>$file</option>";
            }
            break;
        case 'STYLE' :
            $dir = opendir('styles');
            while (false !== ($file = readdir($dir))) {
                if ($file != '.' && $file != '..' && $file != 'CVS') {
                    $filelist[] = str_replace('.css', '', $file);
                }
            }
            closedir($dir);
            sort($filelist);
            foreach ($filelist as $file) {
                if ($file == $selected) {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= "<option value=\"$file\"$sel>$file</option>";
            }
            break;
        case 'BUG_UNCONFIRMED' :
        case 'BUG_PROMOTED' :
        case 'BUG_ASSIGNED' :
        case 'BUG_REOPENED' :
        case 'BUG_CLOSED' :
            static $bug_status_list = array();

            if (empty($bug_status_list)) {
                $bug_status_list = $db->getAssoc("select status_id, status_name from " . TBL_STATUS . " order by status_name");
            }
            foreach ($bug_status_list as $id => $name) {
                $sel = $id == $selected ? ' selected' : '';
                $text .= "<option value=\"$id\"$sel>$name</option>";
            }
            break;
        case 'GROUP_ASSIGN_TO' :
            static $group_list = array();

            if (empty($group_list)) {
                $group_list = $db->getAssoc("select group_id, group_name from " . TBL_AUTH_GROUP . " order by group_name");
            }
            foreach ($group_list as $id => $name) {
                $sel = $id == $selected ? ' selected' : '';
                $text .= "<option value=\"$id\"$sel>$name</option>";
            }
            break;
        default :
            $deadarray = $select[$box];
            while (list($val, $item) = each($deadarray)) {
                if (is_array($selected) && count($selected) && in_array($val, $selected)) {
                    $sel = ' selected';
                } elseif ($selected == $val and $selected != '') {
                    $sel = ' selected';
                } else {
                    $sel = '';
                }
                $text .= "<option value=\"$val\"$sel>$item</option>";
            }
            break;
    }
    echo ($text);
}

///
/// Return human-friendly text for a value
function lookup($var, $val, $project = 0) {
    global $db;

    // create hash to map tablenames
    $cfgDatabase = array(
        'group' => TBL_AUTH_GROUP,
        'project' => TBL_PROJECT,
        'component' => TBL_COMPONENT,
        'status' => TBL_STATUS,
        'resolution' => TBL_RESOLUTION,
        'severity' => TBL_SEVERITY,
        'priority' => TBL_PRIORITY,
        'version' => TBL_VERSION,
        'database' => TBL_DATABASE,
        'site' => TBL_SITE,
        'os' => TBL_OS
    );

    switch ($var) {
        case 'reporter' :
        case 'assigned_to' :
            return maskemail($db->getOne("select login from " . TBL_AUTH_USER . " where user_id = " . $db->quote($val)));
        case 'version' :
            return $db->getOne("select {$var}_name from " . $cfgDatabase[$var] . " where project_id = " . $db->quote($project) . " and {$var}_id = " . $db->quote($val));
        default:
            return $db->getOne("select {$var}_name from " . $cfgDatabase[$var] . " where {$var}_id = " . $db->quote($val));
    }
}

///
/// Divide the results of a database query into multiple pages
function multipages($nr, $page, $urlstr) {
    global $me, $selrange, $t, $u, $db, $perm, $auth;

    $pages = '';
    if (!$page) {
        $page = 1;
    }
    if ($page == 'all') {
        $selrange = $nr;
        $llimit = 0;
        $page = 0;
    } else {
        if ($auth->is_authenticated()) {
            $selrange = $db->getOne('select def_results from ' . TBL_USER_PREF . ' where user_id = ' . $db->quote($u));
        }
        $llimit = ($page - 1) * $selrange;
    }
    if ($nr) {
        $npages = ceil($nr / $selrange);
    } else {
        $npages = 0;
    }
    if ($npages == 1) {
        $pages = 1;
    } else {
        for ($i = 1; $i <= $npages; $i++) {
            $pages .= $i != $page ? " <a href='$me?page=$i&amp;" . qry_amp($urlstr) . "'>$i</a> " : " $i ";
            $pages .= $i != $npages ? '|' : '';
        }
    }
    $t->assign(array(
        'pages' => $pages,
        'first' => $llimit + 1,
        'last' => $llimit + $selrange > $nr ? $nr : $llimit + $selrange,
        'total' => $nr
    ));

    return array($selrange, $llimit);
}

///
/// Sets variables in the templates for the column headers to sort database results
function sorting_headers($url, $headers, $order, $sort, $urlstr = '') {
    global $t;
    $theader = array();
    while (list($k, $v) = each($headers)) {
        $theader[$k]['url'] = "$url?order=$v&amp;sort=" .
                ($order == $v ? ($sort == 'asc' ? 'desc' : 'asc') : 'asc') .
                ($urlstr ? '&amp;' . $urlstr : '');
        $theader[$k]['color'] = $order == $v ? '#bbbbbb' : '#eeeeee';
        $theader[$k]['class'] = $order == $v ? 'selected' : '';
    }
    $t->assign('headers', $theader);
}

///
/// Generates a somewhat random pronounceable password $length letters long
/// (From zend.com user Rival7)
function genpassword($length) {

    srand((double) microtime() * 1000000);

    $vowels = array("a", "e", "i", "o", "u");
    $cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr", "cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");
    $password = '';

    $num_vowels = count($vowels);
    $num_cons = count($cons);

    for ($i = 0; $i < $length; $i++) {
        $password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)];
    }

    return substr($password, 0, $length);
}

///
/// Wrap text - Picked up somewhere on the net - probably zend.com
function textwrap($text, $wrap = 72, $break = "\n") {
    $len = strlen($text);
    if ($len > $wrap) {
        $h = '';
        $lastWhite = 0;
        $lastChar = 0;
        $lastBreak = 0;
        while ($lastChar < $len) {
            $char = substr($text, $lastChar, 1);
            if (($lastChar - $lastBreak > $wrap) && ($lastWhite > $lastBreak)) {
                $h .= substr($text, $lastBreak, ($lastWhite - $lastBreak)) . $break;
                $lastChar = $lastWhite + 1;
                $lastBreak = $lastChar;
            }
            /* You may wish to include other characters as  valid whitespace... */
            if ($char == ' ' || $char == chr(13) || $char == chr(10)) {
                $lastWhite = $lastChar;
            }
            $lastChar = $lastChar + 1;
        }
        $h .= substr($text, $lastBreak);
    } else {
        $h = $text;
    }
    return $h;
}

///
/// Return a delimited list if there is more than one element in $ary, otherwise
/// return the lone element as the list
function delimit_list($delimiter, $ary) {
    if (isset($ary[1])) {
        return join($delimiter, $ary);
    } elseif (isset($ary[0])) {
        return ($ary[0]);
    } else {
        return '';
    }
}

///
/// Check the validity of an email address
/// (From zend.com user russIndr)
function bt_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) == $email;
}

///
/// If the constant is set do a little email masking to make harvesting a little harder
function maskemail($email) {

    if (HIDE_EMAIL && empty($_SESSION['uid'])) {
        return '******';
    } elseif (MASK_EMAIL) {
        return str_replace('@', ' at ', str_replace('.', ' döt ', $email));
    } else {
        return $email;
    }
}

///
/// Build the javascript for the dynamic project -> component -> version select boxes
function build_project_js($no_all = false) {
    global $db, $u, $perm, $QUERY;

    $js = '';
    $js2 = '';

    // Build the javascript-powered select boxes
    if ($perm->have_perm('Admin')) {
        $rs = $db->query("select project_id, project_name from " . TBL_PROJECT . " where active = 1 order by project_name");
    } else {
        $rs = $db->query(sprintf($QUERY['functions-project-js'], @join(',', $_SESSION['group_ids'])));
    }
    $js = "closedversions['All'] = new Array(new Array('','All'),new Array('0','Not Set'));\n";
    while (list($pid, $pname) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
        $pname = addslashes($pname);
        // Version arrays
        $js .= "versions['$pname'] = new Array(" .
                ((!isset($no_all) or ! $no_all) ? "new Array('','All')," : '');
        $js2 = "closedversions['$pname'] = new Array(" .
                ((!isset($no_all) or ! $no_all) ? "new Array('','All'),new Array('0','Not Set')," : "new Array(0, 'Choose One'),");
        $rs2 = $db->query("select version_name, version_id from " . TBL_VERSION . " where project_id = " . $db->quote($pid) . " and active = 1");
        while (list($version, $vid) = $rs2->fetchRow(DB_FETCHMODE_ORDERED)) {
            $version = addslashes($version);
            $js .= "new Array($vid,'$version'),";
            $js2 .= "new Array($vid,'$version'),";
        }
        if (substr($js, -1) == ',') {
            $js = substr($js, 0, -1);
        }
        $js .= ");\n";
        if (substr($js2, -1) == ',') {
            $js2 = substr($js2, 0, -1);
        }
        $js2 .= ");\n";
        $js .= $js2;

        // Component array
        $js .= "components['$pname'] = new Array(";
        $js .= (!isset($no_all) || !$no_all) ? "new Array('','All')," : '';
        $rs2 = $db->query("select component_name, component_id from " . TBL_COMPONENT . " where project_id = " . $db->quote($pid) . " and active = 1");
        while (list($comp, $cid) = $rs2->fetchRow(DB_FETCHMODE_ORDERED)) {
            $comp = addslashes($comp);
            $js .= "new Array($cid,'$comp'),";
        }
        if (substr($js, -1) == ',')
            $js = substr($js, 0, -1);
        $js .= ");\n";
    }
    echo $js;
}

///
/// Database concat
function db_concat() {
    $pieces = func_get_args();

    switch (DB_TYPE) {
        case 'mysqli' :
        case 'mysql' : $retstr = 'concat(' . delimit_list(', ', $pieces) . ')';
            break;
        case 'pgsql' :
        case 'oci8' :
        case 'sybase' :
        case 'ibase' : $retstr = delimit_list(' || ', $pieces);
            break;
        case 'fbsql' : $retstr = 'CONCAT(' . delimit_list(', ', $pieces) . ')';
            break;
        default : $retstr = delimit_list(' + ', $pieces);
            break;
    }
    return $retstr;
}

// Dump a var
function dump($var, $title = '') {
    if ($title) {
        echo "<b>$title</b><br>";
    }
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

// Handle a database error
function handle_db_error(&$obj) {
    if (!defined('RAWERROR')) {
        define('RAWERROR', false);
    }
    if (!RAWERROR) {
        show_text('A database error has occurred');
    } else {
        show_text(htmlentities($obj->message) . '<br>' . htmlentities($obj->userinfo));
    }
    error_log($obj->message . " -- " . htmlentities($obj->userinfo));
    exit;
}

// Date() wrapper for smarty
function bt_date($string, $format) {
    return date($format, $string);
}

/* quoted-printable encoder function
  This encoding has all non-ascii (say >127, <32 and =61 chracters)
  encoded as "=" and it's hexadecimal value. Special case is space
  (32 decimal) at the end of line, which is converted to =20, other-
  wise it's not converted and it's returned as space (32 decimal). */

function qp_enc($input, $line_max = 76) {
    // Initialize variables
    $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
    $eol = "\n";
    $escape = "=";
    $output = "";
    // Do "dos2unix" and split $input into $lines by end of line
    $lines = split("\n", str_replace("\r\n", "\n", $input));
    // Loop throught $lines
    while (list(, $line) = each($lines)) {
        // Trim each line from right side
        $line = rtrim($line);
        // Place line length to $linlen
        $linlen = strlen($line);
        // Initialize $newline
        $newline = "";
        // Loop throught each line and process each character of the line
        for ($i = 0; $i < $linlen; $i++) {
            // Place each character of $line to $c
            $c = substr($line, $i, 1);
            // Place decimal value of $c to $dec
            $dec = ord($c);
            // If $c equals to space (" ") and we are at the end of line place
            // space (" ") to $c
            if (($dec == 32) && ($i == ($linlen - 1))) {
                $c = "=20";
            } elseif (($dec == 61) || ($dec < 32 ) || ($dec > 126)) {
                // Or if $c is not printable character in ascii, convert the
                // character to it's quoted-printable value
                $h2 = floor($dec / 16);
                $h1 = floor($dec % 16);
                $c = $escape . $hex["$h2"] . $hex["$h1"];
            }
            // If we are at the maximum line length, add whole line (converted)
            // with end of line character to $output
            if ((strlen($newline) + strlen($c)) >= $line_max) {
                $output .= $newline . $escape . $eol;
                // And initialize $newline as empty
                $newline = "";
            }
            // Add converted (or ascii) character to $newline
            $newline .= $c;
        }
        // Add $newline with end of line character to output
        $output .= $newline . $eol;
    }
    // Return trimmed output
    return (trim($output));
}

/**
 * Send mail to list of recipients. Works with UTF8
 *
 * @global <type> $pref_site_email
 * @global <type> $pref_site_email_name
 * @param <type> $to list of recipients separated by ;
 * @param <type> $subject
 * @param <type> $message
 * @return <type>
 */
function mass_mail4($to, $subject, $message, $from = ADMIN_EMAIL) {
    //global $pref_site_email, $pref_site_email_name;
    require_once './inc/class.phpmailer-lite.php';
    //require_once(CLASSPATH."libmail.php");
    $toarr = explode(';', $to);
    $res = true;
    foreach ($toarr as $toitem) {
        if (trim($toitem) <> '') {

            $mail = new PHPMailerLite(true); // the true param means it will throw exceptions on errors, which we need to catch
            //$mail->IsSendmail(); // telling the class to use SendMail transport
            $mail->IsMail(); // telling the class to use mail() transport
            $mail->CharSet = 'utf-8';
            try {
                $mail->SetFrom($from);
                $mail->AddAddress(trim($toitem));
                $mail->Subject = $subject;
                //$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
                //$mail->MsgHTML(file_get_contents('contents.html'));
                //$mail->AddAttachment('images/phpmailer.gif');      // attachment
                //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
                $mail->Body = $message;
                //$mail->Priority = $priority;
                //if ($attach != null) {
                //    throw new Exception('Attach not supported');
                //}
                $mail->Send();
                //echo "Message Sent OK</p>\n";
            } catch (phpmailerException $e) {
                //echo $e->errorMessage(); //Pretty error messages from PHPMailer
                $res = false;
                error_log($e->errorMessage());
            } catch (Exception $e) {
                //echo $e->getMessage(); //Boring error messages from anything else!
                $res = false;
                error_log($e->getMessage());
            }
        }
    }
    return $res;
}

// mailer with use of quoted-printable encoding (if configured so)
function qp_mail($to, $subject = 'No subject', $body = '', $from = ADMIN_EMAIL) {
    global $STRING;

    require_once('./inc/htmlMimeMail/htmlMimeMail.php');
    $mail = new htmlMimeMail();

    $mail->setTextCharset('utf-8');
    $mail->setHtmlCharset('utf-8');
    $mail->setHeadCharset('utf-8');

    $mail->setSubject($subject);
    $mail->setFrom($from);
    $mail->setReturnPath(RETURN_PATH);
    $recipient[] = $to;

    if (SEND_MIME_EMAIL) {
        // If configured to send MIME encoded emails
        if (false/* HTML_EMAIL */) {
            $mail->setHtmlEncoding("quoted-printable");
            $mail->setHtml($body);
        } else {
            $mail->setTextEncoding("quoted-printable");
            $mail->setText($body);
        }
    } else {
        $mail->setTextEncoding("8bit");
        $mail->setText($body);
    }

    if (SMTP_EMAIL) {
        $mail->setSMTPParams(SMTP_HOST, SMTP_PORT, SMTP_HELO, SMTP_AUTH, SMTP_AUTH_USER, SMTP_AUTH_PASS);
    }

    $retval = $mail->send($recipient, SMTP_EMAIL ? 'smtp' : 'mail');

    // Returns true if mail is accepted for delivery, otherwise return false
    return ($retval);
}

function translate($string, $plural = false) {
    global $STRING;

    if (USE_GETTEXT) {
        return $plural ? ngettext($string) : gettext($string);
    } else {
        if (!empty($STRING[$string])) {
            return $STRING[$string];
        } else {
            return $string;
        }
    }
}

// Generate a testable WHERE expression for closed bugs
function in_closed($column) {
    global $db;

    $closed_statuses = array(0);

    foreach ($db->getAll('SELECT status_id FROM ' . TBL_STATUS . ' WHERE bug_open = 0') as $row) {
        $closed_statuses[] = (int) $row['status_id'];
    }

    return '(' . $column . ' in (' . (@join(', ', $closed_statuses)) . '))';
}

// Check whether or not a status-id means BUG_CLOSED
function is_closed($status_id) {
    global $db;

    if ($db->getOne('SELECT status_id FROM ' . TBL_STATUS . ' WHERE bug_open = 0 AND status_id = ' . $db->quote($status_id))) {
        return true;
    } else {
        return false;
    }
}

// Check to make sure a bug is numeric
function check_id($id) {
    if (!is_numeric($id)) {
        show_text("Invalid ID");
        exit;
    }
    return $id;
}

// Delete a bug and all associated records from the database
function delete_bug($bug_id) {
    global $db, $perm;

    // Permissions
    $projectid = $db->getOne("select project_id" .
            " from " . TBL_BUG . " b" .
            " where b.bug_id = " . $db->quote($bug_id));
    $perm->check_proj($projectid);

    // Attachments
    $attary = $db->getAll("select file_name, project_id" .
            " from " . TBL_ATTACHMENT . " a, " . TBL_BUG . " b" .
            " where a.bug_id = b.bug_id and b.bug_id = " . $db->quote($bug_id));
    foreach ($attary as $att) {
        @unlink(join('/', array(ATTACHMENT_PATH,
                    $att['project_id'], "$bug_id-{$att['file_name']}")));
    }
    $db->query("delete from " . TBL_ATTACHMENT . " where bug_id = " . $db->quote($bug_id));

    // CCs
    $db->query("delete from " . TBL_BUG_CC . " where bug_id = " . $db->quote($bug_id));

    // Comments
    $db->query("delete from " . TBL_COMMENT . " where bug_id = " . $db->quote($bug_id));

    // Dependencies
    $db->query("delete from " . TBL_BUG_DEPENDENCY .
            " where bug_id = " . $db->quote($bug_id) . " or depends_on = " . $db->quote($bug_id));

    // Groups
    $db->query("delete from " . TBL_BUG_GROUP . " where bug_id = " . $db->quote($bug_id));

    // Histories
    $db->query("delete from " . TBL_BUG_HISTORY . " where bug_id = " . $db->quote($bug_id));

    // Votes
    $db->query("delete from " . TBL_BUG_VOTE . " where bug_id = " . $db->quote($bug_id));

    // And the bug itself
    $db->query("delete from " . TBL_BUG . " where bug_id = " . $db->quote($bug_id));
}

/**
 * Determine whether include files are available
 *
 * @param string $file The name of the file to be included, with full path
 * @return bool
 */
function find_include($file) {
    //Incompatible with PHP <4.3.0:
    //  foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path) { 
    //See http://ru.php.net/get_include_path
    foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path) {
        if (@file_exists("$path/$file")) {
            return true;
        }
    }
    return false;
}

/**
 * Polyfill for PHP 5.4's http_response_code() function.
 *
 * https://gist.github.com/inxilpro/6320414
 */
if (!function_exists('http_response_code')) {

    function http_response_code($code = null) {
        static $defaultCode = 200;

        if (null != $code) {
            switch ($code) {
                case 100: $text = 'Continue';
                    break;                          // RFC2616
                case 101: $text = 'Switching Protocols';
                    break;                          // RFC2616
                case 102: $text = 'Processing';
                    break;                          // RFC2518

                case 200: $text = 'OK';
                    break;                          // RFC2616
                case 201: $text = 'Created';
                    break;                          // RFC2616
                case 202: $text = 'Accepted';
                    break;                          // RFC2616
                case 203: $text = 'Non-Authoritative Information';
                    break;                          // RFC2616
                case 204: $text = 'No Content';
                    break;                          // RFC2616
                case 205: $text = 'Reset Content';
                    break;                          // RFC2616
                case 206: $text = 'Partial Content';
                    break;                          // RFC2616
                case 207: $text = 'Multi-Status';
                    break;                          // RFC4918
                case 208: $text = 'Already Reported';
                    break;                          // RFC5842
                case 226: $text = 'IM Used';
                    break;                          // RFC3229

                case 300: $text = 'Multiple Choices';
                    break;                          // RFC2616
                case 301: $text = 'Moved Permanently';
                    break;                          // RFC2616
                case 302: $text = 'Found';
                    break;                          // RFC2616
                case 303: $text = 'See Other';
                    break;                          // RFC2616
                case 304: $text = 'Not Modified';
                    break;                          // RFC2616
                case 305: $text = 'Use Proxy';
                    break;                          // RFC2616
                case 306: $text = 'Reserved';
                    break;                          // RFC2616
                case 307: $text = 'Temporary Redirect';
                    break;                          // RFC2616
                case 308: $text = 'Permanent Redirect';
                    break;                          // RFC-reschke-http-status-308-07

                case 400: $text = 'Bad Request';
                    break;                          // RFC2616
                case 401: $text = 'Unauthorized';
                    break;                          // RFC2616
                case 402: $text = 'Payment Required';
                    break;                          // RFC2616
                case 403: $text = 'Forbidden';
                    break;                          // RFC2616
                case 404: $text = 'Not Found';
                    break;                          // RFC2616
                case 405: $text = 'Method Not Allowed';
                    break;                          // RFC2616
                case 406: $text = 'Not Acceptable';
                    break;                          // RFC2616
                case 407: $text = 'Proxy Authentication Required';
                    break;                          // RFC2616
                case 408: $text = 'Request Timeout';
                    break;                          // RFC2616
                case 409: $text = 'Conflict';
                    break;                          // RFC2616
                case 410: $text = 'Gone';
                    break;                          // RFC2616
                case 411: $text = 'Length Required';
                    break;                          // RFC2616
                case 412: $text = 'Precondition Failed';
                    break;                          // RFC2616
                case 413: $text = 'Request Entity Too Large';
                    break;                          // RFC2616
                case 414: $text = 'Request-URI Too Long';
                    break;                          // RFC2616
                case 415: $text = 'Unsupported Media Type';
                    break;                          // RFC2616
                case 416: $text = 'Requested Range Not Satisfiable';
                    break;                          // RFC2616
                case 417: $text = 'Expectation Failed';
                    break;                          // RFC2616
                case 422: $text = 'Unprocessable Entity';
                    break;                          // RFC4918
                case 423: $text = 'Locked';
                    break;                          // RFC4918
                case 424: $text = 'Failed Dependency';
                    break;                          // RFC4918
                case 426: $text = 'Upgrade Required';
                    break;                          // RFC2817
                case 428: $text = 'Precondition Required';
                    break;                          // RFC6585
                case 429: $text = 'Too Many Requests';
                    break;                          // RFC6585
                case 431: $text = 'Request Header Fields Too Large';
                    break;                          // RFC6585

                case 500: $text = 'Internal Server Error';
                    break;                          // RFC2616
                case 501: $text = 'Not Implemented';
                    break;                          // RFC2616
                case 502: $text = 'Bad Gateway';
                    break;                          // RFC2616
                case 503: $text = 'Service Unavailable';
                    break;                          // RFC2616
                case 504: $text = 'Gateway Timeout';
                    break;                          // RFC2616
                case 505: $text = 'HTTP Version Not Supported';
                    break;                          // RFC2616
                case 506: $text = 'Variant Also Negotiates';
                    break;                          // RFC2295
                case 507: $text = 'Insufficient Storage';
                    break;                          // RFC4918
                case 508: $text = 'Loop Detected';
                    break;                          // RFC5842
                case 510: $text = 'Not Extended';
                    break;                          // RFC2774
                case 511: $text = 'Network Authentication Required';
                    break;                          // RFC6585

                default:
                    $code = 500;
                    $text = 'Internal Server Error';
            }

            $defaultCode = $code;

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
        }

        return $defaultCode;
    }

}

function die_ex($msg) {
    if (defined('DEBUG_FL') && (DEBUG_FL == 1)) {
        throw new Exception($msg);
    } else {
        die($msg);
    }
}

function qry_amp($ipReqStr) {
    return str_replace('&', '&amp;', $ipReqStr);
}

function gen_ref($ipRef, $ipVarArr) {
    $res = $ipRef;
    $i = 0;
    $keys = array_keys($ipVarArr);
    foreach ($keys as $k) {
        if ($ipVarArr[$k] != null) {
            if ($i == 0) {
                $res .= '?';
            } else {
                $res .= '&';
            }
            $res .= $k . '=' . $ipVarArr[$k];
            $i = 1;
        }
    }
    return $res;
}

/// add parameter to request string
/// if $ipReqStr == 'r' - its - $_REQUEST['QUERY_STRING']
function add_param($ipReqStr, $ipParName, $ipParVal) {
    $str = $ipReqStr;
    if ($str == 'r') {
        //print_r($_SERVER);
        $str = $_SERVER['REQUEST_URI'];
    }
    if (strpos($str, '?') > 0) {
        $url = substr($str, 0, strpos($str, '?'));
        $paramstr = substr($str, strpos($str, '?') + 1);
        $partmp = explode('&', $paramstr);
        $paramarr = array();
        foreach ($partmp as $tmp) {
            $paramarr[substr($tmp, 0, strpos($tmp, '='))] = substr($tmp, strpos($tmp, '=') + 1);
        }
        $paramarr[$ipParName] = $ipParVal;
        $res = gen_ref($url, $paramarr);
    } else {
        $res = $str . '?' . $ipParName . '=' . $ipParVal;
    }
    return $res;
}

function add_paramsa($ipReqStr, $ipParams) {
    if (!is_array($ipParams)) {
        die('add_paramsa: Second argument must be array');
    }
    $keys = array_keys($ipParams);
    $res = $ipReqStr;
    foreach ($keys as $key) {
        $res = add_param($res, $key, $ipParams[$key]);
    }
    return $res;
}

function get_array_value($ipArray, $ipKeyName, $ipDefault = null) {
    if (array_key_exists($ipKeyName, $ipArray)) {
        return $ipArray[$ipKeyName];
    } else {
        return $ipDefault;
    }
}

function get_request_value($ipKeyName, $ipDefault = null) {
    return get_array_value($_REQUEST, $ipKeyName, $ipDefault);
}

function get_post_str($ipKeyName, $ipDefault = null) {
    $val = filter_input(INPUT_POST, $ipKeyName, FILTER_SANITIZE_STRING);
    if ($val === null) { // not set
        if (($ipDefault === false)) {
            die_ex('Required value not defined for: ' . htmlspecialchars($ipKeyName));
        } else {
            $val = $ipDefault;
        }
    }
    return $val;
}

function get_post_val($ipKeyName, $ipDefault = null) {
    $val = filter_input(INPUT_POST, $ipKeyName);
    if ($val === null) { // not set
        if (($ipDefault === false)) {
            die_ex('Required value not defined for: ' . htmlspecialchars($ipKeyName));
        } else {
            $val = $ipDefault;
        }
    }
    return $val;
}

function check_numeric_die($ipValue) {
    if (!is_numeric($ipValue)) {
        if (!headers_sent()) {
            http_response_code(400);
        }
        die('Invalid value');
    }
    return $ipValue;
}

function get_request_int($ipKeyName, $ipDefault = false) {
    $val = get_request_value($ipKeyName, $ipDefault);
    if (($val == '') && ($ipDefault !== false)) {
        return $ipDefault;
    }
    if (($val === false) || (!is_numeric($val)) || (!is_int(0 + $val))) {
        if (!headers_sent()) {
            http_response_code(400);
        }
        die_ex('Invalid parameter value for: ' . htmlspecialchars($ipKeyName));
    }
    return $val;
}

function get_get_int($ipKeyName, $ipDefault = false) {
    return get_input_int(INPUT_GET, $ipKeyName, $ipDefault);
}

function get_post_int($ipKeyName, $ipDefault = false) {
    return get_input_int(INPUT_POST, $ipKeyName, $ipDefault);
}

function get_input_int($ipInputType, $ipKeyName, $ipDefault = false) {
    if (($ipInputType == INPUT_GET) || ($ipInputType == INPUT_POST)) {
        $valNull = filter_input($ipInputType, $ipKeyName);
        if (($valNull == '') && ($ipDefault !== false)) {
            return $ipDefault;
        }
        $val = filter_input($ipInputType, $ipKeyName, FILTER_VALIDATE_INT);
        if ($val === false) {
            if (!headers_sent()) {
                http_response_code(400);
            }
            die_ex('Invalid parameter value for: ' . htmlspecialchars($ipKeyName));
        } else if ($val === null) {
            if ($ipDefault === false) {
                if (!headers_sent()) {
                    http_response_code(400);
                }
                die_ex('Invalid parameter value for: ' . htmlspecialchars($ipKeyName));
            } else {
                $val = $ipDefault;
            }
        }
    } else {
        throw new Exception('unsupported input type');
    }
    return $val;
}
