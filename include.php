<?php

// include.php - Set up global variables and functions
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
// $Id: include.php,v 1.79 2001/12/01 19:51:20 bcurtis Exp $

define ('INSTALL_PATH', dirname($HTTP_SERVER_VARS['SCRIPT_FILENAME']));
if (!defined('INCLUDE_PATH')) {
	define('INCLUDE_PATH', '');
}

include (INSTALL_PATH.'/'.INCLUDE_PATH.'config.php');
if (!defined('DB_HOST')) { // Installation hasn't been completed
	header("Location: install.php");
	exit();
}

class dbclass extends DB_Sql {
  var $classname = 'dbclass';
  var $Host = DB_HOST;
  var $Database = DB_DATABASE;
  var $User = DB_USER;
  var $Password = DB_PASSWORD;
	var $Seq_Table = TBL_DB_SEQUENCE;
  var $Seq_ID_Col    = "nextid";    
  var $Seq_Name_Col  = "seq_name";  
	
	// Attempt to handle different limit syntax
	function limit_query($q_string, $limit, $offset = 0) {
		if ($offset) {
			if (DB_TYPE == 'pgsql') {
				$this->query("$q_string limit $limit offset $offset");
			} else {
				$this->query("$q_string limit $offset, $limit");
			}
		} else {
			$this->query("$q_string limit $limit");
		}
	}		

	// Handle the different types of concats
	function concat() {
		$pieces = func_get_args();
		if (DB_TYPE == 'pgsql') {
			return delimit_list(' || ', $pieces);
		} else {
			return 'concat('. delimit_list(', ', $pieces).')';
		}
	}
	
  function grab($q_string = '') {
    if ($q_string) $this->query($q_string);
    $this->next_record();
    return $this->Record;
  }

  function grab_field($q_string = '') {
    list($retval) = $this->grab($q_string);
    return $retval;
  }
	
	function nextid($seq_name) {
		global $auth;
		
		if ($seq_name == TBL_SAVED_QUERY) {
			if ($id = $this->grab_field("select max(saved_query_id)+1 from ".TBL_SAVED_QUERY." where user_id = ".$auth->auth['uid'])) {
				return $id;
			} else {
				return 1;
			}
		} else {
			return DB_Sql::nextid($seq_name);
		}
	}
}

$q = new dbclass;

//include INSTALL_PATH.'/'.INCLUDE_PATH.'inc/adodb/adodb.inc.php';
//$db =& ADONewConnection(DB_TYPE);
//$db->PConnect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

// Set up the configuration variables
$q->query('select varname, varvalue from '.TBL_CONFIGURATION);
while (list($k, $v) = $q->grab()) {
	define($k, $v);
}

// Localization - include the file with the desired language
include INSTALL_PATH.'/'.INCLUDE_PATH.'languages/'.LANGUAGE.'.php';

$me = $HTTP_SERVER_VARS['PHP_SELF'];
$me2 = $HTTP_SERVER_VARS['REQUEST_URI'];
$selrange = 30;
$now = time();
$_gv = &$HTTP_GET_VARS;
$_pv = &$HTTP_POST_VARS;

$all_db_fields = array(
  'bug_id' => 'ID',
  'title' => 'Title',
  'description' => 'Description',
  'url' => 'URL',
  'severity_name' => 'Severity',
  'priority' => 'Priority',
  'status_name' => 'Status',
  'resolution_name' => 'Resolution',
  'reporter' => 'Reporter',
  'owner' => 'Owner',
  'created_date' => 'Created Date',
  'lastmodifier' => 'Last Modified By',
  'last_modified_date' => 'Last Modified Date',
  'project_name' => 'Project',
  'version_name' => 'Version',
  'component_name' => 'Component',
  'os_name' => 'OS',
  'browser_string' => 'Browser',
  'close_date' => 'Closed Date'
  );

$default_db_fields = array('bug_id', 'title', 'reporter', 'owner',
  'severity_name', 'priority', 'status_name', 'resolution_name');

class usess extends Session {
  var $classname = 'usess';
  var $lifetime = 0;
  var $allowcache = '';
}

