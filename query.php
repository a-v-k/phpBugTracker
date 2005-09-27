<?php

// query.php - Query the bug database
// ------------------------------------------------------------------------
// Copyright (c) 2001 - 2004 The phpBugTracker Group
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
// $Id: query.php,v 1.106 2005/09/27 19:49:42 ulferikson Exp $

include 'include.php';

function delete_saved_query($queryid) {
	global $db, $u, $me;

	$db->query("delete from ".TBL_SAVED_QUERY." where user_id = $u and saved_query_id = $queryid");
	if (!empty($_GET['form']) and $_GET['form'] == 'advanced') {
		header("Location: $me?op=query&form=advanced");
	} else {
		header("Location: $me?op=query");
	}
}

function show_query($edit = false) {
	global $db, $t, $u;

	if ($u != 'nobody') {
		// Grab the saved queries if there are any
		$t->assign('queries',
			$db->getAll("select * from ".TBL_SAVED_QUERY." where user_id = '$u'"));
	}

	if ($edit) {
		extract($_GET);
		if (isset($_SESSION['queryinfo']['queryparams'])) {
			extract($_SESSION['queryinfo']['queryparams']);
		}
		$t->assign('project', isset($projects) ? $projects : null);
		$t->assign('version', isset($versions) ? $versions : null);
		$t->assign('component', isset($components) ? $components : null);
		$t->assign('status', isset($status) ? $status : null);
		$t->assign('resolution', isset($resolution) ? $resolution : null);
		$t->assign('os', isset($os) ? $os : null);
		$t->assign('priority', isset($priority) ? $priority : null);
		$t->assign('severity', isset($severity) ? $severity : null);
		$t->assign('database', isset($database) ? $database : null);
		$t->assign('site', isset($site) ? $site : null);
		$t->assign('emailsearch1', isset($emailsearch1) ? $emailsearch1 : null);
		$t->assign('closedinversion', isset($closedinversion) ? $closedinversion : null);
		$t->assign('tobeclosedinversion', isset($tobeclosedinversion) ? $tobeclosedinversion : null);
		$t->assign('order', isset($order) ? $order : null);
		$t->assign('sort', isset($sort) ? $sort : null);
		$t->assign('emailsearch1', isset($emailsearch1) ? $emailsearch1 : null);
		$t->assign('email1', isset($email1) ? $email1 : null);
		$t->assign('emailtype1', isset($emailtype1) ? $emailtype1 : null);
		$t->assign('emailfield1', isset($emailfield1) ? $emailfield1 : null);
		$t->assign('title', isset($title) ? $title : null);
		$t->assign('title_type', isset($title_type) ? $title_type : null);
		$t->assign('description', isset($description) ? $description : null);
		$t->assign('description_type', isset($description_type) ? $description_type : null);
		$t->assign('url', isset($url) ? $url : null);
		$t->assign('url_type', isset($url_type) ? $url_type : null);
		$t->assign('start_date', isset($start_date) ? $start_date : null);
		$t->assign('end_date', isset($end_date) ? $end_date : null);
		$t->assign('closed_start_date', isset($closed_start_date) ? $closed_start_date : null);
		$t->assign('closed_end_date', isset($closed_end_date) ? $closed_end_date : null);
	}

	// Show the advanced query form
	if (!empty($_GET['form']) and $_GET['form'] == 'advanced') {
		$t->render('queryform.html', translate("Query Bugs"));
	} else { // or show the simple one
		$t->render('queryform-simple.html', translate("Query Bugs"));
	}

}

