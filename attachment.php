<?php

// attachment.php - Adding, deleting, and displaying attachments
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

function show_attachment($attachid) {
	global $q;
	
	if (!is_numeric($attachid)) {
		show_text($STRING['bad_attachment'], true);
		return;
	}
	$ainfo = $q->grab("select a.BugID, FileName, MimeType, Project from Attachment a, Bug b where AttachmentID = $attachid and a.BugID = b.BugID");
	if ($q->num_rows() != 1) {
		show_text($STRING['bad_attachment'], true);
		return;
	}
	$filename = join('/',array(INSTALLPATH, ATTACHMENT_PATH, 
		$ainfo['Project'], "{$ainfo['BugID']}-{$ainfo['FileName']}"));
	if (!is_readable($filename)) {
		show_text($STRING['bad_attachment'], true);
		return;
	}
	header("Content-type: {$ainfo['MimeType']}");
	@readfile($filename);
}

function add_attachment($projectid, $bugid, $description) {
	global $q, $HTTP_POST_FILES, $now, $u, $STRING;
	
	if (!isset($HTTP_POST_FILES['attachment']) || 
		$HTTP_POST_FILES['attachment']['tmp_name'] == 'none') {
		show_attachment_form($bugid, $STRING['give_attachment']);
		return;
	}

	$filepath = INSTALLPATH.'/'.ATTACHMENT_PATH;
	$tmpfilename = $HTTP_POST_FILES['attachment']['tmp_name'];
	$filename = "$bugid-{$HTTP_POST_FILES['attachment']['name']}";

	if (!is_dir($filepath)) {
		show_attachment_form($bugid, $STRING['no_attachment_save_path']);
		return;
	}

	if (!is_writeable($filepath)) {
		show_attachment_form($bugid, $STRING['attachment_path_not_writeable']);
		return;
	}

	if (!is_dir("$filepath/$projectid")) {
		@mkdir("$filepath/$projectid", 0775);
	}

	if (!@move_uploaded_file($HTTP_POST_FILES['attachment']['tmp_name'],
		"$filepath/$projectid/$filename")) {
		show_attachment_form($bugid, $STRING['attachment_move_error']);
		return;
	}

	@chmod("$filepath/$projectid/$filename", 0766);
	$q->query("insert into Attachment (AttachmentID, BugID, FileName, Description, FileSize, MimeType, CreatedBy, CreatedDate) values (".$q->nextid('Attachment').", $bugid, '{$HTTP_POST_FILES['attachment']['name']}', '$description', {$HTTP_POST_FILES['attachment']['size']}, '{$HTTP_POST_FILES['attachment']['type']}', $u, $now)");
	$t->set_file('content', 'bugattachmentsuccess.html');
	$t->set_var('bugid', $bugid);
}

function show_attachment_form($bugid, $error = '') {
	global $q, $t;
	
	$t->set_file('content', 'bugattachmentform.html');
	if (!is_numeric($bugid) || !$projectid = $q->grab_field("select Project from Bug where BugID = $bugid")) {
		show_text($STRING['nobug'], true);
		return;
	}
	$t->set_var(array(
		'error' => $error,
		'bugid' => $bugid,
		'projectid' => $projectid
		'description' => stripslashes($description),
		));
}		

$t->set_file('wrap','wrap.html');

if (isset($HTTP_POST_FILES)) add_attachment($_pv['projectid'], $_pv['bugid'],
	$_pv['description']);
elseif (isset($_gv['attachid'])) show_attachment($_gv['attachid']);
else function show_attachment_form($_gv['bugid']);

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
