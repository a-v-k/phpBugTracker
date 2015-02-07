<?php

// severity.php - Interface to the severity table
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
// $Id: severity.php,v 1.26 2004/10/25 12:06:59 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($severityid = 0) {
    global $db, $me;

    if ($severityid) {
        // Make sure we are going after a valid record
        $itemexists = $db->getOne('select count(*) from ' . TBL_SEVERITY . " where severity_id = $severityid");
        // Are there any bugs tied to this one?
        $bugcount = $db->getOne('select count(*) from ' . TBL_BUG . " where severity_id = $severityid");
        if (($itemexists > 0 ) && ($bugcount == 0)) {
            $db->query('delete from ' . TBL_SEVERITY . " where severity_id = $severityid");
        }
    }
    header("Location: $me?");
}

function do_form($severityId = 0) {
    global $db, $me, $t;

//    var_dump($_POST);
//    die();
//    extract($_POST);
//    array (size=8)
//      'severity_name' => string 'aaa' (length=3)
//      'severity_desc' => string 'sss' (length=3)
//      'sort_order' => string '44' (length=2)
//      'severity_color' => string '33' (length=2)
//      'submit' => string 'Submit' (length=6)
//      'severity_id' => string '' (length=0)
//      'use_js' => string '1' (length=1)
//      'op' => string 'save' (length=4)


    $error = '';
    $severityName = trim(get_post_val('severity_name', null));
    $severityDesc = trim(get_post_val('severity_desc', null));
    $severityColor = preg_replace("/[^a-zA-Z0-9#]+/", "", trim(get_post_val('severity_color', null)));
    $sortOrder = get_post_int('sort_order', 0);
    $useJs = get_request_int('use_js', 0);
    // Validation
    if ($severityName == '') {
        $error = translate("Please enter a name");
    } elseif ($severityDesc == '') {
        $error = translate("Please enter a description");
    }
    if ($error) {
        show_form($severityId, $error);
        return;
    }

    if (!$severityId) {
        $db->query("insert into " . TBL_SEVERITY . " (severity_id, severity_name, severity_desc, sort_order, severity_color)  values (" . $db->nextId(TBL_SEVERITY) . ', ' . $db->quote(stripslashes($severityName)) . ', ' . $db->quote(stripslashes($severityDesc)) . ", $sortOrder, " . $db->quote(stripslashes($severityColor)) . ')');
    } else {
        $db->query("update " . TBL_SEVERITY . " set severity_name = " . $db->quote(stripslashes($severityName)) . ', severity_desc = ' . $db->quote(stripslashes($severityDesc)) . ", sort_order = $sortOrder, severity_color = " . $db->quote(stripslashes($severityColor)) . " where severity_id = $severityId");
    }
    if ($useJs) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?");
    }
}

function show_form($severityid = 0, $error = '') {
    global $db, $me, $t;

    $useJs = get_request_int('use_js', 0);

    if ($severityid && !$error) {
        $t->assign($db->getRow("select * from " . TBL_SEVERITY . " where severity_id = '$severityid'"));
    } else {
        $t->assign($_POST);
    }
    $t->assign('error', $error);
    $t->assign('useJs', $useJs);
    $t->render('severity-edit.html.php', translate("Edit Severity"), ($useJs == 1) ? 'wrap-popup.php' : 'wrap.php');
}

function list_items($severityid = 0, $error = '') {
    global $me, $db, $t, $QUERY;

    if (empty($_GET['order'])) {
        $order = 'sort_order';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $nr = $db->getOne("select count(*) from " . TBL_SEVERITY);

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

    $t->assign('severities', $db->getAll($db->modifyLimitQuery(
                            sprintf($QUERY['admin-list-severities'], $order, $sort), $llimit, $selrange)));


    $headers = array(
        'severityid' => 'severity_id',
        'name' => 'severity_name',
        'description' => 'severity_desc',
        'sortorder' => 'sort_order',
        'color' => 'severity_color');

    sorting_headers($me, $headers, $order, $sort);

    $t->render('severitylist.html.php', translate("Severity List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : list_items();
            break;
        case 'edit' : show_form(get_get_int('severity_id', null));
            break;
        case 'del' : del_item(get_get_int('severity_id'));
            break;
        case 'save' : do_form(get_post_int('severity_id', null));
    }
} else {
    list_items();
}

//