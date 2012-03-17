<?php

// newaccount.php - Set up new user accounts
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
// $Id: newaccount.php,v 1.37 2008/01/28 00:10:40 brycen Exp $

define('NO_AUTH', 1);
include 'include.php'; 

function do_form() {
	global $db, $t, $now, $u;
	
	if (NEW_ACCOUNTS_DISABLED) {
		$t->render('newaccount-disabled.html');
		return;
	}

	if (!EMAIL_IS_LOGIN && !$_POST['login'] = trim($_POST['login'])) 
		$error = translate("Please enter a login");
	elseif (!$_POST['email'] or !bt_valid_email($_POST['email'])) 
		$error = translate("Please enter a valid email");
	elseif ($db->getOne("select user_id from ".TBL_AUTH_USER." where email = '{$_POST['email']}' ".(!empty($_POST['login']) ? "or login = '{$_POST['login']}'" : '')))
		$error = translate("That login has already been used");
	if (!empty($error)) { 
		show_form($error);
		return;
	}
	$firstname = htmlspecialchars($_POST['firstname']);
	$lastname = htmlspecialchars($_POST['lastname']);
	$password = genpassword(10);
	if (ENCRYPT_PASS) {
		$mpassword = $db->quote(md5($password));
	} else {
		$mpassword = $db->quote(stripslashes($password));
	}
	if (EMAIL_IS_LOGIN) {
		$login = $_POST['email'];
	} else {
		$login = $_POST['login'];
	}
	$user_id = $db->nextId(TBL_AUTH_USER);
    // Change this line to make new member-created accounts inactive.
	$db->query("insert into ".TBL_AUTH_USER." (user_id, login, first_name, last_name, email, password, active, created_date, last_modified_date) values (".join(', ', array($user_id, $db->quote(stripslashes($login)), $db->quote(stripslashes($firstname)), $db->quote(stripslashes($lastname)), $db->quote($_POST['email']), $mpassword, 1, $now, $now)).")");
	$db->query("insert into ".TBL_USER_GROUP." (user_id, group_id, created_by, created_date) select $user_id, group_id, 0, $now from ".TBL_AUTH_GROUP." where group_name = '".NEW_ACCOUNTS_GROUP."'"); 
	$db->query("insert into ".TBL_USER_PREF." (user_id) values ($user_id)");
	
	mass_mail4($_POST['email'],
		translate("phpBugTracker Login"), 
		sprintf(translate("Your phpBugTracker password is %s"), $password),
		ADMIN_EMAIL);


	$t->render('newaccountsuccess.html', translate("New account created"));
}

function show_form($error = '') {
    global $t, $_POST;

    $t->assign('error', $error);

    if (NEW_ACCOUNTS_DISABLED) {
        $t->render('newaccount-disabled.html', translate("Disabled"));
    } else {
        $t->assign(array(
            'txtTitle' => translate("Create a new account"),
            'txtLogin' => translate("Login"),
            'txtEmail' => translate("Email"),
            'txtFirstName' => translate("First Name"),
            'txtLastName' => translate("Last Name"),
            'txtOptional' => translate("optional"),
            'txtCreateNewAccount' => translate("Create new account"),
            'formAction' => $_SERVER['PHP_SELF'],
            'EMAIL_IS_LOGIN' => EMAIL_IS_LOGIN,
            'test' => '<b>html cocode</b>',
            'login' => htmlspecialchars(get_request_value('login')),
            'email' => htmlspecialchars(get_request_value('email')),
            'firstname' => htmlspecialchars(get_request_value('firstname')),
            'lastname' => htmlspecialchars(get_request_value('lastname')),
        ));
        $t->render('newaccount.tpl', translate("Create new account"));
    }
}

if (isset($_POST['createaccount'])) do_form();
else show_form();

?>
