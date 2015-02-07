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

//    var_dump($_POST);
//    die();
//    extract($_POST);

//array (size=8)
//  'status_name' => string 'dfdfd' (length=5)
//  'status_desc' => string 'ffddf' (length=5)
//  'sort_order' => string '' (length=0)
//  'bug_open' => string '1' (length=1)
//  'submit' => string 'Submit' (length=6)
//  'status_id' => string '' (length=0)
//  'use_js' => string '1' (length=1)
//  'op' => string 'save' (length=4


    $error = '';
    $statusName = trim(get_post_val('status_name', null));
    $statusDesc = trim(get_post_val('status_desc', null));
    $sortOrder = get_post_int('sort_order', 0);
    $bugOpen = get_post_int('bug_open', 1);
    $useJs = get_request_int('use_js', 0);

    // Validation
    if ($statusName == '') {
        $error = translate("Please enter a name");
    } elseif ($statusDesc == '') {
        $error = translate("Please enter a description");
    }
    if ($error) {
        show_form($statusid, $error);
        return;
    }

    if (!$statusid) {
        $db->query("insert into " . TBL_STATUS . " (status_id, status_name, status_desc, bug_open, sort_order) values (" . $db->nextId(TBL_STATUS) . ', ' . $db->quote(stripslashes($statusName)) . ', ' . $db->quote(stripslashes($statusDesc)) . ', ' . (int) $bugOpen . ", '$sortOrder')");
    } else {
        $db->query("update " . TBL_STATUS . " set status_name = " . $db->quote(stripslashes($statusName)) . ', status_desc = ' . $db->quote(stripslashes($statusDesc)) . ', bug_open = ' . (int) $bugOpen . ", sort_order = $sortOrder where status_id = $statusid");
    }
    if ($useJs) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?");
    }
}

function show_form($statusid = 0, $error = '') {
    global $db, $me, $t;

    $useJs = get_request_int('use_js', 0);
    //extract($_POST);
    if ($statusid && !$error) {
        $t->assign($db->getRow("select * from " . TBL_STATUS . " where status_id = '$statusid'"));
    } else {
        $t->assign($_POST);
        if (empty($_POST)) {
            $t->assign('bug_open', 1);
        } // new bugs def. open :)
    }
    $t->assign('error', $error);
    $t->assign('useJs', $useJs);
    $t->render('status-edit.html.php', translate("Edit Status"), $useJs == 1 ? 'wrap-popup.php' : 'wrap.php');
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

    $t->render('statuslist.html.php', translate("Status List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : list_items();
            break;
        case 'edit' : show_form(get_request_int('status_id', null));
            break;
        case 'del' : del_item(get_get_int('status_id'));
            break;
        case 'save' : do_form(get_post_int('status_id', null));
            break;
    }
} else {
    list_items();
}

//