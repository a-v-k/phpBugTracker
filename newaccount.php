<?php

// newaccount.php - Set up new user accounts
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
// $Id: newaccount.php,v 1.32 2002/05/19 16:59:16 firma Exp $

define('NO_AUTH', 1);
include 'include.php'; 

function do_form() {
	global $db, $t, $_pv, $STRING, $now, $u;
	
	if (NEW_ACCOUNTS_DISABLED) {
		$t->wrap('newaccount-disabled.html');
		return;
	}

	if (!EMAIL_IS_LOGIN && !$_pv['login'] = trim($_pv['login'])) 
		$error = $STRING['givelogin'];
	elseif (!$_pv['email'] or !bt_valid_email($_pv['email'])) 
		$error = $STRING['giveemail'];
	elseif ($db->getOne("select user_id from ".TBL_AUTH_USER.
		" where email = '{$_pv['email']}' ".
		(!empty($_pv['login']) ? "or login = '{$_pv['login']}'" : '')))
		$error = $STRING['loginused'];
	if (!empty($error)) { 
		show_form($error);
		return;
	}
	$firstname = htmlspecialchars($_pv['firstname']);
	$lastname = htmlspecialchars($_pv['lastname']);
	$password = genpassword(10);
	if (ENCRYPT_PASS) {
		$mpassword = $db->quote(md5($password));
	} else {
		$mpassword = $db->quote(stripslashes($password));
	}
	if (EMAIL_IS_LOGIN) {
		$login = $_pv['email'];
	} else {
		$login = $_pv['login'];
	}
	$user_id = $db->nextId(TBL_AUTH_USER);
	$db->query("insert into ".TBL_AUTH_USER." (user_id, login, first_name, last_name, email, password, active, created_date, last_modified_date)"
		." values (".join(', ', array($user_id, $db->quote(stripslashes($login)), 
			$db->quote(stripslashes($firstname)), 
			$db->quote(stripslashes($lastname)), $db->quote($_pv['email']), 
			$mpassword, 1, $now, $now)).")");
	$db->query("insert into ".TBL_USER_GROUP.
		" (user_id, group_id, created_by, created_date)
	  select $user_id, group_id, 0, $now from ".TBL_AUTH_GROUP.
		" where group_name = 'User'"); 
	$db->query("insert into ".TBL_USER_PREF." (user_id) values ($user_id)");
	
	qp_mail($_pv['email'], $STRING['newacctsubject'], sprintf($STRING['newacctmessage'], $password),
	    sprintf("From: %s",ADMIN_EMAIL));

	$t->wrap('newaccountsuccess.html', 'accountcreated');
}

function show_form($error = '') {
	global $t, $_pv;
	
	if (NEW_ACCOUNTS_DISABLED) {
		$t->wrap('newaccount-disabled.html');
	} else {
		$t->wrap('newaccount.html', 'newaccount');
	}
}

if (isset($_pv['createaccount'])) do_form();
else show_form();

?>
