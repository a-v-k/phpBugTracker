<?php

// project.php - Create and update projects
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
// $Id: project.php,v 1.19 2001/10/02 03:41:19 bcurtis Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function save_version($versionid = 0) {
  global $q, $me, $_pv, $STRING, $now, $u;

  // Validation
  if (!$_pv['vf_version'] = trim($_pv['vf_version']))
    $error['version'] = $STRING['giveversion'];
  if ($error) { show_project($_pv['projectid'], $error); return; }

	foreach ($_pv as $k => $v) $$k = $v;
  if (!$vf_active) $vf_active = 0;
  if (!$versionid) {
    $q->query('insert into '.TBL_VERSION
			." (version_id, project_id, version_name, active, created_by, created_date) 
			values (".$q->nextid(TBL_VERSION).", $projectid, '$vf_version', $vf_active, $u, $now)");
  } else {
    $q->query('update '.TBL_VERSION
			." set project_id = $projectid, version_name = $vf_version, 
			active = $vf_active where version_id = '$versionid'");
  }
  header("Location: project.php?op=edit&id=$projectid");
}

function show_version($versionid = 0, $error = '') {
  global $q, $t, $_pv, $STRING;

	foreach ($_pv as $k => $v) $$k = $v;
	
  if ($versionid && !$error) {
    $row = $q->grab("select v.*, p.project_name as project_name"
    	." from ".TBL_VERSION." v left join ".TBL_PROJECT." p using(project_id)"
			." where version_id = '$versionid'");
    $t->set_var(array(
      'vf_error' => '',
      'versionid' => $row['version_id'],
      'vf_version' => $row['version_name'],
      'vf_active' => $row['active'] ? 'checked' : '',
      'vf_action' => $STRING['edit']));
  } else {
    $t->set_var(array(
      'vf_error' => $error,
      'versionid' => $versionid,
      'vf_version' => $vf_version,
      'vf_active' => isset($vf_active) && !$vf_active ? '' : ' checked',
      'vf_action' => $versionid ? $STRING['edit'] : $STRING['addnew']));
  }
}

function list_versions($projectid) {
  global $q, $t, $STRING;

  $q->query("select * from ".TBL_VERSION." where project_id = $projectid");
  if (!$q->num_rows()) {
    $t->set_var('verrows',"<tr><td colspan='2' align='center'>{$STRING['noversions']}</td></tr>");
    return;
  }

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'verid' => $row['version_id'],
      'vername' => $row['version_name'],
      'verdate' => date(DATEFORMAT,$row['created_date']),
      'veractive' => $row['active'] ? 'Y' : 'N'
      ));
    $t->parse('verrows','verrow',true);
  }
}

function save_component($componentid = 0) {
	global $q, $me, $_pv, $u, $STRING, $now;
	
	// Validation
	if (!$_pv['cf_name'] = trim($_pv['cf_name'])) 
		$error['component'] = $STRING['givename'];
	elseif (!$_pv['cf_description'] = trim($_pv['cf_description']))
		$error['component'] = $STRING['givedesc'];
	if ($error) { show_project($_pv['projectid'], $error); return; }
	
	foreach ($_pv as $k => $v) $$k = $v;
	if (!$cf_owner) $cf_owner = 0;
	if (!$cf_active) $cf_active = 0;
	if (!$componentid) {
		$q->query('insert into '.TBL_COMPONENT
			." (component_id, project_id, component_name, component_desc, owner, 
			active, created_by, created_date, last_modified_by, last_modified_date) 
			values (".$q->nextid(TBL_COMPONENT).", $projectid, '$cf_name', 
			'$cf_description', $cf_owner, $cf_active, $u, $now, $u, $now)");
	} else {
		$q->query('update '.TBL_COMPONENT
			." set component_name = '$cf_name', component_desc = '$cf_description', 
			owner = $cf_owner, active = $cf_active, last_modified_by = $u, 
			last_modified_date = $now where component_id = '$componentid'");
	}
	header("Location: project.php?op=edit&id=$projectid");
}	

function show_component($componentid = 0, $error = '') {
	global $q, $t, $_pv, $STRING;
	
	foreach ($_pv as $k => $v) $$k = $v;
	
	if ($componentid && !$error) {
		$row = $q->grab('select c.*, p.project_name as project_name
			from '.TBL_COMPONENT.' c  left join '.TBL_PROJECT." p using (project_id)
			where component_id = '$componentid'");
		$t->set_var(array(
			'cf_error' => '',
			'componentid' => $row['component_id'],
			'cf_name' => $row['component_name'],
			'cf_description' => $row['component_desc'],
			'cf_owner' => build_select('owner', $row['owner']),
			'cf_active' => $row['active'] ? 'checked' : '',
			'cf_action' => $STRING['edit']));
	} else {
		$t->set_var(array(
			'cf_error' => $error,
			'componentid' => $componentid,
			'cf_name' => $cf_name,
			'cf_description' => $cf_description,
			'cf_owner' => build_select('owner', $cf_owner),
			'cf_active' => (isset($cf_active) and !$cf_active) ? '' : 'checked',
			'cf_action' => $componentid ? $STRING['edit'] : $STRING['addnew']));
	}
}

function list_components($projectid) {
  global $q, $t, $STRING;

  $q->query("select * from ".TBL_COMPONENT." where project_id = $projectid");
  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td colspan='2' align='center'>{$STRING['nocomponents']}</td></tr>");
    return;
  }

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'compid' => $row['component_id'],
      'compname' => $row['component_name'],
      'compdesc' => stripslashes($row['project_desc']),
      'owner' => $row['Owner'],
      'compactive' => $row['active'] ? 'Y' : 'N',
      'createdby' => $row['created_by'],
      'compdate' => date(DATEFORMAT,$row['created_date']),
      'lastmodifiedby' => $row['last_modified_by'],
      'lastmodifieddate' => date(DATEFORMAT,$row['last_modified_date'])
      ));
    $t->parse('rows','row',true);
  }
}

