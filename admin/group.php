<?php

// group.php - Administer the user groups
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
// $Id: group.php,v 1.2 2001/12/04 14:32:46 bcurtis Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function purge_group($groupid = 0) {
	global $q;
	
	$q->query("delete from ".TBL_USER_GROUP." where group_id = $groupid");
}

function del_group($groupid = 0) {
	global $q;
	
	purge_group($groupid);
	$q->query("delete from ".TBL_AUTH_GROUP." where group_id = $groupid");
}

function do_form($groupid = 0) {
  global $q, $me, $_pv, $STRING, $u, $now;

	extract($_pv);
	$error = '';
  // Validation
  if (!$fname = trim($fname))
    $error = $STRING['givename'];
  if ($error) { list_items($groupid, $error); return; }

  if (!$groupid) {
    $q->query("insert into ".TBL_AUTH_GROUP.
			" (group_id, group_name, created_by, created_date, last_modified_by, last_modified_date)"
			." values (".$q->nextid(TBL_AUTH_GROUP).", '$fname', $u, $now, $u, $now)");
  } else {
    $q->query("update ".TBL_AUTH_GROUP.
			" set group_name = '$fname', last_modified_by = $u, 
			last_modified_date = $now where group_id = '$groupid'");
  }
  header("Location: $me?");
}

function show_form($groupid = 0, $error = '') {
  global $q, $me, $t, $_pv, $STRING;

  if ($groupid && !$error) {
    $row = $q->grab("select * from ".TBL_AUTH_GROUP.
			" where group_id = '$groupid'");
    $t->set_var(array(
      'action' => $STRING['edit'],
      'fgroupid' => $row['group_id'],
      'fname' => $row['group_name']));
  } else {
    $t->set_var(array(
      'action' => $groupid ? $STRING['edit'] : $STRING['addnew'],
      'error' => $error,
      'fgroupid' => $groupid,
      'fname' => isset($fname) ? $fname : ''));
  }
}


function list_items($groupid = 0, $error = '') {
  global $me, $q, $t, $_gv, $STRING, $TITLE;

  $t->set_file('content','grouplist.html');
  $t->set_block('content','row','rows');
  $t->set_block('row','lockedblock','lockedb');
  $t->set_block('row','unlockedblock','unlockedb');

  if (empty($_gv['order'])) { 
		$order = 'group_name'; 
		$sort = 'asc'; 
	} else {
		$order = $_gv['order']; 
		$sort = $_gv['sort']; 
	}
	
	$page = isset($_gv['page']) ? $_gv['page'] : 0;
	
  $nr = $q->grab_field("select count(*) from ".TBL_AUTH_GROUP);

  list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
    "order=$order&sort=$sort");

  $t->set_var(array(
    'pages' => '[ '.$pages.' ]',
    'first' => $llimit+1,
    'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
    'records' => $nr));

  $q->limit_query("select ag.group_id, group_name, locked, count(ug.group_id) as count from ".
		TBL_AUTH_GROUP." ag left join ".TBL_USER_GROUP." ug using (group_id) left join ".
		TBL_AUTH_USER." using (user_id) group by ag.group_id, group_name, locked order by $order $sort", 
		$selrange, $llimit);

  if (!$q->num_rows()) {
		// This should never happen, as admin, user, and developer groups must exist
    $t->set_var('rows',"");
    return;
  }

  $headers = array(
    'groupid' => 'group_id',
    'name' => 'group_name',
		'count' => '4');

  sorting_headers($me, $headers, $order, $sort);

	$i = 0;
  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'trclass' => $i % 2 ? '' : 'alt',
      'groupid' => $row['group_id'],
      'name' => $row['group_name'],
			'count' => $row['count']
			));
			
		// Some groups (e.g. admin, developer, user) cannot be edited or deleted
		if ($row['locked']) {
			$t->parse('lockedb', 'lockedblock', false);
			$t->set_var('unlockedb', '');
		} else {
			$t->parse('unlockedb', 'unlockedblock', false);
			$t->set_var('lockedb', '');
		}
    $t->parse('rows','row',true);
  }

  show_form($groupid, $error);
  $t->set_var('TITLE',$TITLE['group']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Admin');

if (isset($_gv['op'])) switch($_gv['op']) {
  case 'add' : list_items(); break;
  case 'edit' : list_items($_gv['id']); break;
	case 'del' : del_group($_gv['id']); list_items($_gv['id']); break;
	case 'purge' : purge_group($_gv['id']); list_items($_gv['id']); break;
} elseif(isset($_pv['submit'])) {
  do_form($_pv['id']);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

?>
