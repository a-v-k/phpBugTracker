<?php

// group.php - Administer the user groups
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
// $Id: group.php,v 1.12 2003/06/25 02:11:10 kennyt Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function purge_group($groupid = 0) {
	global $db;
	
	$db->query("delete from ".TBL_USER_GROUP." where group_id = $groupid");
}

function del_group($groupid = 0) {
	global $db;
	
	purge_group($groupid);
	$db->query("delete from ".TBL_AUTH_GROUP." where group_id = $groupid");
}

function do_form($groupid = 0) {
	global $db, $me, $_pv, $STRING, $u, $now, $t;

	extract($_pv);
	$error = '';
	// Validation
	if (!$group_name = trim($group_name))
		$error = $STRING['givename'];
	if ($error) { show_form($groupid, $error); return; }

	if (!$groupid) {
		$db->query("insert into ".TBL_AUTH_GROUP.
			" (group_id, group_name, created_by, created_date, last_modified_by, last_modified_date, assignable)"
			." values (".$db->nextId(TBL_AUTH_GROUP).", ".
			$db->quote(stripslashes($group_name)).", $u, $now, $u, $now, ". ((int)$assignable).')');
	} else {
		$db->query("update ".TBL_AUTH_GROUP.
			" set group_name = ".$db->quote(stripslashes($group_name)).
			", last_modified_by = $u, last_modified_date = $now, assignable = ".($assignable?1:0)." where group_id = '$groupid'");
	}
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($groupid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	if ($groupid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_AUTH_GROUP.
			" where group_id = '$groupid'"));
	} else {
		$t->assign($_pv);
	}
	$t->assign('error', $error);
	$t->wrap('admin/group-edit.html', ($groupid ? 'editgroup' : 'addgroup'));
}


function list_items($groupid = 0, $error = '') {
	global $me, $db, $t, $_gv, $STRING, $TITLE, $QUERY;

	if (empty($_gv['order'])) { 
		$order = 'group_name'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}

	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
	$nr = $db->getOne("select count(*) from ".TBL_AUTH_GROUP);

	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

	$t->assign('groups', $db->getAll($db->modifyLimitQuery(
		sprintf($QUERY['admin-list-groups'], $order, $sort), $llimit, $selrange)));

	$headers = array(
		'groupid' => 'group_id',
		'name' => 'group_name',
		'count' => '4');

	sorting_headers($me, $headers, $order, $sort, "page=$page");

	$t->wrap('admin/grouplist.html', 'group');
}

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'edit' : show_form($_gv['group_id']); break;
	case 'del' : del_group($_gv['group_id']); list_items($_gv['group_id']); break;
	case 'purge' : purge_group($_gv['group_id']); list_items($_gv['group_id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['group_id']);
} else list_items();

?>
