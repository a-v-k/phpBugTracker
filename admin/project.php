<?php

// project.php - Create and update projects
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
// $Id: project.php,v 1.14 2001/09/01 15:44:20 mohni Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function do_form($projectid = 0) {
  global $q, $me, $name, $description, $active, $version, $u, $STRING, $now;

  // Validation
  if (!$name = htmlspecialchars(trim($name)))
    $error = $STRING['givename'];
  elseif (!$description = htmlspecialchars(trim($description)))
    $error = $STRING['givedesc'];
  elseif (!projectid and !$version = htmlspecialchars(trim($version)))
    $error = $STRING['giveversion'];
  if ($error) { show_form($projectid, $error); return; }

  if (!$active) $active = 0;
  if (!$projectid) {
    $projectid = $q->nextid('project');
    $q->query("insert into ".TBL_PROJECT." (project_id, project_name, project_desc, active, created_by, created_date)"
             ." values ($projectid , '$name', '$description', $active, $u, $now)");
    $q->query("insert into ".TBL_VERSION." (version_id, project_id, version_name, active, created_by, created_date)"
             ." values (".$q->nextid('version').", $projectid, '$version', $active, $u, $now)");
    $location = "component.php?op=add&projectid=$projectid";
  } else {
    $q->query("update ".TBL_PROJECT." set project_name = '$name', project_desc = '$description', active = $active where project_id = $projectid");
    $location = "$me?";
  }
  header("Location: $location");
}

function show_form($projectid = 0, $error = '') {
  global $q, $me, $t, $name, $description, $active, $version, $TITLE;

  $t->set_file('content','projectform.html');
  $t->set_block('content','box','details');
  $t->set_block('content','vfield','verfield');
  if ($projectid && !$error) {
    $row = $q->grab("select * from ".TBL_PROJECT." where project_id = $projectid");
    $t->set_var(array(
      'projectid' => $row['project_id'],
      'name' => $row['project_name'],
      'description' => $row['project_desc'],
      'active' => $row['active'] ? 'checked' : '',
      'createdby' => $row['created_by'],
      'createddate' => $row['created_date'],
      'TITLE' => $TITLE['editproject']
      ));
  } else {
    $t->set_var(array(
      'error' => $error,
      'projectid' => $projectid,
      'name' => $name,
      'description' => $description,
      'active' => (isset($active) and !$active) ? '' : 'checked',
      'createdby' => $createdby,
      'createddate' => $createddate,
      'TITLE' => $projectid ? $TITLE['editproject'] : $TITLE['addproject']
      ));
  }
  if ($projectid) {
    $t->set_var('verfield','');
    list_components($projectid);
    list_versions($projectid);
    $t->parse('details','box',true);
  } else {
    $t->set_var(array(
      'details' => '',
      'version' => $version
      ));
    $t->parse('verfield','vfield',true);
  }
}

function list_versions($projectid) {
  global $q, $t, $STRING;

  $t->set_block('box','verrow','verrows');
  $q->query("select * from ".TBL_VERSION." where project_id = $projectid");
  if (!$q->num_rows()) {
    $t->set_var('verrows',"<tr><td colspan='2' align='center'>{$STRING['noversions']}</td></tr>");
    return;
  }

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'verid' => $row['version_id'],
      'vername' => $row['version_name'],
      'verdate' => date(DATEFORMAT,$row['created_date']),
      'veractive' => $row['active'] ? 'Y' : 'N'
      ));
    $t->parse('verrows','verrow',true);
  }
}


function list_components($projectid) {
  global $q, $t, $STRING;

  $t->set_block('box','row','rows');
  $q->query("select * from ".TBL_COMPONENT." where project_id = $projectid");
  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td colspan='2' align='center'>{$STRING['nocomponents']}</td></tr>");
    return;
  }

  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'compid' => $row['component_id'],
      'compname' => $row['component_name'],
      'compdesc' => stripslashes($row['project_desc']),
      'owner' => $row['Owner'],
      'compactive' => $row['active'] ? 'Y' : 'N',
      'createdby' => $row['created_by'],
      'compdate' => date(DATEFORMAT,$row['created_date']),
      'lastmodifiedby' => $row['last_modified_by'],
      'lastmodifieddate' => date(DATEFORMAT,$row['last_modified_date'])
      ));
    $t->parse('rows','row',true);
  }
}

function list_items() {
  global $me, $q, $t, $selrange, $order, $sort, $STRING, $TITLE, $page;

  $t->set_file('content','projectlist.html');
  $t->set_block('content','row','rows');

  if (!$order) { $order = '1'; $sort = 'asc'; }
  $nr = $q->query("select count(*) from ".TBL_PROJECT);

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr,
    'TITLE' => $TITLE['project']
    ));

  $q->query("select * from ".TBL_PROJECT." order by $order $sort limit $llimit, $selrange");

  if (!$q->num_rows()) {
    $t->set_var('rows',"<tr><td>{$STRING['noprojects']}</td></tr>");
    return;
  }

  $headers = array(
    'projectid' => 'project_id',
    'name' => 'project_name',
    'description' => 'project_desc',
    'active' => 'active',
    'createdby' => 'created_by',
    'createddate' => 'created_date'
    );

  sorting_headers($me, $headers, $order, $sort);
  $i = 0;
  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'projectid' => $row['project_id'],
      'name' => $row['project_name'],
      'description' => stripslashes($row['project_desc']),
      'active' => $row['active'] ? 'Y' : 'N',
      'createdby' => $row['created_by'],
      'createddate' => date(DATEFORMAT,$row['created_date'])
      ));
    $t->parse('rows','row',true);
  }
}

$t->set_file('wrap','wrap.html');

$perm->check('Administrator');

if (isset($_gv['op'])) switch($_gv['op']) {
  case 'add' : show_form(); break;
  case 'edit' : show_form($_gv['id']); break;
} elseif(isset($_pv['submit'])) {
  do_form($_pv['id']);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
