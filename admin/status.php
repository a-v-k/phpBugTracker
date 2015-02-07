<?php

// status.php - Interface to the Status table
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
// $Id: status.php,v 1.31 2004/10/25 12:06:59 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($statusid = 0) {
    global $db, $me;

    if ($statusid) {
        // Make sure we are going after a valid record
        $itemexists = $db->getOne('select count(*) from ' . TBL_STATUS . " where status_id = $statusid");
        // Are there any bugs tied to this one?
        $bugcount = $db->getOne('select count(*) from ' . TBL_BUG . " where status_id = $statusid");
        if ($itemexists and ! $bugcount) {
            $db->query('delete from ' . TBL_STATUS . " where status_id = $statusid");
        }
    }
    header("Location: $me?");
}

function do_form($statusid = 0) {
    global $db, $me, $t;

    extract($_POST);
    $error = '';
    // Validation
    if (!$status_name = trim($status_name)) {
        $error = translate("Please enter a name");
    } elseif (!$status_desc = trim($status_desc)) {
        $error = translate("Please enter a description");
    }
    if ($error) {
        show_form($statusid, $error);
        return;
    }

    if (empty($sort_order)) {
        $sort_order = 0;
    }
    if (!$statusid) {
        $db->query("insert into " . TBL_STATUS . " (status_id, status_name, status_desc, bug_open, sort_order) values (" . $db->nextId(TBL_STATUS) . ', ' . $db->quote(stripslashes($status_name)) . ', ' . $db->quote(stripslashes($status_desc)) . ', ' . (int) $bug_open . ", '$sort_order')");
    } else {
        $db->query("update " . TBL_STATUS . " set status_name = " . $db->quote(stripslashes($status_name)) . ', status_desc = ' . $db->quote(stripslashes($status_desc)) . ', bug_open = ' . (int) $bug_open . ", sort_order = $sort_order where status_id = $statusid");
    }
    if ($use_js) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?");
    }
}

function show_form($statusid = 0, $error = '') {
    global $db, $me, $t;

    extract($_POST);
    if ($statusid && !$error) {
        $t->assign($db->getRow("select * from " . TBL_STATUS . " where status_id = '$statusid'"));
    } else {
        $t->assign($_POST);
        if (empty($_POST)) {
            $t->assign('bug_open', 1);
        } // new bugs def. open :)
    }
    $t->assign('error', $error);
    $t->render('status-edit.html', translate("Edit Status"), !empty($_REQUEST['use_js']) ? 'wrap-popup.php' : 'wrap.php');
}

function list_items($statusid = 0, $error = '') {
    global $me, $db, $t, $QUERY;

    if (empty($_GET['order'])) {
        $order = 'sort_order';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $nr = $db->getOne("select count(*) from " . TBL_STATUS);

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

    $t->assign('statuses', $db->getAll($db->modifyLimitQuery(
                            sprintf($QUERY['admin-list-statuses'], $order, $sort), $llimit, $selrange)));

    $headers = array(
        'statusid' => 'status_id',
        'name' => 'status_name',
        'description' => 'status_desc',
        'sortorder' => 'sort_order');

    sorting_headers($me, $headers, $order, $sort);

    $t->render('statuslist.html', translate("Status List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : list_items();
            break;
        case 'edit' : show_form($_REQUEST['status_id']);
            break;
        case 'del' : del_item($_REQUEST['status_id']);
            break;
        case 'save' : do_form($_POST['status_id']);
            break;
    }
} else {
    list_items();
}

//