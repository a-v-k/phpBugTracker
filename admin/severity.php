<?php

// severity.php - Interface to the severity table
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
// $Id: severity.php,v 1.3 2001/08/23 01:32:05 bcurtis Exp $

ini_set('include_path', '../'.ini_get('include_path'));
include 'include.php';

function do_form($severityid = 0) {
  global $q, $me, $fname, $fdescription, $fsortorder, $fcolor, $STRING;

  // Validation
  if (!$fname = trim($fname))
    $error = $STRING['givename'];
  elseif (!$fdescription = trim($fdescription))
    $error = $STRING['givedesc'];
  if ($error) { list_items($severityid, $error); return; }

  if (!$severityid) {
    $q->query("insert into severity (severity_id, severity_name, severity_desc, sort_order, severity_color) values (".$q->nextid('severity').", '$fname', '$fdescription', '$fsortorder', '$fcolor')");
  } else {
    $q->query("update severity set severity_name = '$fname', severity_desc = '$fdescription', sort_order = '$fsortorder', severity_color = '$fcolor' where severity_id = '$severityid'");
  }
  header("Location: $me?");
}

function show_form($severityid = 0, $error = '') {
  global $q, $me, $t, $fname, $fdescription, $fsortorder, $STRING;

  if ($severityid && !$error) {
    $row = $q->grab("select * from severity where severity_id = '$severityid'");
    $t->set_var(array(
      'action' => $STRING['edit'],
      'fseverityid' => $row['severity_id'],
      'fname' => $row['severity_name'],
      'fdescription' => $row['severity_desc'],
      'fsortorder' => $row['sort_order'],
      'fcolor' => $row['severity_color']));
  } else {
    $t->set_var(array(
      'action' => $severityid ? $STRING['edit'] : $STRING['addnew'],
      'error' => $error,
      'fseverityid' => $severityid,
      'fname' => $fname,
      'fdescription' => $fdescription,
      'fsortorder' => $fsortorder,
      'fcolor' => $fcolor));
  }
}


function list_items($severityid = 0, $error = '') {
  global $q, $t, $selrange, $order, $sort, $STRING, $TITLE;

  $t->set_file('content','severitylist.html');
  $t->set_block('content','row','rows');

  if (!$order) { $order = 'sort_order'; $sort = 'asc'; }
  $nr = $q->query("select count(*) from severity where severity_id = '$severityid' order by $order $sort");

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr));

  $q->query("select * from severity order by $order $sort limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['noseverities']}</td></tr>");
    return;
  }

  $headers = array(
    'severityid' => 'severity_id',
    'name' => 'severity_name',
    'description' => 'severity_desc',
    'sortorder' => 'sort_order',
    'color' => 'severity_color');

  sorting_headers($me, $headers, $order, $sort);

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => $row['severity_color'],
      'severityid' => $row['severity_id'],
      'name' => $row['severity_name'],
      'description' => $row['severity_desc'],
      'sortorder' => $row['sort_order']));
    $t->parse('rows','row',true);
  }

  show_form($severityid, $error);
  $t->set_var('TITLE',$TITLE['severity']);
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
