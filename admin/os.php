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

include '../include.php';

function do_form($osid = 0) {
	global $q, $me, $fname, $fregex, $fsortorder, $STRING;
	
	// Validation
	if (!$fname = trim($fname)) 
		$error = $STRING['givename'];
	if ($error) { list_items($osid, $error); return; }
	
	if (!$osid) {
		$q->query("insert into OS (OSID, Name, Regex, SortOrder) values (".$q->nextid('OS').", '$fname', '$fregex', '$fsortorder')");
	} else {
		$q->query("update OS set Name = '$fname', Regex = '$fregex', SortOrder = '$fsortorder' where OSID = '$osid'");
	}
	header("Location: $me?");
}	

function show_form($osid = 0, $error = '') {
	global $q, $me, $t, $fname, $fregex, $fsortorder, $STRING;
	
	#$t->set_file('content','osform.html');
	if ($osid && !$error) {
		$row = $q->grab("select * from OS where OSID = '$osid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fosid' => $row['OSID'],
			'fname' => $row['Name'],
			'fregex' => $row['Regex'],
			'fsortorder' => $row['SortOrder']));
	} else {
		$t->set_var(array(
			'action' => $osid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fosid' => $osid,
			'fname' => $fname,
			'fregex' => $fregex,
			'fsortorder' => $fsortorder));
	}
}


function list_items($osid = 0, $error = '') {
	global $q, $t, $selrange, $order, $sort, $STRING, $TITLE;
				
	$t->set_file('content','oslist.html');
	$t->set_block('content','row','rows');
				
	if (!$order) { $order = 'SortOrder'; $sort = 'asc'; }
	$nr = $q->query("select count(*) from OS where OSID = '$osid' order by $order $sort");

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");
								
	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));
								
	$q->query("select * from OS order by $order $sort limit $llimit, $selrange");
				
	if (!$q->num_rows()) {
		$t->set_var('rows',"<tr><td>{$STRING['nooses']}</td></tr>");
		return;
	}

	$headers = array(
		'osid' => 'OSID',
		'name' => 'Name',
		'regex' => 'Regex',
		'sortorder' => 'SortOrder');

	sorting_headers($me, $headers, $order, $sort);
				
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'osid' => $row['OSID'],
			'name' => $row['Name'],
			'regex' => $row['Regex'],
			'sortorder' => $row['SortOrder']));
		$t->parse('rows','row',true);
	}
	
	show_form($osid, $error);
	$t->set_var('TITLE',$TITLE['os']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Administrator');

if ($op) switch($op) {
	case 'add' : list_items(); break;
	case 'edit' : list_items($id); break;
} elseif($submit) {		 
	do_form($id);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
