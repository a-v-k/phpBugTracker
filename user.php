<?php

// user.php - Preferences page
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

function change_bug_list_columns($column_list) {
	global $q, $u, $t;
	
	$column_list = serialize($column_list);
	$q->query("update user set bug_list_fields = '$column_list' where user_id = $u");
	$t->set_file('content', 'columnlistchanged.html');
}

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
	
	$q->query("update user set password = '$mpassword' where user_id = $u");
	$t->set_file('content', 'passwordchanged.html');
}

function show_preferences_form($error = '') {
	global $t, $pass1, $pass2;
	
	$t->set_file('content', 'user.html');
	$t->set_var(array(
		'error' => $error ? $error.'<br><br>' : '',
		'pass1' => $pass1,
		'pass2' => $pass2
		));
	
}

$t->set_file('wrap', 'wrap.html');
$perm->check('User');

if ($do) switch ($op) {
	case 'changepassword' : change_password($_gv['pass1'], $_gv['pass2']); break;
	case 'changecolumnlist' : change_bug_list_columns($_gv['column_list']); break;
	default : show_preferences_form();
else show_preferences_form();

$t->pparse('main', array('content', 'wrap', 'main'));

page_close();

?>
