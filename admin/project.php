<?php

// project.php - Create and update projects
// ------------------------------------------------------------------------
// Copyright (c) 2001, 2002 The phpBugTracker Group
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
// $Id: project.php,v 1.42 2002/05/18 03:00:00 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_version($versionid, $projectid) {
	global $db, $me;
	
	if (!$db->getOne('select count(*) from '.TBL_BUG." where version_id = $versionid")) {
		$db->query("delete from ".TBL_VERSION." where version_id = $versionid");
	}
	header("Location: $me?op=edit&id=$projectid&");
}

function save_version($versionid = 0) {
  global $db, $me, $_pv, $STRING, $now, $u, $t;

	$error = '';
  // Validation
  if (!$_pv['version_name'] = trim($_pv['version_name']))
    $error = $STRING['giveversion'];
  if ($error) { show_version($_pv['versionid'], $error); return; }

	extract($_pv);
  if (!isset($active)) $active = 0;
  if (!$versionid) {
    $db->query('insert into '.TBL_VERSION
			." (version_id, project_id, version_name, active, created_by, created_date) 
			values (".$db->nextId(TBL_VERSION).", $projectid, ".
			$db->quote(stripslashes($version_name)).", $active, $u, $now)");
  } else {
    $db->query('update '.TBL_VERSION
			." set project_id = $projectid, version_name = ".
			$db->quote(stripslashes($version_name)).
			", active = $active where version_id = '$versionid'");
  }
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
  	header("Location:$me?op=edit&id=$projectid");
	}
}

function show_version($versionid = 0, $error = '') {
  global $db, $t, $_pv, $STRING, $QUERY, $_gv;

	foreach ($_pv as $k => $v) $$k = $v;
	
	if ($versionid) {
		$t->assign($db->getRow(sprintf($QUERY['admin-show-version'], $versionid)));
	} else {
		if (!empty($_pv)) {
		    $t->assign($_pv);
		} else {
		    $t->assign(array(
					'active' => 1,
					'project_id' => $_gv['project_id']
					));
		}
	}
	$t->assign('error', $error);
	$t->wrap('admin/version-edit.html', ($versionid ? 'editversion' : 'addversion'));
}

function del_component($componentid, $projectid) {
	global $db, $me;
	
	if (!$db->getOne('select count(*) from '.TBL_BUG." where component_id = $componentid")) {
		$db->query("delete from ".TBL_COMPONENT." where component_id = $componentid");
	}
	header("Location: $me?op=edit&id=$projectid&");
}

function save_component($componentid = 0) {
	global $db, $me, $_pv, $u, $STRING, $now, $t;
	
	$error = '';
	// Validation
	if (!$_pv['component_name'] = trim($_pv['component_name'])) 
		$error = $STRING['givename'];
	elseif (!$_pv['component_desc'] = trim($_pv['component_desc']))
		$error = $STRING['givedesc'];
	if ($error) { show_component($_pv['componentid'], $error); return; }
	
	foreach ($_pv as $k => $v) $$k = $v;
	if (!$owner) $owner = 0;
	if (!$active) $active = 0;
	if (!$componentid) {
		$db->query('insert into '.TBL_COMPONENT
			." (component_id, project_id, component_name, component_desc, owner, 
			active, created_by, created_date, last_modified_by, last_modified_date) 
			values (".$db->nextId(TBL_COMPONENT).", $projectid, ".
			$db->quote(stripslashes($component_name)).", ".
			$db->quote(stripslashes($component_desc)).
			", $owner, $active, $u, $now, $u, $now)");
	} else {
		$db->query('update '.TBL_COMPONENT
			." set component_name = ".$db->quote(stripslashes($component_name)).
			', component_desc = '.$db->quote(stripslashes($component_desc)).
			", owner = $owner, active = $active, last_modified_by = $u, ". 
			"last_modified_date = $now where component_id = $componentid");
	}
	if ($use_js) {
		$t->display('admin/edit-submit.html');
	} else {
  	header("Location: $me?op=edit&id=$projectid");
	}
}	

function show_component($componentid = 0, $error = '') {
	global $db, $t, $_pv, $STRING, $QUERY, $_gv;
	
	if ($componentid) {
		$t->assign($db->getRow(sprintf($QUERY['admin-show-component'], $componentid)));
	} else {
		if (!empty($_pv)) {
		    $t->assign($_pv);
		} else {
		    $t->assign(array(
					'active' => 1,
					'project_id' => $_gv['project_id']
					));
		}
	}
	$t->assign('error', $error);
	$t->wrap('admin/component-edit.html', ($componentid ? 'editcomponent' : 'addcomponent'));
}

