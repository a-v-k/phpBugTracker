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
// $Id: project.php,v 1.51 2005/10/18 19:00:30 ulferikson Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_version($versionid, $projectid = 0) {
    global $db, $me, $perm;

    $perm->check_proj($projectid);

    if (!$db->getOne('select count(*) from ' . TBL_BUG . " where version_id = $versionid")) {
        $db->query("delete from " . TBL_VERSION . " where version_id = $versionid");
    }
    header("Location: $me?op=edit&id=$projectid&");
}

function save_version($versionId = 0, $projectId = 0) {
    global $db, $me, $now, $u, $t, $perm;

    $perm->check_proj($projectId);

    //extract($_POST);
    //generate_inputs_die($_POST);
    //array (size=7)
    //  'version_name' => string '<script>alert('b');</script>' (length=28)
    //  'sort_order' => string '0' (length=1)
    //  'active' => string '1' (length=1)
    //  'version_id' => string '12' (length=2)
    //  'project_id' => string '7' (length=1)
    //  'use_js' => string '1' (length=1)
    //  'op' => string 'save_version' (length=12)
    //  for post  //
    $versionName = trim(get_post_val('version_name', null));
    $sortOrder = get_post_int('sort_order', 0);
    $active = get_post_int('active', 0);
    //$versionId = get_post_int('version_id', null);
    //$projectId = get_post_int('project_id', null);
    $useJs = get_post_int('use_js', null);
    //$op = trim(get_post_val('op', null));

    $error = '';
    // Validation
    if ($versionName == '') {
        $error = translate("Please enter a version");
    }
    if ($error) {
        show_version($versionId, $error);
        return;
    }

    if (!$versionId) {
        if ($db->getOne('select count(*) from ' . TBL_VERSION . " where project_id = $projectId and version_name = " . $db->quote(stripslashes($versionName)))) {
            $error = translate("That version already exists");
            show_version($versionId, $error);
            return;
        }
        $db->query('insert into ' . TBL_VERSION . " (version_id, project_id, version_name, active, sort_order, created_by, created_date) values (" . $db->nextId(TBL_VERSION) . ", $projectId, " . $db->quote(stripslashes($versionName)) . ", $active, $sortOrder, $u, $now)");
    } else {
        $db->query('update ' . TBL_VERSION . " set project_id = $projectId, version_name = " . $db->quote(stripslashes($versionName)) . ", active = $active, sort_order = $sortOrder where version_id = '$versionId'");
    }
    if ($useJs) {
        $t->render('edit-submit.html');
    } else {
        header("Location:$me?op=edit&id=$projectId");
    }
}

function show_version($versionid = 0, $error = '') {
    global $db, $t, $QUERY;

    // extract($_POST);  WTF?
    if ($versionid != 0) {
        $t->assign($db->getRow(sprintf($QUERY['admin-show-version'], $versionid)));
    } else {
        if (filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT)) {
            $t->assign('project_id', filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT));
        }
        $t->assign('version_name', null);
        $t->assign('active', null);
        $t->assign('version_id', null);
        $t->assign('sort_order', null);
        //$t->assign($_POST);
    }
    $useJs = get_request_int('use_js', 0);
    $t->assign('use_js', $useJs);
    $t->assign('error', $error);
    $t->render('version-edit.html', translate("Edit Version"), ($useJs == 1) ? 'wrap-popup.php' : 'wrap.php');
}

function del_component($componentid, $projectid = 0) {
    global $db, $me, $perm;

    $perm->check_proj($projectid);

    if (!$db->getOne('select count(*) from ' . TBL_BUG . " where component_id = $componentid")) {
        $db->query("delete from " . TBL_COMPONENT . " where component_id = $componentid");
    }
    header("Location: $me?op=edit&id=$projectid&");
}

