<?php

// os.php - Interface to the OS table
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
// $Id: os.php,v 1.25 2002/04/03 00:58:26 bcurtis Exp $

define('TEMPLATE_PATH', 'admin');
include '../include.php';

function del_item($osid = 0) {
	global $db, $me;
	
	if ($osid) {
		// Make sure we are going after a valid record
		$itemexists = $db->getOne('select count(*) from '.TBL_OS.
			" where os_id = $osid");
		// Are there any bugs tied to this one?
		$bugcount = $db->getOne('select count(*) from '.TBL_BUG.
			" where os_id = $osid");
		if ($itemexists and !$bugcount) {
			$db->query('delete from '.TBL_OS." where os_id = $osid");
		}
	}
	header("Location: $me?");
}

function do_form($osid = 0) {
	global $db, $me, $_pv, $STRING, $t;

	extract($_pv);
	$error = '';
	// Validation
	if (!$os_name = trim($os_name))
		$error = $STRING['givename'];
	if ($error) { show_form($osid, $error); return; }

	if (!$osid) {
		$db->query("insert into ".TBL_OS." (os_id, os_name, regex, sort_order) ".
			"values (".$db->nextId(TBL_OS).", ".$db->quote(stripslashes($os_name)).
			", '$regex', '$sort_order')");
	} else {
		$db->query("update ".TBL_OS." set os_name = ".$db->quote(stripslashes($os_name)).
			", regex = '$regex', sort_order = '$sort_order' where os_id = '$os_id'");
	}
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($osid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	extract($_pv);
	if ($osid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_OS." where os_id = '$osid'"));
	} else {
		$t->assign($_pv);
	}
	$t->assign('error', $error);
	$t->display('admin/os-edit.html');
}


function list_items($osid = 0, $error = '') {
	global $db, $me, $t, $_gv, $STRING, $TITLE, $QUERY;

	if (empty($_gv['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
	$nr = $db->getOne("select count(*) from ".TBL_OS);

	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

	$t->assign('oses', $db->getAll($db->modifyLimitQuery(
		sprintf($QUERY['admin-list-oses'], $order, $sort), $llimit, $selrange)));

	$headers = array(
		'osid' => 'os_id',
		'name' => 'os_name',
		'regex' => 'regex',
		'sortorder' => 'sort_order');

	sorting_headers($me, $headers, $order, $sort, "page=$page");

	$t->display('admin/oslist.html');
}

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'edit' : show_form($_gv['os_id']); break;
	case 'del' : del_item($_gv['os_id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['os_id']);
} else list_items();

?>
