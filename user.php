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
// $Id: user.php,v 1.21 2002/03/20 20:04:04 bcurtis Exp $

include 'include.php';

function delete_vote($bug_id) {
	global $db, $u, $me, $now;
	
	$db->query("delete from ".TBL_BUG_VOTE." where user_id = $u and bug_id = $bug_id");
	header("Location: $me?r=$now");
}

function change_bug_list_columns($column_list) {
	global $db, $u, $t, $_sv;
	
	$_sv['db_fields'] = $column_list;
	$column_list = serialize($column_list);
	$db->query("update ".TBL_AUTH_USER." set bug_list_fields = '$column_list' where user_id = $u");
	//$t->set_file('content', 'columnlistchanged.html');
	show_text('Your bug list column preferences have been saved');
}

function change_password($pass1, $pass2) {
	global $t, $db, $u, $STRING;
	
	if (!$pass1 = trim($pass1)) $error = $STRING['givepassword'];
	elseif ($pass1 != $pass2) $error = $STRING['passwordmatch'];
	
	if ($error) {
		show_password_form($error);
		return;
	}
	
	if (ENCRYPT_PASS) {
		$mpassword = md5($pass1);
	} else {
		$mpassword = $pass1;
	}
	
	$db->query("update ".TBL_AUTH_USER." set password = '$mpassword' where user_id = $u");
	$t->set_file('content', 'passwordchanged.html');
}

function show_preferences_form($error = '') {
	global $t, $pass1, $pass2, $all_db_fields, $default_db_fields, $_sv, $db, $u;
	
	$t->set_file('content', 'user.html');
	$t->set_block('content', 'column_list_row', 'list_rows');
	$t->set_block('content', 'votesblock', 'votesb');
	$t->set_block('votesblock', 'vote_row', 'vote_rows');
	
	$t->set_var(array(
		'error' => $error ? $error.'<br><br>' : '',
		'pass1' => $pass1,
		'pass2' => $pass2
		));
		
	$my_fields = $_sv['db_fields'] ? $_sv['db_fields'] :
		$default_db_fields;
	foreach ($my_fields as $field) {
		$checked[$field] = true;
	}
	foreach ($all_db_fields as $field => $title) {
		$t->set_var(array(
			'column_name' => $field,
			'column_title' => $title,
			'checked' => !empty($checked[$field]) ? 'checked' : ''
			));
		$t->parse('list_rows', 'column_list_row', true);
	}
	
	// Display the votes (if any)
	$rs = $db->query("select * from ".TBL_BUG_VOTE." where user_id = $u");
	if (!$rs->numRows()) {
		$t->set_var('votesb', '&nbsp;');
	} else {
		$i = 0;
		while ($rs->fetchInto($row)) {
			$t->set_var(array(
      	'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
				'trclass' => $i % 2 ? '' : 'alt',
				'bugid' => $row['bug_id'],
				'date' => date(DATE_FORMAT.' '.TIME_FORMAT, $row['created_date'])
				));
			$t->parse('vote_rows', 'vote_row', true);
		}
		$t->parse('votesb', 'votesblock', true);
	}
}

$t->set_file('wrap', 'wrap.html');
$perm->check_group('User');

if (isset($_gv['op'])) switch ($_gv['op']) {
	case 'delvote' : delete_vote($_gv['bugid']); break;
}
elseif (isset($_pv['do'])) switch ($_pv['do']) {
	case 'changepassword' : change_password($_pv['pass1'], $_pv['pass2']); break;
	case 'changecolumnlist' : change_bug_list_columns($_pv['column_list']); break;
	default : show_preferences_form();
}
else show_preferences_form();

$t->pparse('main', array('content', 'wrap', 'main'));

?>
