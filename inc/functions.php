<?php

// functions.php - Set up global functions
// ------------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
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
// $Id: functions.php,v 1.7 2002/03/17 01:36:43 bcurtis Exp $

///
/// Show text to the browser - escape hatch
function show_text($text, $iserror = false) {
  global $t;

  $t->set_file('content','error.html');
  if (!$iserror) $t->set_var('text',$text);
  else $t->set_var('text',"<font color=red>$text</font>");
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
function build_select($box, $value = '', $project = 0) {
  global $q, $select, $perm, $auth, $STRING, $restricted_projects;

  //create hash to map tablenames
  $cfgDatabase = array(
    'group' => TBL_AUTH_GROUP,
    'project' => TBL_PROJECT,
    'component' => TBL_COMPONENT,
    'status' => TBL_STATUS,
    'resolution' => TBL_RESOLUTION,
    'severity' => TBL_SEVERITY,
    'version' => TBL_VERSION
  );

  $text = '';
  if (isset($cfgDatabase[$box])) {
    $querystart = "select {$box}_id, {$box}_name from $cfgDatabase[$box]";
    $queries = array(
      'group' => $querystart.' where group_name <> \'User\' order by group_name',
      'severity' => $querystart.' where sort_order > 0 order by sort_order',
      'status' => $querystart.' where sort_order > 0 order by sort_order',
      'resolution' => $querystart.' where sort_order > 0 order by sort_order',
      'project' => $perm->have_perm('Admin')
        ? $querystart." where active > 0 order by {$box}_name"
        : $querystart." where project_id not in ($restricted_projects)".
					" and active > 0 order by {$box}_name",
      'component' => $querystart." where project_id = $project order by {$box}_name",
      'version' => $querystart." where project_id = $project order by {$box}_id desc"
      );
  }

  switch($box) {
		case 'user_filter' : 
			foreach ($STRING['user_filter'] as $k => $v) {
				$text .= sprintf("<option value=\"%d\"%s>%s</option>",
					$k, ($k == $value ? ' selected' : ''), $v);
			}
			break;
    case 'group' :
      if ($project) { // If we are building for project admin page
        if (!count($value) or (count($value) && in_array(0, $value))) {
          $sel = ' selected';
        } else {
          $sel = '';
        }
        $text = "<option value=\"all\"$sel>All Groups</option>";
      }
      $q->query($queries[$box]);
      while ($row = $q->grab()) {
        if (count($value) && in_array($row[$box.'_id'], $value)) $sel = ' selected';
        else $sel = '';
        $text .= '<option value="'.
          $row[$box.'_id']."\"$sel>".$row[$box.'_name'].'</option>';
      }
      break;
    case 'severity' :
    case 'status' :
    case 'resolution' :
    case 'project' :
    case 'component' :
    case 'version' :
      $q->query($queries[$box]);
      while ($row = $q->grab()) {
        if ($value == $row[$box.'_id'] and $value != '') $sel = ' selected';
        else $sel = '';
        $text .= '<option value="'.
          $row[$box.'_id']."\"$sel>".$row[$box.'_name'].'</option>';
      }
      break;
    case 'os' :
      $q->query("select {$box}_id, {$box}_name, regex from ".TBL_OS." where sort_order > 0 order by sort_order");
      while ($row = $q->grab()) {
        if ($value == '' and isset($row['Regex']) and
          preg_match($row['Regex'],$GLOBALS['HTTP_USER_AGENT'])) $sel = ' selected';
        elseif ($value == $row[$box.'_id']) $sel = ' selected';
        else $sel = '';
        $text .= '<option value="'.
          $row[$box.'_id']."\"$sel>".$row[$box.'_name']."</option>";
      }
      break;
    case 'owner' :
      $q->query("select u.user_id, login from ".TBL_AUTH_USER." u, ".TBL_USER_GROUP." ug, ".TBL_AUTH_GROUP." g where u.active > 0 and u.user_id = ug.user_id and ug.group_id = g.group_id and group_name = 'Developer' order by login");
      while ($row = $q->grab()) {
        if ($value == $row['user_id']) $sel = ' selected';
        else $sel = '';
        $text .= "<option value=\"{$row['user_id']}\"$sel>{$row['login']}</option>";
      }
      break;
    case 'bug_cc' :
      $q->query('select b.user_id, login from '.TBL_BUG_CC.' b left join '.
        TBL_AUTH_USER." using(user_id) where bug_id = $value");
      while (list($uid, $user) = $q->grab()) {
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
      $dir = opendir(INSTALL_PATH.'/languages');
      while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..' && $file != 'CVS') {
          $filelist[] = str_replace('.php', '', $file);
				}
			}
			closedir($dir);
			sort($filelist);
			foreach ($filelist as $file) {
        if ($file == $value) {
          $sel = ' selected';
        } else {
          $sel = '';
        }
        $text .= "<option value=\"$file\"$sel>$file</option>";
      }
      break;
    case 'THEME' :
      $dir = opendir(INSTALL_PATH.'/templates');
      while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..' && $file != 'CVS') {
          $filelist[] = str_replace('.php', '', $file);
				}
			}
			closedir($dir);
			sort($filelist);
			foreach ($filelist as $file) {
        if ($file == $value) {
          $sel = ' selected';
        } else {
          $sel = '';
        }
        $text .= "<option value=\"$file\"$sel>$file</option>";
      }
      break;
    default :
      $deadarray = $select[$box];
      while(list($val,$item) = each($deadarray)) {
        if ($value == $val and $value != '') $sel = ' selected';
        else $sel = '';
        $text .= "<option value=\"$val\"$sel>$item</option>";
      }
      break;
  }
  return $text;
}

