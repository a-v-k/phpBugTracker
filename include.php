<?php

// include.php - Set up global variables
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
// $Id: include.php,v 1.104 2002/03/28 22:14:44 bcurtis Exp $

ini_set("magic_quotes_runtime", 0); 

// Where are we?
define ('INSTALL_PATH', dirname(__FILE__));

if (!@include (INSTALL_PATH.'/config.php')) {
  header("Location: install.php");
  exit();
}
if (!defined('DB_HOST')) { // Installation hasn't been completed
  header("Location: install.php");
  exit();
}

// Grab the global functions
include (INSTALL_PATH.'/inc/functions.php');

// PEAR::DB
require_once('DB.php');
$dsn = array(
	'phptype' => DB_TYPE,
	'hostspec' => DB_HOST,
	'database' => DB_DATABASE,
	'username' => DB_USER,
	'password' => DB_PASSWORD
	);
$db = DB::Connect($dsn);
$db->setOption('optimize', 'portability');
$db->setFetchMode(DB_FETCHMODE_ASSOC);
$db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_db_error");

// Set up the configuration variables
$rs = $db->query('select varname, varvalue from '.TBL_CONFIGURATION);
while (list($k, $v) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
  define($k, $v);
}

// Localization - include the file with the desired language
include INSTALL_PATH.'/languages/'.LANGUAGE.'.php';

$me = $HTTP_SERVER_VARS['PHP_SELF'];
$me2 = !empty($HTTP_SERVER_VARS['REQUEST_URI']) ? $HTTP_SERVER_VARS['REQUEST_URI'] : 
	$HTTP_SERVER_VARS['SCRIPT_NAME'].'?'.$HTTP_SERVER_VARS['QUERY_STRING'];
$selrange = 30;
$now = time();
$_gv =& $HTTP_GET_VARS;
$_pv =& $HTTP_POST_VARS;

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