function build_query($assignedto, $reportedby, $open, $bookmarked) {
	global $db, $perm, $restricted_projects;

	$paramstr = '';
	foreach ($_GET as $k => $v) {
		$$k = $v;
		if ($k == 'order' or $k == 'sort') continue;
		if (is_array($v)) {
			foreach ($v as $value) {
				$paramstr .= "&{$k}[]=$value";
			}
		} else {
			$paramstr .= "&$k=$v";
		}
	}

	// Open bugs assigned to the user -- a hit list
	if ($assignedto || $reportedby || $bookmarked) {
		$query[] = 'b.status_id '.($open ? '' : 'not ').
			'in ('.OPEN_BUG_STATUSES.')';
		if ($assignedto) {
			$query[] = "assigned_to = {$_SESSION['uid']}";
		} else if ($bookmarked) {
			$query[] = "b.bug_id = bookmark.bug_id AND bookmark.user_id = {$_SESSION['uid']}";
		} else {
			$query[] = "b.created_by = {$_SESSION['uid']}";
		}
	} else {
		// Select boxes
		$flags = array();
		// Need to check $array[0] for Opera --
		// it passes non-empty arrays for every multi-choice select box
		if (!empty($status) and $status[0]) {
			$flags[] = 'b.status_id in ('.@join(',',$status).')';
		}
		// If $resolution[0] == 0 then 'None' was selected
		if (!empty($resolution) or isset($resolution[0])) {
			$flags[] = 'b.resolution_id in ('.@join(',',$resolution).')';
		}
		if (!empty($os) and $os[0]) {
			$flags[] = 'b.os_id in ('.@join(',',$os).')';
		}
		if (!empty($priority) and $priority[0]) {
			$flags[] = 'b.priority in ('.@join(',',$priority).')';
		}
		if (!empty($severity) and $severity[0]) {
			$flags[] = 'b.severity_id in ('.@join(',',$severity).')';
		}
		if (!empty($database) and isset($database[0])) {
			// $database[0] can be 0, which stands for no database reported
			$flags[] = 'b.database_id in ('.@join(',',$database).')';
		}
		if (!empty($site) and $site[0]) {
			$flags[] = 'b.site_id in ('.@join(',',$site).')';
		}
		if (!empty($flags)) {
			$query[] = '('.@join(' and ',$flags).')';
		}
		if (!empty($start_date)) {
			$query[] = 'b.created_date > '.strtotime($start_date);
		}
		if (!empty($end_date)) {
			$query[] = 'b.created_date < '.strtotime($end_date);
		}
		if (!empty($closed_start_date)) {
			$query[] = 'b.close_date > '.strtotime($closed_start_date);
		}
		if (!empty($closed_end_date)) {
			$query[] = 'b.close_date < '.strtotime($closed_end_date);
		}

		// Email field(s)
		if (!empty($email1) && !empty($emailfield1)) {
			switch($emailtype1) {
				case 'like' : $econd = "like '%$email1%'"; break;
				case 'rlike' :
				case 'not rlike' :
				case '=' : $econd = "$emailtype1 '$email1'"; break;
			}
			foreach($emailfield1 as $field) $equery[] = "$field.$emailsearch1 $econd";
			$query[] = '('.@join(' and ',$equery).')';
		}

		// Search for additional comments with 'description'
		// TODO: Change this to match the condition selected (see below for rlike, not rlike, etc.)
		if (!empty($description)) {
			$bugs_with_comment = array(0);
			foreach ($db->getAll('SELECT bug_id FROM '.TBL_COMMENT.' WHERE comment_text LIKE \'%'.$description.'%\'') as $row) {
				$bugs_with_comment[] = $row['bug_id'];
			}
		}
		
		// Text search field(s)
		foreach(array('title','url', 'description') as $searchfield) {
			if (!empty($$searchfield)) {
				switch (${$searchfield."_type"}) {
					case 'like' : $cond = "like '%".$$searchfield."%'"; break;
					case 'rlike' : $cond = "rlike '".$$searchfield."'"; break;
					case 'not rlike' : $cond = "not rlike '".$$searchfield."'"; break;
				}
				$fields[] = "$searchfield $cond".
					($searchfield == 'description' 
						? ' or bug_id in ('.@join(', ', $bugs_with_comment).')'
						: '');
			}
		}
		if (!empty($fields)) $query[] = '('.@join(' and ',$fields).')';

		// Project/Version/Component
		if (!empty($projects)) {
			$proj[] = "b.project_id = '$projects'";
			if (!empty($versions) and $versions != 'All') $proj[] = "b.version_id = '$versions'";
			if (!empty($closedinversion) and $closedinversion != 'All') $proj[] = "b.closed_in_version_id = '$closedinversion'";
			if (!empty($tobeclosedinversion) and $tobeclosedinversion != 'All') $proj[] = "b.to_be_closed_in_version_id = '$tobeclosedinversion'";
			if (!empty($components) and $components != 'All') $proj[] = "b.component_id = '$components'";
			$query[] = '('.@join(' and ',$proj).')';
		} elseif (!$perm->have_perm('Admin')) { // Filter results from hidden projects
			$query[] = "b.project_id not in ($restricted_projects)";
		}
		// TODO: Something like this can be used for searching descriptions
		/* 
		select b.bug_id, b.title, b.description, c.comment_id, c.comment_text 
		from bug b left join comment c using (bug_id)
		where description like '%yet%' or comment_text like '%yet%'
		*/
	}

	if (!empty($query)) {
		return array(@join(' and ',$query), $paramstr);
	} else {
		return array('', '');
	}
}

