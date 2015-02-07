<?php

// priority.php - Interface to the priority table
// ------------------------------------------------------------------------
// Copyright (c) 2005 The phpBugTracker Group
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

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($priorityid = 0) {
    global $db, $me;

    if ($priorityid) {
        // Make sure we are going after a valid record
        $itemexists = $db->getOne('select count(*) from ' . TBL_PRIORITY . " where priority_id = $priorityid");
        // Are there any bugs tied to this one?
        $bugcount = $db->getOne('select count(*) from ' . TBL_BUG . " where priority_id = $priorityid");
        if ($itemexists and ! $bugcount) {
            $db->query('delete from ' . TBL_PRIORITY . " where priority_id = $priorityid");
        }
    }
    header("Location: $me?");
}

function do_form($priorityid = 0) {
    global $db, $me, $t;

    extract($_POST);
    $error = '';
    // Validation
    if (!$priority_name = trim($priority_name)) {
        $error = translate("Please enter a name");
    } elseif (!$priority_desc = trim($priority_desc)) {
        $error = translate("Please enter a description");
    }
    if ($error) {
        show_form($priorityid, $error);
        return;
    }

    if (empty($sort_order)) {
        $sort_order = 0;
    }
    if (!$priorityid) {
        $db->query("insert into " . TBL_PRIORITY . " (priority_id, priority_name, priority_desc, sort_order, priority_color)  values (" . $db->nextId(TBL_PRIORITY) . ', ' . $db->quote(stripslashes($priority_name)) . ', ' . $db->quote(stripslashes($priority_desc)) . ", $sort_order, " . $db->quote(stripslashes($priority_color)) . ')');
    } else {
        $db->query("update " . TBL_PRIORITY . " set priority_name = " . $db->quote(stripslashes($priority_name)) . ', priority_desc = ' . $db->quote(stripslashes($priority_desc)) . ", sort_order = $sort_order, priority_color = " . $db->quote(stripslashes($priority_color)) . " where priority_id = $priority_id");
    }
    if ($use_js) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?");
    }
}

function show_form($priorityid = 0, $error = '') {
    global $db, $me, $t;

    if ($priorityid && !$error) {
        $t->assign($db->getRow("select * from " . TBL_PRIORITY . " where priority_id = '$priorityid'"));
    } else {
        $t->assign($_POST);
    }
    $t->assign('error', $error);
    $t->render('priority-edit.html', translate("Edit Priority"), !empty($_REQUEST['use_js']) ? 'wrap-popup.php' : 'wrap.php');
}

function list_items($priorityid = 0, $error = '') {
    global $me, $db, $t, $QUERY;

    if (empty($_GET['order'])) {
        $order = 'sort_order';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $nr = $db->getOne("select count(*) from " . TBL_PRIORITY);

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

    $t->assign('priorities', $db->getAll($db->modifyLimitQuery(
                            sprintf($QUERY['admin-list-priorities'], $order, $sort), $llimit, $selrange)));


    $headers = array(
        'priorityid' => 'priority_id',
        'name' => 'priority_name',
        'description' => 'priority_desc',
        'sortorder' => 'sort_order',
        'color' => 'priority_color');

    sorting_headers($me, $headers, $order, $sort);

    $t->render('prioritylist.html', translate("Priority List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : list_items();
            break;
        case 'edit' : show_form($_GET['priority_id']);
            break;
        case 'del' : del_item($_GET['priority_id']);
            break;
        case 'save' : do_form($_POST['priority_id']);
    }
} else {
    list_items();
}

//