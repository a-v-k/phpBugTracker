<?php

// install.php -- Web-based installation script
// Thanks to the phpBB crew for an example on how this can be done.
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
// $Id: install.php,v 1.7 2001/12/04 14:27:23 bcurtis Exp $

define ('INSTALL_PATH', dirname($HTTP_SERVER_VARS['SCRIPT_FILENAME']));

include (INSTALL_PATH.'/inc/template.php');
$t = new Template('templates/default', 'keep');
$t->set_var('me', $HTTP_SERVER_VARS['PHP_SELF']);
$_gv =& $HTTP_GET_VARS;
$_pv =& $HTTP_POST_VARS;

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
		'/TBL_BUG_GROUP/' => $_pv['tbl_prefix'].'bug_group',
		'/TBL_BUG_HISTORY/' => $_pv['tbl_prefix'].'bug_history',
		'/TBL_BUG/' => $_pv['tbl_prefix'].'bug',
		'/TBL_COMMENT/' => $_pv['tbl_prefix'].'comment',
		'/TBL_COMPONENT/' => $_pv['tbl_prefix'].'component',
		'/TBL_CONFIGURATION/' => $_pv['tbl_prefix'].'configuration',
		'/TBL_GROUP_PERM/' => $_pv['tbl_prefix'].'group_perm',
		'/TBL_OS/' => $_pv['tbl_prefix'].'os',
		'/TBL_PROJECT/' => $_pv['tbl_prefix'].'project',
		'/TBL_RESOLUTION/'  => $_pv['tbl_prefix'].'resolution',
		'/TBL_SAVED_QUERY/' => $_pv['tbl_prefix'].'saved_query',
		'/TBL_SEVERITY/' => $_pv['tbl_prefix'].'severity',
		'/TBL_STATUS/' => $_pv['tbl_prefix'].'status',
		'/TBL_USER_GROUP/' => $_pv['tbl_prefix'].'user_group',
		'/TBL_USER_PERM/' => $_pv['tbl_prefix'].'user_perm',
		'/TBL_VERSION/' => $_pv['tbl_prefix'].'version',
		'/TBL_PROJECT_GROUP/' => $_pv['tbl_prefix'].'project_group',
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

function build_select($box, $value = '', $ary) {
	$text = '';
	foreach ($ary as $val => $item) {
		if ($value == $val and $value != '') $sel = ' selected';
    else $sel = '';
    $text .= "<option value=\"$val\"$sel>$item</option>";
	}
	return $text;
}

///
/// Check the validity of an email address
/// (From zend.com user russIndr)
function valid_email($email) {
  return eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $email);
}

function grab_config_file() {
	global $t, $_pv;

	$t->set_root('.');
	$t->set_file('content', 'config-dist.php');
	$t->set_var($_pv);
	return "<?php\n".$t->finish($t->parse('main', 'content'));
}

function create_tables() {
	global $_pv, $tables;
	
	include_once(INSTALL_PATH.'/inc/db/'.$_pv['db_type'].'.php');

	$db = new DB_Sql;
	$db->Host = $_pv['db_host'];
	$db->Database = $_pv['db_database'];
	$db->User = $_pv['db_user'];
	$db->Password = $_pv['db_pass'];

	$q_temp_ary = file('schemas/'.$_pv['db_type'].'.in');
	$queries = preg_replace(array_keys($tables), array_values($tables), 
		$q_temp_ary);
	$do_query = '';
	foreach ($queries as $query) {
		// First, collect multi-line queries into one line, then run the query
		$do_query .= chop($query);
		if (empty($do_query) or substr($do_query, -1) != ';') continue;
		$db->query($do_query);
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
	} elseif (!valid_email($_pv['admin_login'])) {
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
	show_finished();
}

function save_config_file() {

	if (!check_vars()) return;
	create_tables();
	if (!$fp = fopen('config.php', 'w')) {
		show_front('Error writing to config.php');
	} else {
		fwrite($fp, grab_config_file());
		fclose($fp);
	}
	show_finished();
}

function show_finished() {
	global $t, $_pv;
	
	$t->set_root('templates/default');
	$t->set_file('done', 'install-complete.html');
	$t->set_block('done', 'writeableblock', 'writeable');
	$t->set_block('done', 'unwriteableblock', 'unwriteable');
	$t->set_var('login', $_pv['admin_login']);
	if (is_writeable('config.php')) {
		$t->parse('writeable', 'writeableblock', true);
		$t->set_var('unwriteable', '');
	} else {
		$t->parse('unwriteable', 'unwriteableblock', true);
		$t->set_var('writeable', '');
	}

	print $t->finish($t->parse('main', 'done'));
}

function show_front($error = '') {
	global $t, $_pv, $select, $HTTP_SERVER_VARS;
	
	$db_types = array(
		'mysql' => 'MySQL',
		'pgsql' => 'PostgreSQL');
		
	foreach ($_pv as $k => $v) $$k = $v;
	
	$t->set_file('content', 'install.html');
	$t->set_block('content', 'writeableblock', 'writeable');
	$t->set_block('content', 'unwriteableblock', 'unwriteable');
	$t->set_var(array(
		'error' => !empty($error) ? "<div class=\"error\">$error</div>" : '',
		'db_type' => build_select('db', (isset($db_type) ? $db_type : ''), &$db_types),
		'db_host' => !empty($db_host) ? $db_host : 'localhost',
		'db_database' => !empty($db_database) ? $db_database : 'bug_tracker',
		'db_user' => !empty($db_user) ? $db_user : 'root',
		'db_pass' => '',
		'tbl_prefix' => !empty($tbl_prefix) ? $tbl_prefix : 'phpbt_',
		'admin_login' => !empty($admin_login) ? $admin_login : '',
		'phpbt_email' => !empty($phpbt_email) ? $phpbt_email : 
			'phpbt@'.$HTTP_SERVER_VARS['SERVER_NAME']
		));

	// If we can write to the config file, show that we will do that, otherwise
	// offer the config file as a download
	if (is_writeable('config.php')) {
		$t->parse('writeable', 'writeableblock', true);
		$t->set_var('unwriteable', '');
	} else {
		$t->parse('unwriteable', 'unwriteableblock', true);
		$t->set_var('writeable', '');
	}

	print $t->finish($t->parse('main', 'content'));
}

if (isset($_pv['op'])) {
	switch ($_pv['op']) {
		case 'save_config_file' : save_config_file(); break;
		case 'dump_config_file' : dump_config_file(); break;
	}
} else {
	show_front();
}

?>
