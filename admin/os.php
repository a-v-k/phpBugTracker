<?php

// os.php - Interface to the OS table
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
// $Id: os.php,v 1.29 2004/10/25 12:06:59 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($osid = 0) {
    global $db, $me;

    if ($osid) {
        // Make sure we are going after a valid record
        $itemexists = $db->getOne('select count(*) from ' . TBL_OS . " where os_id = $osid");
        // Are there any bugs tied to this one?
        $bugcount = $db->getOne('select count(*) from ' . TBL_BUG . " where os_id = $osid");
        if ($itemexists and ! $bugcount) {
            $db->query('delete from ' . TBL_OS . " where os_id = $osid");
        }
    }
    header("Location: $me?");
}

function do_form($osId = 0) {
    global $db, $me, $t;

//    var_dump($_POST);
//    die();
//    extract($_POST);
//    array (size=7)
//      'os_name' => string 'dfdf' (length=4)
//      'regex' => string 'dfddf' (length=5)
//      'sort_order' => string '33' (length=2)
//      'submit' => string 'Submit' (length=6)
//      'op' => string 'save' (length=4)
//      'os_id' => string '' (length=0)
//      'use_js' => string '1' (length=1)

    $error = '';
    $osName = trim(get_post_val('os_name', null));
    $regex = trim(get_post_val('regex', null));
    $sortOrder = get_post_int('sort_order', 0);
    $useJs = get_request_int('use_js', 0);
    // Validation
    if ($osName == '') {
        $error = translate("Please enter a name");
    }
    if ($error) {
        show_form($osId, $error);
        return;
    }

    if (!$osId) {
        //$db->query("insert into " . TBL_OS . " (os_id, os_name, regex, sort_order) values (" . $db->nextId(TBL_OS) . ", " . $db->quote(stripslashes($osName)) . ", '$regex', '$sortOrder')");
        $db->query(
                "insert into " . TBL_OS . " (os_id, os_name, regex, sort_order) values (:next_id, :os_name, :regex, :sort_order) ", array(':next_id' => $db->nextId(TBL_OS), ':os_name' => $osName,
            ':regex' => $regex, ':sort_order' => $sortOrder)
        );
    } else {
        //$db->query("update " . TBL_OS . " set os_name = " . $db->quote(stripslashes($osName)) . ", regex = '$regex', sort_order = '$sortOrder' where os_id = '$osId'");
        $db->query("update " . TBL_OS . " set os_name = :os_name, regex = :regex, sort_order = :sort_order where os_id = :os_id ", array(':os_name' => $osName,
            ':regex' => $regex, ':sort_order' => $sortOrder, ':os_id' => $osId)
        );
    }
    if ($useJs) {
        $t->render('edit-submit.html', '', 'wrap-popup.php');
    } else {
        header("Location: $me?");
    }
}

function show_form($osId = 0, $error = '') {
    global $db, $me, $t;
    $useJs = get_request_int('use_js', 0);

    //extract($_POST);
    if ($osId && !$error) {
        $t->assign($db->getRow("select * from " . TBL_OS . " where os_id = '$osId'"));
    } else {
        $t->assign($_POST);
    }
    $t->assign('error', $error);
    $t->assign('useJs', $useJs);
    $t->render('os-edit.html.php', translate("Edit Operating System"), ($useJs == 1) ? 'wrap-popup.php' : 'wrap.php');
}

function list_items($osid = 0, $error = '') {
    global $db, $me, $t, $QUERY;

    if (empty($_GET['order'])) {
        $order = 'sort_order';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $nr = $db->getOne("select count(*) from " . TBL_OS);

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

    $t->assign('oses', $db->getAll($db->modifyLimitQuery(
                            sprintf($QUERY['admin-list-oses'], $order, $sort), $llimit, $selrange)));

    $headers = array(
        'osid' => 'os_id',
        'name' => 'os_name',
        'regex' => 'regex',
        'sortorder' => 'sort_order');

    sorting_headers($me, $headers, $order, $sort, "page=$page");

    $t->render('oslist.html.php', translate("Operating System List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'edit' : show_form(get_get_int('os_id', null));
            break;
        case 'del' :
            if (check_action_key_die()) {
                del_item(get_get_int('os_id'));
            }
            break;
        case 'save' : do_form(get_post_int('os_id', null));
            break;
    }
} else {
    list_items();
}

//
