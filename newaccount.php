<?php

// newaccount.php - Set up new user accounts

include 'include.php'; 

///
/// Check the validity of an email address
/// (From zend.com)
function valid_email($email) {     
  return eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$', $email); 
}

function do_form() {
	global $q, $t, $email, $firstname, $lastname, $STRING;
	
	if (!$email or !valid_email($email)) 
		$error = $STRING[giveemail];
	elseif ($q->grab_field("select UserID from User where Email = '$email'"))
		$error = $STRING[loginused];
	if ($error) { 
		show_form($error);
		return;
	}
	$firstname = htmlspecialchars($firstname);
	$lastname = htmlspecialchars($lastname);
	$password = genpassword(10);
	$q->query("insert into User (UserID, FirstName, LastName, Email, Password, 
		CreatedDate, UserLevel) values (".$q->nextid('User').", '$firstname', 
		'$lastname', '$email', '$password', ".time().", 1)");
	mail($email,$STRING[newacctsubject],sprintf($STRING[newacctmessage], $password),
		'From: '.ADMINEMAIL);
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
$t->set_var('TITLE',$TITLE[newaccount]);

if ($email) do_form();
else show_form();

$t->pparse('main',array('content','wrap','main'));

?>
