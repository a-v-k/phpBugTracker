<?php

// resolution.php - Interface to the Resolution table
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
// $Id: resolution.php,v 1.30 2002/08/26 18:06:01 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($resolutionid = 0) {
	global $db, $me;
	
	if ($resolutionid) {
		// Make sure we are going after a valid record
		$itemexists = $db->getOne('select count(*) from '.TBL_RESOLUTION.
			" where resolution_id = $resolutionid");
		// Are there any bugs tied to this one?
		$bugcount = $db->getOne('select count(*) from '.TBL_BUG.
			" where resolution_id = $resolutionid");
		if ($itemexists and !$bugcount) {
			$db->query('delete from '.TBL_RESOLUTION." where resolution_id = $resolutionid");
		}
	}
	header("Location: $me?");
}

function do_form($resolutionid = 0) {
	global $db, $me, $_pv, $STRING, $t;

	extract($_pv);
	$error = '';
	// Validation
	if (!$resolution_name = trim($resolution_name))
		$error = $STRING['givename'];
	elseif (!$resolution_desc = trim($resolution_desc))
		$error = $STRING['givedesc'];
	if ($error) { show_form($resolutionid, $error); return; }

	if (empty($sort_order)) $sort_order = 0;
	if (!$resolutionid) {
		$db->query("insert into ".TBL_RESOLUTION.
			" (resolution_id, resolution_name, resolution_desc, sort_order)"
			." values (".$db->nextId(TBL_RESOLUTION).", ".
			$db->quote(stripslashes($resolution_name)).', '.
			$db->quote(stripslashes($resolution_desc)).', '.$sort_order.')');
	} else {
		$db->query("update ".TBL_RESOLUTION.
			' set resolution_name = '.$db->quote(stripslashes($resolution_name)).
			', resolution_desc = '.$db->quote(stripslashes($resolution_desc)).
			", sort_order = $sort_order where resolution_id = $resolutionid");
	}
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
		header("Location: $me?");
	}
}

function show_form($resolutionid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	extract($_pv);
	if ($resolutionid && !$error) {
		$t->assign($db->getRow("select * from ".TBL_RESOLUTION.
			" where resolution_id = '$resolutionid'"));
	} else {
 		$t->assign($_pv);
	}
	$t->assign('error', $error);
	$t->wrap('admin/resolution-edit.html', ($resolutionid ? 'editresolution' : 'addresolution'));
}


function list_items($resolutionid = 0, $error = '') {
	global $me, $db, $t, $STRING, $TITLE, $_gv, $QUERY;

	if (empty($_gv['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
	$nr = $db->getOne("select count(*) from ".TBL_RESOLUTION);

	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

	$t->assign('resolutions', $db->getAll($db->modifyLimitQuery(
		sprintf($QUERY['admin-list-resolutions'], $order, $sort), $llimit, $selrange)));

	$headers = array(
		'resolutionid' => 'resolution_id',
		'name' => 'resolution_name',
		'description' => 'resolution_desc',
		'sortorder' => 'sort_order');

	sorting_headers($me, $headers, $order, $sort, "page=$page");

	$t->wrap('admin/resolutionlist.html', 'resolution');
}

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'edit' : show_form($_gv['resolution_id']); break;
	case 'del' : del_item($_gv['resolution_id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['resolution_id']);
} else list_items();

?>
