<?php

// install.php -- Web-based installation script
// Thanks to the phpBB crew for an example on how this can be done.
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
// $Id: install.php,v 1.47 2005/07/19 19:25:36 ulferikson Exp $

include_once('inc/functions.php');
define('THEME', 'default');
define('RAWERROR', true);

$log_text = "";
$num_errors = 0;

// Handle a database error
function handle_install_error(&$obj) {
	global $log_text;

	$log_text .= "<div class=\"error\">";
	$log_text .= htmlentities($obj->message).'<br>'.htmlentities($obj->userinfo);
	$log_text .= "</div>\n";
}

function log_query($str) {
	global $db, $log_text, $num_errors;

	$log_text .= "SQL: " . $str . "<br>\n";
	$result = $db->query($str);
	if (DB::isError($result)) {
		$num_errors = $num_errors + 1;
	}
	$log_text .= "<br>\n";
}

// Template class
class template {
	var $vars;

	function template($vars = array()) {
		$this->vars = $vars;
	}

	function render($content_template, $page_title, $wrap_file = '') {
		extract($this->vars);
		$path = defined('TEMPLATE_PATH')
		? './templates/'.THEME.'/'.TEMPLATE_PATH.'/'
		: './templates/'.THEME.'/';
		include($wrap_file ? $path.$wrap_file : $path.'wrap.html');
	}

	function assign($var, $value = '') {
		if (is_array($var)) {
			foreach ($var as $k => $v) {
				$this->vars[$k] = $v;
			}
		} else {
			$this->vars[$var] = $value;
		}
	}
}

$t = new template(array(
	'template_path' => 'templates/default'));

$db_types = array(
	'mysql' => 'MySQL < 4.1',
	'mysqli' => 'MySQL >= 4.1',
	'oci8' => 'Oracle 8.1.x',
	'pgsql' => 'PostgreSQL');

ini_set("magic_quotes_runtime", 0); // runtime quotes will kill the included sql
ini_set("magic_quotes_sybase", 0);

if (!empty($_POST)) {
	$tables = array(
		'/^#.*/' => '',
		'/^--.*/' => '',
		'/TBL_ACTIVE_SESSIONS/' => $_POST['tbl_prefix'].'active_sessions',
		'/TBL_DB_SEQUENCE/' => $_POST['tbl_prefix'].'db_sequence',
		'/TBL_ATTACHMENT/' => $_POST['tbl_prefix'].'attachment',
		'/TBL_AUTH_GROUP/' => $_POST['tbl_prefix'].'auth_group',
		'/TBL_AUTH_PERM/' => $_POST['tbl_prefix'].'auth_perm',
		'/TBL_AUTH_USER/' => $_POST['tbl_prefix'].'auth_user',
		'/TBL_BUG_CC/' => $_POST['tbl_prefix'].'bug_cc',
		'/TBL_BUG_DEPENDENCY/' => $_POST['tbl_prefix'].'bug_dependency',
		'/TBL_BUG_GROUP/' => $_POST['tbl_prefix'].'bug_group',
		'/TBL_BUG_HISTORY/' => $_POST['tbl_prefix'].'bug_history',
		'/TBL_BUG_VOTE/' => $_POST['tbl_prefix'].'bug_vote',
		'/TBL_BUG/' => $_POST['tbl_prefix'].'bug',
		'/TBL_COMMENT/' => $_POST['tbl_prefix'].'comment',
		'/TBL_COMPONENT/' => $_POST['tbl_prefix'].'component',
		'/TBL_CONFIGURATION/' => $_POST['tbl_prefix'].'configuration',
		'/TBL_GROUP_PERM/' => $_POST['tbl_prefix'].'group_perm',
		'/TBL_OS/' => $_POST['tbl_prefix'].'os',
		'/TBL_PROJECT_GROUP/' => $_POST['tbl_prefix'].'project_group',
		'/TBL_PROJECT_PERM/' => $_POST['tbl_prefix'].'project_perm',
		'/TBL_PROJECT/' => $_POST['tbl_prefix'].'project',
		'/TBL_RESOLUTION/'  => $_POST['tbl_prefix'].'resolution',
		'/TBL_SAVED_QUERY/' => $_POST['tbl_prefix'].'saved_query',
		'/TBL_SEVERITY/' => $_POST['tbl_prefix'].'severity',
		'/TBL_STATUS/' => $_POST['tbl_prefix'].'status',
		'/TBL_USER_GROUP/' => $_POST['tbl_prefix'].'user_group',
		'/TBL_USER_PERM/' => $_POST['tbl_prefix'].'user_perm',
		'/TBL_USER_PREF/' => $_POST['tbl_prefix'].'user_pref',
		'/TBL_VERSION/' => $_POST['tbl_prefix'].'version',
		'/TBL_PROJECT_PERM/' => $_POST['tbl_prefix'].'project_perm',
		'/TBL_DATABASE/' => $_POST['tbl_prefix'].'database_server',
		'/TBL_SITE/' => $_POST['tbl_prefix'].'site',
		'/OPTION_ADMIN_EMAIL/' => $_POST['admin_login'],
		'/OPTION_ADMIN_PASS/' => $_POST['encrypt_pass'] ? md5($_POST['admin_pass']) : $_POST['admin_pass'],
		'/OPTION_PHPBT_EMAIL/' => $_POST['phpbt_email'],
		'/OPTION_ENCRYPT_PASS/' => $_POST['encrypt_pass'],
		'/OPTION_INSTALL_URL/' => 'http://'.$HTTP_SERVER_VARS['SERVER_NAME'].dirname($HTTP_SERVER_VARS['SCRIPT_NAME']),
		);
}

