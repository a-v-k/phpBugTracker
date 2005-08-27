<?php

// project.php - Create and update projects
// ------------------------------------------------------------------------
// Copyright (c) 2001 - 2004 The phpBugTracker Group
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
// $Id: project.php,v 1.49 2005/08/27 13:14:28 ulferikson Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_version($versionid, $projectid) {
	global $db, $me, $perm;

	$perm->check_proj($projectid);

	if (!$db->getOne('select count(*) from '.TBL_BUG." where version_id = $versionid")) {
		$db->query("delete from ".TBL_VERSION." where version_id = $versionid");
	}
	header("Location: $me?op=edit&id=$projectid&");
}

function save_version($version_id = 0) {
	global $db, $me, $now, $u, $t, $perm;

	$perm->check_proj($projectid);

	$error = '';
	// Validation
	if (!$_POST['version_name'] = trim($_POST['version_name']))
		$error = translate("Please enter a version");
	if ($error) {
		show_version($_POST['version_id'], $error); return;
	}

	extract($_POST);
	if (!isset($active)) $active = 0;
	if (empty($sort_order)) $sort_order = 0;
	if (!$version_id) {
		$db->query('insert into '.TBL_VERSION." (version_id, project_id, version_name, active, sort_order, created_by, created_date) values (".$db->nextId(TBL_VERSION).", $project_id, ".$db->quote(stripslashes($version_name)).", $active, $sort_order, $u, $now)");
	} else {
		$db->query('update '.TBL_VERSION." set project_id = $project_id, version_name = ".$db->quote(stripslashes($version_name)).", active = $active, sort_order = $sort_order where version_id = '$version_id'");
	}
	if ($use_js) {
		$t->render('edit-submit.html');
	} else {
		header("Location:$me?op=edit&id=$project_id");
	}
}

function show_version($versionid = 0, $error = '') {
	global $db, $t, $QUERY;

	extract($_POST);
	if ($versionid) {
		$t->assign($db->getRow(sprintf($QUERY['admin-show-version'], $versionid)));
	} else {
		if (!empty($_GET['project_id'])) 
			$t->assign('project_id', $_GET['project_id']);
		$t->assign($_POST);
	}
	$t->assign('error', $error);
	$t->render('version-edit.html', translate("Edit Version"), 
		!empty($_REQUEST['use_js']) ? 'wrap-popup.html' : 'wrap.html');
}

function del_component($componentid, $projectid) {
	global $db, $me, $perm;

	$perm->check_proj($projectid);

	if (!$db->getOne('select count(*) from '.TBL_BUG." where component_id = $componentid")) {
		$db->query("delete from ".TBL_COMPONENT." where component_id = $componentid");
	}
	header("Location: $me?op=edit&id=$projectid&");
}

function save_component($component_id = 0) {
	global $db, $me, $u, $now, $t, $perm;

	$perm->check_proj($projectid);

	$error = '';
	// Validation
	if (!$_POST['component_name'] = trim($_POST['component_name'])) {
		$error = translate("Please enter a name");
	} elseif (!$_POST['component_desc'] = trim($_POST['component_desc'])) {
		$error = translate("Please enter a description");
	}
	if ($error) {
		show_component($_POST['component_id'], $error);
		return;
	}

	extract($_POST);
	if (!$owner) $owner = 0;
	if (!$active) $active = 0;
	if (empty($sort_order)) $sort_order = 0;
	if (!$component_id) {
		$db->query('insert into '.TBL_COMPONENT." (component_id, project_id, component_name, component_desc, owner, active, sort_order, created_by, created_date, last_modified_by, last_modified_date) values (".$db->nextId(TBL_COMPONENT).", $project_id, ".$db->quote(stripslashes($component_name)).", ".$db->quote(stripslashes($component_desc)).", $owner, $active, $sort_order, $u, $now, $u, $now)");
	} else {
		$db->query('update '.TBL_COMPONENT." set component_name = ".$db->quote(stripslashes($component_name)).', component_desc = '.$db->quote(stripslashes($component_desc)).", owner = $owner, active = $active, sort_order = $sort_order, last_modified_by = $u, "."last_modified_date = $now where component_id = $component_id");
	}
	if ($use_js) {
		$t->render('edit-submit.html');
	} else {
		header("Location: $me?op=edit&id=$project_id");
	}
}

