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
// $Id: user.php,v 1.16 2001/08/23 01:39:03 bcurtis Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function do_form($userid = 0) {
  global $q, $me, $ffirstname, $flastname, $femail, $fpassword, $usertype, $STRING, $now;

  // Validation
  if (!valid_email($femail))
    $error = $STRING['giveemail'];
  elseif (!$fpassword = trim($fpassword))
    $error = $STRING['givepassword'];
  if ($error) { list_items($userid, $error); return; }

  if (!$userid) {
    if (ENCRYPTPASS) $mpassword = md5($fpassword);
    else $mpassword = $fpassword;
    $q->query("insert into user (user_id, first_name, last_name, email, password, user_level, created_date) values (".$q->nextid('user').", '$ffirstname', '$flastname', '$femail', '$mpassword', $usertype, $now)");
  } else {
    if (ENCRYPTPASS) {
      $oldpass = $q->grab_field("select password from user where user_id = $userid");
      if ($oldpass != $fpassword) {
        $pquery = "password = '".md5($fpassword)."',";
      } else {
        $pquery = '';
      }
    } else {
      $pquery = "password = '$fpassword',";
    }
    $q->query("update user set first_name = '$ffirstname', last_name = '$flastname', email = '$femail', $pquery user_level = $usertype where user_id = '$userid'");
  }
  header("Location: $me?");
}

function show_form($userid = 0, $error = '') {
  global $q, $me, $t, $firstname, $lastname, $email, $password, $usertype, $STRING;

  if ($userid && !$error) {
    $row = $q->grab("select * from user where user_id = '$userid'");
    $t->set_var(array(
      'action' => $STRING['edit'],
      'fuserid' => $row['user_id'],
      'ffirstname' => stripslashes($row['first_name']),
      'flastname' => stripslashes($row['last_name']),
      'femail' => $row['email'],
      'fpassword' => $row['password'],
      'usertype' => build_select('authlevels',$row['user_level']),
      'createddate' => $row['created_date']));
  } else {
    $t->set_var(array(
      'action' => $userid ? $STRING['edit'] : $STRING['addnew'],
      'error' => $error,
      'fuserid' => $userid,
      'ffirstname' => stripslashes($firstname),
      'flastname' => stripslashes($lastname),
      'femail' => $email,
      'fpassword' => $password ? $password : genpassword(10),
      'usertype' => build_select('authlevels',$usertype),
      'createddate' => $createddate));
  }
}

function list_items($userid = 0, $error = '') {
  global $me, $q, $t, $selrange, $order, $sort, $select, $STRING, $TITLE, $page;

  $t->set_file('content','userlist.html');
  $t->set_block('content','row','rows');

  if (!$order) { $order = '1'; $sort = 'asc'; }
  $nr = $q->grab_field("select count(*) from user");

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr));

  $q->query("select user_id, concat(first_name,'&nbsp;',last_name) as fullname, email,
    created_date, user_level from user order by $order $sort
    limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['nousers']}</td></tr>");
    return;
  }

  $headers = array(
    'userid' => 'user_id',
    'name' =>  'last_name',
    'login' => 'email',
    'password' => 'password',
    'userlevel' => 'user_level',
    'date' => 'created_date');

  sorting_headers($me, $headers, $order, $sort);

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'userid' => $row['user_id'],
      'name' => stripslashes($row['fullname']),
      'email' => $row['email'],
      'userlevel' => $select['authlevels'][$row['user_level']],
      'date' => date(DATEFORMAT,$row['created_date'])));
    $t->parse('rows','row',true);
  }

  show_form($userid, $error);
  $t->set_var('TITLE',$TITLE['user']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Administrator');

if ($op) switch($op) {
  case 'add' : list_items(); break;
  case 'edit' : list_items($id); break;
} elseif($submit) {
  do_form($id);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