///
/// Divide the results of a database query into multiple pages
function multipages($nr, $page, $urlstr) {
  global $me, $selrange;

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
  return array($selrange, $llimit, $npages, $pages);
}

///
/// Sets variables in the templates for the column headers to sort database results
function sorting_headers($url, $headers, $order, $sort, $urlstr = '') {
  global $t;

  while(list($k, $v) = each($headers)) {
    $t->set_var($k.'url', "$url?order=$v&sort=".
      ($order == $v ? ($sort == 'asc' ? 'desc' : 'asc') : 'asc').
      ($urlstr ? '&'.$urlstr : ''));
    $t->set_var($k.'color', $order == $v ? '#bbbbbb' : '#eeeeee');
    $t->set_var($k.'class', $order == $v ? 'head-selected' : 'head');
  }
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
function valid_email($email) {
  return eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $email);
}

///
/// If the constant is set do a little email masking to make harvesting a little harder
function maskemail($email) {
  global $auth;

  if (HIDE_EMAIL && empty($auth->auth['uid'])) {
    return '******';
  } elseif (MASK_EMAIL) {
    return str_replace('@', ' at ', str_replace('.', ' dot ', $email));
  } else {
    return $email;
  }
}

///
/// Build the javascript for the dynamic project -> component -> version select boxes
function build_project_js() {
	global $q, $u, $perm, $auth;
	
	$nq = new dbclass;
	$js = '';
	
	// Build the javascript-powered select boxes
	if ($perm->have_perm('Admin')) {
		$q->query("select project_id, project_name from ".TBL_PROJECT.
			" where active = 1 order by project_name");
	} else {
		$q->query('select p.project_id, project_name from '.TBL_PROJECT.
			' p left join '.TBL_PROJECT_GROUP.' pg using(project_id) 
			where active = 1 and (pg.project_id is null or pg.group_id in ('.
			delimit_list(',', $auth->auth['group_ids']).')) group by 
			p.project_id, p.project_name order by project_name');
	}
	while (list($pid, $pname) = $q->grab()) {
		$pname = addslashes($pname);
		// Version array
		$js .= "versions['$pname'] = new Array(new Array('','All'),";
		$nq->query("select version_name, version_id from ".TBL_VERSION.
			" where project_id = $pid and active = 1");
		while (list($version,$vid) = $nq->grab()) {
			$version = addslashes($version);
			$js .= "new Array($vid,'$version'),";
		}
		if (substr($js,-1) == ',') $js = substr($js,0,-1);
		$js .= ");\n";
		
		// Component array
		$js .= "components['$pname'] = new Array(new Array('','All'),";
		$nq->query("select component_name, component_id from ".TBL_COMPONENT.
			" where project_id = $pid and active = 1");
		while (list($comp,$cid) = $nq->grab()) {
			$comp = addslashes($comp);
			$js .= "new Array($cid,'$comp'),";
		}
		if (substr($js,-1) == ',') $js = substr($js,0,-1);
		$js .= ");\n";
	}
	return $js;
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
  return $retstr
}

?>