class templateclass extends Template {
  function pparse($target, $handle, $append = false) {
    global $_sv, $perm, $db, $HTTP_COOKIE_VARS;

    $u = isset($_sv['uid']) ? $_sv['uid'] : 0;
    $this->set_block('wrap', 'logoutblock', 'loblock');
    $this->set_block('wrap', 'loginblock', 'liblock');
    $this->set_block('wrap', 'adminnavblock', 'anblock');
    if ($u) {
      list($owner_open, $owner_closed) = $db->getRow("SELECT sum(CASE WHEN status_name in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END ) ,"
        ."sum(CASE WHEN status_name not in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END )"
        ."from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) where assigned_to = $u",
				DB_FETCHMODE_ORDERED);
      list($reporter_open, $reporter_closed) = $db->getRow("SELECT sum(CASE WHEN status_name in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END ) ,"
        ."sum(CASE WHEN status_name not in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END )"
        ."from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) where created_by = $u",
				DB_FETCHMODE_ORDERED);
      $this->set_var(array(
        'loggedinas' => $_sv['uname'],
        'liblock' => '',
        'owner_open' => $owner_open ? $owner_open : 0,
        'owner_closed' => $owner_closed ? $owner_closed : 0,
        'reporter_open' => $reporter_open ? $reporter_open : 0,
        'reporter_closed' => $reporter_closed ? $reporter_closed : 0
        ));
      $this->parse('loblock', 'logoutblock', true);
    } else {
    	$this->set_block('loginblock', 'cookieblock', 'ckblock');
      $this->set_var(array(
        'loggedinas' => '',
        'loblock' => '',
        'loginlabel' => EMAIL_IS_LOGIN ? 'Email' : 'Login'
        ));
			if (RECALL_LOGIN) {
				if (!empty($HTTP_COOKIE_VARS['phpbt_user'])) {
					$this->set_var(array(
						'cookielogin' => $HTTP_COOKIE_VARS['phpbt_user'],
						'cookiechecked' => 'checked'
						));
				} else {
					$this->set_var(array(
						'cookielogin' => '',
						'cookiechecked' => ''
						));
				}
				$this->parse('ckblock', 'cookieblock', true);
			} else {
				$this->set_var(array(
					'cookielogin' => '',
					'ckblock' => ''
					));
			}
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

if (defined('TEMPLATE_PATH')) {
  $t = new templateclass(INSTALL_PATH.'/templates/'.THEME.'/'.TEMPLATE_PATH, 'keep');
	$t->set_var('template_path', '../templates/'.THEME.'/'.TEMPLATE_PATH);
} else {
  $t = new templateclass(INSTALL_PATH.'/templates/'.THEME, 'keep');
	$t->set_var('template_path', 'templates/'.THEME);
}

$t->set_var(array(
  'TITLE' => '',
  'me' => $me,
  'me2' => $me2,
  'error' => '',
  'loginerror' => ''));

// End classes -- Begin page

if (!defined('NO_AUTH')) {
  session_start();
  $_sv =& $HTTP_SESSION_VARS;
  $auth = new uauth;
  $perm = new uperm;
  $u = isset($_sv['uid']) ? $_sv['uid'] : 0;
}

// Check to see if the user is trying to login
if (isset($_pv['dologin'])) {
  if (!empty($_pv['sendpass'])) {
		$username = $_pv['username'];
    list($email, $password) = $db->getRow("select email, password from ".TBL_AUTH_USER." where login = '{$_pv['username']}' and active > 0", null, DB_FETCHMODE_ORDERED);
    if (!$email) {
      $t->set_var('loginerror', '<div class="error">Invalid login</div>');
    } else {
      if (ENCRYPT_PASS) {
        $password = genpassword(10);
        $mpassword = md5($password);
        $db->query("update ".TBL_AUTH_USER." set password = '$mpassword' where login = '$username'");
      }
      mail($email, $STRING['newacctsubject'], sprintf($STRING['newacctmessage'],
        $password),  sprintf("From: %s\nContent-Type: text/plain; charset=%s\nContent-Transfer-Encoding: 8bit\n",ADMIN_EMAIL, $STRING['lang_charset']));
      $t->set_var('loginerror',
        '<div class="result">Your password has been emailed to you</div>');
			$emailsuccess = true;
    }
  } else {
    if (!$u = $auth->auth_validatelogin()) {
      $t->set_var('loginerror', '<div class="error">Invalid login</div>');
			$username = $_pv['username'];
    }
  }

	// "Remember me" handling
	if (RECALL_LOGIN) {
		if (!empty($_pv["savecookie"])) {
			setcookie('phpbt_user', $_pv["username"], $now + 18144000); // 3 week expiration
		} elseif (!empty($HTTP_COOKIE_VARS['phpbt_user'])) {
			// Clear the cookie if the cookie is populated and the box wasn't checked
			setcookie('phpbt_user');
		}
	}
		
}

if (defined('FORCE_LOGIN') and FORCE_LOGIN and !$u and !defined('NO_AUTH')) {
	include(INSTALL_PATH.'/templates/'.THEME.'/login.html');
	exit;
}

$op = isset($_gv['op']) ? $_gv['op'] : (isset($_pv['op']) ? $_pv['op'] : '');

if (!defined('NO_AUTH')) {
	// Check to see if we have projects that shouldn't be visible to the user
	$restricted_projects = '0';
	if (!$perm->have_perm('Admin')) {
		$viewable_projects = delimit_list(',', 
			$db->getCol("select project_id from ".TBL_PROJECT_GROUP.
				" where group_id in (".delimit_list(',', $_sv['group_ids']).")"));
		$viewable_projects = $viewable_projects ? $viewable_projects : '0';
		$matching_projects = delimit_list(',', 
			$db->getCol("select project_id from ".TBL_PROJECT_GROUP.
				" where project_id not in ($viewable_projects) group by project_id"));
		if ($matching_projects) {
			$restricted_projects .= ",$matching_projects";
		}
	}
}

?>
