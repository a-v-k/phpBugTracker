<?php

// query.php - Query the bug database
// ------------------------------------------------------------------------
// Copyright (c) 2001, 2002 The phpBugTracker Group
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
// $Id: query.php,v 1.80 2002/06/13 15:07:10 firma Exp $

include 'include.php';

function delete_saved_query($queryid) {
	global $db, $u, $me, $_gv;
	
	$db->query("delete from ".TBL_SAVED_QUERY." where user_id = $u 
		and saved_query_id = $queryid");
	if (!empty($_gv['form']) and $_gv['form'] == 'advanced') {
		header("Location: $me?op=query&form=advanced");
	} else {
		header("Location: $me?op=query");
	}
}

function show_query() {
	global $db, $t, $TITLE, $u, $_gv;
	
	if ($u != 'nobody') {
		// Grab the saved queries if there are any
		$t->assign('queries', 
			$db->getAll("select * from ".TBL_SAVED_QUERY." where user_id = '$u'"));
	}

	// Show the advanced query form
	if (!empty($_gv['form']) and $_gv['form'] == 'advanced') {
		$t->wrap('queryform.html', 'bugquery');
	} else { // or show the simple one
		$t->wrap('queryform-simple.html', 'bugquery');
	}

}

function build_query($assignedto, $reportedby, $open) {
	global $db, $_sv, $_gv, $perm, $restricted_projects;

	foreach ($_gv as $k => $v) { $$k = $v; }
	
	// Open bugs assigned to the user -- a hit list
	if ($assignedto || $reportedby) {
		$status = $db->getCol("select status_id from ".TBL_STATUS.
			" where status_name ".($open ? '' : 'not ').
			"in ('Unconfirmed', 'New', 'Assigned', 'Reopened')");
		$query[] = 'b.status_id in ('.delimit_list(',',$status).')';
		if ($assignedto) {
			$query[] = "assigned_to = {$_sv['uid']}";
		} else {
			$query[] = "b.created_by = {$_sv['uid']}";
		}
	} else {
		// Select boxes
		$flags = array();
		// Need to check $array[0] for Opera -- 
		// it passes non-empty arrays for every multi-choice select box
		if (!empty($status) and $status[0]) {
			$flags[] = 'b.status_id in ('.delimit_list(',',$status).')';
		}
		if (!empty($resolution) and $resolution[0]) {
			$flags[] = 'b.resolution_id in ('.delimit_list(',',$resolution).')';
		}
		if (!empty($os) and $os[0]) {
			$flags[] = 'b.os_id in ('.delimit_list(',',$os).')';
		}
		if (!empty($priority) and $priority[0]) {
			$flags[] = 'b.priority in ('.delimit_list(',',$priority).')';
		}
		if (!empty($severity) and $severity[0]) {
			$flags[] = 'b.severity_id in ('.delimit_list(',',$severity).')';
		}
		if (!empty($database) and $database[0]) {
			$flags[] = 'b.database_id in ('.delimit_list(',',$database).')';
		}
		if (!empty($site) and $site[0]) {
			$flags[] = 'b.site_id in ('.delimit_list(',',$site).')';
		}
		if (!empty($flags)) {
			$query[] = '('.delimit_list(' or ',$flags).')';
		}

		// Email field(s)
		if (!empty($email1)) {
			switch($emailtype1) {
				case 'like' : $econd = "like '%$email1%'"; break;
				case 'rlike' : 
				case 'not rlike' : 
				case '=' : $econd = "$emailtype1 '$email1'"; break;
			}
			foreach($emailfield1 as $field) $equery[] = "$field.$emailsearch1 $econd";
			$query[] = '('.delimit_list(' or ',$equery).')';
		}

		// Text search field(s)
		foreach(array('title','description','url') as $searchfield) {
			if (!empty($$searchfield)) {
				switch (${$searchfield."_type"}) {
					case 'like' : $cond = "like '%".$$searchfield."%'"; break;
					case 'rlike' : $cond = "rlike '".$$searchfield."'"; break;
					case 'not rlike' :$cond = "not rlike '".$$searchfield."'"; break;
				}
				$fields[] = "$searchfield $cond";
			}
		}
		if (!empty($fields)) $query[] = '('.delimit_list(' or ',$fields).')';

		// Project/Version/Component
		if (!empty($projects)) {
			$proj[] = "b.project_id = $projects";
			if (!empty($versions)) $proj[] = "b.version_id = $versions";
			if (!empty($components)) $proj[] = "b.component_id = $components";
			$query[] = '('.delimit_list(' and ',$proj).')';
		} elseif (!$perm->have_perm('Admin')) { // Filter results from hidden projects
			$query[] = "b.project_id not in ($restricted_projects)";
		}
	}
	
	if (!empty($query)) {
		return delimit_list(' and ',$query);
	} else {
		return '';
	}
}

// Handle the formatting for various types of bug info in the bug list
function format_bug_col($colvalue, $coltype, $bugid, $pos) {
	global $select;
	
	$pos--;
	
	switch ($coltype) {
	 case 'url' : 
  	 echo "<a href=\"$colvalue\" target=\"_new\">$colvalue</a>"; 
  	 break;
	 case 'created_date' :
	 case 'last_modified_date' :
	 case 'close_date' : 
  	 echo '<div align="center">'.
		 	$colvalue ? date(DATE_FORMAT, $colvalue) : '&nbsp;'.
			'</div>';
  	 break;
	 case 'bug_id' :
	 case 'title' :
  	 echo "<a href=\"bug.php?op=show&bugid=$bugid&pos=$pos\">$colvalue</a>"; 
  	 break;
	 case 'reporter' :
	 case 'owner' : 
	 case 'lastmodifier' : 
  	 echo '<div align="center">'.
		 	(!empty($colvalue) ? maskemail($colvalue) : '').'</div>';
  	 break;
	 case 'priority' :
  	 echo '<div align="center">'.$select['priority'][$colvalue].'</div>';
  	 break;
	 default :
  	 echo '<div align="center">'.
		 	(!empty($colvalue) ? $colvalue : '').'</div>';
  	 break;
	}
}

$t->register_modifier('modify_bug_col', 'format_bug_col');

function list_items($assignedto = 0, $reportedby = 0, $open = 0) {
	global $me, $db, $t, $select, $TITLE, $STRING, $_gv, $u, 
		$default_db_fields, $all_db_fields, $HTTP_SESSION_VARS, $HTTP_SERVER_VARS,
		$QUERY;

	$query_db_fields = array(
	    'bug_id' => 'bug_id',
  	    'title' => 'title',
	    'description' => 'description',
	    'url' => 'url',
	    'severity_name' => 'severity.severity_name',
	    'priority' => 'priority',
	    'status_name' => 'status.status_name',
	    'resolution_name' => 'resolution_name',
	    'reporter' => 'reporter.login as reporter',
	    'owner' => 'owner.login as owner',
	    'created_date' => 'b.created_date',
	    'lastmodifier' => 'lastmodifier.login as lastmodifier',
	    'last_modified_date' => 'b.last_modified_date',
	    'project_name' => 'project.project_name',
	    'version_name' => 'version.version_name',
	    'to_be_closed_in_version_name' => 'version2.version_name as v2',
	    'closed_in_version_name' => 'version3.version_name as v3',
	    'database_provider' => TBL_DATABASE.'.database_name',
	    'database_version' => TBL_DATABASE.'.database_version',
	    'site_name' => 'site.site_name',
	    'component_name' => 'component.component_name',
	    'os_name' => 'os.os_name',
	    'browser_string' => 'browser_string',
	    'close_date' => 'close_date'
  	);

	$db_headers = array(
	    'bug_id' => 'bug_id',
	    'title' => 'title',
	    'description' => 'description',
	    'url' => 'url',
	    'severity_name' => 'severity.sort_order',
	    'priority' => 'b.priority',
	    'status_name' => 'status.sort_order',
	    'owner' => 'owner',
	    'reporter' => 'reporter.login',
	    'lastmodifier' => 'last_modifier',
	    'created_date' => 'b.created_date',
	    'last_modified_date' => 'b.last_modified_date',
	    'project_name' => 'project_name',
	    'component_name' => 'component_name',
	    'version_name' => 'version_name',
	    'os_name' => 'os_name',
	    'to_be_closed_in_version_name' => 'v2',
	    'closed_in_version_name' => 'v3',
	    'database_provider' => TBL_DATABASE.'.database_name',
	    'database_version' => TBL_DATABASE.'.database_version',
	    'site_name' => 'site.sort_order',
	    'browser_string' => 'browser_string',
	    'resolution_name' => 'resolution.sort_order',
	    'close_date' => 'close_date'
	);

	extract($_gv);
	if (!isset($page)) {
		$page = 1;
	}
	// Save the query if requested
	if (!empty($savedqueryname)) {
		$savedquerystring = ereg_replace('&savedqueryname=.*(&?)', '\1', $HTTP_SERVER_VARS['QUERY_STRING']);
		$nextid = $db->getOne("select max(saved_query_id)+1 from ".TBL_SAVED_QUERY." where user_id = $u");
		$nextid = $nextid ? $nextid : 1;
		$db->query("insert into ".TBL_SAVED_QUERY.
			" (saved_query_id, user_id, saved_query_name, saved_query_string) 
			values (".join(', ', array($nextid, $u, 
				$db->quote(stripslashes($savedqueryname)), 
				$db->quote(stripslashes($savedquerystring)))).")");
	}
	if (!isset($order)) { 
		if (isset($HTTP_SESSION_VARS['queryinfo']['order'])) {
			$order = $HTTP_SESSION_VARS['queryinfo']['order'];
			$sort = $HTTP_SESSION_VARS['queryinfo']['sort'];
		} else {
			$order = 'bug_id'; 
			$sort = 'asc'; 
		}
	}
	if (!session_is_registered('queryinfo')) {
		session_register('queryinfo');
		$HTTP_SESSION_VARS['queryinfo'] = array();
	}
	
	$HTTP_SESSION_VARS['queryinfo']['order'] = $order;
	$HTTP_SESSION_VARS['queryinfo']['sort'] = $sort;
	
	if (empty($HTTP_SESSION_VARS['queryinfo']['query']) or isset($op)) {
		$HTTP_SESSION_VARS['queryinfo']['query'] = build_query($assignedto, $reportedby, $open);
	}
	
	$nr = $db->getOne($QUERY['query-list-bugs-count'].
		(!empty($HTTP_SESSION_VARS['queryinfo']['query']) 
			? "where {$HTTP_SESSION_VARS['queryinfo']['query']}": ''));

	$HTTP_SESSION_VARS['queryinfo']['numrows'] = $nr;
	list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");
	
	$desired_fields = !empty($HTTP_SESSION_VARS['db_fields']) ? 
		$HTTP_SESSION_VARS['db_fields'] :	$default_db_fields;

	$query_fields = array('bug_id as bug_link_id', 'severity.severity_color');
	foreach ($desired_fields as $field) {
		$query_fields[] = $query_db_fields[$field];
		$field_titles[] = $all_db_fields[$field];
		$headers[] = $db_headers[$field];
	}

	$t->assign(array(
		'db_fields' => $desired_fields,
		'field_titles' => $field_titles
		));
		
	$t->assign('bugs', $db->getAll($db->modifyLimitQuery(
		sprintf($QUERY['query-list-bugs'], join(', ', $query_fields), 
			(!empty($HTTP_SESSION_VARS['queryinfo']['query']) 
			? "and {$HTTP_SESSION_VARS['queryinfo']['query']} " : ''),
		$order, $sort), $llimit, $selrange)));
				
	sorting_headers($me, $headers, $order, $sort, "page=$page");
	$t->wrap('buglist.html', 'buglist');
}

$reportedby = !empty($_gv['reportedby']) ? $_gv['reportedby'] : 0;
$assignedto = !empty($_gv['assignedto']) ? $_gv['assignedto'] : 0;
$open = !empty($_gv['open']) ? $_gv['open'] : 0;

if (isset($_gv['op'])) switch($_gv['op']) {
	case 'query' : show_query(); break;
	case 'doquery' : $_sv['queryinfo'] = array(); list_items(); break;
	case 'delquery' : delete_saved_query($_gv['queryid']); break;
	case 'mybugs' : list_items($assignedto, $reportedby, $open); break;
	default : show_query(); break;
}
else list_items($assignedto, $reportedby, $open);

?>
