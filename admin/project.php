<?php

include '../include.php';
error_reporting(E_ALL);
function do_form($projectid = 0) {
	global $q, $me, $name, $description, $active, $version, $u, $STRING, $now;
	
	// Validation
	if (!$name = htmlspecialchars(trim($name))) 
		$error = $STRING['givename'];
	elseif (!$description = htmlspecialchars(trim($description)))
		$error = $STRING['givedesc'];
	elseif (!projectid and !$version = htmlspecialchars(trim($version)))
		$error = $STRING['giveversion'];
	if ($error) { show_form($projectid, $error); return; }
	
	if (!$active) $active = 0;
	if (!$projectid) {
		$projectid = $q->nextid('Project');
		$q->query("insert into Project (ProjectID, Name, Description, Active, CreatedBy, CreatedDate) values ($projectid , '$name', '$description', $active, $u, $now)");
		$q->query("insert into Version (VersionID, ProjectID, Name, Active, CreatedBy, CreatedDate) values (".$q->nextid('Version').", $projectid, '$version', $active, $u, $now)");
		$location = "component.php?op=add&projectid=$projectid";
	} else {
		$q->query("update Project set Name = '$name', Description = '$description', Active = $active where ProjectID = $projectid"); 
		$location = "$me?";
	}
	header("Location: $location");
}	

function show_form($projectid = 0, $error = '') {
	global $q, $me, $t, $name, $description, $active, $version, $TITLE;
	
	$t->set_file('content','projectform.html');
	$t->set_block('content','box','details');
	$t->set_block('content','vfield','verfield');
	if ($projectid && !$error) {
		$row = $q->grab("select * from Project where ProjectID = $projectid");
		$t->set_var(array(
			'projectid' => $row['ProjectID'],
			'name' => $row['Name'],
			'description' => $row['Description'],
			'active' => $row['Active'] ? 'checked' : '',
			'createdby' => $row['CreatedBy'],
			'createddate' => $row['CreatedDate'],
			'TITLE' => $TITLE['editproject']
			));
	} else {
		$t->set_var(array(
			'error' => $error,
			'projectid' => $projectid,
			'name' => $name,
			'description' => $description,
			'active' => (isset($active) and !$active) ? '' : 'checked',
			'createdby' => $createdby,
			'createddate' => $createddate,
			'TITLE' => $projectid ? $TITLE['editproject'] : $TITLE['addproject']
			));
	}
	if ($projectid) {
		$t->set_var('verfield','');
		list_components($projectid);
		list_versions($projectid);
		$t->parse('details','box',true);
	} else {
		$t->set_var(array(
			'details' => '',
			'version' => $version
			));
		$t->parse('verfield','vfield',true);
	}
}

function list_versions($projectid) {
	global $q, $t, $STRING;
	
	$t->set_block('box','verrow','verrows');
	$q->query("select * from Version where ProjectID = $projectid");
	if (!$q->num_rows()) {
		$t->set_var('verrows',"<tr><td colspan='2' align='center'>{$STRING['noversions']}</td></tr>");
		return;
	}

	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'verid' => $row['VersionID'],
			'vername' => $row['Name'],
			'verdate' => date(DATEFORMAT,$row['CreatedDate']),
			'veractive' => $row['Active'] ? 'Y' : 'N'
			));
		$t->parse('verrows','verrow',true);
	}
}
			

function list_components($projectid) {
	global $q, $t, $STRING;
				
	$t->set_block('box','row','rows');
	$q->query("select * from Component where ProjectID = $projectid");
	if (!$q->num_rows()) {
		$t->set_var('rows',"<tr><td colspan='2' align='center'>{$STRING['nocomponents']}</td></tr>");
		return;
	}

	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'compid' => $row['ComponentID'],
			'compname' => $row['Name'],
			'compdesc' => stripslashes($row['Description']),
			'owner' => $row['Owner'],
			'compactive' => $row['Active'] ? 'Y' : 'N',
			'createdby' => $row['CreatedBy'],
			'compdate' => date(DATEFORMAT,$row['CreatedDate']),
			'lastmodifiedby' => $row['LastModifiedBy'],
			'lastmodifieddate' => date(DATEFORMAT,$row['LastModifiedDate'])
			));
		$t->parse('rows','row',true);
	}
}

function list_items() {
	global $me, $q, $t, $selrange, $order, $sort, $STRING, $TITLE, $page;
				
	$t->set_file('content','projectlist.html');
	$t->set_block('content','row','rows');
				
	if (!$order) { $order = '1'; $sort = 'asc'; }
	$nr = $q->query("select count(*) from Project");

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");
								
	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr,
		'TITLE' => $TITLE['project']
		));
								
	$q->query("select * from Project order by $order $sort limit $llimit, $selrange");
				
	if (!$q->num_rows()) {
		$t->set_var('rows',"<tr><td>{$STRING['noprojects']}</td></tr>");
		return;
	}

	$headers = array(
		'projectid' => 'ProjectID',
		'name' => 'Name',
		'description' => 'Description',
		'active' => 'Active',
		'createdby' => 'CreatedBy',
		'createddate' => 'CreatedDate'
		);

	sorting_headers($me, $headers, $order, $sort);
	$i = 0;			
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'projectid' => $row['ProjectID'],
			'name' => $row['Name'],
			'description' => stripslashes($row['Description']),
			'active' => $row['Active'] ? 'Y' : 'N',
			'createdby' => $row['CreatedBy'],
			'createddate' => date(DATEFORMAT,$row['CreatedDate'])
			));
		$t->parse('rows','row',true);
	}
}

$t->set_file('wrap','wrap.html');

$perm->check('Administrator');

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'add' : show_form(); break;
	case 'edit' : show_form($_gv['id']); break;
} elseif(isset($_pv['submit'])) {		 
	do_form($_pv['id']);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
