<?php

// status.php - Interface to the Status table
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
// $Id: status.php,v 1.10 2001/08/23 01:32:05 bcurtis Exp $

ini_set('include_path', '../'.ini_get('include_path'));
include 'include.php';

function do_form($statusid = 0) {
  global $q, $me, $fname, $fdescription, $fsortorder, $STRING;

  // Validation
  if (!$fname = trim($fname))
    $error = $STRING['givename'];
  elseif (!$fdescription = trim($fdescription))
    $error = $STRING['givedesc'];
  if ($error) { list_items($statusid, $error); return; }

  if (!$statusid) {
    $q->query("insert into status (status_id, status_name, status_desc, sort_order) values (".$q->nextid('status').", '$fname', '$fdescription', '$fsortorder')");
  } else {
    $q->query("update status set status_name = '$fname', status_desc = '$fdescription', sort_order = '$fsortorder' where status_id = '$statusid'");
  }
  header("Location: $me?");
}

function show_form($statusid = 0, $error = '') {
  global $q, $me, $t, $fname, $fdescription, $fsortorder, $STRING;

  if ($statusid && !$error) {
    $row = $q->grab("select * from status where status_id = '$statusid'");
    $t->set_var(array(
      'action' => $STRING['edit'],
      'fstatusid' => $row['status_id'],
      'fname' => $row['status_name'],
      'fdescription' => $row['status_desc'],
      'fsortorder' => $row['sort_order']));
  } else {
    $t->set_var(array(
      'action' => $statusid ? $STRING['edit'] : $STRING['addnew'],
      'error' => $error,
      'fstatusid' => $statusid,
      'fname' => $fname,
      'fdescription' => $fdescription,
      'fsortorder' => $fsortorder));
  }
}


function list_items($statusid = 0, $error = '') {
  global $q, $t, $selrange, $order, $sort, $STRING, $TITLE;

  $t->set_file('content','statuslist.html');
  $t->set_block('content','row','rows');

  if (!$order) { $order = 'sort_order'; $sort = 'asc'; }
  $nr = $q->query("select count(*) from status where status_id = '$statusid' order by $order $sort");

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr));

  $q->query("select * from status order by $order $sort limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['nostatuses']}</td></tr>");
    return;
  }

  $headers = array(
    'statusid' => 'status_id',
    'name' => 'status_name',
    'description' => 'status_desc',
    'sortorder' => 'sort_order');

  sorting_headers($me, $headers, $order, $sort);

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'statusid' => $row['status_id'],
      'name' => $row['status_name'],
      'description' => $row['status_desc'],
      'sortorder' => $row['sort_order']));
    $t->parse('rows','row',true);
  }

  show_form($statusid, $error);
  $t->set_var('TITLE',$TITLE['status']);
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