function save_project($projectid = 0) {
  global $db, $me, $u, $STRING, $now, $_pv;

	$error = '';
  // Validation
  if (!$_pv['project_name'] = htmlspecialchars(trim($_pv['project_name']))) {
    $error = $STRING['givename'];
  } elseif (!$_pv['project_desc'] = htmlspecialchars(trim($_pv['project_desc']))) {
    $error = $STRING['givedesc'];
  } elseif (isset($_pv['usergroup']) and is_array($_pv['usergroup']) and 
		in_array('all', $_pv['usergroup']) and count($_pv['usergroup']) > 1) {
		$error = $STRING['project_only_all_groups'];
	}
	if ($error) { show_project($projectid, $error); return; }
	
	if (!$projectid) {
		if (!$_pv['version_name'] = htmlspecialchars(trim($_pv['version_name']))) {
    	$error['version_error'] = $STRING['giveversion'];
  	} elseif (!$_pv['component_name'] = trim($_pv['component_name'])) {
			$error['component_error'] = $STRING['givename'];
		} elseif (!$_pv['component_desc'] = trim($_pv['component_desc'])) {
			$error['component_error'] = $STRING['givedesc'];
		}
	}
	if ($error) { show_project($projectid, $error); return; }

	foreach ($_pv as $k => $v) $$k = $v;
  if (!isset($active)) $active = 0;
  if (!$projectid) {
    $projectid = $db->nextId(TBL_PROJECT);
    $db->query('insert into '.TBL_PROJECT
			." (project_id, project_name, project_desc, active, created_by, created_date)
      values ($projectid , ".$db->quote(stripslashes($project_name)).", ".
			$db->quote(stripslashes($project_desc)).", $active, $u, $now)");
    $db->query('insert into '.TBL_VERSION
			." (version_id, project_id, version_name, active, created_by, created_date) 
			values (".$db->nextId(TBL_VERSION).", $projectid, ".
			$db->quote(stripslashes($version_name)).", 1, $u, $now)");
		$db->query('insert into '.TBL_COMPONENT
			." (component_id, project_id, component_name, component_desc, owner, 
			active, created_by, created_date, last_modified_by, last_modified_date) 
			values (".$db->nextId(TBL_COMPONENT).", $projectid, ".
			$db->quote(stripslashes($component_name)).", ".
			$db->quote(stripslashes($component_desc)).
			", $owner, 1, $u, $now, $u, $now)");
  } else {
    $db->query('update '.TBL_PROJECT
			." set project_name = ".$db->quote(stripslashes($project_name)).
			", project_desc = ".$db->quote(stripslashes($project_desc)).
			", active = $active where project_id = $projectid");
  }
	
	// Handle project -> group relationship
	$old_usergroup = $db->getCol('select group_id from '.TBL_PROJECT_GROUP.
		" where project_id = $projectid");
	if (isset($usergroup) and is_array($usergroup) and count($usergroup)) {
		if (in_array('all', $usergroup)) {
			// User selected 'All groups'
			if (count($old_usergroup)) {
				$db->query('delete from '.TBL_PROJECT_GROUP." where project_id = $projectid");
			}
		} else {
			// Compute differences between old and new
			$remove_from = array_diff($old_usergroup, $usergroup);
    	$add_to = array_diff($usergroup, $old_usergroup);
			
			if (count($remove_from)) {
				foreach ($remove_from as $group) {
					$db->query('delete from '.TBL_PROJECT_GROUP." where project_id = $projectid
					 and group_id = $group");
				}
			}
			if (count($add_to)) {
      	foreach ($add_to as $group) {
        	$db->query("insert into ".TBL_PROJECT_GROUP
          	." (project_id, group_id, created_by, created_date)
          	values ('$projectid' ,'$group', $u, $now)");
      	}
    	}
		}
	} elseif (count($old_usergroup)) {
		// User selected nothing, so consider it 'All groups'
		$db->query('delete from '.TBL_PROJECT_GROUP." where project_id = $projectid");
	}
		
  header("Location: $me?op=edit&id=$projectid");
}

function show_project($projectid = 0, $error = null) {
  global $db, $me, $t, $TITLE, $_gv, $_pv, $QUERY;

	if (is_array($error)) $t->assign($error);
	else $t->assign('error', $error);
  $t->assign('project_groups', $db->getCol('select group_id from '.
		TBL_PROJECT_GROUP." where project_id = $projectid"));

  if ($projectid) {
    $t->assign($db->getRow('select * from '.TBL_PROJECT
			." where project_id = $projectid"));
  	$t->assign(array(
			'components' =>	$db->getAll(sprintf($QUERY['admin-list-components'], 
				$projectid)),
  		'versions' =>	$db->getAll(sprintf($QUERY['admin-list-versions'], 
				$projectid))
			));

		$t->wrap('admin/project-edit.html', 'editproject');
  } else {
		if (!empty($_pv)) {
		    $t->assign($_pv);
		} else {
		    $t->assign('active', 1);
		}
		$t->wrap('admin/project-add.html', 'addproject');
  }
	
}

function list_projects() {
  global $me, $db, $t, $selrange, $_gv, $STRING, $TITLE;

  if (!isset($_gv['order'])) { $order = 'created_date'; $sort = 'asc'; }
	else { $order = $_gv['order']; $sort = $_gv['sort']; }
	$page = isset($_gv['page']) ? $_gv['page'] : 1;
	
  $nr = $db->getOne("select count(*) from ".TBL_PROJECT);

  list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

  $t->assign('projects', $db->getAll($db->modifyLimitQuery(
		"select * from ".TBL_PROJECT." order by $order $sort", $llimit, $selrange)));

  $headers = array(
    'projectid' => 'project_id',
    'name' => 'project_name',
    'description' => 'project_desc',
    'active' => 'active',
    'createdby' => 'created_by',
    'createddate' => 'created_date'
    );

  sorting_headers($me, $headers, $order, $sort);
	
	$t->wrap('admin/projectlist.html', 'project');
}

$perm->check('Admin');

if (isset($_gv['op'])) {
	switch($_gv['op']) {
  	case 'add' : show_project(); break;
  	case 'edit' : show_project($_gv['id']); break;
  	case 'edit_component' : show_component($_gv['id']);	break;
  	case 'edit_version' : show_version($_gv['id']); break;
  	case 'del_component' : del_component($_gv['id'], $_gv['project_id']); break;
  	case 'del_version' : del_version($_gv['id'], $_gv['project_id']); break;
	}
} elseif (isset($_pv['do'])) {
	switch($_pv['do']) {
  	case 'project' : save_project($_pv['id']); break;
		case 'version' : save_version($_pv['versionid']); break;
		case 'component' : save_component($_pv['componentid']); break;
	}
} else list_projects();

?>
