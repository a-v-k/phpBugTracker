<?php

// install.php -- Web-based installation script
// Thanks to the phpBB crew for an example on how this can be done.
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
// $Id: install.php,v 1.29 2002/06/13 14:26:34 firma Exp $

// Location of smarty templates class
define ('SMARTY_PATH', '');
// Example if smarty is installed within the phpBugTracker tree.
//define ('SMARTY_PATH','./inc/smarty/');

if (!@include(SMARTY_PATH . 'Smarty.class.php')) { // Template class
	include('templates/default/base/smartymissing.html');
	exit;
}
if (!@is_writeable('c_templates')) {
	include('templates/default/base/templatesperm.html');
	exit;
}

// Template class
class extSmarty extends Smarty {

	function fetch($_smarty_tpl_file, $_smarty_cache_id = null, $_smarty_compile_id = null, $_smarty_display = false) {
		error_reporting(E_ALL ^ E_NOTICE); // Clobber Smarty warnings
		return Smarty::fetch($_smarty_tpl_file, $_smarty_cache_id, $_smarty_compile_id, $_smarty_display);
	}
}

$t = new extSmarty;
$t->template_dir = 'templates/default';
$t->compile_dir = 'c_templates';
$t->config_dir = '.';
$t->register_function('build_select', 'build_select');

$_gv =& $HTTP_GET_VARS;
$_pv =& $HTTP_POST_VARS;

$db_types = array(
	'mysql' => 'MySQL',
	'oci8' => 'Oracle 8.1.x',
	'pgsql' => 'PostgreSQL');
		
ini_set("magic_quotes_runtime", 0); // runtime quotes will kill the included sql

if (!empty($_pv)) {
	$tables = array(
		'/^#.*/' => '',
		'/^--.*/' => '',
		'/TBL_ACTIVE_SESSIONS/' => $_pv['tbl_prefix'].'active_sessions',
		'/TBL_DB_SEQUENCE/' => $_pv['tbl_prefix'].'db_sequence',
		'/TBL_ATTACHMENT/' => $_pv['tbl_prefix'].'attachment',
		'/TBL_AUTH_GROUP/' => $_pv['tbl_prefix'].'auth_group',
		'/TBL_AUTH_PERM/' => $_pv['tbl_prefix'].'auth_perm',
		'/TBL_AUTH_USER/' => $_pv['tbl_prefix'].'auth_user',
		'/TBL_BUG_CC/' => $_pv['tbl_prefix'].'bug_cc',
		'/TBL_BUG_DEPENDENCY/' => $_pv['tbl_prefix'].'bug_dependency',
		'/TBL_BUG_GROUP/' => $_pv['tbl_prefix'].'bug_group',
		'/TBL_BUG_HISTORY/' => $_pv['tbl_prefix'].'bug_history',
		'/TBL_BUG_VOTE/' => $_pv['tbl_prefix'].'bug_vote',
		'/TBL_BUG/' => $_pv['tbl_prefix'].'bug',
		'/TBL_COMMENT/' => $_pv['tbl_prefix'].'comment',
		'/TBL_COMPONENT/' => $_pv['tbl_prefix'].'component',
		'/TBL_CONFIGURATION/' => $_pv['tbl_prefix'].'configuration',
		'/TBL_GROUP_PERM/' => $_pv['tbl_prefix'].'group_perm',
		'/TBL_OS/' => $_pv['tbl_prefix'].'os',
		'/TBL_PROJECT_GROUP/' => $_pv['tbl_prefix'].'project_group',
		'/TBL_PROJECT/' => $_pv['tbl_prefix'].'project',
		'/TBL_RESOLUTION/'  => $_pv['tbl_prefix'].'resolution',
		'/TBL_SAVED_QUERY/' => $_pv['tbl_prefix'].'saved_query',
		'/TBL_SEVERITY/' => $_pv['tbl_prefix'].'severity',
		'/TBL_STATUS/' => $_pv['tbl_prefix'].'status',
		'/TBL_USER_GROUP/' => $_pv['tbl_prefix'].'user_group',
		'/TBL_USER_PERM/' => $_pv['tbl_prefix'].'user_perm',
		'/TBL_USER_PREF/' => $_pv['tbl_prefix'].'user_pref',
		'/TBL_VERSION/' => $_pv['tbl_prefix'].'version',
		'/TBL_PROJECT_GROUP/' => $_pv['tbl_prefix'].'project_group',
		'/TBL_DATABASE/' => $_pv['tbl_prefix'].'database_server',
		'/TBL_SITE/' => $_pv['tbl_prefix'].'site',
		'/OPTION_ADMIN_EMAIL/' => $_pv['admin_login'],
		'/OPTION_ADMIN_PASS/' => $_pv['encrypt_pass'] ? md5($_pv['admin_pass']) 
			: $_pv['admin_pass'],
		'/OPTION_PHPBT_EMAIL/' => $_pv['phpbt_email'],
		'/OPTION_ENCRYPT_PASS/' => $_pv['encrypt_pass'],
		'/OPTION_INSTALL_URL/' => 'http://'.$HTTP_SERVER_VARS['SERVER_NAME'].
			dirname($HTTP_SERVER_VARS['SCRIPT_NAME']),
			);
}

