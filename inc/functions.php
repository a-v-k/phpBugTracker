<?php

// functions.php - Set up global functions
// ------------------------------------------------------------------------
// Copyright (c) 2001, 2002 The phpBugTracker Group
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
// $Id: functions.php,v 1.34 2002/09/14 19:03:48 bcurtis Exp $

///
/// Show text to the browser - escape hatch
function show_text($text, $iserror = false) {
  global $t;

	$t->assign(array(
		'text' => $text,
		'iserror' => $iserror
		));
	$t->wrap('error.html');
}

$select['priority'] = array(
  1 => '1 - Low',
  2 => '2',
  3 => '3 - Medium',
  4 => '4',
  5 => '5 - High'
  );

///
/// Build a select box with the item matching $value selected
function build_select($params) {
    global $db, $select, $perm, $STRING, $restricted_projects, $QUERY;

    extract($params);
	if (!isset($selected)) {
		$selected = '';
	}

    // create hash to map tablenames
	$cfgDatabase = array(
		'group' => TBL_AUTH_GROUP,
		'project' => TBL_PROJECT,
		'component' => TBL_COMPONENT,
		'status' => TBL_STATUS,
		'resolution' => TBL_RESOLUTION,
		'severity' => TBL_SEVERITY,
		'version' => TBL_VERSION,
		'database' => TBL_DATABASE,
		'site' => TBL_SITE
    	);

    $text = '';

    if (isset($cfgDatabase[$box])) {
		$querystart = "select {$box}_id, {$box}_name from $cfgDatabase[$box]";
		$querymid = ' where sort_order > 0 order by sort_order';
		$queries = array(
	    	'group' => $querystart.' where group_name <> \'User\' order by group_name',
	    	'severity' => $querystart.$querymid,
	    	'site' => $querystart.$querymid,
	    	'status' => $querystart.$querymid,
	    	'resolution' => $querystart.$querymid,
	    	'project' => $perm->have_perm('Admin')
			? $querystart." where ".
		    	($selected ? "(active > 0 or project_id in ($selected))" : 'active > 0').
		    	" order by {$box}_name"
			: $querystart." where project_id not in ($restricted_projects)".
		    	" and ".
		    	($selected ? " (active > 0 or project_id in ($selected))" : 'active > 0').
		    	" order by {$box}_name",
	    	'component' => $querystart." where project_id = $project and active = 1 order by {$box}_name",
	    	'version' => $querystart." where project_id = $project and active = 1 order by {$box}_id desc",
	    	'database' => $querystart.$querymid
			);
    }

    switch($box) {
	case 'user_filter':
	    foreach ($STRING['user_filter'] as $k => $v) {
			$text .= sprintf("<option value=\"%d\"%s>%s</option>",
			$k, ($k == $selected ? ' selected' : ''), $v);
	    }
		break;
	case 'group':
	    if ($project) { // If we are building for project admin page
			if (!count($selected) or (count($selected) && in_array(0, $selected))) {
		    	$sel = ' selected';
			} else {
		    	$sel = '';
			}
			$text = "<option value=\"all\"$sel>All Groups</option>";
	    }
	    $rs = $db->query($queries[$box]);
	    while ($rs->fetchInto($row)) {
			if (count($selected) && in_array($row[$box.'_id'], $selected)) {
		    	$sel = ' selected';
			} else {
		    	$sel = '';
			}
			$text .= '<option value="'.
			$row[$box.'_id']."\"$sel>".$row[$box.'_name'].'</option>';
	    }
		break;
	case 'database': $text = '<option value="0">None</option>';
	case 'severity':
	case 'status':
	case 'resolution':
	case 'project':
	case 'site':
	case 'component':
	case 'version':
	    $rs = $db->query($queries[$box]);
	    while ($rs->fetchInto($row)) {
			if ($selected == $row[$box.'_id'] and $selected != '') {
		    	$sel = ' selected';
			} else {
		    	$sel = '';
			}
			$text .= '<option value="'.
			$row[$box.'_id']."\"$sel>".$row[$box.'_name'].'</option>';
	    }
		break;
	case 'os':
	    $rs = $db->query("select {$box}_id, {$box}_name, regex from ".TBL_OS." where sort_order > 0 order by sort_order");
	    while ($rs->fetchInto($row)) {
			if ($selected == '' and isset($row['Regex']) and
		    	preg_match($row['Regex'],$GLOBALS['HTTP_USER_AGENT'])) {
		    	$sel = ' selected';
			} elseif ($selected == $row[$box.'_id']) {
		    	$sel = ' selected';
			} else {
		    	$sel = '';
			}
			$text .= '<option value="'.$row[$box.'_id']."\"$sel>".$row[$box.'_name']."</option>";
	    }
		break;
	case 'owner':
	    $rs = $db->query("select u.user_id, login from ".TBL_AUTH_USER." u, ".TBL_USER_GROUP." ug, ".TBL_AUTH_GROUP." g where u.active > 0 and u.user_id = ug.user_id and ug.group_id = g.group_id and group_name = 'Developer' order by login");
	    while ($rs->fetchInto($row)) {
			if ($selected == $row['user_id']) {
		    	$sel = ' selected';
			} else {
		    	$sel = '';
			}
			$text .= "<option value=\"{$row['user_id']}\"$sel>{$row['login']}</option>";
	    }
		break;
	case 'bug_cc':
		$rs = $db->query(sprintf($QUERY['functions-bug-cc'], $selected));
		while (list($uid, $user) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
			$text .= "<option value=\"$uid\">".maskemail($user).'</option>';
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
		static $bug_status_list = array();

		if (empty($bug_status_list)) {
			$bug_status_list = $db->getAssoc("select status_id, status_name".
				" from ".TBL_STATUS." order by status_name");
		}
		foreach ($bug_status_list as $id => $name) {
			$sel = $id == $selected ? ' selected' : '';
			$text .= "<option value=\"$id\"$sel>$name</option>";
		}
		break;
	default :
	    $deadarray = $select[$box];
	    while(list($val,$item) = each($deadarray)) {
			if ($selected == $val and $selected != '') {
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
/// Divide the results of a database query into multiple pages
function multipages($nr, $page, $urlstr) {
  global $me, $selrange, $t;

  $pages = '';
  if (!$page) $page = 1;
  if ($page == 'all') {
    $selrange = $nr;
    $llimit = 0;
    $page = 0;
  } else {
    #$selrange = 60;
    $llimit = ($page-1)*$selrange;
  }
  if ($nr) $npages = ceil($nr/$selrange);
  else $npages = 0;
  if ($npages == 1) $pages = 1;
  else {
    for ($i=1; $i<=$npages; $i++) {
      $pages .= $i != $page ? " <a href='$me?page=$i&$urlstr'>$i</a> " : " $i ";
      $pages .= $i != $npages ? '|' : '';
    }
  }
	$t->assign(array(
		'pages' => $pages,
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'total' => $nr
		));

  return array($selrange, $llimit);
}

///
/// Sets variables in the templates for the column headers to sort database results
function sorting_headers($url, $headers, $order, $sort, $urlstr = '') {
  global $t;

  while(list($k, $v) = each($headers)) {
		$theader[$k]['url'] = "$url?order=$v&sort=".
      ($order == $v ? ($sort == 'asc' ? 'desc' : 'asc') : 'asc').
      ($urlstr ? '&'.$urlstr : '');
    $theader[$k]['color'] = $order == $v ? '#bbbbbb' : '#eeeeee';
    $theader[$k]['class'] = $order == $v ? 'selected' : '';
  }
	$t->assign('headers', $theader);
}

///
/// Generates a somewhat random pronounceable password $length letters long
/// (From zend.com user Rival7)
function genpassword($length){

    srand((double)microtime()*1000000);

    $vowels = array("a", "e", "i", "o", "u");
    $cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr", "cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");
    $password = '';

    $num_vowels = count($vowels);
    $num_cons = count($cons);

    for($i = 0; $i < $length; $i++){
        $password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)];
    }

    return substr($password, 0, $length);
}

///
/// Wrap text - Picked up somewhere on the net - probably zend.com
function textwrap($text, $wrap=72, $break="\n"){
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
      if ($char == ' ' || $char == chr(13) || $char == chr(10))
        $lastWhite = $lastChar;
      $lastChar = $lastChar + 1;
    }
    $h .= substr($text, $lastBreak);
  }
  else $h = $text;
  return $h;
}

///
/// Return a delimited list if there is more than one element in $ary, otherwise
/// return the lone element as the list
function delimit_list($delimiter, $ary) {
  if (isset($ary[1])) return join($delimiter, $ary);
  elseif (isset($ary[0])) return ($ary[0]);
  else return '';
}

///
/// Check the validity of an email address
/// (From zend.com user russIndr)
function bt_valid_email($email) {
  return eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$', $email);
}

///
/// If the constant is set do a little email masking to make harvesting a little harder
function maskemail($email) {
  global $_sv;

  if (HIDE_EMAIL && empty($_sv['uid'])) {
    return '******';
  } elseif (MASK_EMAIL) {
    return str_replace('@', ' at ', str_replace('.', ' dot ', $email));
  } else {
    return $email;
  }
}

///
/// Build the javascript for the dynamic project -> component -> version select boxes
function build_project_js($params) {
	global $db, $u, $perm, $_sv, $QUERY;

	extract($params);
	$js = ''; $js2 = '';

	// Build the javascript-powered select boxes
	if ($perm->have_perm('Admin')) {
		$rs = $db->query("select project_id, project_name from ".TBL_PROJECT.
			" where active = 1 order by project_name");
	} else {
		$rs = $db->query(sprintf($QUERY['functions-project-js'],
			delimit_list(',', $_sv['group_ids'])));
	}
	while (list($pid, $pname) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
		$pname = addslashes($pname);
		// Version arrays
		$js .= "versions['$pname'] = new Array(".
			((!isset($no_all) or !$no_all) ? "new Array('','All')," : '');
		$js2 = "closedversions['$pname'] = new Array(".
			((!isset($no_all) or !$no_all) ? "new Array('','All'),"
				: "new Array(0, 'Choose One'),");
		$rs2 = $db->query("select version_name, version_id from ".TBL_VERSION.
			" where project_id = $pid and active = 1");
		while (list($version,$vid) = $rs2->fetchRow(DB_FETCHMODE_ORDERED)) {
			$version = addslashes($version);
			$js .= "new Array($vid,'$version'),";
			$js2 .= "new Array($vid,'$version'),";
		}
		if (substr($js,-1) == ',') $js = substr($js,0,-1);
		$js .= ");\n";
		if (substr($js2,-1) == ',') $js2 = substr($js2,0,-1);
		$js2 .= ");\n";
		$js .= $js2;

		// Component array
		$js .= "components['$pname'] = new Array(";
		$js .= (!isset($no_all) || !$no_all) ? "new Array('','All')," : '';
		$rs2 = $db->query("select component_name, component_id from ".TBL_COMPONENT.
			" where project_id = $pid and active = 1");
		while (list($comp,$cid) = $rs2->fetchRow(DB_FETCHMODE_ORDERED)) {
			$comp = addslashes($comp);
			$js .= "new Array($cid,'$comp'),";
		}
		if (substr($js,-1) == ',') $js = substr($js,0,-1);
		$js .= ");\n";
	}
	echo $js;
}

///
/// Database concat
function db_concat() {
	$pieces = func_get_args();

	switch(DB_TYPE) {
		case 'mysql' : $retstr = 'concat('. delimit_list(', ', $pieces).')'; break;
		case 'pgsql' :
		case 'oci8' :
		case 'sybase' :
		case 'ibase' : $retstr = delimit_list(' || ', $pieces); break;
		case 'fbsql' : $retstr = 'CONCAT('. delimit_list(', ', $pieces).')'; break;
		default : $retstr = delimit_list(' + ', $pieces); break;
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
	die($obj->message.'<br>'.$obj->userinfo);
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
    $hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
    $eol = "\n";
    $escape = "=";
    $output = "";
    // Do "dos2unix" and split $input into $lines by end of line
    $lines = split("\n", str_replace("\r\n", "\n", $input));
    // Loop throught $lines
    while( list(, $line) = each($lines) ) {
	// Trim each line from right side
	$line = rtrim($line);
	// Place line length to $linlen
	$linlen = strlen($line);
	// Initialize $newline
	$newline = "";
	// Loop throught each line and process each character of the line
	for($i = 0; $i < $linlen; $i++) {
	    // Place each character of $line to $c
	    $c = substr($line, $i, 1);
	    // Place decimal value of $c to $dec
	    $dec = ord($c);
	    // If $c equals to space (" ") and we are at the end of line place
	    // space (" ") to $c
	    if (($dec == 32) && ($i == ($linlen - 1))) {
		$c = "=20";
	    } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) {
		// Or if $c is not printable character in ascii, convert the
		// character to it's quoted-printable value
		$h2 = floor($dec/16); $h1 = floor($dec%16);
        	$c = $escape.$hex["$h2"].$hex["$h1"];
	    }
	    // If we are at the maximum line length, add whole line (converted)
	    // with end of line character to $output
	    if ( (strlen($newline) + strlen($c)) >= $line_max ) {
		$output .= $newline.$escape.$eol;
		// And initialize $newline as empty
		$newline = "";
	    }
	    // Add converted (or ascii) character to $newline
	    $newline .= $c;
	}
	// Add $newline with end of line character to output
	$output .= $newline.$eol;
    }
    // Return trimmed output
    return (trim($output));
}

// mailer with use of quoted-printable encoding (if configured so)
function qp_mail($to, $subject = 'No subject', $body, $headers = '') {
    global $STRING;

    if ($headers != '') {
        $headers .= "\n";
        // There have to be no newline at the end of $headers
    }
    $headers .= "Content-Type: text/plain; charset=\"".$STRING['lang_charset']."\"\nContent-Transfer-Encoding: ";

    // If configured to send MIME encoded emails
    if (SEND_MIME_EMAIL) {
	$retval = mail ($to, $subject, qp_enc($body), $headers.
	    "quoted-printable\nMIME-Version: 1.0");
    } else {
	$retval = mail ($to, $subject, $body, $headers.
	    "8bit");
    }

    // Returns true if mail is eccepted for delivery, otherwise return false
    return ($retval);
}

?>
