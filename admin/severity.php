<?php

// severity.php - Interface to the severity table
// ------------------------------------------------------------------------
// Copyright (c) 2001 - 2004 The phpBugTracker Group
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
// $Id: severity.php,v 1.26 2004/10/25 12:06:59 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($severityid = 0) {
	global $db, $me;
	
	if ($severityid) {
		// Make sure we are going after a valid record
		$itemexists = $db->getOne('select count(*) from '.TBL_SEVERITY." where severity_id = $severityid");
		// Are there any bugs tied to this one?
		$bugcount = $db->getOne('select count(*) from '.TBL_BUG." where severity_id = $severityid");
		if ($itemexists and !$bugcount) {
			$db->query('delete from '.TBL_SEVERITY." where severity_id = $severityid");
		}
	}
	header("Location: $me?");
}

function do_form($severityid = 0) {
	global $db, $me, $t;

	extract($_POST);
	$error = '';
	// Validation
	if (!$severity_name = trim($severity_name))
		$error = translate("Please enter a name");
	elseif (!$severity_desc = trim($severity_desc))
		$error = translate("Please enter a description");
	if ($error) { show_form($severityid, $error); return; }

	if (empty($sort_order)) $sort_order = 0;
	if (!$severityid) {
		$db->query("insert into ".TBL_SEVERITY." (severity_id, severity_name, severity_desc, sort_order, severity_color)  values (".$db->nextId(TBL_SEVERITY).', '.$db->quote(stripslashes($severity_name)).', '.$db->quote(stripslashes($severity_desc)).", $sort_order, ".$db->quote(stripslashes($severity_color)).')');
	} else {
		$db->query("update ".TBL_SEVERITY." set severity_name = ".$db->quote(stripslashes($severity_name)).', severity_desc = '.$db->quote(stripslashes($severity_desc)).", sort_order = $sort_order, severity_color = ".$db->quote(stripslashes($severity_color))." where severity_id = $severity_id");
	}
	if ($use_js) {
		$t->render('edit-submit.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($severityid = 0, $error = '') {
	global $db, $me, $t;

	if ($severityid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_SEVERITY." where severity_id = '$severityid'"));
	} else {
 		$t->assign($_POST);
	}
	$t->assign('error', $error);
	$t->render('severity-edit.html', translate("Edit Severity"),
		!empty($_REQUEST['use_js']) ? 'wrap-popup.html' : 'wrap.html');
}


function list_items($severityid = 0, $error = '') {
	global $me, $db, $t, $QUERY;

	if (empty($_GET['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_GET['order']; 
		$sort = $_GET['sort']; 
	}
	
	$page = isset($_GET['page']) ? $_GET['page'] : 0;
	
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

	$t->render('severitylist.html', translate("Severity List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
	switch($_REQUEST['op']) {
		case 'add' : list_items(); break;
		case 'edit' : show_form($_GET['severity_id']); break;
		case 'del' : del_item($_GET['severity_id']); break;
		case 'save' : do_form($_POST['severity_id']);
	}
} else list_items();

?>