function save_project($projectid = 0) {
  global $q, $me, $u, $STRING, $now, $_pv;

  // Validation
  if (!$_pv['name'] = htmlspecialchars(trim($_pv['name']))) {
    $error['project'] = $STRING['givename'];
  } elseif (!$_pv['description'] = htmlspecialchars(trim($_pv['description']))) {
    $error['project'] = $STRING['givedesc'];
  } 
	if ($error) { show_project($projectid, $error); return; }
	
	if (!$projectid) {
		if (!$_pv['vf_version'] = htmlspecialchars(trim($_pv['vf_version']))) {
    	$error['version'] = $STRING['giveversion'];
  	} elseif (!$_pv['cf_name'] = trim($_pv['cf_name'])) {
			$error['component'] = $STRING['givename'];
		} elseif (!$_pv['cf_description'] = trim($_pv['cf_description'])) {
			$error['component'] = $STRING['givedesc'];
		}
	}
	if ($error) { show_project($projectid, $error); return; }

	foreach ($_pv as $k => $v) $$k = $v;
  if (!$active) $active = 0;
  if (!$projectid) {
    $projectid = $q->nextid(TBL_PROJECT);
    $q->query('insert into '.TBL_PROJECT
			." (project_id, project_name, project_desc, active, created_by, created_date)
      values ($projectid , '$name', '$description', $active, $u, $now)");
    $q->query('insert into '.TBL_VERSION
			." (version_id, project_id, version_name, active, created_by, created_date) 
			values (".$q->nextid(TBL_VERSION).", $projectid, '$vf_version', 1, $u, $now)");
		$q->query('insert into '.TBL_COMPONENT
			." (component_id, project_id, component_name, component_desc, owner, 
			active, created_by, created_date, last_modified_by, last_modified_date) 
			values (".$q->nextid(TBL_COMPONENT).", $projectid, '$cf_name', 
			'$cf_description', $cf_owner, 1, $u, $now, $u, $now)");
  } else {
    $q->query('update '.TBL_PROJECT
			." set project_name = '$name', project_desc = '$description', 
			active = $active where project_id = $projectid");
  }
  header("Location: $me?op=edit&id=$projectid");
}

function show_project($projectid = 0, $error = array()) {
  global $q, $me, $t, $name, $description, $active, $TITLE, $_gv;

  if ($projectid && !$error) {
    $row = $q->grab('select * from '.TBL_PROJECT
			." where project_id = $projectid");
    $t->set_var(array(
      'projectid' => $row['project_id'],
      'name' => $row['project_name'],
      'description' => $row['project_desc'],
      'active' => $row['active'] ? 'checked' : '',
      'TITLE' => $TITLE['editproject']
      ));
  } else {
    $t->set_var(array(
      'error' => $error['project'],
      'projectid' => $projectid,
      'name' => stripslashes($name),
      'description' => stripslashes($description),
      'active' => (isset($active) and !$active) ? '' : 'checked',
      'TITLE' => $projectid ? $TITLE['editproject'] : $TITLE['addproject']
      ));
  }
	
  if ($projectid) {
		$t->set_file('content', 'project-edit.html');
		$t->set_block('content', 'verrow', 'verrows');
		$t->set_block('content', 'row', 'rows');
    list_components($projectid);
    list_versions($projectid);
  } else {
		$t->set_file('content','project-add.html');
	}
	show_version($_gv['versionid'], $error['version']);
	show_component($_gv['componentid'], $error['component']);
}

function list_projects() {
  global $me, $q, $t, $selrange, $order, $sort, $STRING, $TITLE, $page;

  $t->set_file('content','projectlist.html');
  $t->set_block('content','row','rows');

  if (!$order) { $order = '1'; $sort = 'asc'; }
  $nr = $q->query("select count(*) from ".TBL_PROJECT);

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr,
    'TITLE' => $TITLE['project']
    ));

  $q->query("select * from ".TBL_PROJECT." order by $order $sort limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['noprojects']}</td></tr>");
    return;
  }

  $headers = array(
    'projectid' => 'project_id',
    'name' => 'project_name',
    'description' => 'project_desc',
    'active' => 'active',
    'createdby' => 'created_by',
    'createddate' => 'created_date'
    );

  sorting_headers($me, $headers, $order, $sort);
  $i = 0;
  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'projectid' => $row['project_id'],
      'name' => $row['project_name'],
      'description' => stripslashes($row['project_desc']),
      'active' => $row['active'] ? 'Y' : 'N',
      'createdby' => $row['created_by'],
      'createddate' => date(DATEFORMAT,$row['created_date'])
      ));
    $t->parse('rows','row',true);
  }
}

$t->set_file('wrap','wrap.html');

$perm->check('Admin');

if (isset($_gv['op'])) {
	switch($_gv['op']) {
  	case 'add' : show_project(); break;
  	case 'edit' : show_project($_gv['id']); break;
	}
} elseif (isset($_pv['do'])) {
	switch($_pv['do']) {
  	case 'project' : save_project($_pv['id']); break;
		case 'version' : save_version($_pv['versionid']); break;
		case 'component' : save_component($_pv['componentid']); break;
	}
} else list_projects();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
