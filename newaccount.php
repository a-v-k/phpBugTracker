<?php

// newaccount.php - Set up new user accounts
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
// $Id: newaccount.php,v 1.22 2001/11/19 16:49:20 bcurtis Exp $

include 'include.php'; 

function do_form() {
	global $q, $t, $_pv, $STRING, $now, $u;
	
	if (NEW_ACCOUNTS_DISABLED) {
		$t->set_file('content', 'newaccount-disabled');
		return;
	}

	if (!EMAIL_IS_LOGIN && !$_pv['login'] = trim($_pv['login'])) 
		$error = $STRING['givelogin'];
	elseif (!$_pv['email'] or !valid_email($_pv['email'])) 
		$error = $STRING['giveemail'];
	elseif ($q->grab_field("select user_id from ".TBL_AUTH_USER." where email = '{$_pv['email']}' or login = '{$_pv['login']}'"))
		$error = $STRING['loginused'];
	if ($error) { 
		show_form($error);
		return;
	}
	$firstname = htmlspecialchars($_pv['firstname']);
	$lastname = htmlspecialchars($_pv['lastname']);
	$password = genpassword(10);
	if (ENCRYPT_PASS) {
		$mpassword = md5($password);
	} else {
		$mpassword = $password;
	}
	if (EMAIL_IS_LOGIN) {
		$login = $_pv['email'];
	} else {
		$login = $_pv['login'];
	}
	$user_id = $q->nextid(TBL_AUTH_USER);
	$q->query("insert into ".TBL_AUTH_USER." (user_id, login, first_name, last_name, email, password, active, created_date, last_modified_date)"
	         ." values ($user_id, '$login', '$firstname', '$lastname', '{$_pv['email']}', '$mpassword', 1, $now, $now)");
	$q->query("insert into ".TBL_USER_GROUP." (user_id, group_id)"
	         ." select $user_id, group_id from ".TBL_AUTH_GROUP." where group_name = 'user'"); 
	mail($_pv['email'], $STRING['newacctsubject'], sprintf($STRING['newacctmessage'], 
		$password),	sprintf("From: %s\nContent-Type: text/plain; charset=%s\nContent-Transfer-Encoding: 8bit\n",ADMIN_EMAIL, $STRING['lang_charset']));
	$t->set_file('content','newaccountsuccess.html');
}

function show_form($error = '') {
	global $q, $t, $_pv;
	
	if (NEW_ACCOUNTS_DISABLED) {
		$t->set_file('content', 'newaccount-disabled.html');
		return;
	}

	$t->set_file('content','newaccount.html');
	$t->set_block('content', 'loginentryarea', 'loginarea');
	$t->set_var(array(
		'error' => $error,
		'login' => isset($_pv['login']) ? stripslashes($_pv['login']) : '',
		'email' => isset($_pv['email']) ? $_pv['email'] : '',
		'firstname' => isset($_pv['firstname']) ? stripslashes($_pv['firstname'])
			: '',
		'lastname' => isset($_pv['lastname']) ? stripslashes($_pv['lastname'])
			: ''
		));
		
	// Show the login field if necessary
	if (EMAIL_IS_LOGIN) {
		$t->set_var('loginarea', '');
	} else {
		$t->parse('loginarea', 'loginentryarea', true);
	}
}

$t->set_file('wrap','wrap.html');
$t->set_var('TITLE',$TITLE['newaccount']);

if (isset($_pv['createaccount'])) do_form();
else show_form();

$t->pparse('main',array('content','wrap','main'));

?>
