<?php

// os.php - Interface to the OS table
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
// $Id: os.php,v 1.29 2004/10/25 12:06:59 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($osid = 0) {
	global $db, $me;
	
	if ($osid) {
		// Make sure we are going after a valid record
		$itemexists = $db->getOne('select count(*) from '.TBL_OS." where os_id = $osid");
		// Are there any bugs tied to this one?
		$bugcount = $db->getOne('select count(*) from '.TBL_BUG." where os_id = $osid");
		if ($itemexists and !$bugcount) {
			$db->query('delete from '.TBL_OS." where os_id = $osid");
		}
	}
	header("Location: $me?");
}

function do_form($osid = 0) {
	global $db, $me, $t;

	extract($_POST);
	$error = '';
	// Validation
	if (!$os_name = trim($os_name))
		$error = translate("Please enter a name");
	if ($error) { show_form($osid, $error); return; }

	if (empty($sort_order)) $sort_order = 0;
	if (!$osid) {
		$db->query("insert into ".TBL_OS." (os_id, os_name, regex, sort_order) values (".$db->nextId(TBL_OS).", ".$db->quote(stripslashes($os_name)).", '$regex', '$sort_order')");
	} else {
		$db->query("update ".TBL_OS." set os_name = ".$db->quote(stripslashes($os_name)).", regex = '$regex', sort_order = '$sort_order' where os_id = '$os_id'");
	}
	if ($use_js) {
		$t->render('edit-submit.html', '', 'wrap-popup.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($osid = 0, $error = '') {
	global $db, $me, $t;

	extract($_POST);
	if ($osid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_OS." where os_id = '$osid'"));
	} else {
		$t->assign($_POST);
	}
	$t->assign('error', $error);
	$t->render('os-edit.html', translate("Edit Operating System"), 
		!empty($_REQUEST['use_js']) ? 'wrap-popup.html' : 'wrap.html');
}


function list_items($osid = 0, $error = '') {
	global $db, $me, $t, $QUERY;

	if (empty($_GET['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_GET['order']; 
		$sort = $_GET['sort']; 
	}
	
	$page = isset($_GET['page']) ? $_GET['page'] : 0;
	
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

	$t->render('oslist.html', translate("Operating System List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
	switch($_REQUEST['op']) {
		case 'save' : do_form($_POST['os_id']); break;
		case 'edit' : show_form($_GET['os_id']); break;
		case 'del' : del_item($_GET['os_id']); break;
	}
} else list_items();

?>
