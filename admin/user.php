<?php

// user.php - Create and update users
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
// $Id: user.php,v 1.49 2002/05/18 03:00:00 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function do_form($userid = 0) {
	global $db, $me, $_pv, $STRING, $now, $u, $QUERY, $t;

	$error = '';
	// Validation
	if (!EMAIL_IS_LOGIN && !$_pv['login'] = trim($_pv['login'])) {
		$error = $STRING['givelogin'];
	} elseif (!bt_valid_email($_pv['email'])) {
		$error = $STRING['giveemail'];
	} elseif (!$_pv['password'] = trim($_pv['password'])) {
		$error = $STRING['givepassword'];
	}
	if ($error) {
		show_form($userid, $error);
		return;
	}
	if (!isset($_pv['active'])) $_pv['active'] = 0;
	if (!isset($_pv['fe_notice'])) $_pv['fe_notice'] = 0;

	if (EMAIL_IS_LOGIN) {
		$login = $_pv['email'];
	} else {
		$login = $_pv['login'];
	}

	if (!$userid) {
		if (ENCRYPT_PASS) $mpassword = $db->quote(md5($_pv['password']));
		else $mpassword = $db->quote(stripslashes($_pv['password']));
		$new_user_id = $db->nextId(TBL_AUTH_USER);
		$db->query('insert into '.TBL_AUTH_USER
			." (user_id, first_name, last_name, login, email, password, active,
			created_by, created_date, last_modified_by, last_modified_date)
			values (".join(', ', array($new_user_id, 
				$db->quote(stripslashes($_pv['first_name'])), 
				$db->quote(stripslashes($_pv['last_name'])),
				$db->quote(stripslashes($login)), $db->quote($_pv['email']), $mpassword, 
				$_pv['active'], $u, $now, $u, $now)).')');
		// Add to the selected groups
		if (isset($_pv['fusergroup']) and is_array($_pv['fusergroup']) and
			$_pv['fusergroup'][0]) {
			foreach ($_pv['fusergroup'] as $group) {
				$db->query("insert into ".TBL_USER_GROUP
					." (user_id, group_id, created_by, created_date)
					values ('$new_user_id' ,'$group', $u, $now)");
			}
		}
		// Add to prefs
		$db->query("INSERT INTO ".TBL_USER_PREF." (user_id, email_notices) 
			    VALUES ($new_user_id, '{$_pv['fe_notice']}')");

		// And add to the user group
		$db->query("insert into ".TBL_USER_GROUP.
			" (user_id, group_id, created_by, created_date) 
			select $new_user_id, group_id, $u, $now from ".TBL_AUTH_GROUP.
			" where group_name = 'User'");
	} else {
		if (ENCRYPT_PASS) {
			$oldpass = $db->getOne("select password from ".TBL_AUTH_USER
				." where user_id = $userid");
			if ($oldpass != $_pv['password']) {
				$pquery = "password = '".md5($_pv['password'])."',";
			} else {
				$pquery = '';
			}
		} else {
			$pquery = "password = ".$db->quote(stripslashes($_pv['password'])).",";
		}
		$db->query("update ".TBL_AUTH_USER.
			" set first_name = ".$db->quote(stripslashes($_pv['first_name'])).
			", last_name = ".$db->quote(stripslashes($_pv['last_name'])).
			", login = ".$db->quote(stripslashes($login)).
			", email = '{$_pv['email']}', $pquery active = {$_pv['active']} ".
			"where user_id = $userid");
			
		// Update preferences
		$db->query("update ".TBL_USER_PREF.
			" set email_notices = {$_pv['fe_notice']}	where user_id = $userid");

		// Update group memberships
		// Get user's groups (without dropping the user group)
		$user_groups = $db->getCol(sprintf($QUERY['admin-user-groups'], $userid));

		// Compute differences between old and new
		if (!isset($user_groups) or !is_array($user_groups)) {
			$user_groups = array();
		}
		if (!isset($_pv['fusergroup']) or !is_array($_pv['fusergroup']) or 
			!$_pv['fusergroup'][0]) {
			$_pv['fusergroup'] = array();
		}

		$remove_from = array_diff($user_groups, $_pv['fusergroup']);
		$add_to = array_diff($_pv['fusergroup'], $user_groups);

		if (count($remove_from)) {
			foreach ($remove_from as $group) {
				$db->query('delete from '.TBL_USER_GROUP
					." where user_id = $userid and group_id = $group");
			}
		}
		if (count($add_to)) {
			foreach ($add_to as $group) {
				$db->query("insert into ".TBL_USER_GROUP
					." (user_id, group_id, created_by, created_date)
					values ('$userid' ,'$group', $u, $now)");
			}
		}
	}
	if ($_pv['use_js']) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: $me?filter={$_pv['filter']}");
	}
}

function show_form($userid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;


	if ($userid && !$error) {
		$t->assign($db->getRow('select * from '.TBL_AUTH_USER." u, ".
			TBL_USER_PREF." p where u.user_id = $userid and u.user_id = p.user_id"));

		// Get user's groups
		$t->assign('user_groups', $db->getCol('select group_id from '.TBL_USER_GROUP.
			" where user_id = $userid"));

		$t->assign('hadadmin', $db->getOne('select count(*) from '.TBL_USER_GROUP.
				" where user_id = $userid and group_id = 1"));
	} else {
 		$t->assign($_pv);
		$t->assign(array(
			'error' => $error,
			'password' => isset($_pv['password']) ? $_pv['password'] : 
				genpassword(10),
			'user_groups' => isset($_pv['fusergroup']) ? $_pv['fusergroup'] : array(),
			// Whether or not this user has admin rights
			'hadadmin' => 0
			));
	}
	// The number of admins in the system
	$t->assign('numadmins', $db->getOne('select count(*) from '.TBL_USER_GROUP.
		' where group_id = 1'));
	$t->wrap('admin/user-edit.html', ($userid ? 'edituser' : 'adduser'));
}

function list_items($userid = 0, $error = '') {
	global $me, $db, $t, $_gv, $STRING, $TITLE;

	if (empty($_gv['order'])) { 
		$order = 'login'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 1;
	$user_filter = isset($_gv['filter']) ? $_gv['filter'] : 0;
	
	$filter_query = '';
	if (isset($_gv['filter'])) switch($_gv['filter']) {
		case 1 : $filter_query = ' where active = 1'; break;
		case 2 : $filter_query = ' where active = 0'; break;
		default : $filter_query = '';
	}
	$nr = $db->getOne("select count(*) from ".TBL_AUTH_USER.$filter_query);

	list($selrange, $llimit) = multipages($nr, $page, 
		"order=$order&sort=$sort&filter=$user_filter");

	$t->assign('users', $db->getAll($db->modifyLimitQuery(
		"select user_id, first_name, last_name,	email, login, created_date, active ".
		"from ".TBL_AUTH_USER."$filter_query order by $order $sort", 
		$llimit, $selrange)));

	$headers = array(
		'userid' => 'user_id',
		'name' => 'last_name',
		'login' => 'login',
		'email' => 'email',
		'password' => 'password',
		'active' => 'active',
		'date' => 'created_date');

	sorting_headers($me, $headers, $order, $sort, "page=$page&filter=$user_filter");

	$t->assign('filter', $user_filter);
	$t->wrap('admin/userlist.html', 'user');
}

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : list_items(); break;
	case 'edit' : show_form($_gv['user_id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['user_id']);
} else list_items();

?>
