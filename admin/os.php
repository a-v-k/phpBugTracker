<?php

// os.php - Interface to the OS table
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
// $Id: os.php,v 1.11 2001/09/01 15:44:20 mohni Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function do_form($osid = 0) {
  global $q, $me, $fname, $fregex, $fsortorder, $STRING;

  // Validation
  if (!$fname = trim($fname))
    $error = $STRING['givename'];
  if ($error) { list_items($osid, $error); return; }

  if (!$osid) {
    $q->query("insert into ".TBL_OS." (os_id, os_name, regex, sort_order) values (".$q->nextid('os').", '$fname', '$fregex', '$fsortorder')");
  } else {
    $q->query("update ".TBL_OS." set os_name = '$fname', regex = '$fregex', sort_order = '$fsortorder' where os_id = '$osid'");
  }
  header("Location: $me?");
}

function show_form($osid = 0, $error = '') {
  global $q, $me, $t, $fname, $fregex, $fsortorder, $STRING;

  #$t->set_file('content','osform.html');
  if ($osid && !$error) {
    $row = $q->grab("select * from ".TBL_OS." where os_id = '$osid'");
    $t->set_var(array(
      'action' => $STRING['edit'],
      'fosid' => $row['os_id'],
      'fname' => $row['os_name'],
      'fregex' => $row['regex'],
      'fsortorder' => $row['sort_order']));
  } else {
    $t->set_var(array(
      'action' => $osid ? $STRING['edit'] : $STRING['addnew'],
      'error' => $error,
      'fosid' => $osid,
      'fname' => $fname,
      'fregex' => $fregex,
      'fsortorder' => $fsortorder));
  }
}


function list_items($osid = 0, $error = '') {
  global $q, $t, $selrange, $order, $sort, $STRING, $TITLE;

  $t->set_file('content','oslist.html');
  $t->set_block('content','row','rows');

  if (!$order) { $order = 'sort_order'; $sort = 'asc'; }
  $nr = $q->query("select count(*) from ".TBL_OS." where os_id = '$osid' order by $order $sort");

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr));

  $q->query("select * from ".TBL_OS." order by $order $sort limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['nooses']}</td></tr>");
    return;
  }

  $headers = array(
    'osid' => 'os_id',
    'name' => 'os_name',
    'regex' => 'regex',
    'sortorder' => 'sort_order');

  sorting_headers($me, $headers, $order, $sort);

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'osid' => $row['os_id'],
      'name' => $row['os_name'],
      'regex' => $row['regex'],
      'sortorder' => $row['sort_order']));
    $t->parse('rows','row',true);
  }

  show_form($osid, $error);
  $t->set_var('TITLE',$TITLE['os']);
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