class templateclass extends Template {
  function pparse($target, $handle, $append = false) {
    global $auth, $perm, $q;

    $u = isset($auth->auth['uid']) ? $auth->auth['uid'] : 0;
    $this->set_block('wrap', 'logoutblock', 'loblock');
    $this->set_block('wrap', 'loginblock', 'liblock');
    $this->set_block('wrap', 'adminnavblock', 'anblock');
    if ($u) {
      list($owner_open, $owner_closed) = $q->grab("SELECT sum(CASE WHEN status_name in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END ) ,"
				."sum(CASE WHEN status_name not in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END )"
				."from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) where assigned_to = $u");
      list($reporter_open, $reporter_closed) = $q->grab("SELECT sum(CASE WHEN status_name in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END ) ,"
				."sum(CASE WHEN status_name not in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END )"
				."from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) where created_by = $u");
      $this->set_var(array(
        'loggedinas' => $auth->auth['uname'],
        'liblock' => '',
        'owner_open' => $owner_open ? $owner_open : 0,
        'owner_closed' => $owner_closed ? $owner_closed : 0,
        'reporter_open' => $reporter_open ? $reporter_open : 0,
        'reporter_closed' => $reporter_closed ? $reporter_closed : 0
        ));
      $this->parse('loblock', 'logoutblock', true);
    } else {
      $this->set_var(array(
        'loggedinas' => '',
        'loblock' => ''
        ));
      $this->parse('liblock', 'loginblock', true);
    }
    if (isset($perm) && $perm->have_perm('Administrator')) {
      $this->parse('anblock', 'adminnavblock', true);
    } else {
      $this->set_var('anblock', '');
    }
    print $this->finish($this->parse($target, $handle, $append));
    return false;
  }
}

if (INCLUDE_PATH == '../') {
	$t = new templateclass(INCLUDE_PATH.'templates/'.THEME.'/admin', 'keep');
} else {
	$t = new templateclass('templates/'.THEME.'/', 'keep');
}

$t->set_var(array(
  'TITLE' => '',
  'me' => $me,
  'me2' => $me2,
  'error' => '',
  'loginerror' => '',
	'template_path' => INCLUDE_PATH.'templates/'.THEME));

// End classes -- Begin helper functions

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
  global $q, $select;

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
    	'project' => $querystart." where active > 0 order by {$box}_name",
    	'component' => $querystart." where project_id = $project order by {$box}_name",
    	'version' => $querystart." where project_id = $project order by {$box}_name"
    	);
	}

  switch($box) {
    case 'group' :
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
      $q->query("select {$box}_id, {$box}_name, regex from ".TBL_OS." order by sort_order");
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
			$dir = opendir(INSTALL_PATH.'/'.INCLUDE_PATH.'languages');
			while (false !== ($file = readdir($dir))) {
				if ($file != '.' && $file != '..' && $file != 'CVS') {
					$file = str_replace('.php', '', $file);
					if ($file == $value) {
						$sel = ' selected';
					} else {
						$sel = '';
					}
					$text .= "<option value=\"$file\"$sel>$file</option>";
				}
			}
			break;
		case 'THEME' :
			$dir = opendir(INSTALL_PATH.'/'.INCLUDE_PATH.'templates');
			while (false !== ($file = readdir($dir))) {
				if ($file != '.' && $file != '..' && $file != 'CVS') {
					$file = str_replace('.php', '', $file);
					if ($file == $value) {
						$sel = ' selected';
					} else {
						$sel = '';
					}
					$text .= "<option value=\"$file\"$sel>$file</option>";
				}
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

  if (HIDE_EMAIL && !$auth->auth['uid']) {
    return '******';
  } elseif (MASK_EMAIL) {
    return str_replace('@', ' at ', str_replace('.', ' dot ', $email));
  } else {
    return $email;
  }
}

// Begin every page with a page_open
if (!defined('NO_AUTH')) {
  page_open(array('sess' => 'usess', 'auth' => 'uauth', 'perm' => 'uperm'));
  $u = isset($auth->auth['uid']) ? $auth->auth['uid'] : 0;
}

// Check to see if the user is trying to login
if (isset($HTTP_POST_VARS['dologin'])) {
  if (isset($HTTP_POST_VARS['sendpass'])) {
    list($email, $password) = $q->grab("select email, password from ".TBL_AUTH_USER." where login = '$username' and active > 0");
    if (!$q->num_rows()) {
      $t->set_var(array(
        'loginerrorcolor' => '#ff0000',
        'loginerror' => 'Invalid login<br>'
        ));
    } else {
      if (ENCRYPT_PASS) {
        $password = genpassword(10);
        $mpassword = md5($password);
        $q->query("update ".TBL_AUTH_USER." set password = '$mpassword' where login = '$username'");
      }
      mail($email, $STRING['newacctsubject'], sprintf($STRING['newacctmessage'],
        $password),  sprintf("From: %s\nContent-Type: text/plain; charset=%s\nContent-Transfer-Encoding: 8bit\n",ADMIN_EMAIL, $STRING['lang_charset']));
      $t->set_var(array(
        'loginerrorcolor' => '#0000ff',
        'loginerror' => 'Your password has been emailed to you<br>'
        ));
    }
  } else {
    $auth->auth['uid'] = $auth->auth_validatelogin();
    if (!$auth->auth['uid']) {
      $t->set_var(array(
        'loginerrorcolor' => '#ff0000',
        'loginerror' => 'Invalid login<br>'
        ));
    }
  }
}

?>