function save_component() {
    global $db, $me, $u, $now, $t, $perm;

    $componentId = get_post_int('component_id', null);
    $projectId = get_post_int('project_id');
    $componentName = trim(get_post_str('component_name'));
    $componentDesc = trim(get_post_val('component_desc'));

    $perm->check_proj($projectId);

    $error = '';
    // Validation
    if ($componentName == '') {
        $error = translate("Please enter a name");
    } elseif ($componentDesc == '') {
        $error = translate("Please enter a description");
    }
    if ($error) {
        show_component($componentId, $error);
        return;
    }

    $owner = get_post_int('owner', 0);
    $active = get_post_int('active', 0);
    $sort_order = get_post_int('sort_order', 0);
    $use_js = get_post_int('use_js', 0);

    if (!$componentId) {
        if ($db->getOne('select count(*) from ' . TBL_COMPONENT . " where project_id = $projectId and component_name = " . $db->quote(stripslashes($componentName)))) {
            $error = translate("That component already exists");
            show_component(get_post_int('component_id'), $error);
            return;
        }
        $db->query('insert into ' . TBL_COMPONENT . " (component_id, project_id, component_name, component_desc, owner, active, sort_order, created_by, created_date, last_modified_by, last_modified_date) values ("
                . $db->nextId(TBL_COMPONENT) . ", $projectId, "
                . $db->quote(stripslashes($componentName)) . ", "
                . $db->quote(stripslashes($componentDesc)) . ", $owner, $active, $sort_order, $u, $now, $u, $now)");
    } else {
        $db->query('update ' . TBL_COMPONENT . " set component_name = " . $db->quote(stripslashes($componentName)) . ', component_desc = ' . $db->quote(stripslashes($componentDesc)) . ", owner = $owner, active = $active, sort_order = $sort_order, last_modified_by = $u, " . "last_modified_date = $now where component_id = $componentId");
    }
    if ($use_js) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?op=edit&id=$projectId");
    }
}

function show_component($componentId = 0, $error = '') {
    global $db, $t, $QUERY;

    $vaComponentId = check_numeric_die($componentId);
    if ($vaComponentId != 0) {
        $t->assign($db->getRow(sprintf($QUERY['admin-show-component'], $vaComponentId)));
    } else {
        if (filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT)) {
            $t->assign('project_id', filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT));
        }
        $t->assign('component_name', null);
        $t->assign('component_desc', null);
        $t->assign('owner', null);
        $t->assign('active', null);
        $t->assign('component_id', null);
        $t->assign('sort_order', null);
        //$t->assign($_POST);
    }
    $useJs = get_request_int('use_js', 0);
    $t->assign('use_js', $useJs);
    $t->assign('error', $error);
    $t->render('component-edit.html', translate("Edit Component"), ($useJs == 1) ? 'wrap-popup.php' : 'wrap.php');
}

