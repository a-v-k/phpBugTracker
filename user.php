<?php

// user.php - Preferences page
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
// $Id: user.php,v 1.28 2003/06/04 18:47:19 kennyt Exp $

include 'include.php';

function delete_vote($bug_id) {
    global $db, $u, $me, $now;
	
    $db->query("delete from ".TBL_BUG_VOTE." where user_id = $u and bug_id = $bug_id");
    header("Location: $me?r=$now");
}

function change_bug_list_columns($column_list) {
    global $db, $u, $t, $HTTP_SESSION_VARS, $STRING;
	
    $HTTP_SESSION_VARS['db_fields'] = $column_list;
    $column_list = serialize($column_list);
    $db->query("update ".TBL_AUTH_USER." set bug_list_fields = '$column_list' where user_id = $u");
    show_text($STRING['USER_PREF']['ColumnPreferencesSaved']);
}

function change_password($pass1, $pass2) {
    global $t, $db, $u, $STRING;

    if (!$pass1 = trim($pass1)) {
	$error = $STRING['givepassword'];
    } elseif ($pass1 != $pass2) {
	$error = $STRING['passwordmatch'];
    } else {
	$error = false;
    }

    if ($error) {
	show_preferences_form($error);
	return;
    }

    if (ENCRYPT_PASS) {
	$mpassword = md5($pass1);
    } else {
	$mpassword = $pass1;
    }
	
    $db->query("update ".TBL_AUTH_USER." set password = '$mpassword' where user_id = $u");
    $t->assign('changetext', $STRING['password_changed']);
    $t->wrap('changessaved.html', 'changessaved');
}

// Save changes to a user's preferences
function change_preferences($prefs) {
    global $db, $u, $t, $STRING;

    $updates = array();
    $old_prefs = $db->getRow("select * from ".TBL_USER_PREF." where user_id = $u");

    array_shift($old_prefs); // Drop the user_id field
    $updates = array();
    foreach ($old_prefs as $pref => $val) {
	if ($pref == 'def_results') continue;
	if (in_array($pref, $prefs) and !$val) {
	    $updates[] = "$pref = 1";
	} elseif (!in_array($pref, $prefs) and $val) {
	    $updates[] = "$pref = 0";
	}
    }

	$updates[] = 'def_results = '.(int)$prefs['def_results']; // override previous set
	
    if (count($updates)) {
	$db->query("update ".TBL_USER_PREF.' set '.@join(', ', $updates).
	    " where user_id = $u");
    }

    $t->assign('changetext', $STRING['prefs_changed']);
    $t->wrap('changessaved.html', 'changessaved');
}


function show_preferences_form($error = '') {
    global $t, $all_db_fields, $default_db_fields, $_sv, $db, $u, $STRING;

    // Display the votes (if any)
    $t->assign('votes',
	$db->getAll("select * from ".TBL_BUG_VOTE." where user_id = $u"));

    // Display current preference settings
    $pref_labels = array(
	'email_notices' => $STRING['USER_PREF']['ReceiveNotifications'],
	'saved_queries' => $STRING['USER_PREF']['ShowSavedQueries']
	);
    
    $prefs = $db->getRow("select * from ".TBL_USER_PREF." where user_id = $u");
    foreach ($pref_labels as $pref => $label) {
	$preferences[] = array(
	    'pref' => $pref,
	    'label' => $label,
	    'checked' => $prefs[$pref]
	);
	}
	
	$def_results = $prefs['def_results'];

    $t->assign(array(
	'error' => $error,
	'my_fields' => $_sv['db_fields'] ? $_sv['db_fields'] : $default_db_fields,
	'field_titles' => $all_db_fields,
	'preferences' => $preferences,
    'def_results' => $def_results
	));
		
    $t->wrap('user.html', 'preferences');
}

$perm->check_group('User');

if (isset($_gv['op'])) {
    switch ($_gv['op']) {
	case 'delvote':
	    delete_vote($_gv['bugid']);
	break;
    }
} elseif (isset($_pv['do'])) {
    switch ($_pv['do']) {
	case 'changepassword':
	    change_password($_pv['pass1'], $_pv['pass2']);
	break;
	case 'changecolumnlist':
	    change_bug_list_columns($_pv['column_list']);
	break;
	case 'changeprefs':
	    change_preferences(isset($_pv['preferences']) ? array_merge($_pv['preferences'], array('def_results' => $_pv['def_results'])) : array());
	break;
	default:
	    show_preferences_form();
    }
} else {
    show_preferences_form();
}

?>
