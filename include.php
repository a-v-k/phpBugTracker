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

define ('INSTALLPATH','/home/bcurtis/public_html/phpbt');
define ('INSTALLURL','http://localhost/~bcurtis/phpbt');
// Location of phplib -- only necessary if you can't define your include path
define ('PHPLIBPATH',''); 
define ('ONEDAY',86400);
define ('DATEFORMAT','m-d-Y');
define ('TIMEFORMAT','g:i A');
define ('ADMINEMAIL','phpbt@bencurtis.com');
define ('ENCRYPTPASS',0);  // Whether to store passwords encrypted
define ('THEME','default/'); // Which set of templates to use
define ('USE_JPGRAPH',0); // Whether to show images or not
define ('JPGRAPH_PATH', '/home/bcurtis/public_html/jp/'); // If it's not in the include path
define ('MASK_EMAIL', 1); // Should email addresses be plainly visible?
define ('HIDE_EMAIL', 1); // Should email addresses be hidden for those not logged in?
// Sub-dir of the INSTALLPATH - Needs to be writeable by the web process
define ('ATTACHMENT_PATH', 'attachments'); 
require PHPLIBPATH.'db_mysql.inc';
require PHPLIBPATH.'ct_sql.inc';
require PHPLIBPATH.'session.inc';
require PHPLIBPATH.'auth.inc';
require PHPLIBPATH.'perm.inc';
require PHPLIBPATH.'page.inc';
require PHPLIBPATH.'template.inc';

// Localization - include the file with the desired language
include INSTALLPATH.'/strings-en.php';

// Edit this class with your database information
class dbclass extends DB_Sql {
	var $classname = 'dbclass';
	var $Host = 'localhost';
	var $Database = 'BugTracker';
	var $User = 'root';
	var $Password = '';

	function grab($q_string = '') {
		if ($q_string) $this->query($q_string);
		$this->next_record();
		return $this->Record;
	}

	function grab_field($q_string = '') {
		list($retval) = $this->grab($q_string);
		return $retval;
	}
}

$q = new dbclass;
$cssfile = 'global.css';
$me = $PHP_SELF;
$me2 = $REQUEST_URI;
$selrange = 30;
$now = time();
$_gv = $HTTP_GET_VARS;
$_pv = $HTTP_POST_VARS;

$select['authlevels'] = array(
	0 => 'Inactive',
	1 => 'User',
	3 => 'Developer',
	7 => 'Manager',
	15 => 'Administrator'
	);

class sqlclass extends CT_Sql {
	var $database_class = 'dbclass';
	var $database_table = 'active_sessions';
}

class usess extends Session {
	var $classname = 'usess';
	var $magic = 'gerdisbad';
	var $mode = 'cookie';
	#var $fallback_mode = 'get';
	var $lifetime = 0;
	var $that_class = 'sqlclass';
	var $allowcache = 'jl';
}

class uauth extends Auth {
	var $classname = 'uauth';
	var $lifetime = 0;
	var $magic = 'looneyville';
	var $nobody = true;
	
	function auth_loginform() {
		global $sess;
		
		include 'templates/'.THEME.'login.html';
		
	}
	
	function auth_validatelogin() {
		global $username, $password, $q, $select, $emailpass, $emailsuccess, $STRING;
		
		if (!$username) return 'nobody';
		$this->auth['uname'] = $username;
		if (ENCRYPTPASS) {
			$password = md5($password);
		}
		$u = $q->grab("select * from User where Email = '$username' and Password = '$password' and UserLevel > 0");
		if (!$q->num_rows()) {
			return 'nobody';
		} else {
			$this->auth['fname'] = $u['FirstName'];
			$this->auth['lname'] = $u['LastName'];
			$this->auth['email'] = $u['Email'];
			$this->auth['perm'] = $select['authlevels'][$u['UserLevel']];
			return $u['UserID'];
		}
	}
}

class uperm extends Perm {
	var $classname = 'uperm';
	var $permissions = array(
		'Inactive' => 0,
		'User' => 1,
		'Developer' => 3,
		'Manager' => 7,
		'Administrator' => 15,
		);
	
	function perm_invalid() {	
		global $t, $auth;
		$t->set_file('content','badperm.html');
		$t->pparse('main',array('content','wrap','main'));
	}
}