function show_component($componentid = 0, $error = '') {
	global $db, $t, $QUERY;

	if ($componentid) {
		$t->assign($db->getRow(sprintf($QUERY['admin-show-component'], $componentid)));
	} else {
		if (!empty($_GET['project_id'])) $t->assign('project_id', $_GET['project_id']);
		$t->assign($_POST);
	}
	$t->assign('error', $error);
	$t->render('component-edit.html', translate("Edit Component"), 
		!empty($_REQUEST['use_js']) ? 'wrap-popup.html' : 'wrap.html');
}

function save_project($projectid = 0) {
	global $db, $me, $u, $now, $perm;

	$perm->check_proj($projectid);

	$error = '';
	// Validation
	if (!$_POST['project_name'] = htmlspecialchars(trim($_POST['project_name']))) {
		$error = translate("Please enter a name");
	} elseif (!$_POST['project_desc'] = htmlspecialchars(trim($_POST['project_desc']))) {
		$error = translate("Please enter a description");
	} elseif (isset($_POST['usergroup']) and is_array($_POST['usergroup']) and
			  in_array('all', $_POST['usergroup']) and count($_POST['usergroup']) > 1) {
		$error = translate("You cannot choose specific groups when \"All Groups\" is chosen");
	}
	if ($error) { 
		show_project($projectid, $error); 
		return; 
	}

	if (!$projectid) {
		if (!$_POST['version_name'] = htmlspecialchars(trim($_POST['version_name']))) {
			$error['version_error'] = translate("Please enter a version");
		} elseif (!$_POST['component_name'] = trim($_POST['component_name'])) {
			$error['component_error'] = translate("Please enter a name");
		} elseif (!$_POST['component_desc'] = trim($_POST['component_desc'])) {
			$error['component_error'] = translate("Please enter a description");
		}
	}
	if ($error) { 
		show_project($projectid, $error); 
		return; 
	}

	extract($_POST);
	if (!isset($active)) $active = 0;
	if (empty($version_sortorder)) $version_sortorder = 0;
	if (empty($component_sortorder)) $component_sortorder = 0;
	if (!$projectid) {
		$projectid = $db->nextId(TBL_PROJECT);
		$db->query('insert into '.TBL_PROJECT." (project_id, project_name, project_desc, active, created_by, created_date) values ($projectid , ".$db->quote(stripslashes($project_name)).", ".$db->quote(stripslashes($project_desc)).", $active, $u, $now)");
		$db->query('insert into '.TBL_VERSION." (version_id, project_id, version_name, active, sort_order, created_by, created_date) values (".$db->nextId(TBL_VERSION).", $projectid, ".$db->quote(stripslashes($version_name)).", 1, $version_sortorder, $u, $now)");
		$db->query('insert into '.TBL_COMPONENT." (component_id, project_id, component_name, component_desc, owner, active, sort_order, created_by, created_date, last_modified_by, last_modified_date) values (".$db->nextId(TBL_COMPONENT).", $projectid, ".$db->quote(stripslashes($component_name)).", ".$db->quote(stripslashes($component_desc)).", $owner, 1, $component_sortorder, $u, $now, $u, $now)");
	} else {
		$db->query('update '.TBL_PROJECT." set project_name = ".$db->quote(stripslashes($project_name)).", project_desc = ".$db->quote(stripslashes($project_desc)).", active = $active where project_id = $projectid");
	}
	// project -> user relationship
	$old_useradmin = $db->getCol('select user_id from '.TBL_PROJECT_PERM." where project_id = $projectid");
	if (isset($useradmin) and is_array($useradmin) and count($useradmin)) {
		// Compute differences between old and new
		$remove_from = array_diff($old_useradmin, $useradmin);
		$add_to = array_diff($useradmin, $old_useradmin);

		if (count($remove_from)) {
			foreach ($remove_from as $user) {
				$db->query('delete from '.TBL_PROJECT_PERM." where project_id = $projectid and user_id = $user");
			}
		}
		if (count($add_to)) {
			foreach ($add_to as $user) {
				$db->query("insert into ".TBL_PROJECT_PERM." (project_id, user_id) values ('$projectid', $user)");
			}
		}
	} elseif (count($old_useradmin)) {
		// user killed em all
		$db->query('delete from '.TBL_PROJECT_PERM." where project_id = $projectid");
	}


	// Handle project -> group relationship
	$old_usergroup = $db->getCol('select group_id from '.TBL_PROJECT_GROUP." where project_id = $projectid");
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
					$db->query('delete from '.TBL_PROJECT_GROUP." where project_id = $projectid and group_id = $group");
				}
			}
			if (count($add_to)) {
				foreach ($add_to as $group) {
					$db->query("insert into ".TBL_PROJECT_GROUP." (project_id, group_id, created_by, created_date) values ('$projectid' ,'$group', $u, $now)");
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
	global $db, $me, $t, $QUERY, $perm;

	if (is_array($error)) $t->assign($error);
	else $t->assign('error', $error);
	$t->assign('project_groups', 
		$db->getCol('select group_id from '.TBL_PROJECT_GROUP." where project_id = $projectid"));
	if ($perm->have_perm('Admin')) {
		$t->assign('project_admins', 
			$db->getCol('select user_id from '.TBL_PROJECT_PERM." where project_id = $projectid"));

	} else {
		$t->assign('project_admins', 
			$db->getCol('select u.login from '.TBL_AUTH_USER.' as u, '.TBL_PROJECT_PERM.' as p where u.user_id = p.user_id and p.project_id = '.$projectid));
	}

	if ($projectid) {
		$t->assign($db->getRow('select * from '.TBL_PROJECT." where project_id = $projectid"));
		$t->assign(array(
			'components' => $db->getAll(sprintf($QUERY['admin-list-components'], $projectid)),
			'versions' =>   $db->getAll(sprintf($QUERY['admin-list-versions'], $projectid))
			));

		$t->render('project-edit.html', translate("Edit Project"));
	} else {
		if (!empty($_POST)) {
			$t->assign($_POST);
		} else {
			$t->assign('active', 1);
		}
		$t->render('project-add.html', translate("Edit Project"));
	}

}

function list_projects() {
	global $me, $db, $t, $selrange;

	if (!isset($_GET['order'])) { 
		$order = 'created_date'; $sort = 'asc'; 
	}
	else { 
		$order = $_GET['order']; $sort = $_GET['sort']; 
	}
	$page = isset($_GET['page']) ? $_GET['page'] : 1;

	$nr = $db->getOne("select count(*) from ".TBL_PROJECT);

	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

	$t->assign('projects', 
		$db->getAll($db->modifyLimitQuery("select * from ".TBL_PROJECT." order by $order $sort", $llimit, $selrange)));

	$headers = array(
		'projectid' => 'project_id',
		'name' => 'project_name',
		'description' => 'project_desc',
		'active' => 'active',
		'sortorder' => 'sort_order',
		'createdby' => 'created_by',
		'createddate' => 'created_date'
		);

	sorting_headers($me, $headers, $order, $sort);

	$t->render('projectlist.html', translate("Project List"));
}

// $perm->check('Admin');

if (isset($_REQUEST['op'])) {
	switch($_REQUEST['op']) {
		case 'add' : show_project(); break;
		case 'edit' : show_project($_REQUEST['id']); break;
		case 'edit_component' : show_component($_REQUEST['id']);     break;
		case 'edit_version' : show_version($_REQUEST['id']); break;
		case 'del_component' : del_component($_REQUEST['id'], $_REQUEST['project_id']); break;
		case 'del_version' : del_version($_REQUEST['id'], $_REQUEST['project_id']); break;
		case 'save_project' : save_project($_POST['id']); break;
		case 'save_version' : save_version($_POST['version_id']); break;
		case 'save_component' : save_component($_POST['component_id']); break;
	}
} else list_projects();

?>
