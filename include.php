<?php

// include.php - Set up global variables and functions

define ('INSTALLPATH','/home/bcurtis/public_html/phpbt');
define ('INSTALLURL','http://localhost/~bcurtis/phpbt');
// Location of phplib -- only necessary if you can't define your include path
define ('PHPLIBPATH',''); 
define ('ONEDAY',86400);
define ('DATEFORMAT','m-d-Y');
define ('TIMEFORMAT','g:i A');
define ('ADMINEMAIL','phpbt@bencurtis.com');

require PHPLIBPATH.'db_mysql.inc';
require PHPLIBPATH.'ct_sql.inc';
require PHPLIBPATH.'session.inc';
require PHPLIBPATH.'auth.inc';
require PHPLIBPATH.'perm.inc';
require PHPLIBPATH.'page.inc';
require PHPLIBPATH.'template.inc';

// Localization - include the file with the desired language
include INSTALLPATH.'/strings-en.php';

$cssfile = 'global.css';


$me = $PHP_SELF;
$me2 = $REQUEST_URI;
$selrange = 30;
$now = time();
$select['authlevels'] = array(
	0 => 'Inactive',
	1 => 'User',
	3 => 'Developer',
	7 => 'Manager',
	15 => 'Administrator'
	);


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
	
	function auth_loginform() {
		global $sess;
		
		include 'templates/login.html';
		
	}
	
	function auth_validatelogin() {
		global $username, $password, $q, $select, $emailpass, $emailsuccess, $STRING;
		
		if (!$username) return false;
		if ($emailpass) {
			list($email, $password) = $q->grab("select Email, Password from User where Email = '$username' and UserLevel > 0");
			if (!$q->num_rows()) {echo 'bob'; return false;}
			mail($email, $STRING['newacctsubject'], sprintf($STRING['newacctmessage'], 
				$password),	'From: '.ADMINEMAIL);
			$emailsuccess = true;
			return false;
		}
		$this->auth['uname'] = $username;
		$u = $q->grab("select * from User where Email = '$username' and Password = '$password' and UserLevel > 0");
		if (!$q->num_rows()) return false;
		else {
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
		global $auth;
		
		$this->set_block('wrap', 'logoutblock', 'lblock');
		if ($auth->auth['uid']) {
			$this->set_var('loggedinas', $auth->auth['email']);
			$this->parse('lblock', 'logoutblock', true);
		} else {
			$this->set_var(array(
				'loggedinas' => '',
				'lblock' => ''
				));
		}
		print $this->finish($this->parse($target, $handle, $append));
		return false;
	}
}

$t = new templateclass('templates','keep');
$t->set_var(array(
	'TITLE' => '', 
	'me' => $PHP_SELF,
	'error' => '',
	'cssfile' => $cssfile));
	
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
			$q->query("Select UserID, Email from User where UserLevel order by Email");
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
/// (From zend.com)
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
/// (From zend.com)
function valid_email($email) {		 
	return eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $email); 
}

?>
