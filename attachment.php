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

function add_attachment($projectid, $bugid, $description) {
	global $q, $HTTP_POST_FILES, $now, $u, $STRING;
	
	if (!isset($HTTP_POST_FILES['attachment']) || 
		$HTTP_POST_FILES['attachment']['tmp_name'] == 'none') {
		return false;
	}
	$filepath = INSTALLPATH.'/'.ATTACHMENT_PATH;
	$tmpfilename = $HTTP_POST_FILES['attachment']['tmp_name'];
	$filename = "$bugid-{$HTTP_POST_FILES['attachment']['name']}";
	if (!is_dir($filepath)) {
		show_attachment_form($bugid, $STRING['no_attachment_save_path']);
	}
	if (!is_writeable($filepath)) {
		show_attachment_form($bugid, $STRING['attachment_path_not_writeable']);
	}
	if (!is_dir("$filepath/$projectid")) {
		#umask(011);
		@mkdir("$filepath/$projectid", 0775);
	}
	if (!@move_uploaded_file($HTTP_POST_FILES['attachment']['tmp_name'],
		"$filepath/$projectid/$filename")) {
		show_attachment_form($bugid, $STRING['attachment_move_error']);
	}
	chmod("$filepath/$projectid/$filename", 0766);
	$q->query("insert into Attachment (AttachmentID, BugID, FileName, Description, FileSize, MimeType, CreatedBy, CreatedDate) values (".$q->nextid('Attachment').", $bugid, '{$HTTP_POST_FILES['attachment']['name']}', '$description', {$HTTP_POST_FILES['attachment']['size']}, '{$HTTP_POST_FILES['attachment']['type']}', $u, $now)");
	return true;
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
