<?php

// status.php - Interface to the Status table
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
// $Id: status.php,v 1.23 2002/03/17 01:38:31 bcurtis Exp $

define('TEMPLATE_PATH', 'admin');
include '../include.php';

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
	global $db, $me, $_pv, $STRING;

	extract($_pv);
	$error = '';
	// Validation
	if (!$fname = trim($fname))
		$error = $STRING['givename'];
	elseif (!$fdescription = trim($fdescription))
		$error = $STRING['givedesc'];
	if ($error) { list_items($statusid, $error); return; }

	if (!$statusid) {
		$db->query("insert into ".TBL_STATUS.
			" (status_id, status_name, status_desc, sort_order) values (".
			$db->nextId(TBL_STATUS).", '$fname', '$fdescription', '$fsortorder')");
	} else {
		$db->query("update ".TBL_STATUS.
			" set status_name = '$fname', status_desc = '$fdescription', 
			sort_order = '$fsortorder' where status_id = '$statusid'");
	}
	header("Location: $me?");
}

function show_form($statusid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	extract($_pv);
	if ($statusid && !$error) {
		$row = $db->getRow("select * from ".TBL_STATUS.
			" where status_id = '$statusid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fstatusid' => $row['status_id'],
			'fname' => $row['status_name'],
			'fdescription' => $row['status_desc'],
			'fsortorder' => $row['sort_order']));
	} else {
		$t->set_var(array(
			'action' => $statusid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fstatusid' => $statusid,
			'fname' => isset($fname) ? $fname : '',
			'fdescription' => isset($fdescription) ? $fdescription : '',
			'fsortorder' => isset($fsortorder) ? $fsortorder : 0));
	}
}


function list_items($statusid = 0, $error = '') {
	global $me, $db, $t, $_gv, $STRING, $TITLE;

	$t->set_file('content','statuslist.html');
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
	
	$nr = $db->getOne("select count(*) from ".TBL_STATUS);

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");

	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));

	$rs = $db->limitQuery('select s.status_id, status_name, status_desc,'.
		' sort_order, count(bug_id) as bug_count from '.TBL_STATUS.' s left join '.
		TBL_BUG.' using (status_id) group by s.status_id, status_name, status_desc,'.
		" sort_order order by $order $sort", $llimit, $selrange);

	if (!$rs->numRows()) {
		$t->set_var('rows',"<tr><td>{$STRING['nostatuses']}</td></tr>");
		return;
	}

	$headers = array(
		'statusid' => 'status_id',
		'name' => 'status_name',
		'description' => 'status_desc',
		'sortorder' => 'sort_order');

	sorting_headers($me, $headers, $order, $sort);

	$i = 0;
	while ($rs->fetchInto($row)) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'trclass' => $i % 2 ? '' : 'alt',
			'statusid' => $row['status_id'],
			'name' => $row['status_name'],
			'description' => $row['status_desc'],
			'sortorder' => $row['sort_order'],
			));
		if ($row['bug_count']) {
			$t->set_var('deleteb', '&nbsp');
		} else {
			$t->parse('deleteb', 'deleteblock', false);
		}
		$t->parse('rows','row',true);
	}

	show_form($statusid, $error);
	$t->set_var('TITLE',$TITLE['status']);
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