// Formatting for spreadsheet
function format_spreadsheet_col($colvalue, $coltype) {
	global $select;
	
	switch($coltype) {
		case 'created_date' :
		case 'last_modified_date' :
		case 'close_date' :
			return ($colvalue ? date(DATE_FORMAT, $colvalue) : '');
		case 'lastmodifier' :
			return (!empty($colvalue) ? maskemail($colvalue) : '');
		case 'reporter' :
		case 'owner' :
		case 'lastmodifier' :
			return (!empty($colvalue) ? maskemail($colvalue) : '');
			break;
		default: return $colvalue;
	}
}

// Handle the formatting for various types of bug info in the bug list
function format_bug_col($colvalue, $coltype, $bugid, $pos) {
	global $select;

	switch ($coltype) {
		case 'url' :
			return "<a href=\"$colvalue\" target=\"_new\">$colvalue</a>";
			break;
		case 'created_date' :
		case 'last_modified_date' :
		case 'close_date' :
			return '<div align="center">'.
				($colvalue ? date(DATE_FORMAT, $colvalue) : '&nbsp;').
				'</div>';
			break;
		case 'bug_id' :
		case 'title' :
			return "<a href=\"bug.php?op=show&bugid=$bugid&pos=$pos\">$colvalue</a>";
			break;
		case 'reporter' :
		case 'owner' :
		case 'lastmodifier' :
			return '<div align="center">'.
				(!empty($colvalue) ? maskemail($colvalue) : '').'</div>';
			break;
		default :
			return '<div align="center">'.
				(!empty($colvalue) ? $colvalue : '').'</div>';
			break;
	}
}

