<?php

// status.php - Interface to the Status table
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
// $Id: status.php,v 1.29 2002/08/26 18:11:13 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($statusid = 0) {
	global $db, $me;
	
	if ($statusid) {
		// Make sure we are going after a valid record
		$itemexists = $db->getOne('select count(*) from '.TBL_STATUS.
			" where status_id = $statusid");
		// Are there any bugs tied to this one?
		$bugcount = $db->getOne('select count(*) from '.TBL_BUG.
			" where status_id = $statusid");
		if ($itemexists and !$bugcount) {
			$db->query('delete from '.TBL_STATUS." where status_id = $statusid");
		}
	}
	header("Location: $me?");
}

function do_form($statusid = 0) {
	global $db, $me, $_pv, $STRING, $t;

	extract($_pv);
	$error = '';
	// Validation
	if (!$status_name = trim($status_name))
		$error = $STRING['givename'];
	elseif (!$status_desc = trim($status_desc))
		$error = $STRING['givedesc'];
	if ($error) { show_form($statusid, $error); return; }

	if (empty($sort_order)) $sort_order = 0;
	if (!$statusid) {
		$db->query("insert into ".TBL_STATUS.
			" (status_id, status_name, status_desc, sort_order) values (".
			$db->nextId(TBL_STATUS).', '.
			$db->quote(stripslashes($status_name)).', '.
			$db->quote(stripslashes($status_desc)).", '$sort_order')");
	} else {
		$db->query("update ".TBL_STATUS.
			" set status_name = ".$db->quote(stripslashes($status_name)).
			', status_desc = '.$db->quote(stripslashes($status_desc)).
			", sort_order = $sort_order where status_id = $statusid");
	}
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($statusid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	extract($_pv);
	if ($statusid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_STATUS.
			" where status_id = '$statusid'"));
	} else {
 		$t->assign($_pv);
	}
	$t->assign('error', $error);
	$t->wrap('admin/status-edit.html', ($statusid ? 'editstatus' : 'addstatus'));
}


function list_items($statusid = 0, $error = '') {
	global $me, $db, $t, $_gv, $STRING, $TITLE, $QUERY;

	if (empty($_gv['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
	$nr = $db->getOne("select count(*) from ".TBL_STATUS);

	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

	$t->assign('statuses', $db->getAll($db->modifyLimitQuery(
		sprintf($QUERY['admin-list-statuses'], $order, $sort), $llimit, $selrange)));

	$headers = array(
		'statusid' => 'status_id',
		'name' => 'status_name',
		'description' => 'status_desc',
		'sortorder' => 'sort_order');

	sorting_headers($me, $headers, $order, $sort);

	$t->wrap('admin/statuslist.html', 'status');
}

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : list_items(); break;
	case 'edit' : show_form($_gv['status_id']); break;
	case 'del' : del_item($_gv['status_id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['status_id']);
} else list_items();

?>
