<?php

// resolution.php - Interface to the Resolution table
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
// $Id: resolution.php,v 1.10 2001/08/23 01:32:05 bcurtis Exp $

ini_set('include_path', '../'.ini_get('include_path'));
include 'include.php';

function do_form($resolutionid = 0) {
  global $q, $me, $fname, $fdescription, $fsortorder, $STRING;

  // Validation
  if (!$fname = trim($fname))
    $error = $STRING['givename'];
  elseif (!$fdescription = trim($fdescription))
    $error = $STRING['givedesc'];
  if ($error) { list_items($resolutionid, $error); return; }

  if (!$resolutionid) {
    $q->query("insert into resolution (resolution_id, resolution_name, resolution_desc, sort_order) values (".$q->nextid('resolution').", '$fname', '$fdescription', '$fsortorder')");
  } else {
    $q->query("update resolution set resolution_name = '$fname', resolution_desc = '$fdescription', sort_order = '$fsortorder' where resolution_id = '$resolutionid'");
  }
  header("Location: $me?");
}

function show_form($resolutionid = 0, $error = '') {
  global $q, $me, $t, $fname, $fdescription, $fsortorder, $STRING;

  if ($resolutionid && !$error) {
    $row = $q->grab("select * from resolution where resolution_id = '$resolutionid'");
    $t->set_var(array(
      'action' => $STRING['edit'],
      'fresolutionid' => $row['resolution_id'],
      'fname' => $row['resolution_name'],
      'fdescription' => $row['resolution_desc'],
      'fsortorder' => $row['sort_order']));
  } else {
    $t->set_var(array(
      'action' => $resolutionid ? $STRING['edit'] : $STRING['addnew'],
      'error' => $error,
      'fresolutionid' => $resolutionid,
      'fname' => $fname,
      'fdescription' => $fdescription,
      'fsortorder' => $fsortorder));
  }
}


function list_items($resolutionid = 0, $error = '') {
  global $q, $t, $selrange, $order, $sort, $STRING, $TITLE;

  $t->set_file('content','resolutionlist.html');
  $t->set_block('content','row','rows');

  if (!$order) { $order = 'sort_order'; $sort = 'asc'; }
  $nr = $q->query("select count(*) from resolution where resolution_id = '$resolutionid' order by $order $sort");

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr));

  $q->query("select * from resolution order by $order $sort limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['noresolutions']}</td></tr>");
    return;
  }

  $headers = array(
    'resolutionid' => 'resolution_id',
    'name' => 'resolution_name',
    'description' => 'resolution_desc',
    'sortorder' => 'sort_order');

  sorting_headers($me, $headers, $order, $sort);

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'resolutionid' => $row['resolution_id'],
      'name' => $row['resolution_name'],
      'description' => $row['resolution_desc'],
      'sortorder' => $row['sort_order']));
    $t->parse('rows','row',true);
  }

  show_form($resolutionid, $error);
  $t->set_var('TITLE',$TITLE['resolution']);
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
