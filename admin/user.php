<?php

// user.php - Create and update users
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
// $Id: user.php,v 1.53 2005/09/03 16:41:48 ulferikson Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

function do_form($userid = 0) {
    global $db, $me, $now, $u, $QUERY, $t;

    $error = '';
    // Validation
    if (!EMAIL_IS_LOGIN && !$_POST['login'] = trim($_POST['login'])) {
        $error = translate("Please enter a login");
    } elseif (!bt_valid_email($_POST['email'])) {
        $error = translate("Please enter an email");
    } elseif (!$_POST['password'] = trim($_POST['password'])) {
        $error = translate("Please enter a password");
    }
    if ($error) {
        show_form($userid, $error);
        return;
    }
    if (!isset($_POST['active'])) {
        $_POST['active'] = 0;
    }
    if (!isset($_POST['fe_notice'])) {
        $_POST['fe_notice'] = 0;
    }

    if (EMAIL_IS_LOGIN) {
        $login = $_POST['email'];
    } else {
        $login = $_POST['login'];
    }

    if (!$userid) {
        if (ENCRYPT_PASS) {
            $mpassword = $db->quote(md5($_POST['password']));
        } else {
            $mpassword = $db->quote(stripslashes($_POST['password']));
        }
        $new_user_id = $db->nextId(TBL_AUTH_USER);
        $db->query('insert into ' . TBL_AUTH_USER . " (user_id, first_name, last_name, login, email, password, active,created_by, created_date, last_modified_by, last_modified_date) values (" . join(', ', array($new_user_id, $db->quote(stripslashes($_POST['first_name'])), $db->quote(stripslashes($_POST['last_name'])), $db->quote(stripslashes($login)), $db->quote($_POST['email']), $mpassword, $_POST['active'], $u, $now, $u, $now)) . ')');
        // Add to the selected groups
        if (isset($_POST['fusergroup']) and is_array($_POST['fusergroup']) and
                $_POST['fusergroup'][0]) {
            foreach ($_POST['fusergroup'] as $group) {
                $db->query("insert into " . TBL_USER_GROUP . " (user_id, group_id, created_by, created_date) values ('$new_user_id' ,'$group', $u, $now)");
            }
        }
        // Add to prefs
        $db->query("INSERT INTO " . TBL_USER_PREF . " (user_id, email_notices) VALUES ($new_user_id, '{$_POST['fe_notice']}')");
    } else {
        if (ENCRYPT_PASS) {
            $oldpass = $db->getOne("select password from " . TBL_AUTH_USER . " where user_id = $userid");
            if ($oldpass != $_POST['password']) {
                $pquery = "password = '" . md5($_POST['password']) . "',";
            } else {
                $pquery = '';
            }
        } else {
            $pquery = "password = " . $db->quote(stripslashes($_POST['password'])) . ",";
        }
        $db->query("update " . TBL_AUTH_USER . " set first_name = " . $db->quote(stripslashes($_POST['first_name'])) . ", last_name = " . $db->quote(stripslashes($_POST['last_name'])) . ", login = " . $db->quote(stripslashes($login)) . ", email = '{$_POST['email']}', $pquery active = {$_POST['active']}  where user_id = $userid");

        // Update preferences
        $db->query("update " . TBL_USER_PREF . " set email_notices = {$_POST['fe_notice']} where user_id = $userid");

        // Update group memberships
        // Get user's groups (without dropping the user group)
        $user_groups = $db->getCol(sprintf($QUERY['admin-user-groups'], $userid));

        // Compute differences between old and new
        if (!isset($user_groups) or ! is_array($user_groups)) {
            $user_groups = array();
        }
        if (!isset($_POST['fusergroup']) or ! is_array($_POST['fusergroup']) or ! $_POST['fusergroup'][0]) {
            $_POST['fusergroup'] = array();
        }

        $remove_from = array_diff($user_groups, $_POST['fusergroup']);
        $add_to = array_diff($_POST['fusergroup'], $user_groups);

        if (count($remove_from)) {
            foreach ($remove_from as $group) {
                $db->query('delete from ' . TBL_USER_GROUP . " where user_id = $userid and group_id = $group");
            }
        }
        if (count($add_to)) {
            foreach ($add_to as $group) {
                $db->query("insert into " . TBL_USER_GROUP . " (user_id, group_id, created_by, created_date) values ('$userid' ,'$group', $u, $now)");
            }
        }
    }
    if ($_POST['use_js']) {
        $t->render('edit-submit.html');
    } else {
        header("Location: $me?userfilter={$_POST['userfilter']}&groupfilter={$_POST['groupfilter']}");
    }
}

