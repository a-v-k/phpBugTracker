<?php

// database.php - Interface to the database table
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
// $Id: database.php,v 1.4 2004/10/25 12:06:59 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function del_item($databaseid = 0) {
    global $db, $me;

    if ($databaseid) {
        // Make sure we are going after a valid record
        $itemexists = $db->getOne('select count(*) from ' . TBL_DATABASE . " where database_id = $databaseid");
        // Are there any bugs tied to this one?
        $bugcount = $db->getOne('select count(*) from ' . TBL_BUG . " where database_id = $databaseid");
        if ($itemexists and ! $bugcount) {
            $db->query('delete from ' . TBL_DATABASE . " where database_id = $databaseid");
        }
    }
    header("Location: $me?");
}

function do_form($databaseId = 0) {
    global $db, $me, $t;

//    var_dump($_POST);
//    die();
//    extract($_POST);
//    array (size=6)
//      'database_name' => string 'dddddd' (length=6)
//      'sort_order' => string '44' (length=2)
//      'submit' => string 'Submit' (length=6)
//      'database_id' => string '' (length=0)
//      'use_js' => string '1' (length=1)
//      'op' => string 'save' (length=4)

    $error = '';
    $databaseName = trim(get_post_val('database_name', null));
    $sortOrder = get_post_int('sort_order', 0);
    $useJs = get_request_int('use_js', 0);
    // Validation
    if ($databaseName == '') {
        $error = translate("Please enter a name");
    }
    if ($error) {
        show_form($databaseId, $error);
        return;
    }

    if (!$databaseId) {
        $db->query("insert into " . TBL_DATABASE . " (database_id, database_name, sort_order) values (" . $db->nextId(TBL_DATABASE) . ', ' . $db->quote(stripslashes($databaseName)) . ", $sortOrder)");
    } else {
        $db->query("update " . TBL_DATABASE . " set database_name = " . $db->quote(stripslashes($databaseName)) . ", sort_order = $sortOrder where database_id = $databaseId");
    }
    if ($useJs) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?");
    }
}

function show_form($databaseid = 0, $error = '') {
    global $db, $me, $t;
    $useJs = get_request_int('use_js', 0);

    if ($databaseid && !$error) {
        $t->assign($db->getRow("select * from " . TBL_DATABASE . " where database_id = '$databaseid'"));
    } else {
        $t->assign($_POST);
    }
    $t->assign('error', $error);
    $t->assign('useJs', $useJs);
    $t->render('database-edit.html.php', translate("Edit Database"), ($useJs == 1) ? 'wrap-popup.php' : '');
}

function list_items($databaseId = 0, $error = '') {
    global $me, $db, $t, $QUERY;

    if (empty($_GET['order'])) {
        $order = 'sort_order';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $nr = $db->getOne("select count(*) from " . TBL_DATABASE);

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");

    $t->assign('databases', $db->getAll($db->modifyLimitQuery(
                            sprintf($QUERY['admin-list-databases'], $order, $sort), $llimit, $selrange)));

    $headers = array(
        'databaseid' => 'database_id',
        'name' => 'database_name',
        'sortorder' => 'sort_order');

    sorting_headers($me, $headers, $order, $sort);

    $t->render('databaselist.html.php', translate("Database List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : list_items();
            break;
        case 'edit' : show_form(get_get_int('database_id', null));
            break;
        case 'del' : del_item(get_get_int('database_id'));
            break;
        case 'save' : do_form(get_post_int('database_id', null));
            break;
    }
} else {
    list_items();
}

//