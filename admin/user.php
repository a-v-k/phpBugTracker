<?php

// user.php - Create and update users
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
// $Id: user.php,v 1.43 2002/03/30 19:12:30 bcurtis Exp $

define('TEMPLATE_PATH', 'admin');
include '../include.php';

function do_form($userid = 0) {
	global $db, $me, $_pv, $STRING, $now, $u, $QUERY;

	$error = '';
	// Validation
	if (!EMAIL_IS_LOGIN && !$_pv['flogin'] = trim($_pv['flogin'])) {
		$error = $STRING['givelogin'];
	} elseif (!valid_email($_pv['femail'])) {
		$error = $STRING['giveemail'];
	} elseif (!$_pv['fpassword'] = trim($_pv['fpassword'])) {
		$error = $STRING['givepassword'];
	}
	if ($error) {
		list_items($userid, $error);
		return;
	}
	if (!isset($_pv['factive'])) $_pv['factive'] = 0;

	if (EMAIL_IS_LOGIN) {
		$login = $_pv['femail'];
	} else {
		$login = $_pv['flogin'];
	}

	if (!$userid) {
		if (ENCRYPT_PASS) $mpassword = $db->quote(md5($_pv['fpassword']));
		else $mpassword = $db->quote(stripslashes($_pv['fpassword']));
		$new_user_id = $db->nextId(TBL_AUTH_USER);
		$db->query('insert into '.TBL_AUTH_USER
			." (user_id, first_name, last_name, login, email, password, active,
			created_by, created_date, last_modified_by, last_modified_date)
			values (".join(', ', array($new_user_id, 
				$db->quote(stripslashes($_pv['ffirstname'])), 
				$db->quote(stripslashes($_pv['flastname'])),
				$db->quote(stripslashes($login)), $_pv['femail'], $mpassword, 
				$_pv['factive'], $u, $now, $u, $now)).')');
		// Add to the selected groups
		if (isset($_pv['fusergroup']) and is_array($_pv['fusergroup']) and
			$_pv['fusergroup'][0]) {
			foreach ($_pv['fusergroup'] as $group) {
				$db->query("insert into ".TBL_USER_GROUP
					." (user_id, group_id, created_by, created_date)
					values ('$new_user_id' ,'$group', $u, $now)");
			}
		}
		// And add to the user group
		$db->query("insert into ".TBL_USER_GROUP.
			" (user_id, group_id, created_by, created_date) 
			select $new_user_id, group_id, $u, $now from ".TBL_AUTH_GROUP.
			" where group_name = 'User'");
	} else {
		if (ENCRYPT_PASS) {
			$oldpass = $db->getOne("select password from ".TBL_AUTH_USER
				." where user_id = $userid");
			if ($oldpass != $_pv['fpassword']) {
				$pquery = "password = '".md5($_pv['fpassword'])."',";
			} else {
				$pquery = '';
			}
		} else {
			$pquery = "password = ".$db->quote(stripslashes($_pv['fpassword'])).",";
		}
		$db->query("update ".TBL_AUTH_USER.
			" set first_name = ".$db->quote(stripslashes($_pv['ffirstname'])).
			", last_name = ".$db->quote(stripslashes($_pv['flastname'])).
			", login = ".$db->quote(stripslashes($login)).
			", email = '{$_pv['femail']}', $pquery active = {$_pv['factive']} ".
			"where user_id = '$userid'");

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
	header("Location: $me?filter={$_pv['filter']}");
}

function show_form($userid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;


	if ($userid && !$error) {
		$row = $db->getRow("select * from ".TBL_AUTH_USER." where user_id = '$userid'");

		// Get user's groups
		$user_groups = $db->getCol('select group_id from '.TBL_USER_GROUP.
			" where user_id = {$row['user_id']}");

		$t->set_var(array(
			'action' => $STRING['edit'],
			'fuserid' => $row['user_id'],
			'flogin' => $row['login'],
			'ffirstname' => stripslashes($row['first_name']),
			'flastname' => stripslashes($row['last_name']),
			'femail' => $row['email'],
			'fpassword' => $row['password'],
			'factive' => $row['active'] ? 'checked' : '',
			'fusergroup' => build_select('group', $user_groups),
			// Whether or not this user has admin rights
			'hadadmin' => $db->getOne('select count(*) from '.TBL_USER_GROUP.
				" where user_id = {$row['user_id']} and group_id = 1")
			));
	} else {
		$t->set_var(array(
			'action' => $userid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fuserid' => $userid,
			'flogin' => isset($_pv['flogin']) ? $_pv['flogin'] : '',
			'ffirstname' => isset($_pv['firstname']) ? 
				stripslashes($_pv['firstname']) : '',
			'flastname' => isset($_pv['flastname']) ? 
				stripslashes($_pv['flastname']) : '',
			'femail' => isset($_pv['femail']) ? $_pv['femail'] : '',
			'fpassword' => isset($_pv['fpassword']) ? $_pv['fpassword'] : 
				genpassword(10),
			'factive' => isset($_pv['factive']) ? ($_pv['factive'] ? 'checked' : '')
				: 'checked',
			'fusergroup' => build_select('group', (isset($_pv['fusergroup']) ? 
				$_pv['fusergroup'] : array())),
			// Whether or not this user has admin rights
			'hadadmin' => 0
			));
	}
	// The number of admins in the system
	$t->set_var('numadmins', $db->getOne('select count(*) from '.TBL_USER_GROUP.
		' where group_id = 1'));

	// Show the login field only if login is not tied to email address
	if (EMAIL_IS_LOGIN) {
		$t->set_var('loginarea', '');
	} else {
		$t->parse('loginarea', 'loginentryarea', true);
	}
}

function list_items($userid = 0, $error = '') {
	global $me, $db, $t, $_gv, $STRING, $TITLE;

	$t->set_file('content', 'userlist.html');
	$t->set_block('content', 'row', 'rows');
	$t->set_block('content', 'loginentryarea', 'loginarea');

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

	list($selrange, $llimit, $npages, $pages) = multipages($nr, $page,
		"order=$order&sort=$sort&filter=$user_filter");

	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));

	$rs = $db->limitQuery("select user_id, first_name, last_name,
		email, login, created_date, active from ".TBL_AUTH_USER
		."$filter_query order by $order $sort", $llimit, $selrange);

	$headers = array(
		'userid' => 'user_id',
		'name' => 'last_name',
		'login' => 'login',
		'email' => 'email',
		'password' => 'password',
		'active' => 'active',
		'date' => 'created_date');

	sorting_headers($me, $headers, $order, $sort, "page=$page&filter=$user_filter");

	if (!$rs->numRows()) {
		$t->set_var('rows',"<tr><td>{$STRING['nousers']}</td></tr>");
	} else {
		$i = 0;
		while ($rs->fetchInto($row)) {
			$t->set_var(array(
				'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
				'trclass' => $i % 2 ? '' : 'alt',
				'userid' => $row['user_id'],
				'login' => $row['login'],
				'name' => stripslashes("{$row['first_name']}&nbsp;{$row['last_name']}"),
				'email' => $row['email'],
				'active' => $row['active'] ? 'Yes' : 'No',
				'date' => date(DATE_FORMAT, $row['created_date'])));
			$t->parse('rows','row',true);
		}
	}
	$t->set_var(array(
		'filter_select' => build_select('user_filter', $user_filter),
		'filter' => $user_filter));
	
	show_form($userid, $error);
	$t->set_var('TITLE', $TITLE['user']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : list_items(); break;
	case 'edit' : list_items($_gv['id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['id']);
} else list_items();

$t->pparse('main',array('content', 'wrap', 'main'));

?>
