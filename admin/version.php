<?php

// version.php - Admin versions of a project
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
// $Id: version.php,v 1.11 2001/08/23 01:39:03 bcurtis Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function do_form($versionid = 0) {
  global $q, $me, $projectid, $version, $active, $STRING, $now, $u;

  // Validation
  if (!$version = trim($version))
    $error = $STRING['giveversion'];
  if ($error) { show_form($versionid, $error); return; }

  if (!$active) $active = 0;
  if (!$versionid) {
    $q->query("insert into version (version_id, project_id, version_name, active, created_by, created_date) values (".$q->nextid('version').", $projectid, '$version', '$active', $u, $now)");
  } else {
    $q->query("update version set project_id = $projectid, version_name = '$version', active = '$active' where version_id = '$versionid'");
  }
  header("Location: project.php?op=edit&id=$projectid");
}

function show_form($versionid = 0, $error = '') {
  global $q, $me, $t, $projectid, $version, $active, $TITLE;

  $t->set_file('content','versionform.html');
  if ($versionid && !$error) {
    $row = $q->grab("select v.*, p.project_name as project_name from version v left join project p using(project_id) where version_id = '$versionid'");
    $t->set_var(array(
      'versionid' => $row['version_id'],
      'projectid' => $row['project_id'],
      'project' => $row['project_name'],
      'version' => $row['version_name'],
      'active' => $row['active'] ? 'checked' : '',
      'TITLE' => $TITLE['editversion']));
  } else {
    $t->set_var(array(
      'id' => $id,
      'me' => $me,
      'error' => $error,
      'versionid' => $versionid,
      'projectid' => $projectid,
      'project' => $q->grab_field("select project_name from project where project_id = $projectid"),
      'version' => $version,
      'active' => $active ? ' checked' : '',
      'TITLE' => $id ? $TITLE['editversion'] : $TITLE['addversion']));
  }
}

$t->set_file('wrap','wrap.html');

if ($op) switch($op) {
  case 'add' : show_form(); break;
  case 'edit' : show_form($id); break;
} elseif($submit) {
  do_form($id);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
