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

include 'include.php'; 

function do_form() {
	global $q, $t, $email, $firstname, $lastname, $STRING, $now, $u;
	
	if (!$email or !valid_email($email)) 
		$error = $STRING['giveemail'];
	elseif ($q->grab_field("select user_id from auth_user where email = '$email'"))
		$error = $STRING['loginused'];
	if ($error) { 
		show_form($error);
		return;
	}
	$firstname = htmlspecialchars($firstname);
	$lastname = htmlspecialchars($lastname);
	$password = genpassword(10);
	if (ENCRYPTPASS) {
		$mpassword = md5($password);
	} else {
		$mpassword = $password;
	}
	$q->query("insert into auth_user (user_id, first_name, last_name, email, password, user_level, created_date, last_modified_date) values (".$q->nextid('user').", '$firstname', '$lastname', '$email', '$mpassword', 1, $now, $now)");
	mail($email, $STRING['newacctsubject'], sprintf($STRING['newacctmessage'], 
		$password),	'From: '.ADMINEMAIL);
	$t->set_file('content','newaccountsuccess.html');
}

function show_form($error = '') {
	global $q, $t, $email, $firstname, $lastname;
	
	$t->set_file('content','newaccount.html');
	$t->set_var(array(
		'error' => $error,
		'email' => $email,
		'firstname' => $firstname,
		'lastname' => $lastname
		));
}

$t->set_file('wrap','wrap.html');
$t->set_var('TITLE',$TITLE['newaccount']);

if ($email) do_form();
else show_form();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
