<?php

// newaccount.php - Set up new user accounts
// --------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
// ---------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// ---------------------------------------------------------------------

include 'include.php'; 

function do_form() {
	global $q, $t, $email, $firstname, $lastname, $STRING, $now;
	
	if (!$email or !valid_email($email)) 
		$error = $STRING['giveemail'];
	elseif ($q->grab_field("select UserID from User where Email = '$email'"))
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
	$q->query("insert into User (UserID, FirstName, LastName, Email, Password, CreatedDate, UserLevel) values (".$q->nextid('User').", '$firstname', '$lastname', '$email', '$mpassword', $now, 1)");
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
