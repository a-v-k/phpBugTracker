<?php

// include.php - Set up global variables
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
// $Id: include.php,v 1.125 2002/10/18 17:48:34 bcurtis Exp $

ini_set("magic_quotes_runtime", 0);
ini_set("magic_quotes_sybase", 0);

if (!@include('config.php')) {
  header("Location: install.php");
  exit();
}
if (!defined('DB_HOST')) { // Installation hasn't been completed
  header("Location: install.php");
  exit();
}

// Grab the global functions
include ('inc/functions.php');

// PEAR::DB
define('PEAR_PATH', 'inc/pear/'); // Set this to '' to use system-wide PEAR
require_once(PEAR_PATH.'DB.php');
$dsn = array(
	'phptype' => DB_TYPE,
	'hostspec' => DB_HOST,
	'database' => DB_DATABASE,
	'username' => DB_USER,
	'password' => DB_PASSWORD
	);
$db = DB::Connect($dsn);
if (DB::isError($db)) {
	die($db->message.'<br>'.$db->userinfo);
}
$db->setOption('optimize', 'portability');
$db->setFetchMode(DB_FETCHMODE_ASSOC);
$db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_db_error");

// Set up the configuration variables
$rs = $db->query('select varname, varvalue from '.TBL_CONFIGURATION);
while (list($k, $v) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
  define($k, $v);
}
define('OPEN_BUG_STATUSES', join(', ', array(BUG_UNCONFIRMED, BUG_PROMOTED,
	BUG_ASSIGNED, BUG_REOPENED)));

require_once ('inc/db/'.DB_TYPE.'.php');

// Localization - include the file with the desired language
include 'languages/'.LANGUAGE.'.php';

$me = $HTTP_SERVER_VARS['PHP_SELF'];
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
  'closed_in_version_name' => 'Closed in Version',
  'to_be_closed_in_version_name' => 'To be Closed in Version',
  'database_name' => 'Database',
  'site_name' => 'Site',
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


// Template class
if (!@include(SMARTY_PATH . 'Smarty.class.php')) {
	include('templates/default/base/smartymissing.html');
	exit;
}

class extSmarty extends Smarty {

	function fetch($_smarty_tpl_file, $_smarty_cache_id = null, $_smarty_compile_id = null, $_smarty_display = false) {
		error_reporting(E_ALL ^ E_NOTICE); // Clobber Smarty warnings
		return Smarty::fetch($_smarty_tpl_file, $_smarty_cache_id, $_smarty_compile_id, $_smarty_display);
	}

	function wrap($template, $title = '') {
		global $TITLE, $_gv, $_pv;

		$this->assign(array(
			'content_template' => $template,
			'page_title' => isset($TITLE[$title]) ? $TITLE[$title] : $title
			));

		// Use a popup wrap?
		if ((isset($_gv['use_js']) and $_gv['use_js']) or
			(isset($_pv['use_js']) and $_pv['use_js'])) {
			$wrap = 'wrap-popup.html';
		} else {
			$wrap = 'wrap.html';
		}
		if (($dir = dirname($template)) != '.') {
			$this->display("$dir/$wrap");
		} else {
			$this->display($wrap);
		}
	}
}

$t = new extSmarty;
$t->template_dir = 'templates/'.THEME.'/';
$t->compile_dir = 'c_templates';
$t->config_dir = '.';
$t->use_sub_dirs = false;
$t->register_function('build_select', 'build_select');
$t->register_function('project_js', 'build_project_js');
$t->register_modifier('date', 'bt_date');
$t->assign(array(
	'STRING' => $STRING,
	'TITLE' => $TITLE,
	'STYLE' => STYLE
	));

if (defined('TEMPLATE_PATH')) {
	$t->assign('template_path', '../templates/'.THEME.'/'.TEMPLATE_PATH);
} else {
	$t->assign('template_path', 'templates/'.THEME);
}

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
      $t->assign('loginerror', '<div class="error">Invalid login</div>');
    } else {
      if (ENCRYPT_PASS) {
        $password = genpassword(10);
        $mpassword = md5($password);
        $db->query("update ".TBL_AUTH_USER." set password = '$mpassword' where login = '$username'");
      }
      qp_mail($email, $STRING['newacctsubject'], sprintf($STRING['newacctmessage'], $password),
        sprintf("From: %s",ADMIN_EMAIL));
      $t->assign('loginerror',
        '<div class="result">Your password has been emailed to you</div>');
			$emailsuccess = true;
    }
  } else {
    if (!$u = $auth->auth_validatelogin()) {
      $t->assign('loginerror', '<div class="error">Invalid login</div>');
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

if (!empty($u)) {
  list($owner_open, $owner_closed) =
		$db->getRow(sprintf($QUERY['include-template-owner'], $u),
			DB_FETCHMODE_ORDERED);
  list($reporter_open, $reporter_closed) =
		$db->getRow(sprintf($QUERY['include-template-reporter'], $u),
			DB_FETCHMODE_ORDERED);
  $t->assign(array(
    'owner_open' => $owner_open ? $owner_open : 0,
    'owner_closed' => $owner_closed ? $owner_closed : 0,
    'reporter_open' => $reporter_open ? $reporter_open : 0,
    'reporter_closed' => $reporter_closed ? $reporter_closed : 0,
		'perm' => $perm,
    ));
}

if (defined('FORCE_LOGIN') and FORCE_LOGIN and !$u and !defined('NO_AUTH')) {
	include('templates/'.THEME.'/login.html');
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