function list_items($assignedto = 0, $reportedby = 0, $open = 0, $bookmarked = 0) {
	global $me, $db, $t, $select, $u, $default_db_fields, $all_db_fields, $QUERY;

	$query_db_fields = array(
		'bug_id' => 'b.bug_id',
		'title' => 'title',
		'description' => 'description',
		'url' => 'url',
		'severity_name' => 'severity.severity_name',
		'priority_name' => 'priority.priority_name',
		'status_name' => 'status.status_name',
		'resolution_name' => 'resolution_name',
		'reporter' => 'reporter.login as reporter',
		'owner' => 'owner.login as owner',
		'created_date' => 'b.created_date',
		'lastmodifier' => 'lastmodifier.login as lastmodifier',
		'last_modified_date' => 'b.last_modified_date',
		'project_name' => 'project.project_name',
		'version_name' => 'version.version_name',
		'to_be_closed_in_version_name' => 'version2.version_name as to_be_closed_in_version_name',
		'closed_in_version_name' => 'version3.version_name as closed_in_version_name',
		'database_name' => TBL_DATABASE.'.database_name',
		'site_name' => 'site.site_name',
		'component_name' => 'component.component_name',
		'os_name' => 'os.os_name',
		'browser_string' => 'browser_string',
		'close_date' => 'close_date',
		'comments' => 'count(distinct comment.comment_id) as comments',
		'attachments' => 'count(distinct attachment.attachment_id) as attachments',
		'votes' => 'count(distinct vote.user_id) as votes'
	);

	$db_headers = array(
		'bug_id' => 'b.bug_id',
		'title' => 'title',
		'description' => 'description',
		'url' => 'url',
		'severity_name' => 'severity.sort_order',
		'priority_name' => 'priority.sort_order',
		'status_name' => 'status.sort_order',
		'owner' => 'owner',
		'reporter' => 'reporter.login',
		'lastmodifier' => 'lastmodifier.login',
		'created_date' => 'b.created_date',
		'last_modified_date' => 'b.last_modified_date',
		'project_name' => 'project_name',
		'component_name' => 'component_name',
		'version_name' => 'version_name',
		'os_name' => 'os_name',
		'to_be_closed_in_version_name' => 'version2.version_name',
		'closed_in_version_name' => 'version3.version_name',
		'database_name' => TBL_DATABASE.'.database_name',
		'site_name' => 'site.sort_order',
		'browser_string' => 'browser_string',
		'resolution_name' => 'resolution.sort_order',
		'close_date' => 'close_date',
		'comments' => 'comments',
		'attachments' => 'attachments',
		'votes' => 'votes'
	);

	extract($_GET);
	if (!isset($page)) {
		$page = 1;
	}
	// Save the query if requested
	if (!empty($savedqueryname)) {
		$savedquerystring = ereg_replace('&savedqueryname=.*(&?)', '\1', $_SERVER['QUERY_STRING']);
		$savedquerystring .= '&op=doquery';
		if ($savedqueryoverride) { // Updating an existing query
			$db->query("update ".TBL_SAVED_QUERY." set saved_query_string = ".$db->quote(stripslashes($savedquerystring))." where user_id = $u and saved_query_name = ".$db->quote(stripslashes($savedqueryname)));
		} else { // Adding a new saved query
			$nextid = $db->getOne("select max(saved_query_id)+1 from ".TBL_SAVED_QUERY." where user_id = $u");
			$nextid = $nextid ? $nextid : 1;
			$db->query("insert into ".TBL_SAVED_QUERY." (saved_query_id, user_id, saved_query_name, saved_query_string) values (".join(', ', array($nextid, $u, $db->quote(stripslashes($savedqueryname)), $db->quote(stripslashes($savedquerystring)))).")");
		}
	}
	if (!isset($order)) {
		if (isset($_SESSION['queryinfo']['order'])) {
			$order = $_SESSION['queryinfo']['order'];
			$sort = $_SESSION['queryinfo']['sort'];
		} else {
			$order = 'b.bug_id';
			$sort = 'asc';
		}
	}
	// Taint checking
	if (empty($db_headers[$order])) $order = 'bug_id';
	if (!in_array($sort, array('asc', 'desc'))) $sort = 'asc';
	
	if (empty($_SESSION['queryinfo'])) $_SESSION['queryinfo'] = array();
	$_SESSION['queryinfo']['order'] = $db_headers[$order];;
	$_SESSION['queryinfo']['sort'] = $sort;
	if (empty($_SESSION['queryinfo']['queryparams']) || !empty($_GET)) {
	      $_SESSION['queryinfo']['queryparams'] = $_GET;
	}

	if (empty($_SESSION['queryinfo']['query']) or isset($op)) {
		list($_SESSION['queryinfo']['query'], $paramstr) =
			build_query($assignedto, $reportedby, $open, $bookmarked);
	}
	
	$desired_fields = !empty($_SESSION['db_fields']) ?
		$_SESSION['db_fields'] : $default_db_fields;

	$query_fields = array('b.bug_id as bug_link_id', 
		'severity.severity_color', 'priority.priority_color');
	foreach ($desired_fields as $field) {
		$query_fields[] = $query_db_fields[$field];
		$field_titles[] = $all_db_fields[$field];
		$headers[] = $field;
	}

	if (empty($_GET['xl'])) { // HTML view
		$nr = $db->getOne($QUERY['query-list-bugs-count'].
			(!empty($_SESSION['queryinfo']['query'])
				? $QUERY['query-list-bugs-count-join'].
					$_SESSION['queryinfo']['query']
				: ''));
	
		$_SESSION['queryinfo']['numrows'] = $nr;
		list($selrange, $llimit) = multipages($nr, $page, "order=$order&sort=$sort");
	
		$t->assign(array(
			'db_fields' => $desired_fields,
			'field_titles' => $field_titles,
			'has_excel' => find_include('Spreadsheet/Excel/Writer.php')
			));
		
		$t->assign('bugs', $db->getAll($db->modifyLimitQuery(
			sprintf($QUERY['query-list-bugs'], join(', ', $query_fields),
				(!empty($_SESSION['queryinfo']['query'])
				? "and {$_SESSION['queryinfo']['query']} " : ''),
			$db_headers[$order], $sort), $llimit, $selrange)));
	
		sorting_headers($me, $headers, $order, $sort, "page=$page".
			(!empty($paramstr) ? $paramstr : ''));
		$t->render('buglist.html', translate("Bug List"));
	} else { // Spreasheet download
		dump_spreadsheet($desired_fields, $field_titles, $db->getAll(
			sprintf($QUERY['query-list-bugs'], join(', ', $query_fields),
				(!empty($_SESSION['queryinfo']['query'])
				? "and {$_SESSION['queryinfo']['query']} " : ''),
			$db_headers[$order], $sort)));
	}
}

