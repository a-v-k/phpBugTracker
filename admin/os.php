<?php

// os.php - Interface to the OS table
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
// $Id: os.php,v 1.24 2002/03/30 19:12:28 bcurtis Exp $

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
	global $db, $me, $_pv, $STRING;

	extract($_pv);
	$error = '';
	// Validation
	if (!$fname = trim($fname))
		$error = $STRING['givename'];
	if ($error) { list_items($osid, $error); return; }

	if (!$osid) {
		$db->query("insert into ".TBL_OS." (os_id, os_name, regex, sort_order) ".
			"values (".$db->nextId(TBL_OS).", ".$db->quote(stripslashes($fname)).
			", '$fregex', '$fsortorder')");
	} else {
		$db->query("update ".TBL_OS." set os_name = ".$db->quote(stripslashes($fname)).
			", regex = '$fregex', sort_order = '$fsortorder' where os_id = '$osid'");
	}
	header("Location: $me?");
}

function show_form($osid = 0, $error = '') {
	global $db, $me, $t, $_pv, $STRING;

	extract($_pv);
	if ($osid && !$error) {
		$row = $db->getRow("select * from ".TBL_OS." where os_id = '$osid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fosid' => $row['os_id'],
			'fname' => $row['os_name'],
			'fregex' => $row['regex'],
			'fsortorder' => $row['sort_order']));
	} else {
		$t->set_var(array(
			'action' => $osid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fosid' => $osid,
			'fname' => isset($fname) ? $fname : '',
			'fregex' => isset($fregex) ? $fregex : '',
			'fsortorder' => isset($fsortorder) ? $fsortorder : ''));
	}
}


function list_items($osid = 0, $error = '') {
	global $db, $me, $t, $_gv, $STRING, $TITLE, $QUERY;

	$t->set_file('content','oslist.html');
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
	
	
	$nr = $db->getOne("select count(*) from ".TBL_OS);

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");

	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));

	$rs = $db->limitQuery(sprintf($QUERY['admin-list-oses'], $order, $sort), 
		$llimit, $selrange);

	if (!$rs->numRows()) {
		$t->set_var('rows',"<tr><td>{$STRING['nooses']}</td></tr>");
		return;
	}

	$headers = array(
		'osid' => 'os_id',
		'name' => 'os_name',
		'regex' => 'regex',
		'sortorder' => 'sort_order');

	sorting_headers($me, $headers, $order, $sort);

	$i = 0;
	while ($rs->fetchInto($row)) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'trclass' => $i % 2 ? '' : 'alt',
			'osid' => $row['os_id'],
			'name' => $row['os_name'],
			'regex' => $row['regex'],
			'sortorder' => $row['sort_order']));
		if ($row['bug_count']) {
			$t->set_var('deleteb', '&nbsp');
		} else {
			$t->parse('deleteb', 'deleteblock', false);
		}
		$t->parse('rows','row',true);
	}

	show_form($osid, $error);
	$t->set_var('TITLE',$TITLE['os']);
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
