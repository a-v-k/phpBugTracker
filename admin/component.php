<?php

// component.php - Admin the components of projects
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

function do_form($componentid = 0) {
	global $q, $me, $projectid, $name, $description, $owner, $active, $u, $STRING;
	
	// Validation
	if (!$name = trim($name)) 
		$error = $STRING['givename'];
	elseif (!$description = trim($description))
		$error = $STRING['givedesc'];
	if ($error) { show_form($componentid, $error); return; }
	
	$time = time();
	if (!$owner) $owner = 0;
	if (!$active) $active = 0;
	if (!$componentid) {
		$q->query("insert into Component (ComponentID, ProjectID, Name, Description, Owner, Active, CreatedBy, CreatedDate, LastModifiedBy, LastModifiedDate) values (".$q->nextid('Component').", $projectid, '$name', '$description', $owner, $active, $u, $time, $u, $time)");
	} else {
		$q->query("update Component set Name = '$name', Description = '$description', Owner = $owner, Active = $active, LastModifiedBy = $u, LastModifiedDate = $time where ComponentID = '$componentid'");
	}
	header("Location: project.php?op=edit&id=$projectid");
}	

function show_form($componentid = 0, $error = '') {
	global $q, $me, $t, $projectid, $name, $description, $owner, $active, 
		$createdby, $createddate, $lastmodifiedby, $lastmodifieddate, $TITLE;
	
	$t->set_file('content','componentform.html');
	if ($componentid && !$error) {
		$row = $q->grab("select c.*, p.Name as ProjectName from Component c left join Project p using (ProjectID) where ComponentID = '$componentid'");
		$t->set_var(array(
			'componentid' => $row['ComponentID'],
			'projectid' => $row['ProjectID'],
			'project' => $row['ProjectName'],
			'name' => $row['Name'],
			'description' => $row['Description'],
			'owner' => build_select('owner',$row['Owner']),
			'active' => $row['Active'] ? 'checked' : '',
			'createdby' => $row['CreatedBy'],
			'createddate' => $row['CreatedDate'],
			'lastmodifiedby' => $row['LastModifiedBy'],
			'lastmodifieddate' => $row['LastModifiedDate'],
			'TITLE' => $TITLE['editcomponent']));
	} else {
		$t->set_var(array(
			'me' => $me,
			'error' => $error,
			'componentid' => $componentid,
			'projectid' => $projectid,
			'project' => $q->grab_field("select Name from Project where ProjectID = $projectid"),
			'name' => $name,
			'description' => $description,
			'owner' => build_select('owner',$owner),
			'active' => (isset($active) and !$active) ? '' : 'checked',
			'createdby' => $createdby,
			'createddate' => $createddate,
			'lastmodifiedby' => $lastmodifiedby,
			'lastmodifieddate' => $lastmodifieddate,
			'TITLE' => $componentid ? $TITLE['editcomponent'] : $TITLE['addcomponent']));
	}
}

$t->set_file('wrap','wrap.html');

$perm->check('Administrator');

if ($op) switch($op) {
	case 'add' : show_form(); break;
	case 'edit' : show_form($id); break;
} elseif($submit) {		 
	do_form($id);
} else header("Location: project.php");

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
