<?php

// status.php - Interface to the Status table

include '../include.php';

page_open(array('sess' => 'usess', 'auth' => 'uauth', 'perm' => 'uperm'));

function do_form($statusid = 0) {
	global $q, $me, $fname, $fdescription, $fsortorder, $STRING;
	
	// Validation
	if (!$fname = trim($fname))
		$error = $STRING['givename'];
	elseif (!$fdescription = trim($fdescription))
		$error = $STRING['givedesc'];
	if ($error) { list_items($statusid, $error); return; }
	
	if (!$statusid) {
		$q->query("insert into Status (StatusID, Name, Description, SortOrder) values (".$q->nextid('Status').", '$fname', '$fdescription', '$fsortorder')");
	} else {
		$q->query("update Status set Name = '$fname', Description = '$fdescription', SortOrder = '$fsortorder' where StatusID = '$statusid'");
	}
	header("Location: $me?");
}	

function show_form($statusid = 0, $error = '') {
	global $q, $me, $t, $fname, $fdescription, $fsortorder, $STRING;
	
	if ($statusid && !$error) {
		$row = $q->grab("select * from Status where StatusID = '$statusid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fstatusid' => $row['StatusID'],
			'fname' => $row['Name'],
			'fdescription' => $row['Description'],
			'fsortorder' => $row['SortOrder']));
	} else {
		$t->set_var(array(
			'action' => $statusid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fstatusid' => $statusid,
			'fname' => $fname,
			'fdescription' => $fdescription,
			'fsortorder' => $fsortorder));
	}
}


function list_items($statusid = 0, $error = '') {
	global $q, $t, $selrange, $order, $sort, $STRING, $TITLE;
				
	$t->set_file('content','statuslist.html');
	$t->set_block('content','row','rows');
				
	if (!$order) { $order = 'SortOrder'; $sort = 'asc'; }
	$nr = $q->query("select count(*) from Status where StatusID = '$statusid' order by $order $sort");

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");
								
	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));
								
	$q->query("select * from Status order by $order $sort limit $llimit, $selrange");
				
	if (!$q->num_rows()) {
		$t->set_var('rows',"<tr><td>{$STRING['nostatuses']}</td></tr>");
		return;
	}

	$headers = array(
		'statusid' => 'StatusID',
		'name' => 'Name',
		'description' => 'Description',
		'sortorder' => 'SortOrder');

	sorting_headers($me, $headers, $order, $sort);
				
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'statusid' => $row['StatusID'],
			'name' => $row['Name'],
			'description' => $row['Description'],
			'sortorder' => $row['SortOrder']));
		$t->parse('rows','row',true);
	}
	
	show_form($statusid, $error);
	$t->set_var('TITLE',$TITLE['status']);
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