function show_form($userid = 0, $error = '') {
    global $db, $me, $t;


    if ($userid && !$error) {
        $t->assign($db->getRow('select * from ' . TBL_AUTH_USER . " u, " . TBL_USER_PREF . " p where u.user_id = $userid and u.user_id = p.user_id"));

        // Get user's groups
        $t->assign('user_groups', $db->getCol('select group_id from ' . TBL_USER_GROUP . " where user_id = $userid"));

        $t->assign('hadadmin', $db->getOne('select count(*) from ' . TBL_USER_GROUP . " where user_id = $userid and group_id = 1"));
    } else {
        $t->assign($_POST);
        $t->assign(array(
            'error' => $error,
            'password' => isset($_POST['password']) ? $_POST['password'] : genpassword(10),
            'user_groups' => isset($_POST['fusergroup']) ? $_POST['fusergroup'] : array(),
            // Whether or not this user has admin rights
            'hadadmin' => 0
        ));
    }
    $useJs = get_request_int('use_js', 0);
    $t->assign('useJs', $useJs);
    // The number of admins in the system
    $t->assign('numadmins', $db->getOne('select count(*) from ' . TBL_USER_GROUP . ' where group_id = 1'));
    $t->render('user-edit.html.php', translate("Edit User"), $useJs == 1 ? 'wrap-popup.php' : 'wrap.php');
}

function list_items($userid = 0, $error = '') {
    global $me, $db, $t;

    if (empty($_GET['order'])) {
        $order = 'login';
        $sort = 'asc';
    } else {
        $order = $_GET['order'];
        $sort = $_GET['sort'];
    }

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $user_filter = isset($_GET['userfilter']) ? $_GET['userfilter'] : 0;
    $group_filter = isset($_GET['groupfilter']) ? $_GET['groupfilter'] : 0;

    $filter_user = '';
    $filter_group = '';
    if (isset($_GET['userfilter']))
        switch ($_GET['userfilter']) {
            case 1 : $filter_user = 'u.active = 1';
                break;
            case 2 : $filter_user = 'u.active = 0';
                break;
        }
    if (isset($_GET['groupfilter'])) {
        if ((int) $_GET['groupfilter'] > 0) {
            $filter_group = 'u.user_id = ug.user_id and ug.group_id = ' . (int) $_GET['groupfilter'];
        }
    }
    if ($filter_group <> '' && $filter_user <> '') {
        $filter_group = 'and ' . $filter_group;
    }
    $nr = $db->getOne("select count(distinct u.user_id)" .
            " from " . TBL_AUTH_USER . " u " .
            ($filter_group <> '' ? ", " . TBL_USER_GROUP . " ug" : "") .
            (($filter_group <> '' or $filter_user <> '') ? " where $filter_user $filter_group" : ""));

    list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort&userfilter=$user_filter&groupfilter=$group_filter");

    $t->assign('users', $db->getAll($db->modifyLimitQuery("select distinct u.user_id, u.first_name, u.last_name, u.email, u.login, u.created_date, u.active" .
                            " from " . TBL_AUTH_USER . " u" .
                            ($filter_group <> '' ? ", " . TBL_USER_GROUP . " ug" : "") .
                            (($filter_group <> '' or $filter_user <> '') ? " where $filter_user $filter_group" : "") .
                            " order by $order $sort", $llimit, $selrange)));

    $headers = array(
        'userid' => 'user_id',
        'name' => 'last_name',
        'login' => 'login',
        'email' => 'email',
        'password' => 'password',
        'active' => 'active',
        'date' => 'created_date');

    sorting_headers($me, $headers, $order, $sort, "page=$page&userfilter=$user_filter&groupfilter=$group_filter");

    $t->assign('userfilter', $user_filter);
    $t->assign('groupfilter', $group_filter);
    $t->render('userlist.html.php', translate("User List"));
}

$perm->check('Admin');

if (isset($_REQUEST['op'])) {
    switch ($_REQUEST['op']) {
        case 'add' : list_items();
            break;
        case 'edit' : show_form(get_request_int('user_id', null));
            break;
        case 'save' : do_form(get_post_int('user_id', null));
            break;
    }
} else {
    list_items();
}

//