class templateclass extends Template {
	function pparse($target, $handle, $append = false) {
		global $auth, $perm;
		
		$this->set_block('wrap', 'logoutblock', 'loblock');
		$this->set_block('wrap', 'loginblock', 'liblock');
		$this->set_block('wrap', 'adminnavblock', 'anblock');
		if ($auth->auth['uid'] && $auth->auth['uid'] != 'nobody') {
			$this->set_var(array(
				'loggedinas' => $auth->auth['email'],
				'liblock' => ''
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

$t = new templateclass('templates/'.THEME,'keep');
$t->set_var(array(
	'TITLE' => '', 
	'me' => $PHP_SELF,
	'me2' => $REQUEST_URI,
	'error' => '',
	'cssfile' => $cssfile,
	'loginerror' => ''));
	
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
function build_select($box, $value='',$project=0) {
	#static $select;
	global $q, $select;

	//include_once "select.php";
	switch($box) {
		case 'Severity' :
		case 'Status' :
		case 'Resolution' :
			$q->query("Select {$box}ID, Name from $box where SortOrder order by SortOrder");
			while ($row = $q->grab()) {
				if ($value == $row[$box.'ID'] and $value != '') $sel = ' selected';
				else $sel = '';
				$text .= '<option value="'.$row[$box.'ID']."\"$sel>{$row['Name']}</option>";
			}
			break;
		case 'Project' :
			$q->query("Select {$box}ID, Name from $box where Active order by Name");
			while ($row = $q->grab()) {
				if ($value == $row[$box.'ID'] and $value != '') $sel = ' selected';
				else $sel = '';
				$text .= '<option value="'.$row[$box.'ID']."\"$sel>{$row['Name']}</option>";
			}
			break;
		case 'Component' :
			$q->query("Select {$box}ID, Name from $box where ProjectID = $project order by Name");
			while ($row = $q->grab()) {
				if ($value == $row[$box.'ID'] and $value != '') $sel = ' selected';
				else $sel = '';
				$text .= '<option value="'.$row[$box.'ID']."\"$sel>{$row['Name']}</option>";
			}
			break;
		case 'OS' :
			$q->query("Select {$box}ID, Name, Regex from $box order by SortOrder");
			while ($row = $q->grab()) {
				if ($value == '' and $row['Regex'] and 
					preg_match($row['Regex'],$GLOBALS['HTTP_USER_AGENT'])) $sel = ' selected';
				elseif ($value == $row[$box.'ID']) $sel = ' selected';
				else $sel = '';
				$text .= '<option value="'.$row[$box.'ID']."\"$sel>{$row['Name']}</option>";
			}
			break;
		case 'Version' :
			$q->query("Select {$box}ID, Name from $box where ProjectID = $project order by Name");
			while ($row = $q->grab()) {
				if ($value == $row[$box.'ID']) $sel = ' selected';
				else $sel = '';
				$text .= '<option value="'.$row[$box.'ID']."\"$sel>{$row['Name']}</option>";
			}
			break;
		case 'owner' :
			$q->query("Select UserID, Email from User where UserLevel > 1 order by Email");
			while ($row = $q->grab()) {
				if ($value == $row['UserID']) $sel = ' selected';
				else $sel = '';
				$text .= '<option value="'.$row['UserID']."\"$sel>{$row['Email']}</option>";
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
/// Convert a date from from MM/DD/YYYY to epoch seconds
function convert_date($date) {
	$temp = explode('/',$date);
	//return join('-',array($temp[2],$temp[0],$temp[1])); - MySQL format
	return mktime(0,0,0,$temp[0],$temp[1],$temp[2]);
}

///
/// Check the format of a date entered
function bad_date($date) {
	return !ereg('[0-9]{2}/[0-9]{2}/[0-9]{4,4}',$date);
}

///
/// Divide the results of a database query into multiple pages
function multipages($nr, $page, $urlstr) {
	global $me, $selrange;
	
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
/// Returns true if the HTTP method is POST
function posted_form() {
	return ($GLOBALS['REQUEST_METHOD'] == 'POST');
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
	}
}

///
/// Generates a somewhat random pronounceable password $length letters long
/// (From zend.com user Rival7)
function genpassword($length){ 

		srand((double)microtime()*1000000); 

		$vowels = array("a", "e", "i", "o", "u"); 
		$cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr",

		"cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl"); 

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
			/* You may wish to include other characters as	valid whitespace... */
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
	if ($ary[1]) return join($delimiter, $ary);
	elseif ($ary[0]) return ($ary[0]);
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
	
	if (HIDE_EMAIL && $auth->auth['uid'] == 'nobody') {
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
	$u = $auth->auth['uid'];
}

// Check to see if the user is trying to login
if (isset($HTTP_POST_VARS['login'])) {
	if (isset($HTTP_POST_VARS['sendpass'])) {
		list($email, $password) = $q->grab("select Email, Password from User where Email = '$username' and UserLevel > 0");
		if (!$q->num_rows()) { 
			$t->set_var(array(
				'loginerrorcolor' => '#ff0000',
				'loginerror' => 'Invalid login<br>'
				));
		} else {
			if (ENCRYPTPASS) {
				$password = genpassword(10);
				$mpassword = md5($password);
				$q->query("update User set Password = '$mpassword' where Email = '$username'");
			}
			mail($email, $STRING['newacctsubject'], sprintf($STRING['newacctmessage'], 
				$password),	'From: '.ADMINEMAIL);
			$t->set_var(array(
				'loginerrorcolor' => '#0000ff',
				'loginerror' => 'Your password has been emailed to you<br>'
				));
		}
	} else {
		$auth->auth['uid'] = $auth->auth_validatelogin();
		if ($auth->auth['uid'] == 'nobody') {
			$t->set_var(array(
				'loginerrorcolor' => '#ff0000',
				'loginerror' => 'Invalid login<br>'
				));
		}
	}
}

?>