@include_once('config.php');
if (defined('DB_HOST')) { // Already configured
	header("Location: index.php");
}

function build_select($params) {
	global $db_types;
	
	extract($params);
	$text = '';
	foreach ($db_types as $val => $item) {
		if ($selected == $val and $selected != '') $sel = ' selected';
    else $sel = '';
    $text .= "<option value=\"$val\"$sel>$item</option>";
	}
	echo $text;
}

///
/// Check the validity of an email address
/// (From zend.com user russIndr)
function bt_valid_email($email) {
  return eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$', $email);
}

function grab_config_file() {
	global $t, $_pv;

	foreach ($_pv as $key => $val) {
		$patterns[] = '{'.$key.'}';
		$replacements[] = $val;
	}
	// Smarty
	$patterns[] = '{smarty_path}';
	$replacements[] = SMARTY_PATH;
	
	$contents = join('', file('config-dist.php'));
	return str_replace($patterns, $replacements, $contents);
	
}

function create_tables() {
	global $_pv, $tables;
	
	// PEAR::DB
	require_once('DB.php');
	$dsn = array(
		'phptype' => $_pv['db_type'],
		'hostspec' => $_pv['db_host'],
		'database'  => $_pv['db_database'],
		'username'  => $_pv['db_user'],
		'password'  => $_pv['db_pass']
		);
	$db = DB::Connect($dsn);
  // Simple error checking on returned DB object to check connection to db 
  if(get_class($db)=='db_error') {
    die('<br><br>
    <div align="center">The installation script could not connect to the database (' . $_pv['db_database'] .
    ') on the host (' . $_pv['db_host'] . ') using the specified username and password.
    <br>
    Please check these details are correct and that the database already exists then retry.
    </div>
    ');
  }


	$db->setOption('optimize', 'portability');

	$q_temp_ary = file('schemas/'.$_pv['db_type'].'.in');
	$queries = preg_replace(array_keys($tables), array_values($tables), 
		$q_temp_ary);
	$do_query = '';
	foreach ($queries as $query) {
		// First, collect multi-line queries into one line, then run the query
		$do_query .= chop($query);
		if (empty($do_query) or substr($do_query, -1) != ';') continue;
		if ($_pv['db_type'] == 'oci8' ) {
		    $do_query = substr($do_query, 0, -1);
		}
		$db->query(stripslashes($do_query));
		$do_query = '';
	}
}

function check_vars() {
	global $_pv;
	
	$error = '';
	if (!$_pv['db_host'] = trim($_pv['db_host'])) {
		$error = 'Please enter the host name for your database server';
	} elseif (!$_pv['db_database'] = trim($_pv['db_database'])) {
		$error = 'Please enter the name of the database you will be using';
	} elseif (!$_pv['db_user'] = trim($_pv['db_user'])) {
		$error = 'Please enter the user name for connecting to the database';
	} elseif (!$_pv['phpbt_email'] = trim($_pv['phpbt_email'])) {
		$error = 'Please enter the phpBT email address';
	} elseif (!$_pv['admin_login'] = trim($_pv['admin_login'])) {
		$error = 'Please enter the admin login';
	} elseif (!bt_valid_email($_pv['admin_login'])) {
		$error = 'Please use a valid email address for the admin login';
	} elseif (!$_pv['admin_pass'] = trim($_pv['admin_pass'])) {
		$error = 'Please enter the admin password';
	} elseif (!$_pv['admin_pass2'] = trim($_pv['admin_pass2'])) {
		$error = 'Please confirm the admin password';
	} elseif ($_pv['admin_pass'] != $_pv['admin_pass2']) {
		$error = 'The admin passwords don\'t match';
	}
	
	if (!empty($error)) {
		show_front($error);
		return false;
	} else {
		return true;
	}
}

function dump_config_file() {

	if (!check_vars()) return;
	create_tables();
 	header('Content-Type: text/x-delimtext; name="config.php"');
 	header('Content-disposition: attachment; filename=config.php');
 	echo grab_config_file();
}

function save_config_file() {

	if (!check_vars()) return;
	create_tables();
	if (!$fp = @fopen('config.php', 'w')) {
		show_front('Error writing to config.php');
	} else {
		fwrite($fp, grab_config_file());
		fclose($fp);
	}
	show_finished();
}

function show_finished() {
	global $t, $_pv;
	
	$t->assign('login', $_pv['admin_login']);
	$t->display('install-complete.html');
}

function show_front($error = '') {
	global $t, $_pv, $select, $HTTP_SERVER_VARS;
	
	$t->assign($_pv);
	$t->assign('error', $error);
	$t->assign('default_email', 'phpbt@'.$HTTP_SERVER_VARS['SERVER_NAME']);
	$t->display('install.html');
}

if (isset($_pv['op'])) {
	switch ($_pv['op']) {
		case 'save_config_file' : save_config_file(); break;
		case 'dump_config_file' : dump_config_file(); break;
	}
} else {
	show_front();
}
// Any whitespace below the end tag will disrupt config.php
?>
