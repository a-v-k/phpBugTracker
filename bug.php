<?php

// bug.php - All the interactions with a bug
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

///
/// Show the activity for a bug
function show_history($bugid) {
	global $q, $t, $STRING;
	
	if (!is_numeric($bugid)) {
		show_text($STRING['nobughistory']);
		return;
	}
	
	$q->query("select bh.*, Email from BugHistory bh left join User on CreatedBy = UserID where BugID = $bugid");
	if (!$q->num_rows()) {
		show_text($STRING['nobughistory']);
		return;
	}
	
	$t->set_file('content','bughistory.html');
	$t->set_block('content', 'row', 'rows');
	$t->set_var('bugid', $bugid);
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'field' => stripslashes($row['ChangedField']),
			'oldvalue' => stripslashes($row['OldValue']),
			'newvalue' => stripslashes($row['NewValue']),
			'createdby' => stripslashes(maskemail($row['Email'])),
			'date' => date(DATEFORMAT.' '.TIMEFORMAT, $row['CreatedDate'])
			));
		$t->parse('rows', 'row', true);
	}
}

///
/// Send the email about changes to the bug and log the changes in the DB
function do_changedfields($userid, $buginfo, $cf, $comments) {
	global $q, $t, $u, $select, $now;
	
	$t->set_file('emailout','bugemail.txt');
	$t->set_block('emailout','commentblock', 'cblock');
	foreach(array('Title','URL') as $field) {
		if (isset($cf[$field])) {
			$q->query("insert into BugHistory (BugID, ChangedField, OldValue, NewValue, CreatedBy, CreatedDate) values ({$buginfo['BugID']}, '$field', '$buginfo[$field]', '$cf[$field]', $u, $now)");
			$t->set_var(array(
				$field => $cf[$field],
				$field.'Stat' => '!'
				));
		} else {
			$t->set_var(array(
				$field => $buginfo[$field],
				$field.'Stat' => ' '
				));
		}
	}
	
	foreach(array('Project','Component','Status','Resolution','Severity','OS',
		'Version') as $field) {
		$oldvalue = $q->grab_field("select Name from $field where ${field}ID = $buginfo[$field]");
		if ($cf[$field]) {
			$newvalue = $q->grab_field("select Name from $field where ${field}ID = $cf[$field]");
			$q->query("insert into BugHistory (BugID, ChangedField, OldValue, NewValue, CreatedBy, CreatedDate) values ({$buginfo['BugID']}, '$field', '$oldvalue', '$newvalue', $u, $now)");
			$t->set_var(array(
				$field => stripslashes($newvalue),
				$field.'Stat' => '!'
				));
		} else {
			$t->set_var(array(
				$field => stripslashes($oldvalue),
				$field.'Stat' => ' '
				));
		}
	}
	
	// Reporter never changes;
	$reporter = $q->grab_field("select Email from User where UserID = 
		{$buginfo['CreatedBy']}");
	$reporterstat = ' ';
	$assignedto = $q->grab_field("select Email from User where UserID = ". ($cf['AssignedTo'] ? $cf['AssignedTo'] : $buginfo['AssignedTo']));
	$assignedtostat = $cf['AssignedTo'] ? '!' : ' ';
	
	// If there are new comments grab the comments immediately before the latest
	if ($comments) {
		$q->query("select u.Email, c.Text, c.CreatedDate from Comment c, User u where BugID = {$buginfo['BugID']} and c.CreatedBy = u.UserID order by CreatedDate desc limit 2");
		$row = $q->grab();
		$t->set_var(array(
			'newpostedby' => $row['Email'],
			'newpostedon' => date(TIMEFORMAT, $row['CreatedDate']).' on '.
				date(DATEFORMAT, $row['CreatedDate']),
			'newcomments' => textwrap('+ '.stripslashes($row['Text']),72,"\n+ ")
			));
		// If this comment is the first additional comment after the creation of the
		// bug then we need to grab the bug's description as the previous comment
		if ($q->num_rows() < 2) {
			list($by, $on, $comments) = $q->grab("select u.Email, b.CreatedDate, b.Description from Bug b, User u where b.CreatedBy = u.UserID and BugID = {$buginfo['BugID']}");
			$t->set_var(array(
				'oldpostedby' => $by,
				'oldpostedon' => date(TIMEFORMAT,$on).' on '.date(DATEFORMAT,$on),
				'oldcomments' => textwrap(stripslashes($comments),72)
				));
		} else {
			$row = $q->grab();
			$t->set_var(array(
				'oldpostedby' => $row['Email'],
				'oldpostedon' => date(TIMEFORMAT,$row['CreatedDate']).' on '.
					date(DATEFORMAT,$row['CreatedDate']),
				'oldcomments' => textwrap(stripslashes($row['Text']),72)
				));
		}
		$t->parse('cblock', 'commentblock', true);
	} else {
		$t->set_var('cblock', '');
	}
		
	// Don't email the person who just made the changes (later, make this 
	// behavior toggable by the user)
	if ($userid != $buginfo['CreatedBy']) 
		$maillist[] = $reporter;
	if ($userid != ($cf['AssignedTo'] ? $cf['AssignedTo'] : $buginfo['AssignedTo']))
		$maillist[] = $assignedto;
	// Later add a watcher (such as QA person) check here
	$toemail = delimit_list(', ',$maillist);
		
	$t->set_var(array(
		'bugid' => $buginfo['BugID'],
		'url' => INSTALLURL."/bug.php?op=show&bugid={$buginfo['BugID']}",
		'Priority' => $select['priority'][($cf['Priority'] ? $cf['Priority'] : $buginfo['Priority'])],
		'PriorityStat' => $cf['Priority'] ? '!' : ' ',
		'Reporter' => $reporter,
		'ReporterStat' => $reporterstat,
		'AssignedTo' => $assignedto,
		'AssignedToStat' => $assignedtostat,
		'Comments' => textwrap($oldcomments,72,"\n	")."\n\n+".
			textwrap($comments,72,"\n+ ")."\n"
		));
	mail($toemail,"[Bug {$buginfo['BugID']}] Changed - ".
		($cf['Title'] ? $cf['Title'] : $buginfo['Title']), $t->parse('main','emailout'),
		sprintf("From: %s\nReply-To: %s\nErrors-To: %s", ADMINEMAIL, ADMINEMAIL,
			ADMINEMAIL));
}

function update_bug($bugid = 0) {
	global $q, $t, $u, $STRING, $perm, $now;
	
	// Pull bug from database to determine changed fields and for user validation
	$buginfo = $q->grab("select * from Bug where BugID = $bugid");
	
	if ($pv = $GLOBALS['HTTP_POST_VARS']) {
		while (list($k,$v) = each($GLOBALS['HTTP_POST_VARS'])) {
			$$k = $v;
			if ($k == 'URL') {
				if ($v == 'http://') $v = '';
				elseif ($v and substr($v,0,7) != 'http://') $v = 'http://'.$v;
				$URL = $v;
			}
			if ($buginfo[$k] != $v) { $changedfields[$k] = $v; }
		}
	}
	
	if (!($u == $buginfo['AssignedTo'] or $u == $buginfo['CreatedBy'] or 
		$perm->have_perm('Manager'))) {
			show_bug($bugid,array('status' => $STRING['bugbadperm']));
			return;
	}
		
	if ($outcome == 'reassign' and 
		(!$assignedto = $q->grab_field("select UserID from User where Email = '$reassignto'"))) {
		show_bug($bugid,array('status' => $STRING['nouser']));
		return;
	}
	
	if ($lastmodifieddate != $buginfo['LastModifiedDate']) {
		show_bug($bugid, array('status' => $STRING['datecollision']));
		return;
	}
	
	switch($outcome) {
		case 'unchanged' : break;
		case 'assign' : $assignedto = $u; $statusfield = 'Assigned'; break;
		case 'reassign' : 
			if (!$assignedto = $q->grab_field("select UserID from User where Email = '$reassignto'")) {
				show_bug($bugid,array('status' => $STRING['nouser']));
				return;
			} else {				
				$statusfield = 'Assigned'; 
				$changedfields['AssignedTo'] = $assignedto;
				break;
			}
		case 'reassigntocomponent' : 
			$assignedto = $q->grab_field("select Owner from Component where ComponentID = $component");
			$statusfield = 'Assigned'; break;
		case 'dupe' : 
			$changeresolution = true;
			if ($dupenum == $bugid) {
				show_bug($bugid,array('status' => $STRING['dupeofself']));
				return;
			} elseif (!$q->grab_field("select BugID from Bug where BugID = $dupenum")) {
				show_bug($bugid,array('status' => $STRING['nobug']));
				return;
			}
			$q->query("insert into Comment (CommentID, BugID, Text, CreatedBy, CreatedDate) values (".$q->nextid('Comment').", $dupenum, 'Bug #$bugid is a duplicate of this bug', $u, $now)");
			$q->query("insert into Comment (CommentID, BugID, Text, CreatedBy, CreatedDate) values (".$q->nextid('Comment').", $bugid, 'This bug is a duplicate of bug #$dupenum', $u, $now)");
			$statusfield = 'Duplicate'; 
			$bugresolution = $q->grab_field("select ResolutionID from Resolution where Name = 'Duplicate'");
			$statusfield = 'Resolved';
			break;
		case 'resolve' : 
			$changeresolution = true;
			$statusfield = 'Resolved'; 
			break;
		case 'reopen' :
			$changeresolution = true;
			$statusfield = 'Reopened';
			$bugresolution = 0;
			break;
		case 'verify' :
			$statusfield = 'Verified';
			break;
		case 'close' :
			$statusfield = 'Closed';
			break;
	}
	if ($statusfield) {
		$status = $q->grab_field("select StatusID from Status where Name = '$statusfield'");
		$changedfields['Status'] = $status; 
	}
	
	if ($comments) {
		$comments = htmlspecialchars($comments);
		$q->query("insert into Comment (CommentID, BugID, Text, CreatedBy, CreatedDate) values (".$q->nextid('Comment').", $bugid, '$comments', $u, $now)");
	}

	$q->query("update Bug set Title = '$Title', URL = '$URL', Severity = $Severity, Priority = $Priority, ".($status ? "Status = $status, " : ''). ($changeresolution ? "Resolution = $bugresolution, " : ''). ($assignedto ? "AssignedTo = $assignedto, " : '')." Project = $Project, Version = $Version, Component = $Component, OS = $OS, LastModifiedBy = $u, LastModifiedDate = $now where BugID = $bugid");
	
	if ($changedfields or $comments) {
		do_changedfields($u, $buginfo, $changedfields, $comments);
	}
	header("Location: bug.php?op=show&bugid=$bugid");
}

function do_form($bugid = 0) {
	global $q, $me, $title, $u, $another, $STRING;
	
	$pv = $GLOBALS['HTTP_POST_VARS'];
	// Validation
	if (!$pv['title'] = htmlspecialchars(trim($pv['title'])))
		$error = $STRING['givesummary'];
	elseif (!$pv['description'] = htmlspecialchars(trim($pv['description'])))
		$error = $STRING['givedesc'];
	if ($error) { show_form($bugid, $error); return; }
	
	while (list($k,$v) = each($pv)) $$k = $v;
	
	if ($url == 'http://') $url = '';
	$time = time();
	if (!$bugid) {
		$status = $q->grab_field("select StatusID from Status where Name = 'Unconfirmed'");
		$q->query("insert into Bug (BugID, Title, Description, URL, Severity, Priority, Status, CreatedBy, CreatedDate, LastModifiedBy, LastModifiedDate, Project, Version, Component, OS, BrowserString) values (".$q->nextid('Bug').", '$title', '$description', '$url', $severity, $priority, $status, $u, $time, $u, $time, $project, $version, $component, '$os', '{$GLOBALS['HTTP_USER_AGENT']}')");
	} else {
		$q->query("update Bug set Title = '$title', Description = '$description', URL = '$url', Severity = '$severity', Priority = '$priority', Status = $status, AssignedTo = '$assignedto', Project = $project, Version = $version, Component = $component, OS = '$os', BrowserString = '{$GLOBALS['HTTP_USER_AGENT']}' LastModifiedBy = $u, LastModifiedDate = $time where BugID = '$bugid'");
	}
	if ($another) header("Location: $me?op=add&project=$project");
	else header("Location: query.php");
}	

function show_form($bugid = 0, $error = '') {
	global $q, $me, $t, $project, $TITLE;
	
	if ($GLOBALS['HTTP_POST_VARS']) 
		while (list($k,$v) = each($GLOBALS['HTTP_POST_VARS'])) $$k = $v;
	
	$t->set_file('content','bugform.html');
	$projectname = $q->grab_field("select Name from Project where ProjectID = $project");
	if ($bugid && !$error) {
		$row = $q->grab("select * from Bug where BugID = '$bugid'");
		$t->set_var(array(
			'bugid' => $bugid,
			'TITLE' => $TITLE['editbug'],
			'title' => stripslashes($row['Title']),
			'description' => stripslashes($row['Description']),
			'url' => $row['URL'],
			'urllabel' => $row['URL'] ? "<a href='{$row['URL']}'>URL</a>" : 'URL',
			'severity' => build_select('Severity',$row['Severity']),
			'priority' => build_select('priority',$row['Priority']),
			'status' => build_select('Status',$row['Status']),
			'resolution' => build_select('Resolution',$row['Resolution']),
			'assignedto' => $row['AssignedTo'],
			'createdby' => $row['CreatedBy'],
			'createddate' => date(DATEFORMAT,$row['CreatedDate']),
			'lastmodifieddate' => $row['LastModifiedDate'],
			'project' => $row['Project'],
			'projectname' => $projectname,
			'version' => build_select('Version',$row['Version'],$row['Project']),
			'component' => build_select('Component',$row['Component'],$row['Project']),
			'os' => build_select('OS',$row['OS']),
			'browserstring' => $row['BrowserString']));
	} else {
		$t->set_var(array(
			'TITLE' => $TITLE['enterbug'],
			'error' => $error,
			'bugid' => $bugid,
			'title' => stripslashes($title),
			'description' => stripslashes($description),
			'url' => $url ? $url : 'http://',
			'urllabel' => $url ? "<a href='$url'>URL</a>" : 'URL',
			'severity' => build_select('Severity',$severity),
			'priority' => build_select('priority',$priority),
			'status' => build_select('Status',$status),
			'resolution' => build_select('Resolution',$resolution),
			'assignedto' => $assignedto,
			'createdby' => $createdby,
			'createddate' => $createddate,
			'lastmodifieddate' => $lastmodifieddate,
			'project' => $project,
			'projectname' => $projectname,
			'version' => build_select('Version',$version,$project),
			'component' => build_select('Component',$component,$project),
			'os' => build_select('OS',$os)));
	}
}

function show_bug($bugid = 0, $error = '') {
	global $q, $me, $t, $project, $STRING, $u; 
	
	if (!ereg('^[0-9]+$',$bugid) or !$row = $q->grab("select BugID, Title, Reporter.Email as Reporter, Owner.Email as Owner, Project, Version, Severity, Bug.CreatedDate, Bug.LastModifiedDate, Status.Name as Status, Priority, Bug.Description, Resolution.Name as Resolution, URL, Component, OS from Bug, Severity, Status left join User Owner on Bug.AssignedTo = Owner.UserID left join User Reporter on Bug.CreatedBy = Reporter.UserID left join Resolution on Resolution = ResolutionID where BugID = '$bugid' and Severity = SeverityID and Status = StatusID")) {
		show_text($STRING['bugbadnum'],true);
		return;
	}
	$t->set_file('content','bugdisplay.html');
	$t->set_block('content','row','rows');
	$t->set_block('content','arow','assignrow');
	$t->set_block('content','rrow','resolverow');
	$t->set_block('content','rerow','reopenrow');
	$t->set_block('content','vrow','verifyrow');
	$t->set_block('content','crow','closerow');
	$t->set_unknowns('remove');
	$t->set_var(array(
		'statuserr' => $error['status'] ? $error['status'].'<br><br>' : '',
		'bugid' => $bugid,
		'TITLE' => "{$TITLE['editbug']} #$bugid",
		'title' => stripslashes($row['Title']),
		'description' => nl2br(stripslashes($row['Description'])),
		'url' => $row['URL'],
		'urllabel' => $row['URL'] ? "<a href='{$row['URL']}'>URL</a>" : 'URL',
		'severity' => build_select('Severity',$row['Severity']),
		'priority' => build_select('priority',$row['Priority']),
		'status' => $row['Status'],
		'resolution' => $row['Resolution'],
		'owner' => maskemail($row['Owner']),
		'reporter' => maskemail($row['Reporter']),
		'createddate' => date(DATEFORMAT,$row['CreatedDate']),
		'createdtime' => date(TIMEFORMAT,$row['CreatedDate']),
		'lastmodifieddate' => $row['LastModifiedDate'],
		'project' => build_select('Project',$row['Project']),
		'projectid' => $row['Project'],
		'version' => build_select('Version',$row['Version'],$row['Project']),
		'component' => build_select('Component',$row['Component'],$row['Project']),
		'os' => build_select('OS',$row['OS']),
		'browserstring' => $row['BrowserString'],
		'bugresolution' => build_select('Resolution'),
		'submit' => $u == 'nobody' ? $STRING['logintomodify'] : 
			'<input type="submit" value="Submit">'
		));
	switch($row['Status']) {
		case 'Unconfirmed' :
		case 'New' :
		case 'Reopened' :
			$t->parse('assignrow','arow',true);
			$t->parse('resolverow','rrow',true);
			break;
		case 'Assigned' :
			$t->parse('resolverow','rrow',true);
			break;
		case 'Resolved' :
			$t->parse('reopenrow','rerow',true);
			$t->parse('verifyrow','vrow',true);
			$t->parse('closerow','crow',true);
			break;
		case 'Verified' :
			$t->parse('reopenrow','rerow',true);
			$t->parse('closerow','crow',true);
			break;
		case 'Closed' :
			$t->parse('reopenrow','rerow',true);
			break;
	}
			
	$q->query("select Text, Comment.CreatedDate, Email from Comment, User where BugID = $bugid and CreatedBy = UserID order by CreatedDate");
	if (!$q->num_rows()) {
		$t->set_var('rows','');
	} else {
		while ($row = $q->grab()) {
			$t->set_var(array(
				'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
				'rdescription' => eregi_replace('(bug)[[:space:]]*(#?)([0-9]+)',
					"\\1 <a href='$me?op=show&bugid=\\3'>\\2\\3</a>",nl2br($row['Text'])),
				'rreporter' => maskemail($row['Email']),
				'rcreateddate' => date(TIMEFORMAT,$row['CreatedDate']).' on '.
					date(DATEFORMAT,$row['CreatedDate'])
				));
			$t->parse('rows','row',true);
		}
	}
}

function show_projects() {
	global $me, $q, $t, $project, $STRING;
	
	$q->query("select * from Project where Active order by Name");
	switch ($q->num_rows()) {
		case 0 :
			$t->set_var('rows',$STRING['noprojects']);
			return;
		case 1 :
			$row = $q->grab();
			$project = $row['ProjectID'];
			show_form();
			break;
		default :
			$t->set_file('content','projectlist.html');
			$t->set_block('content','row','rows');

			while ($row = $q->grab()) {
			$t->set_var(array(
				'id' => $row['ProjectID'],
				'name' => $row['Name'],
				'description' => $row['Description'],
				'date' => date(DATEFORMAT,$row['CreatedDate'])
				));
			$t->parse('rows','row',true);
		}
		$t->set_var('TITLE', $TITLE['enterbug']);
	}	
}

$t->set_file('wrap','wrap.html');


if ($op) {
	switch($op) {
		case 'history' : show_history($bugid); break;
		case 'add' : 
			$perm->check('User');
			if ($project) show_form(); 
			else show_projects();
			break;
		case 'show' : show_bug($bugid); break;
		case 'update' : update_bug($bugid); break;
		case 'do' : do_form($bugid); break;
	}
} else header("Location: query.php");

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
