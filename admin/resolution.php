<?php

// resolution.php - Interface to the Resolution table
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
// $Id: resolution.php,v 1.25 2002/03/30 19:12:30 bcurtis Exp $

define('TEMPLATE_PATH', 'admin');
include '../include.php';

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
	global $db, $me, $_pv, $STRING;

	extract($_pv);
	$error = '';
	// Validation
	if (!$fname = trim($fname))
		$error = $STRING['givename'];
	elseif (!$fdescription = trim($fdescription))
		$error = $STRING['givedesc'];
	if ($error) { list_items($resolutionid, $error); return; }

	if (!$resolutionid) {
		$db->query("insert into ".TBL_RESOLUTION.
			" (resolution_id, resolution_name, resolution_desc, sort_order)"
			." values (".$db->nextId(TBL_RESOLUTION).", ".
			$db->quote(stripslashes($fname)).', '.
			$db->quote(stripslashes($fdescription)).', '.$fsortorder.')');
	} else {
		$db->query("update ".TBL_RESOLUTION.
			' set resolution_name = '.$db->quote(stripslashes($fname)).
			', resolution_desc = '.$db->quote(stripslashes($fdescription)).
			", sort_order = $fsortorder where resolution_id = $resolutionid");
	}
	header("Location: $me?");
}

function show_form($resolutionid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	extract($_pv);
	if ($resolutionid && !$error) {
		$row = $db->getRow("select * from ".TBL_RESOLUTION.
			" where resolution_id = '$resolutionid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fresolutionid' => $row['resolution_id'],
			'fname' => $row['resolution_name'],
			'fdescription' => $row['resolution_desc'],
			'fsortorder' => $row['sort_order']));
	} else {
		$t->set_var(array(
			'action' => $resolutionid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fresolutionid' => $resolutionid,
			'fname' => isset($fname) ? $fname : '',
			'fdescription' => isset($fdescription) ? $fdescription : '',
			'fsortorder' => isset($fsortorder) ? $fsortorder : 0));
	}
}


function list_items($resolutionid = 0, $error = '') {
	global $me, $db, $t, $STRING, $TITLE, $_gv, $QUERY;

	$t->set_file('content','resolutionlist.html');
	$t->set_block('content','row','rows');
	$t->set_block('row','deleteblock','deleteb');

	if (empty($_gv['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
	$nr = $db->getOne("select count(*) from ".TBL_RESOLUTION);

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");

	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));

	$rs = $db->limitQuery(sprintf($QUERY['admin-list-resolutions'], $order, $sort), 
		$llimit, $selrange);

	if (!$rs->numRows()) {
		$t->set_var('rows',"<tr><td>{$STRING['noresolutions']}</td></tr>");
		return;
	}

	$headers = array(
		'resolutionid' => 'resolution_id',
		'name' => 'resolution_name',
		'description' => 'resolution_desc',
		'sortorder' => 'sort_order');

	sorting_headers($me, $headers, $order, $sort);

	$i = 0;
	while ($rs->fetchInto($row)) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'trclass' => $i % 2 ? '' : 'alt',
			'resolutionid' => $row['resolution_id'],
			'name' => $row['resolution_name'],
			'description' => $row['resolution_desc'],
			'sortorder' => $row['sort_order']));
		if ($row['bug_count']) {
			$t->set_var('deleteb', '&nbsp');
		} else {
			$t->parse('deleteb', 'deleteblock', false);
		}
		$t->parse('rows','row',true);
	}

	show_form($resolutionid, $error);
	$t->set_var('TITLE',$TITLE['resolution']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : list_items(); break;
	case 'edit' : list_items($_gv['id']); break;
	case 'del' : del_item($_gv['id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['id']);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

?>
