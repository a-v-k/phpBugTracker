<?php

// severity.php - Interface to the severity table
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
// $Id: severity.php,v 1.24 2002/05/18 03:00:00 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($severityid = 0) {
	global $db, $me;
	
	if ($severityid) {
		// Make sure we are going after a valid record
		$itemexists = $db->getOne('select count(*) from '.TBL_SEVERITY.
			" where severity_id = $severityid");
		// Are there any bugs tied to this one?
		$bugcount = $db->getOne('select count(*) from '.TBL_BUG.
			" where severity_id = $severityid");
		if ($itemexists and !$bugcount) {
			$db->query('delete from '.TBL_SEVERITY." where severity_id = $severityid");
		}
	}
	header("Location: $me?");
}

function do_form($severityid = 0) {
	global $db, $me, $_pv, $STRING, $t;

	extract($_pv);
	$error = '';
	// Validation
	if (!$severity_name = trim($severity_name))
		$error = $STRING['givename'];
	elseif (!$severity_desc = trim($severity_desc))
		$error = $STRING['givedesc'];
	if ($error) { show_form($severityid, $error); return; }

	if (!$severityid) {
		$db->query("insert into ".TBL_SEVERITY.
			" (severity_id, severity_name, severity_desc, sort_order, severity_color) 
			values (".$db->nextId(TBL_SEVERITY).', '.
			$db->quote(stripslashes($severity_name)).', '.
			$db->quote(stripslashes($severity_desc)).", $sort_order, ".
			$db->quote(stripslashes($severity_color)).')');
	} else {
		$db->query("update ".TBL_SEVERITY.
			" set severity_name = ".$db->quote(stripslashes($severity_name)).
			', severity_desc = '.$db->quote(stripslashes($severity_desc)).
			", sort_order = $sort_order, severity_color = ".
			$db->quote(stripslashes($severity_color))." where severity_id = $severity_id");
	}
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($severityid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	if ($severityid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_SEVERITY.
			" where severity_id = '$severityid'"));
	} else {
 		$t->assign($_pv);
	}
	$t->assign('error', $error);
	$t->wrap('admin/severity-edit.html', ($severityid ? 'editseverity' : 'addseverity'));
}


function list_items($severityid = 0, $error = '') {
	global $me, $db, $t, $_gv, $STRING, $TITLE, $QUERY;

	if (empty($_gv['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
	$nr = $db->getOne("select count(*) from ".TBL_SEVERITY);

	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

	$t->assign('severities', $db->getAll($db->modifyLimitQuery(
		sprintf($QUERY['admin-list-severities'], $order, $sort), $llimit, $selrange)));


	$headers = array(
		'severityid' => 'severity_id',
		'name' => 'severity_name',
		'description' => 'severity_desc',
		'sortorder' => 'sort_order',
		'color' => 'severity_color');

	sorting_headers($me, $headers, $order, $sort);

	$t->wrap('admin/severitylist.html', 'severity');
}

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : list_items(); break;
	case 'edit' : show_form($_gv['severity_id']); break;
	case 'del' : del_item($_gv['severity_id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['severity_id']);
} else list_items();

?>
