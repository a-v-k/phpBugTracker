<?php

// user.php - Preferences page

include 'include.php';

function change_password($pass1, $pass2) {
	global $t, $q, $u, $STRING;
	
	if (!$pass1 = trim($pass1)) $error = $STRING['givepassword'];
	elseif ($pass1 != $pass2) $error = $STRING['passwordmatch'];
	
	if ($error) {
		show_password_form($error);
		return;
	}
	
	if (ENCRYPTPASS) {
		$mpassword = md5($pass1);
	} else {
		$mpassword = $pass1;
	}
	
	$q->query("update User set Password = '$mpassword' where UserID = $u");
	$t->set_file('content', 'passwordchanged.html');
}

function show_password_form($error = '') {
	global $t, $pass1, $pass2;
	
	$t->set_file('content', 'passwordform.html');
	$t->set_var(array(
		'error' => $error ? $error.'<br><br>' : '',
		'pass1' => $pass1,
		'pass2' => $pass2
		));
}

$t->set_file('wrap', 'wrap.html');
$perm->check('User');

if ($do) change_password($pass1, $pass2);
else show_password_form();

$t->pparse('main', array('content', 'wrap', 'main'));

page_close();

?>