if (file_exists('config.php')) {
	include_once('config.php');
}
if (defined('DB_HOST')) { // Already configured
	header("Location: index.php");
}

function db_type_options($selected = 0) {
	global $db_types;

	foreach ($db_types as $val => $item) {
		if ($selected == $val and $selected != '') $sel = ' selected';
		else $sel = '';
		echo "<option value=\"$val\"$sel>$item</option>";
	}
}

function grab_config_file() {
	global $t, $_POST;

	foreach ($_POST as $key => $val) {
		$patterns[] = '{'.$key.'}';
		$replacements[] = $val;
	}
	$patterns[] = '{PEAR_PATH}';
	$replacements[] = PEAR_PATH;

	$contents = join('', file('config-dist.php'));
	return str_replace($patterns, $replacements, $contents);

}
function test_database(&$params, $testonly = false) {
	// PEAR::DB
	define('PEAR_PATH', ''); // Set this to '/some/path/' to not use system-wide PEAR
	// define('PEAR_PATH', 'inc/pear/'); // use a locally installed Pear (phpBT v0.9.1)
	if (!@include_once(PEAR_PATH.'DB.php')) {
		$error_message = translate("Failed loading Pear:DB");
		$error_info = translate("Please check your Pear installation and the defined PEAR_PATH in install.php");
		include('templates/default/install-dbfailure.html');
		exit;
	}
	$dsn = array(
		'phptype' => $params['db_type'],
		'hostspec' => $params['db_host'],
		'database'  => $params['db_database'],
		'username'  => $params['db_user'],
		'password'  => $params['db_pass']
		);
	$db = DB::Connect($dsn);

	// Simple error checking on returned DB object to check connection to db
	if (DB::isError($db)) {
		$error_message = $db->message;
		$error_info = $db->user_info;
		include('templates/default/install-dbfailure.html');
		exit;
	} else {
		if ($testonly) {
			include('templates/default/install-dbsuccess.html');
			exit;
		} else {
			return $db;
		}
	}
}