function save_project() {
    global $db, $me, $u, $now, $perm;

    // on Update:
    //array (size=7)
    //  'project_name' => string 'alert(&#39;q&#39;);' (length=19)
    //  'usergroup' =>
    //    array (size=1)
    //      0 => string '4' (length=1)
    //  'project_desc' => string '<script>alert('q');</script>' (length=28)
    //  'active' => string '1' (length=1)
    //  'useradmin' =>
    //    array (size=1)
    //      0 => string '4' (length=1)
    //      //  'submit' => string 'Submit' (length=6)
    //  'id' => string '3' (length=1)
    //  'op' => string 'save_project' (length=12)
    //  for post  //
    $projectId = get_post_int('id', null);
    $projectName = trim(get_post_val('project_name', null));
    $projectDesc = trim(get_post_val('project_desc', null));
    //$userGroups = get_post_val('usergroup', null);
    $userGroups = filter_input(INPUT_POST, 'usergroup', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    if ($userGroups == null) {
        $userGroups = array();
    }
    $userAdmins = filter_input(INPUT_POST, 'useradmin', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    if ($userAdmins == null) {
        $userAdmins = array();
    }
    $active = get_post_int('active', 0);
    //$submit = trim(get_post_val('submit', null));
    //$op = trim(get_post_val('op', null));
    // on New project save:
    //array (size=12)
    //  'project_name' => string '<script>alert('q');</script>' (length=28)
    //  'project_desc' => string '<script>alert('q');</script>' (length=28)
    //  'active' => string '1' (length=1)
    //  'version_name' => string '<script>alert('q');</script>' (length=28)
    //  'version_sortorder' => string '1' (length=1)
    //  'component_name' => string '<script>alert('q');</script>' (length=28)
    //  'component_sortorder' => string '1' (length=1)
    //  'component_desc' => string '<script>alert('q');</script>' (length=28)
    //  'owner' => string '0' (length=1)
    //  'submit' => string 'Submit' (length=6)
    //  'id' => string '0' (length=1)
    //  'op' => string 'save_project' (length=12)
    ////  for post  //
    //$projectName = trim(get_post_val('project_name', null));
    //$projectDesc = trim(get_post_val('project_desc', null));
    //$active = get_post_int('active', 0);
    $versionName = trim(get_post_val('version_name', null));
    $versionSortOrder = get_post_int('version_sortorder', 0);
    $componentName = trim(get_post_val('component_name', null));
    $componentSortOrder = get_post_int('component_sortorder', 0);
    $componentDesc = trim(get_post_val('component_desc', null));
    $owner = get_post_int('owner', null);
    //$submit = trim(get_post_val('submit', null));
    //$id = get_post_int('id', null);
    //$op = trim(get_post_val('op', null));
//    generate_inputs_die($_POST);

    $perm->check_proj($projectId);

    $error = '';
    // Validation
    if ($projectName == '') {
        $error = translate("Please enter a name");
    } elseif ($projectDesc == '') {
        $error = translate("Please enter a description");
    } elseif (is_array($userGroups) and
            in_array('all', $userGroups) and count($userGroups) > 1) {
        $error = translate("You cannot choose specific groups when \"All Groups\" is chosen");
    }
    if ($error) {
        show_project($projectId, $error);
        return;
    }

    $typeOP = 'update';
    if (!$projectId) {
        $typeOP = 'add';
        if ($versionName == '') {
            $error['version_error'] = translate("Please enter a version");
        } elseif ($componentName == '') {
            $error['component_error'] = translate("Please enter a name");
        } elseif ($componentDesc == '') {
            $error['component_error'] = translate("Please enter a description");
        }
    }
    if ($error) {
        show_project($projectId, $error);
        return;
    }
    if (!$projectId) {
        if ($db->getOne('select count(*) from ' . TBL_PROJECT . " where project_name = " . $db->quote(stripslashes($projectName)))) {
            $error = translate("That project already exists");
            show_project($projectId, $error);
            return;
        }
        $projectId = $db->nextId(TBL_PROJECT);
        $db->query('insert into ' . TBL_PROJECT . " (project_id, project_name, project_desc, active, created_by, created_date) values ($projectId , " . $db->quote(stripslashes($projectName)) . ", " . $db->quote(stripslashes($projectDesc)) . ", $active, $u, $now)");
        $db->query('insert into ' . TBL_VERSION . " (version_id, project_id, version_name, active, sort_order, created_by, created_date) values (" . $db->nextId(TBL_VERSION) . ", $projectId, " . $db->quote(stripslashes($versionName)) . ", 1, " . (int) $versionSortOrder . ", $u, $now)");
        $db->query('insert into ' . TBL_COMPONENT . " (component_id, project_id, component_name, component_desc, owner, active, sort_order, created_by, created_date, last_modified_by, last_modified_date) values (" . $db->nextId(TBL_COMPONENT) . ", $projectId, " . $db->quote(stripslashes($componentName)) . ", " . $db->quote(stripslashes($componentDesc)) . ", " . (int) $owner . ", 1, " . (int) $componentSortOrder . ", $u, $now, $u, $now)");
    } else {
        $db->query('update ' . TBL_PROJECT . " set project_name = " . $db->quote(stripslashes($projectName)) . ", project_desc = " . $db->quote(stripslashes($projectDesc)) . ", active = $active where project_id = $projectId");
    }
    // project -> user relationship
    $oldUserAdmins = $db->getCol('select user_id from ' . TBL_PROJECT_PERM . " where project_id = $projectId");
    if (isset($userAdmins) and is_array($userAdmins) and count($userAdmins)) {
        // Compute differences between old and new
        $remove_from = array_diff($oldUserAdmins, $userAdmins);
        $add_to = array_diff($userAdmins, $oldUserAdmins);

        if (count($remove_from)) {
            foreach ($remove_from as $user) {
                $db->query('delete from ' . TBL_PROJECT_PERM . " where project_id = $projectId and user_id = $user");
            }
        }
        if (count($add_to)) {
            foreach ($add_to as $user) {
                if (is_numeric($user)) { // it's coming from request...
                    $db->query("insert into " . TBL_PROJECT_PERM . " (project_id, user_id) values ('$projectId', $user)");
                }
            }
        }
    } elseif (count($oldUserAdmins)) {
        // user killed em all
        $db->query('delete from ' . TBL_PROJECT_PERM . " where project_id = $projectId");
    }


    // Handle project -> group relationship
    $oldUserGroups = $db->getCol('select group_id from ' . TBL_PROJECT_GROUP . " where project_id = $projectId");
    if (isset($userGroups) and is_array($userGroups) and count($userGroups)) {
        if (in_array('all', $userGroups)) {
            // User selected 'All groups'
            if (count($oldUserGroups)) {
                $db->query('delete from ' . TBL_PROJECT_GROUP . " where project_id = $projectId");
            }
        } else {
            // Compute differences between old and new
            $remove_from = array_diff($oldUserGroups, $userGroups);
            $add_to = array_diff($userGroups, $oldUserGroups);

            if (count($remove_from)) {
                foreach ($remove_from as $group) {
                    $db->query('delete from ' . TBL_PROJECT_GROUP . " where project_id = $projectId and group_id = $group");
                }
            }
            if (count($add_to)) {
                foreach ($add_to as $group) {
                    if (is_numeric($group)) { // it's coming from request...
                        $db->query("insert into " . TBL_PROJECT_GROUP . " (project_id, group_id, created_by, created_date) values ('$projectId' ,'$group', $u, $now)");
                    }
                }
            }
        }
    } elseif (count($oldUserGroups)) {
        // User selected nothing, so consider it 'All groups'
        $db->query('delete from ' . TBL_PROJECT_GROUP . " where project_id = $projectId");
    }

    // This should only be used when the conditions for bug #1292113 are true
    if (true) {
        if ($typeOP == 'add') {
            show_project($projectId);
            return;
        } else if ($typeOP == 'update') {
            list_projects();
            return;
        }
    }

    header("Location: $me?op=edit&id=$projectId");
}

function show_project($projectid = 0, $error = null) {
    global $db, $me, $t, $QUERY, $perm;

    $perm->check_proj($projectid);

    if (is_array($error)) {
        $t->assign($error);
    } else {
        $t->assign('error', $error);
    }
    $t->assign('project_groups', $db->getCol('select group_id from ' . TBL_PROJECT_GROUP . " where project_id = $projectid"));
    if ($perm->have_perm('Admin')) {
        $t->assign('project_admins', $db->getCol('select user_id from ' . TBL_PROJECT_PERM . " where project_id = $projectid"));
    } else {
        $t->assign('project_admins', $db->getCol('select u.login from ' . TBL_AUTH_USER . ' as u, ' . TBL_PROJECT_PERM . ' as p where u.user_id = p.user_id and p.project_id = ' . $projectid));
    }

    if ($projectid) {
        $t->assign($db->getRow('select * from ' . TBL_PROJECT . " where project_id = $projectid"));
        $t->assign(array(
            'components' => $db->getAll(sprintf($QUERY['admin-list-components'], $projectid)),
            'versions' => $db->getAll(sprintf($QUERY['admin-list-versions'], $projectid))
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
        $order = 'default';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }
    $page = isset($_GET['page']) ? $_GET['page'] : 1;

    $nr = $db->getOne("select count(*) from " . TBL_PROJECT);

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

    $orderSort = $order . ' ' . $sort;
    if ($order == 'default') {
        $orderSort = ' active desc, project_name asc, created_date asc  ';
    }

    //$t->assign('projects', $db->getAll($db->modifyLimitQuery("select * from " . TBL_PROJECT . " order by $orderSort", $llimit, $selrange)));
    $t->assign('projects', $db->getAll("select * from " . TBL_PROJECT . " order by $orderSort", $llimit, $selrange));

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

    $t->render('projectlist.html.php', translate("Project List"));
}

// $perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : show_project();
            break;
        case 'edit' : show_project(get_request_int('id'));
            break;
        case 'edit_component' : show_component(get_request_int('id'));
            break;
        case 'edit_version' : show_version(get_request_int('id'));
            break;
        case 'del_component' : del_component(get_request_int('id'), get_request_int('project_id'));
            break;
        case 'del_version' : del_version(get_request_int('id'), get_request_int('project_id'));
            break;
        case 'save_project' : save_project();
            break;
        case 'save_version' : save_version(get_post_int('version_id', null), get_post_int('project_id'));
            break;
        case 'save_component' : save_component(get_post_int('component_id', null), get_post_int('project_id'));
            break;
    }
} else {
    list_projects();
}