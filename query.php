<?php

// query.php - Query the bug database
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
// $Id: query.php,v 1.61 2002/03/20 20:02:51 bcurtis Exp $

include 'include.php';

function delete_saved_query($queryid) {
	global $db, $u, $me;
	
	$db->query("delete from ".TBL_SAVED_QUERY." where user_id = $u 
		and saved_query_id = $queryid");
	header("Location: $me?op=query");
}

function show_query() {
	global $db, $t, $TITLE, $u;
	
	$t->set_file('content','queryform.html');
	$t->set_block('content', 'savequeryblock', 'sqblock');
	$t->set_block('savequeryblock','row','rows');
	 
	if ($u != 'nobody') {
		// Grab the saved queries if there are any
		$rs = $db->query("select * from ".TBL_SAVED_QUERY." where user_id = '$u'");
		if (!$rs->numRows()) {
			$t->set_var('rows','');
		} else {
			while ($rs->fetchInto($row)) {
				$t->set_var(array(
					'savedquerystring' => $row['saved_query_string'],
					'savedqueryname' => stripslashes($row['saved_query_name']),
					'savedqueryid' => $row['saved_query_id']
					));
				$t->parse('rows', 'row', true);
			}
		}
		$t->parse('sqblock', 'savequeryblock', true);
	} else {
		$t->set_var('sqblock', '');
	}
	
	$t->set_var(array(
		'js' => build_project_js(),
		'status' => build_select('status'),
		'resolution' => build_select('resolution'),
		'os' => build_select('os',-1), // Prevent the OS regex selection
		'priority' => build_select('priority'),
		'severity' => build_select('severity'),
		'projects' => build_select('project'),
		'TITLE' => $TITLE['bugquery']
		));
			
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
		if (!empty($status) and $status[0])
			$flags[] = 'b.status_id in ('.delimit_list(',',$status).')';
		if (!empty($resolution) and $resolution[0]) 
			$flags[] = 'b.resolution_id in ('.delimit_list(',',$resolution).')';
		if (!empty($os) and $os[0]) 
			$flags[] = 'b.os_id in ('.delimit_list(',',$os).')';
		if (!empty($priority) and $priority[0]) 
			$flags[] = 'b.priority in ('.delimit_list(',',$priority).')';
		if (!empty($severity) and $severity[0]) 
			$flags[] = 'b.severity_id in ('.delimit_list(',',$severity).')';
		if (!empty($flags)) 
			$query[] = '('.delimit_list(' or ',$flags).')';

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
			if ($versions) $proj[] = "b.version_id = $versions";
			if ($components) $proj[] = "b.component_id = $components";
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

function list_items($assignedto = 0, $reportedby = 0, $open = 0) {
	global $me, $db, $t, $select, $TITLE, $STRING, $_gv, $u, 
		$default_db_fields, $all_db_fields, $_sv, $HTTP_SERVER_VARS;

	$t->set_file('content','buglist.html');
	$t->set_block('content','row','rows');
	$t->set_block('row','col','cols');
	
	extract($_gv);
	if (!isset($page)) {
		$page = 1;
	}
	// Save the query if requested
	if (!empty($savedqueryname)) {
		$savedquerystring = ereg_replace('&savedqueryname=.*(&?)', '\\1', $HTTP_SERVER_VARS['QUERY_STRING']);
		$nextid = $db->getOne("select max(saved_query_id)+1 from ".TBL_SAVED_QUERY." where user_id = $u");
		$nextid = $nextid ? $nextid : 1;
		$db->query("insert into ".TBL_SAVED_QUERY.
			" (saved_query_id, user_id, saved_query_name, saved_query_string) 
			values ($nextid, $u, '$savedqueryname', '$savedquerystring')");
	}
	if (!isset($order)) { 
		if (isset($_sv['queryinfo']['order'])) {
			$order = $_sv['queryinfo']['order'];
			$sort = $_sv['queryinfo']['sort'];
		} else {
			$order = 'bug_id'; 
			$sort = 'asc'; 
		}
	}
	$_sv['queryinfo']['order'] = $order;
	$_sv['queryinfo']['sort'] = $sort;
	
	if (empty($_sv['queryinfo']['query']) or isset($op)) {
		$_sv['queryinfo']['query'] = build_query($assignedto, $reportedby, $open);
	}
	
	if (!session_is_registered('queryinfo')) {
		session_register('queryinfo');
		$_sv['queryinfo'] = array();
	}
	
	$nr = $db->getOne('select count(*) from '.TBL_BUG.' b 
		left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id
		left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id '.
		(!empty($_sv['queryinfo']['query']) ? "where {$_sv['queryinfo']['query']}": ''));

	$_sv['queryinfo']['numrows'] = $nr;
	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");
								
	$t->set_var(array(
		'pages' => $pages,
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'total' => $nr,
		'project' => build_select('project'),
		'TITLE' => $TITLE['buglist']));
	
	$rs = $db->limitQuery('select b.*, reporter.login as reporter, owner.login as owner, 
		lastmodifier.login as lastmodifier, project_name, severity_name, severity_color, status_name, 
		os_name, version_name, component_name, resolution_name from '.TBL_BUG.' b 
		left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id 
		left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id 
		left join '.TBL_AUTH_USER.' lastmodifier on b.last_modified_by = lastmodifier.user_id 
		left join '.TBL_RESOLUTION.' resolution on b.resolution_id = resolution.resolution_id, '.
		TBL_SEVERITY.' severity, '.TBL_STATUS.' status, '.TBL_OS.' os, '.
		TBL_VERSION.' version, '.TBL_COMPONENT.' component, '.TBL_PROJECT.' project 
		where b.severity_id = severity.severity_id and b.status_id = status.status_id 
		and b.os_id = os.os_id and b.version_id = version.version_id 
		and b.component_id = component.component_id and b.project_id = project.project_id '.
		(!empty($_sv['queryinfo']['query']) ? "and {$_sv['queryinfo']['query']} " : '').
		"order by $order $sort, bug_id asc", $llimit, $selrange);
				
	$headers = array(
		'bug_id' => 'bug_id',
		'title' => 'title',
		'description' => 'description',
		'url' => 'url',
		'severity_name' => 'severity.sort_order',
		'priority' => 'b.priority',
		'status_name' => 'status.sort_order',
		'owner' => 'owner',
		'reporter' => 'reporter',
		'lastmodifier' => 'last_modifier',
		'created_date' => 'created_date',
		'last_modified_date' => 'last_modified_date',
		'project_name' => 'project_name',
		'component_name' => 'component_name',
		'version_name' => 'version_name',
		'os_name' => 'os_name',
		'browser_string' => 'browser_string',
		'resolution_name' => 'resolution.sort_order',
		'close_date' => 'close_date');

	sorting_headers($me, $headers, $order, $sort, "page=$page");
				
	if (!$rs->numRows()) {
		$t->set_var(array(
			'rows' => "<tr><td>{$STRING['nobugs']}</td></tr>",
			'numcols' => "1"));
		return;
	}
	
	// Header row 
	$db_fields = !empty($_sv['db_fields']) ? $_sv['db_fields'] :
		$default_db_fields;
	foreach ($db_fields as $field) {
		$t->set_var(array(
			'coldata' => "<a href='{{$field}url}'>{$all_db_fields[$field]}</a>",
			'td-extra' => "class=\"{{$field}class}\""
			));
		$t->parse('cols', 'col', true);
	}
	$t->set_var('tr-extra', '');
	$t->parse('rows', 'row', true);
	$t->set_var('cols', '');
	
	$pos = 0;
	// Data rows 
	while ($rs->fetchInto($row)) {
		$bgcolor = USE_SEVERITY_COLOR ? $row['severity_color'] : 
			((++$i % 2 == 0) ? '#dddddd' : '#ffffff');
		$trclass = USE_SEVERITY_COLOR ? '' : ($i % 2 ? '' : 'alt');
		foreach ($db_fields as $field) {
			switch ($field) {
				case 'url' : 
					$coldata = "<a href='{$row[$field]}'>{$row[$field]}</a>"; 
					$td_extra = '';
					break;
				case 'created_date' :
				case 'last_modified_date' :
				case 'close_date' : 
					$coldata = $row[$field] ? date(DATE_FORMAT, $row[$field]) : '&nbsp;';
					$td_extra = 'class="center"';
					break;
				case 'bug_id' :
				case 'title' :
					$coldata = "<a href='bug.php?op=show&bugid={$row['bug_id']}&pos=$pos'>{$row[$field]}</a>"; 
					$td_extra = '';
					break;
				case 'reporter' :
				case 'owner' : 
				case 'lastmodifier' : 
					$coldata = !empty($row[$field]) ? maskemail($row[$field]) : '';
					$td_extra = 'class="center"';
					break;
				case 'priority' :
					$coldata = $select['priority'][$row[$field]];
					$td_extra = 'class="center"';
					break;
				default :
					$coldata = !empty($row[$field]) ? $row[$field] : '';
					$td_extra = 'class="center"';
					break;
			}
			$t->set_var(array(
				'coldata' => "&nbsp;$coldata&nbsp;",
				'td-extra' => $td_extra
				));
			$t->parse('cols', 'col', true);
		}
		$t->set_var('tr-extra', "class='$trclass' bgcolor='$bgcolor' onClick=\"document.location.href='bug.php?op=show&bugid={$row['bug_id']}'\"");
		$t->parse('rows','row',true);
		$t->set_var('cols', '');
		++$pos;
	}
	$t->set_var('numcols', count($db_fields));
}

$t->set_file('wrap','wrap.html');

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

$t->pparse('main',array('content','wrap','main'));

?>
