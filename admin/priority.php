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

function del_item($priorityId = 0) {
    global $db, $me;

    if ($priorityId) {
        // Make sure we are going after a valid record
        $itemexists = $db->getOne('select count(*) from ' . TBL_PRIORITY . " where priority_id = $priorityId");
        // Are there any bugs tied to this one?
        //var_dump($db->getAll('desc ' . TBL_BUG));
        $bugcount = $db->getOne('select count(*) from ' . TBL_BUG . " where priority = $priorityId");
        if ($itemexists and ! $bugcount) {
            $db->query('delete from ' . TBL_PRIORITY . " where priority_id = $priorityId");
        }
    }
    header("Location: $me?");
}

function do_form($priorityId = 0) {
    global $db, $me, $t;


//    var_dump($_POST);
//    die();
//    extract($_POST);
//    array (size=8)
//      'priority_name' => string 'dfdf' (length=4)
//      'priority_desc' => string '              fdfs  ' (length=20)
//      'sort_order' => string '222' (length=3)
//      'priority_color' => string '' (length=0)
//      'submit' => string 'Submit' (length=6)
//      'priority_id' => string '0' (length=1)
//      'use_js' => string '1' (length=1)
//      'op' => string 'save' (length=4)

    $error = '';
    $priorityName = trim(get_post_val('priority_name', null));
    $priorityDesc = trim(get_post_val('priority_desc', null));
    $priorityColor = preg_replace("/[^a-zA-Z0-9#]+/", "", trim(get_post_val('priority_color', null)));
    $sortOrder = get_post_int('sort_order', 0);
    $useJs = get_request_int('use_js', 0);
    // Validation
    if ($priorityName == '') {
        $error = translate("Please enter a name");
    } elseif ($priorityDesc == '') {
        $error = translate("Please enter a description");
    }
    if ($error) {
        show_form($priorityId, $error);
        return;
    }

    if (!$priorityId) {
        $db->query("insert into " . TBL_PRIORITY . " (priority_id, priority_name, priority_desc, sort_order, priority_color)  values (" . $db->nextId(TBL_PRIORITY) . ', ' . $db->quote(stripslashes($priorityName)) . ', ' . $db->quote(stripslashes($priorityDesc)) . ", $sortOrder, " . $db->quote(stripslashes($priorityColor)) . ')');
    } else {
        $db->query("update " . TBL_PRIORITY . " set priority_name = " . $db->quote(stripslashes($priorityName)) . ', priority_desc = ' . $db->quote(stripslashes($priorityDesc)) . ", sort_order = $sortOrder, priority_color = " . $db->quote(stripslashes($priorityColor)) . " where priority_id = $priorityId");
    }
    if ($useJs) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?");
    }
}

function show_form($priorityId = 0, $error = '') {
    global $db, $me, $t;
    $useJs = get_request_int('use_js', 0);

    if ($priorityId && !$error) {
        $t->assign($db->getRow("select * from " . TBL_PRIORITY . " where priority_id = '$priorityId'"));
    } else {
        //$t->assign($_POST);
        $t->assign('priority_id', $priorityId);
        $t->assign('priority_name', get_post_val('priority_name', null));
        $t->assign('priority_desc', get_post_val('priority_desc', null));
        $t->assign('priority_color', get_post_val('priority_color', null));
        $t->assign('sort_order', get_post_val('sort_order', null));
    }
    $t->assign('error', $error);
    $t->assign('useJs', $useJs);
    $t->render('priority-edit.html.php', translate("Edit Priority"), ($useJs == 1) ? 'wrap-popup.php' : 'wrap.php');
}

function list_items($priorityid = 0, $error = '') {
    global $me, $db, $t, $QUERY;

    $rOrder = get_request_value('order', 'sort_order');
    $order = preg_replace("/[^a-zA-Z0-9_]+/", "", $rOrder);
    $sort = get_request_value('sort', 'asc');
    if (!in_array($sort, array('asc', 'desc'))){
        $sort = 'asc';
    }
    $page = get_get_int('page', 1);

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

    $t->render('prioritylist.html.php', translate("Priority List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : list_items();
            break;
        case 'edit' : show_form(get_get_int('priority_id', null));
            break;
        case 'del' :
            if (check_action_key_die()) {
                del_item(get_get_int('priority_id'));
            }
            break;
        case 'save' : do_form(get_post_int('priority_id', null));
    }
} else {
    list_items();
}

//