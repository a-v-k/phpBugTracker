<?php

// severity.php - Interface to the severity table
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
// $Id: severity.php,v 1.17 2002/01/26 16:46:52 bcurtis Exp $

define('TEMPLATE_PATH', 'admin');
include '../include.php';

function do_form($severityid = 0) {
	global $q, $me, $_pv, $STRING;

	extract($_pv);
	$error = '';
	// Validation
	if (!$fname = trim($fname))
		$error = $STRING['givename'];
	elseif (!$fdescription = trim($fdescription))
		$error = $STRING['givedesc'];
	if ($error) { list_items($severityid, $error); return; }

	if (!$severityid) {
		$q->query("insert into ".TBL_SEVERITY.
			" (severity_id, severity_name, severity_desc, sort_order, severity_color) 
			values (".$q->nextid(TBL_SEVERITY).", '$fname', '$fdescription', '$fsortorder', '$fcolor')");
	} else {
		$q->query("update ".TBL_SEVERITY." set severity_name = '$fname', 
			severity_desc = '$fdescription', sort_order = '$fsortorder', 
			severity_color = '$fcolor' where severity_id = '$severityid'");
	}
	header("Location: $me?");
}

function show_form($severityid = 0, $error = '') {
	global $q, $me, $t, $_pv, $STRING;

	if ($severityid && !$error) {
		$row = $q->grab("select * from ".TBL_SEVERITY.
			" where severity_id = '$severityid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fseverityid' => $row['severity_id'],
			'fname' => $row['severity_name'],
			'fdescription' => $row['severity_desc'],
			'fsortorder' => $row['sort_order'],
			'fcolor' => $row['severity_color']));
	} else {
		$t->set_var(array(
			'action' => $severityid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fseverityid' => $severityid,
			'fname' => isset($_pv['fname']) ? stripslashes($_pv['fname']) : '',
			'fdescription' => isset($_pv['fdescription']) ? 
				stripslashes($_pv['fdescription']) : '',
			'fsortorder' => isset($_pv['fsortorder']) ? $_pv['fsortorder'] : '',
			'fcolor' => isset($_pv['fcolor']) ? $_pv['fcolor'] : ''
			));
	}
}


function list_items($severityid = 0, $error = '') {
	global $me, $q, $t, $_gv, $STRING, $TITLE;

	$t->set_file('content','severitylist.html');
	$t->set_block('content','row','rows');

	if (empty($_gv['order'])) { 
		$order = 'sort_order'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
	$nr = $q->query("select count(*) from ".TBL_SEVERITY);

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");

	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));

	$q->limit_query("select * from ".TBL_SEVERITY." order by $order $sort", 
		$selrange, $llimit);


	if (!$q->num_rows()) {
		$t->set_var('rows',"<tr><td>{$STRING['noseverities']}</td></tr>");
		return;
	}

	$headers = array(
		'severityid' => 'severity_id',
		'name' => 'severity_name',
		'description' => 'severity_desc',
		'sortorder' => 'sort_order',
		'color' => 'severity_color');

	sorting_headers($me, $headers, $order, $sort);

	$i = 0;
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => USE_SEVERITY_COLOR ? $row['severity_color'] : 
				((++$i % 2 == 0) ? '#dddddd' : ''),
			'trclass' => USE_SEVERITY_COLOR ? '' : ($i % 2 ? '' : 'alt'),
			'severityid' => $row['severity_id'],
			'name' => $row['severity_name'],
			'description' => $row['severity_desc'],
			'sortorder' => $row['sort_order']));
		$t->parse('rows','row',true);
	}

	show_form($severityid, $error);
	$t->set_var('TITLE',$TITLE['severity']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : list_items(); break;
	case 'edit' : list_items($_gv['id']); break;
} elseif(isset($_pv['submit'])) {
	do_form($_pv['id']);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

?>