function create_tables() {
	global $_POST, $tables;
	global $db, $log_text, $num_errors;

	$db = test_database($_POST);
	$db->setOption('optimize', 'portability');

	$db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_install_error");

	$q_temp_ary = file('schemas/'.$_POST['db_type'].'.in');
	$queries = preg_replace(array_keys($tables), array_values($tables),
							$q_temp_ary);
	$do_query = '';
	foreach ($queries as $query) {
		// First, collect multi-line queries into one line, then run the query
		$do_query .= chop($query);
		if (empty($do_query) or substr($do_query, -1) != ';') continue;
		if ($_POST['db_type'] == 'oci8' ) {
			$do_query = substr($do_query, 0, -1);
		}
		log_query(stripslashes($do_query));
		$do_query = '';
	}
	/*!! BAD! Must figure out how to get db_version from config-dist.php... */
	$query = preg_replace(array_keys($tables), array_values($tables), 'INSERT INTO TBL_CONFIGURATION (varname,varvalue,description,vartype) VALUES (\'DB_VERSION\', './*!!!*/4/*!!!*/.', \'Database Version <b>Warning:</b> Changing this might make things go horribly wrong, so don\\\'t change it.\', \'mixed\')');
	log_query($query);

	if ($num_errors > 0) {
		include('templates/default/install-failure.html');
		exit;
	}
}

function check_vars() {
	global $_POST;

	$error = '';
	if (!$_POST['db_host'] = trim($_POST['db_host'])) {
		$error = translate("Please enter the host name for your database server");
	} elseif (!$_POST['db_database'] = trim($_POST['db_database'])) {
		$error = translate("Please enter the name of the database you will be using");
	} elseif (!$_POST['db_user'] = trim($_POST['db_user'])) {
		$error = translate("Please enter the user name for connecting to the database");
	} elseif (!$_POST['phpbt_email'] = trim($_POST['phpbt_email'])) {
		$error = translate("Please enter the phpBT email address");
	} elseif (!$_POST['admin_login'] = trim($_POST['admin_login'])) {
		$error = translate("Please enter the admin login");
	} elseif (!bt_valid_email($_POST['admin_login'])) {
		$error = translate("Please use a valid email address for the admin login");
	} elseif (!$_POST['admin_pass'] = trim($_POST['admin_pass'])) {
		$error = translate("Please enter the admin password");
	} elseif (!$_POST['admin_pass2'] = trim($_POST['admin_pass2'])) {
		$error = translate("Please confirm the admin password");
	} elseif ($_POST['admin_pass'] != $_POST['admin_pass2']) {
		$error = translate("The admin passwords don't match");
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
		show_front(translate("Error writing to config.php"));
	} else {
		fwrite($fp, grab_config_file());
		fclose($fp);
	}
	show_finished();
}

function show_finished() {
	global $t, $_POST;

	$login = $_POST['admin_login'];
	include('templates/default/install-complete.html');
}

function show_front($error = '') {
	global $t, $_POST, $select, $HTTP_SERVER_VARS;

	extract($_POST);
	$error = $error;
	$default_email = 'phpbt@'.$HTTP_SERVER_VARS['SERVER_NAME'];
	include('templates/default/install.html');
}

if (isset($_POST['op'])) {
	switch ($_POST['op']) {
		case 'save_config_file' : save_config_file(); break;
		case 'dump_config_file' : dump_config_file(); break;
	}
} elseif (isset($_GET['op'])) {
	switch ($_GET['op']) {
		case 'dbtest' : test_database($_GET, true); break;
	}
} else {
	if (!get_magic_quotes_gpc()) {
	        echo "<p>magic_quotes_gpc is OFF!</p>";
	        echo "<p>You must have magic_quotes_gpc set to On either in php.ini or in ";
	        echo ".htaccess (see <a href=\"http://www.php.net/manual/en/configuration.php\">http://www.php.net/manual/en/configuration.php</a> for more info).</p>";
	}
	else {
		show_front();
	}
}
// Any whitespace below the end tag will disrupt config.php
?>