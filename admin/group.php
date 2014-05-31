<?php

// group.php - Administer the user groups
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
// $Id: group.php,v 1.16 2005/10/31 21:34:35 ulferikson Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function purge_group($groupid = 0) {
    global $db;

    $db->query("delete from " . TBL_USER_GROUP . " where group_id = $groupid");
}

function del_group($groupid = 0) {
    global $db;

    purge_group($groupid);
    $db->query("delete from " . TBL_AUTH_GROUP . " where group_id = $groupid");
}

function do_form($groupid = 0) {
    global $db, $me, $u, $now, $t;

    extract($_POST);
    $error = '';
    // Validation
    if (!$group_name = trim($group_name))
        $error = translate("Please enter a name");
    if ($error) {
        show_form($groupid, $error);
        return;
    }

    if (!$groupid) {
        $groupid = $db->nextId(TBL_AUTH_GROUP);
        $db->query("insert into " . TBL_AUTH_GROUP . " (group_id, group_name, created_by, created_date, last_modified_by, last_modified_date) values (" . $groupid . ", " . $db->quote(stripslashes($group_name)) . ", $u, $now, $u, $now)");
    } else {
        $db->query("update " . TBL_AUTH_GROUP . " set group_name = " . $db->quote(stripslashes($group_name)) . ", last_modified_by = $u, last_modified_date = $now where group_id = '$groupid'");
    }

    $db->query("delete from " . TBL_GROUP_PERM . " where group_id = '$groupid'");
    foreach ($perms as $permid) {
        $db->query("insert into " . TBL_GROUP_PERM . " (group_id, perm_id) values ($groupid, $permid)");
    }

    if ($use_js) {
        $t->render('edit-submit.html', '', 'wrap-popup.html');
    } else {
        header("Location: $me?");
    }
}

function show_form($groupid = 0, $error = '') {
    global $db, $me, $t;

    $group_perms = array();
    if ($groupid && !$error) {
        $t->assign($db->getRow("select * from " . TBL_AUTH_GROUP . " where group_id = '$groupid'"));
        $group_perms = $db->getCol("select distinct perm_id from " . TBL_GROUP_PERM . " where group_id = $groupid");
    } else {
        $t->assign($_POST);
    }
    $t->assign('perms', $db->getAll("select * from " . TBL_AUTH_PERM));
    $t->assign('group_perms', $group_perms);
    $t->assign('error', $error);
    $t->render('group-edit.html', translate("Edit Group"), (!empty($_GET['use_js']) ? 'wrap-popup.html' : 'wrap.html'));
}

function list_items($do_group = true, $groupid = 0, $error = '') {
    global $me, $db, $t, $QUERY;

    if (empty($_GET['order'])) {
        $order = 'group_name';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $match = $do_group ? "is_role=0" : "is_role=1";

    $nr = $db->getOne("select count(*) from " . TBL_AUTH_GROUP . " where $match");

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

    $t->assign('groups', $db->getAll($db->modifyLimitQuery(
                            sprintf($QUERY['admin-list-groups'], $match, $order, $sort), $llimit, $selrange)));

    $headers = array(
        'groupid' => 'group_id',
        'name' => 'group_name',
        'count' => '4');

    sorting_headers($me, $headers, $order, $sort, "page=$page");

    $t->assign('do_group', $do_group);
    $t->render('grouplist.html', $do_group ? translate("Group List") : translate("Role List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'save' : do_form($_POST['group_id']);
            break;
        case 'edit' : show_form($_GET['group_id']);
            break;
        case 'del' : del_group($_GET['group_id']);
            list_items(true, $_GET['group_id']);
            break;
        case 'purge' : purge_group($_GET['group_id']);
            list_items(true, $_GET['group_id']);
            break;
        case 'list-roles' : list_items(false);
            break;
        case 'save-role' : do_form($_POST['group_id']);
            break;
        case 'edit-role' : show_form($_GET['group_id']);
            break;
        case 'del-role' : del_group($_GET['group_id']);
            list_items(false, $_GET['group_id']);
            break;
        case 'purge-role' : purge_group($_GET['group_id']);
            list_items(false, $_GET['group_id']);
            break;
    }
} else {
    list_items();
}

//