function dump_spreadsheet($fields, $titles, &$data) {
	include_once('Spreadsheet/Excel/Writer.php');
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->send('buglist.xls');
	error_reporting(0);
	$boldformat =& $workbook->addformat(array('bold' => 1));
	$worksheet =& $workbook->addworksheet('buglist');
	$row = 0;
	for ($i = 0, $colcount = count($fields); $i < $colcount; $i++) {
		$worksheet->write($row, $i, $titles[$i], $boldformat);
	}
	$row++;
	for ($i = 0, $bugcount = count($data); $i < $bugcount; $i++) {
		for ($j = 0; $j < $colcount; $j++) {
			$worksheet->write($row, $j, format_spreadsheet_col($data[$i][$fields[$j]], $fields[$j]));
		}
		$row++;
	}
	$worksheet->freezepanes(array(1, 0));
	$workbook->close();
}

$reportedby = !empty($_GET['reportedby']) ? $_GET['reportedby'] : 0;
$assignedto = !empty($_GET['assignedto']) ? $_GET['assignedto'] : 0;
$open = !empty($_GET['open']) ? $_GET['open'] : 0;
$bookmarked = !empty($_GET['bookmarked']) ? $_GET['bookmarked'] : 0;

// Make sure the page variable is numeric, if it's populated
if (!empty($_GET['page'])) $_GET['page'] =  preg_replace('/[^0-9]/', '', $_GET['page']);

// Make sure the user has permission to list bugs
if (!empty($_GET['projects']) && isset($restricted_projects) && 
	in_array($_GET['projects'], explode(',', $restricted_projects))) {
		show_text(translate("You do not have the rights to view this project.", true));
		exit;
}

if (isset($_GET['op'])) switch($_GET['op']) {
	case 'query' : show_query(); break;
	case 'doquery' : $_SESSION['queryinfo'] = array(); list_items(); break;
	case 'delquery' : 
		if ($auth->is_authenticated()) delete_saved_query(check_id($_GET['queryid']));  
		else show_query(); 
		break;
	case 'mybugs' : 
		if ($auth->is_authenticated()) list_items($assignedto, $reportedby, $open, $bookmarked); 
		else show_query(); 
		break;
	case 'edit' : show_query(true); break;
	default : show_query(); break;
}
else list_items($assignedto, $reportedby, $open, $bookmarked);

?>
