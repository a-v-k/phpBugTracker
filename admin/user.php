<?php

// user.php - Create and update users
// ------------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
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
// $Id: user.php,v 1.27 2001/09/18 03:26:15 bcurtis Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function do_form($userid = 0) {
  global $q, $me, $_pv, $STRING, $now, $u;

  // Validation
  if (!LOGIN_IS_EMAIL && !$_pv['flogin'] = trim($_pv['flogin'])) {
    $error = $STRING['givelogin'];
  } elseif (!valid_email($_pv['femail'])) {
    $error = $STRING['giveemail'];
  } elseif (!$_pv['fpassword'] = trim($_pv['fpassword'])) {
    $error = $STRING['givepassword'];
  }
  if ($error) {
    list_items($userid, $error);
    return;
  }
  if (!isset($_pv['factive'])) $_pv['factive'] = 0;

  if (EMAIL_IS_LOGIN) {
    $login = $_pv['femail'];
  } else {
    $login = $_pv['flogin'];
  }

  if (!$userid) {
    if (ENCRYPTPASS) $mpassword = md5($_pv['fpassword']);
    else $mpassword = $_pv['fpassword'];
    $new_user_id = $q->nextid(TBL_AUTH_USER);
    $q->query('insert into '.TBL_AUTH_USER
      ." (user_id, first_name, last_name, login, email, password, active,
      created_by, created_date, last_modified_by, last_modified_date)
      values ($new_user_id, '{$_pv['ffirstname']}', '{$_pv['flastname']}',
      '$login', '{$_pv['femail']}', '$mpassword', {$_pv['factive']}, $u, $now,
      $u, $now)");
    foreach ($_pv['fusergroup'] as $group) {
      $q->query("insert into ".TBL_USER_GROUP
        ." (user_id, group_id, created_by, created_date)
        values ('$new_user_id' ,'$group', $u, $now)");
    }
  } else {
    if (ENCRYPTPASS) {
      $oldpass = $q->grab_field("select password from ".TBL_AUTH_USER
        ." where user_id = $userid");
      if ($oldpass != $_pv['fpassword']) {
        $pquery = "password = '".md5($_pv['fpassword'])."',";
      } else {
        $pquery = '';
      }
    } else {
      $pquery = "password = '{$_pv['fpassword']}',";
    }
    $q->query("update ".TBL_AUTH_USER." set first_name = '{$_pv['ffirstname']}',
      last_name = '{$_pv['flastname']}', login = '$login', 
			email = '{$_pv['femail']}', $pquery active = {$_pv['factive']} 
			where user_id = '$userid'");

    // Update group memberships
    // Get user's groups (without dropping the user group)
    $q->query('select ug.group_id from '.TBL_USER_GROUP.' ug left join '
      .TBL_AUTH_GROUP.' g using (group_id) '
      ." where user_id = $userid and group_name <> 'User'");
    while ($group_id = $q->grab_field()) {
      $user_groups[] = $group_id;
    }
    // Compute differences between old and new
    if (!is_array($user_groups)) {
      $user_groups = array();
    }
    if (!is_array($_pv['fusergroup'])) {
      $_pv['fusergroup'] = array();
    }

    $remove_from = array_diff($user_groups, $_pv['fusergroup']);
    $add_to = array_diff($_pv['fusergroup'], $user_groups);

    if (count($remove_from)) {
      foreach ($remove_from as $group) {
        $q->query('delete from '.TBL_USER_GROUP
          ." where user_id = $userid and group_id = $group");
      }
    }
    if (count($add_to)) {
      foreach ($add_to as $group) {
        $q->query("insert into ".TBL_USER_GROUP
          ." (user_id, group_id, created_by, created_date)
          values ('$userid' ,'$group', $u, $now)");
      }
    }
  }
  header("Location: $me?");
}

function show_form($userid = 0, $error = '') {
  global $q, $me, $t, $_pv, $STRING;


  if ($userid && !$error) {
    $row = $q->grab("select * from ".TBL_AUTH_USER." where user_id = '$userid'");

    // Get user's groups
    $q->query('select group_id from '.TBL_USER_GROUP." where user_id = {$row['user_id']}");
    while ($group_id = $q->grab_field()) {
      $user_groups[] = $group_id;
    }

    $t->set_var(array(
      'action' => $STRING['edit'],
      'fuserid' => $row['user_id'],
      'flogin' => $row['login'],
      'ffirstname' => stripslashes($row['first_name']),
      'flastname' => stripslashes($row['last_name']),
      'femail' => $row['email'],
      'fpassword' => $row['password'],
      'factive' => $row['active'] ? 'checked' : '',
      'fusergroup' => build_select('group', $user_groups)
      ));
  } else {
    $t->set_var(array(
      'action' => $userid ? $STRING['edit'] : $STRING['addnew'],
      'error' => $error,
      'fuserid' => $_pv['userid'],
      'flogin' => $_pv['flogin'],
      'ffirstname' => stripslashes($_pv['firstname']),
      'flastname' => stripslashes($_pv['flastname']),
      'femail' => $_pv['femail'],
      'fpassword' => $_pv['fpassword'] ? $_pv['fpassword'] : genpassword(10),
      'factive' => isset($_pv['factive']) ? ($_pv['factive'] ? 'checked' : '')
        : 'checked',
      'fusergroup' => build_select('group', $_pv['fusergroup'])
      ));
  }

  // Show the login field only if login is not tied to email address
  if (EMAIL_IS_LOGIN) {
    $t->set_var('loginarea', '');
  } else {
    $t->parse('loginarea', 'loginentryarea', true);
  }
}

function list_items($userid = 0, $error = '') {
  global $me, $q, $t, $selrange, $order, $sort, $STRING, $TITLE, $page;

  $t->set_file('content', 'userlist.html');
  $t->set_block('content', 'row', 'rows');
  $t->set_block('content', 'loginentryarea', 'loginarea');

  if (!$order) { $order = 'login'; $sort = 'asc'; }
  $nr = $q->grab_field("select count(*) from ".TBL_AUTH_USER);

  list($selrange, $llimit, $npages, $pages) = multipages($nr, $page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr));

  $q->query("select user_id, concat(first_name,'&nbsp;',last_name) as fullname,
    email, login, created_date, active from ".TBL_AUTH_USER
    ." order by $order $sort limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['nousers']}</td></tr>");
    return;
  }

  $headers = array(
    'userid' => 'user_id',
    'name' =>  'last_name',
    'login' => 'login',
    'email' => 'email',
    'password' => 'password',
    'active' => 'active',
    'date' => 'created_date');

  sorting_headers($me, $headers, $order, $sort);

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'userid' => $row['user_id'],
      'login' =>  $row['login'],
      'name' => stripslashes($row['fullname']),
      'email' => $row['email'],
      'active' => $row['active'] ? 'Yes' : 'No',
      'date' => date(DATEFORMAT, $row['created_date'])));
    $t->parse('rows','row',true);
  }

  show_form($userid, $error);
  $t->set_var('TITLE', $TITLE['user']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Admin');

if ($op) switch($op) {
  case 'add' : list_items(); break;
  case 'edit' : list_items($id); break;
} elseif($submit) {
  do_form($id);
} else list_items();

$t->pparse('main',array('content', 'wrap', 'main'));

page_close();

?>
