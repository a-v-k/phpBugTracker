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

include 'include.php';

function delete_saved_query($queryid) {
	global $q, $u, $me;
	
	$q->query("delete from saved_query where user_id = $u and saved_query_id = $queryid");
	header("Location: $me?op=query");
}

function show_query() {
	global $q, $t, $status, $resolution, $os, $priority, $severity, $TITLE, $u;
	
	$nq = new dbclass;
	
	$t->set_file('content','queryform.html');
	$t->set_block('content', 'savequeryblock', 'sqblock');
	$t->set_block('savequeryblock','row','rows');
	 
	// Build the javascript-powered select boxes
	$q->query("select project_id, project_name from project where active order by project_name");
	while (list($pid, $pname) = $q->grab()) {
		// Version array
		$js .= "versions['$pname'] = new Array(new Array('','All'),";
		$nq->query("select version_name, version_id from version where project_id = $pid and active");
		while (list($version,$vid) = $nq->grab()) {
			$js .= "new Array($vid,'$version'),";
		}
		if (substr($js,-1) == ',') $js = substr($js,0,-1);
		$js .= ");\n";
		
		// Component array
		$js .= "components['$pname'] = new Array(new Array('','All'),";
		$nq->query("select component_name, component_id from component where project_id = $pid and active");
		while (list($comp,$cid) = $nq->grab()) {
			$js .= "new Array($cid,'$comp'),";
		}
		if (substr($js,-1) == ',') $js = substr($js,0,-1);
		$js .= ");\n";
	}
	
	if ($u != 'nobody') {
		// Grab the saved queries if there are any
		$q->query("select * from saved_query where user_id = '$u'");
		if (!$q->num_rows()) {
			$t->set_var('rows','');
		} else {
			while ($row = $q->grab()) {
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
		'js' => $js,
		'status' => build_select('status',$status),
		'resolution' => build_select('resolution',$resolution),
		'os' => build_select('os',-1), // Prevent the OS regex selection
		'priority' => build_select('priority',$priority),
		'severity' => build_select('severity',$severity),
		'projects' => build_select('project'),
		'TITLE' => $TITLE['bugquery']
		));
			
}

function build_query($assignedto, $reportedby, $open) {
	global $q, $sess, $auth, $querystring, $status, $resolution, $os, $priority, 
		$severity, $email1, $emailtype1, $emailfield1, $Title, $Description, $URL, 
		$Title_type, $Description_type, $URL_type, $projects, $versions, $components;

	// Open bugs assigned to the user -- a hit list
	if ($assignedto || $reportedby) {
		$q->query("select status_id from status where status_name ".($open ? '' : 'not ')."in ('Unconfirmed', 'New', 'Assigned', 'Reopened')");
		while ($statusid = $q->grab_field()) $status[] = $statusid;
		$query[] = 'bug.status_id in ('.delimit_list(',',$status).')';
		if ($assignedto) {
			$query[] = "assigned_to = {$auth->auth['uid']}";
		} else {
			$query[] = "bug.created_by = {$auth->auth['uid']}";
		}
	} else {
		// Select boxes
		if ($status) $flags[] = 'bug.status_id in ('.delimit_list(',',$status).')';
		if ($resolution) $flags[] = 'bug.resolution_id in ('.delimit_list(',',$resolution).')';
		if ($os) $flags[] = 'bug.os_id in ('.delimit_list(',',$os).')';
		if ($priority) $flags[] = 'bug.priority in ('.delimit_list(',',$priority).')';
		if ($severity) $flags[] = 'bug.severity_id in ('.delimit_list(',',$severity).')';
		if ($flags) $query[] = '('.delimit_list(' or ',$flags).')';

		// Email field(s)
		if ($email1) {
			switch($emailtype1) {
				case 'like' : $econd = "like '%$email1%'"; break;
				case 'rlike' : 
				case 'not rlike' : 
				case '=' : $econd = "$emailtype1 '$email1'"; break;
			}
			foreach($emailfield1 as $field) $equery[] = "$field.email $econd";
			$query[] = '('.delimit_list(' or ',$equery).')';
		}

		// Text search field(s)
		foreach(array('title','tescription','url') as $searchfield) {
			if ($$searchfield) {
				switch (${$searchfield."_type"}) {
					case 'like' : $cond = "like '%".$$searchfield."%'"; break;
					case 'rlike' : $cond = "rlike '".$$searchfield."'"; break;
					case 'not rlike' :$cond = "not rlike '".$$searchfield."'"; break;
				}
				$fields[] = "$searchfield $cond";
			}
		}
		if ($fields) $query[] = '('.delimit_list(' or ',$fields).')';

		// Project/Version/Component
		if ($projects) {
			$proj[] = "bug.project_id = $projects";
			if ($versions) $proj[] = "bug.version_id = $versions";
			if ($components) $proj[] = "component_id = $components";
			$query[] = '('.delimit_list(' and ',$proj).')';
		}
	}
	
	if ($query) $querystring = delimit_list(' and ',$query);
	if (!$sess->is_registered('querystring')) $sess->register('querystring');
}

function list_items($assignedto = 0, $reportedby = 0, $open = 0) {
	global $querystring, $me, $q, $t, $selrange, $order, $sort, $query, 
		$page, $op, $select, $TITLE, $STRING, $savedqueryname, $u, $auth, 
		$default_db_fields, $all_db_fields;

	$t->set_file('content','buglist.html');
	$t->set_block('content','row','rows');
	$t->set_block('row','col','cols');
	
	// Save the query if requested
	if ($savedqueryname) {
		$savedquerystring = ereg_replace('&savedqueryname=.*(&?)', '\\1', $GLOBALS['QUERY_STRING']);
		$q->query("insert into saved_query (user_id, saved_query_name, saved_query_string) values ($u, '$savedqueryname', '$savedquerystring')");
	}
	if (!$order) { $order = 'bug_id'; $sort = 'asc'; }
	if (!$querystring or $op) build_query($assignedto, $reportedby, $open);
	$nr = $q->grab_field("select count(*) from bug left join auth_user owner on bug.assigned_to = owner.user_id left join auth_user reporter on bug.created_by = reporter.user_id ".($querystring != '' ? "where $querystring": ''));

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");
								
	$t->set_var(array(
		'pages' => $pages,
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'total' => $nr,
		'project' => build_select('project'),
		'TITLE' => $TITLE['buglist']));
	
	$q->query("select bug.*, reporter.email as reporter, owner.email as owner, lastmodifier.email as lastmodifier, project_name, severity_name, status_name, os_name, version_name, component_name, resolution_name, severity_color from bug left join resolution using (resolution_id), severity, status, os, version, component, project left join auth_user owner on bug.assigned_to = owner.user_id left join auth_user reporter on bug.created_by = reporter.user_id left join auth_user lastmodifier on bug.last_modified_by = lastmodifier.user_id where bug.severity_id = severity.severity_id and bug.status_id = status.status_id and bug.os_id = os.os_id and bug.version_id = version.version_id and bug.component_id = component.component_id and bug.project_id = project.project_id ". ($querystring != '' ? "and $querystring " : ''). "order by $order $sort limit $llimit, $selrange");
				
	$headers = array(
		'bug_id' => 'bug_id',
		'title' => 'title',
		'description' => 'description',
		'url' => 'url',
		'severity_name' => 'severity.sort_order',
		'priority' => 'bug.priority',
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
				
	if (!$q->num_rows()) {
		$t->set_var(array(
			'rows' => "<tr><td>{$STRING['nobugs']}</td></tr>",
			'numcols' => "1"));
		return;
	}
	
	// Header row 
	$db_fields = $auth->auth['db_fields'] ? $auth->auth['db_fields'] :
		$default_db_fields;
	foreach ($db_fields as $field) {
		$t->set_var(array(
			'coldata' => "<a href='{{$field}url}'>{$all_db_fields[$field]}</a>",
			'td-extra' => "class=\"header-col\" bgcolor=\"{{$field}color}\""
			));
		$t->parse('cols', 'col', true);
	}
	$t->set_var('tr-extra', '');
	$t->parse('rows', 'row', true);
	$t->set_var('cols', '');
	
	// Data rows 
	while ($row = $q->grab()) {
		$bgcolor = USE_SEVERITY_COLOR ? $row['severity_color'] : 
			((++$i % 2 == 0) ? '#dddddd' : '#ffffff');
		foreach ($db_fields as $field) {
			switch ($field) {
				case 'url' : 
					$coldata = "<a href='{$row[$field]}'>{$row[$field]}</a>"; 
					$td_extra = '';
					break;
				case 'created_date' :
				case 'last_modified_date' :
				case 'close_date' : 
					$coldata = $row[$field] ? date(DATEFORMAT, $row[$field]) : '&nbsp;';
					$td_extra = 'class="center-col"';
					break;
				case 'bug_id' :
				case 'title' :
					$coldata = "<a href='bug.php?op=show&bugid={$row['bug_id']}'>{$row[$field]}</a>"; 
					$td_extra = '';
					break;
				case 'reporter' :
				case 'owner' : 
				case 'lastmodifier' : 
					$coldata = maskemail($row[$field]);
					$td_extra = 'class="center-col"';
					break;
				case 'priority' :
					$coldata = $select['priority'][$row[$field]];
					$td_extra = 'class="center-col"';
					break;
				default :
					$coldata = $row[$field];
					$td_extra = 'class="center-col"';
					break;
			}
			$t->set_var(array(
				'coldata' => "&nbsp;$coldata&nbsp;",
				'td-extra' => $td_extra
				));
			$t->parse('cols', 'col', true);
		}
		$t->set_var('tr-extra', "bgcolor='$bgcolor' onClick=\"document.location.href='bug.php?op=show&bugid={$row['bug_id']}'\" onMouseOver=\"this.style.backgroundColor='#eeeeee'\" onMouseOut=\"this.style.backgroundColor='$bgcolor'\"");
		$t->parse('rows','row',true);
		$t->set_var('cols', '');
	}
	$t->set_var('numcols', count($db_fields));
}

$t->set_file('wrap','wrap.html');

if ($op) switch($op) {
	case 'query' : show_query(); break;
	case 'doquery' : list_items(); break;
	case 'delquery' : delete_saved_query($queryid); break;
	case 'mybugs' : list_items($assignedto, $reportedby, $open); break;
	default : show_query(); break;
}
else list_items($assignedto, $reportedby, $open);

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
