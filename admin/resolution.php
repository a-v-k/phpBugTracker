<?php

// resolution.php - Interface to the Resolution table

include '../include.php';

page_open(array('sess' => 'usess', 'auth' => 'uauth', 'perm' => 'uperm'));

function do_form($resolutionid = 0) {
	global $q, $me, $fname, $fdescription, $fsortorder, $STRING;
	
	// Validation
	if (!$fname = trim($fname))
		$error = $STRING['givename'];
	elseif (!$fdescription = trim($fdescription))
		$error = $STRING['givedesc'];
	if ($error) { list_items($resolutionid, $error); return; }
	
	if (!$resolutionid) {
		$q->query("insert into Resolution (ResolutionID, Name, Description, SortOrder) values (".$q->nextid('Resolution').", '$fname', '$fdescription', '$fsortorder')");
	} else {
		$q->query("update Resolution set Name = '$fname', Description = '$fdescription', SortOrder = '$fsortorder' where ResolutionID = '$resolutionid'");
	}
	header("Location: $me?");
}	

function show_form($resolutionid = 0, $error = '') {
	global $q, $me, $t, $fname, $fdescription, $fsortorder, $STRING;
	
	if ($resolutionid && !$error) {
		$row = $q->grab("select * from Resolution where ResolutionID = '$resolutionid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fresolutionid' => $row['ResolutionID'],
			'fname' => $row['Name'],
			'fdescription' => $row['Description'],
			'fsortorder' => $row['SortOrder']));
	} else {
		$t->set_var(array(
			'action' => $resolutionid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fresolutionid' => $resolutionid,
			'fname' => $fname,
			'fdescription' => $fdescription,
			'fsortorder' => $fsortorder));
	}
}


function list_items($resolutionid = 0, $error = '') {
	global $q, $t, $selrange, $order, $sort, $STRING, $TITLE;
				
	$t->set_file('content','resolutionlist.html');
	$t->set_block('content','row','rows');
				
	if (!$order) { $order = 'SortOrder'; $sort = 'asc'; }
	$nr = $q->query("select count(*) from Resolution where ResolutionID = '$resolutionid' order by $order $sort");

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");
								
	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));
								
	$q->query("select * from Resolution order by $order $sort limit $llimit, $selrange");
				
	if (!$q->num_rows()) {
		$t->set_var('rows',"<tr><td>{$STRING['noresolutions']}</td></tr>");
		return;
	}

	$headers = array(
		'resolutionid' => 'ResolutionID',
		'name' => 'Name',
		'description' => 'Description',
		'sortorder' => 'SortOrder');

	sorting_headers($me, $headers, $order, $sort);
				
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'resolutionid' => $row['ResolutionID'],
			'name' => $row['Name'],
			'description' => $row['Description'],
			'sortorder' => $row['SortOrder']));
		$t->parse('rows','row',true);
	}
	
	show_form($resolutionid, $error);
	$t->set_var('TITLE',$TITLE['resolution']);
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
