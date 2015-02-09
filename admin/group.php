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

function do_form() {
    global $db, $me, $u, $now, $t;

    $groupId = get_request_int('group_id', null);
    $perms = filter_input(INPUT_POST, 'perms', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
    if ($perms == null) {
        $perms = array();
    }

//    var_dump($_POST); die();
//    array (size=5)
//      'group_name' => string 'ssdfsf sf sdfs s' (length=16)
//      'perms' => 
//        array (size=6)
//          0 => string '5' (length=1)
//          1 => string '8' (length=1)
//          2 => string '9' (length=1)
//          3 => string '10' (length=2)
//          4 => string '11' (length=2)
//          5 => string '12' (length=2)
//      'op' => string 'save' (length=4)
//      'group_id' => string '0' (length=1)
//      'use_js' => string '1' (length=1)

    $error = '';
    $groupName = trim(get_post_val('group_name'));
    // Validation
    if ($groupName == '') {
        $error = translate("Please enter a name");
    }
    if ($error) {
        show_form($groupId, $error);
        return;
    }

    if (!$groupId) {
        $groupId = $db->nextId(TBL_AUTH_GROUP);
        $db->query("insert into " . TBL_AUTH_GROUP . " (group_id, group_name, created_by, created_date, last_modified_by, last_modified_date) values (" . $groupId . ", " . $db->quote(stripslashes($groupName)) . ", $u, $now, $u, $now)");
    } else {
        $db->query("update " . TBL_AUTH_GROUP . " set group_name = " . $db->quote(stripslashes($groupName)) . ", last_modified_by = $u, last_modified_date = $now where group_id = '$groupId'");
    }

    $db->query("delete from " . TBL_GROUP_PERM . " where group_id = '$groupId'");
    foreach ($perms as $permid) {
        $intPerm = (int) $permid;
        $db->query("insert into " . TBL_GROUP_PERM . " (group_id, perm_id) values ($groupId, $intPerm)");
    }

    $useJs = get_request_int('use_js', 0);
    $t->assign('useJs', $useJs);
    if ($useJs) {
        $t->render('edit-submit.html', '', 'wrap-popup.php');
    } else {
        header("Location: $me?");
    }
}

function show_form($groupId = 0, $error = '') {
    global $db, $me, $t;

    $group_perms = array();
    if ($groupId && !$error) {
        $t->assign($db->getRow("select * from " . TBL_AUTH_GROUP . " where group_id = '$groupId'"));
        $group_perms = $db->getCol("select distinct perm_id from " . TBL_GROUP_PERM . " where group_id = $groupId");
    } else {
        $t->assign($_POST);
        $t->assign('group_id', $groupId);
    }
    $useJs = get_request_int('use_js', 0);
    $t->assign('useJs', $useJs);
    $t->assign('perms', $db->getAll("select * from " . TBL_AUTH_PERM));
    $t->assign('group_perms', $group_perms);
    $t->assign('error', $error);
    $t->render('group-edit.html.php', translate("Edit Group"), ($useJs == 1 ? 'wrap-popup.php' : 'wrap.php'));
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
    $t->render('grouplist.html.php', $do_group ? translate("Group List") : translate("Role List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'save' : do_form(get_post_int('group_id', null));
            break;
        case 'edit' : show_form(get_get_int('group_id', null));
            break;
        case 'del' :
            if (check_action_key_die()) {
                del_group(get_get_int('group_id'));
                list_items(true, get_get_int('group_id'));
            }
            break;
        case 'purge' :
            if (check_action_key_die()) {
                purge_group(get_get_int('group_id'));
                list_items(true, get_get_int('group_id'));
            }
            break;
        case 'list-roles' : list_items(false);
            break;
        case 'save-role' : do_form(get_post_int('group_id', null));
            break;
        case 'edit-role' : show_form(get_get_int('group_id', null));
            break;
        case 'del-role' :
            if (check_action_key_die()) {
                del_group(get_get_int('group_id'));
                list_items(false, get_get_int('group_id', null));
            }
            break;
        case 'purge-role' :
            if (check_action_key_die()) {
                purge_group(get_get_int('group_id'));
                list_items(false, get_get_int('group_id', null));
            }
            break;
    }
} else {
    list_items();
}

//
