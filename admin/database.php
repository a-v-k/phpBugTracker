<?php

// database.php - Interface to the database table
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
// $Id: database.php,v 1.3 2002/09/30 18:02:05 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($databaseid = 0) {
	global $db, $me;

	if ($databaseid) {
		// Make sure we are going after a valid record
		$itemexists = $db->getOne('select count(*) from '.TBL_DATABASE.
			" where database_id = $databaseid");
		// Are there any bugs tied to this one?
		$bugcount = $db->getOne('select count(*) from '.TBL_BUG.
			" where database_id = $databaseid");
		if ($itemexists and !$bugcount) {
			$db->query('delete from '.TBL_DATABASE." where database_id = $databaseid");
		}
	}
	header("Location: $me?");
}

function do_form($databaseid = 0) {
	global $db, $me, $_pv, $STRING, $t;

	extract($_pv);
	$error = '';
	// Validation
	if (!$database_name = trim($database_name))
		$error = $STRING['givename'];
	if ($error) { show_form($databaseid, $error); return; }

	if (empty($sort_order)) $sort_order = 0;
	if (!$databaseid) {
		$db->query("insert into ".TBL_DATABASE.
			" (database_id, database_name, sort_order)
			values (".$db->nextId(TBL_DATABASE).', '.
			$db->quote(stripslashes($database_name)).
			", $sort_order)");
	} else {
		$db->query("update ".TBL_DATABASE.
			" set database_name = ".$db->quote(stripslashes($database_name)).
			", sort_order = $sort_order where database_id = $database_id");
	}
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($databaseid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	if ($databaseid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_DATABASE.
			" where database_id = '$databaseid'"));
	} else {
 		$t->assign($_pv);
	}
	$t->assign('error', $error);
	$t->wrap('admin/database-edit.html', ($databaseid ? 'editdatabase' : 'adddatabase'));
}

function list_items($databaseid = 0, $error = '') {
	global $me, $db, $t, $_gv, $STRING, $TITLE, $QUERY;

	if (empty($_gv['order'])) {
		$order = 'sort_order';
		$sort = 'asc';
	} else {
		$order = $_gv['order'];
		$sort = $_gv['sort'];
	}

	$page = isset($_gv['page']) ? $_gv['page'] : 0;

	$nr = $db->getOne("select count(*) from ".TBL_DATABASE);

	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

	$t->assign('databases', $db->getAll($db->modifyLimitQuery(
		sprintf($QUERY['admin-list-databases'], $order, $sort), $llimit, $selrange)));

	$headers = array(
		'databaseid' => 'database_id',
		'name' => 'database_name',
		'sortorder' => 'sort_order');

	sorting_headers($me, $headers, $order, $sort);

	$t->wrap('admin/databaselist.html', 'database');
}

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : list_items(); break;
	case 'edit' : show_form($_gv['database_id']); break;
	case 'del' : del_item($_gv['database_id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['database_id']);
} else list_items();

?>
