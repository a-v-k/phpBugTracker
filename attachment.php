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
// $Id: attachment.php,v 1.12 2002/01/19 15:11:26 bcurtis Exp $

include 'include.php';

function del_attachment($attachid) {
	global $q;
	
	if (list($filename, $mimetype) = grab_attachment($attachid)) {
		$q->query("delete from ".TBL_ATTACHMENT." where attachment_id = $attachid");
		unlink($filename);
		header("Location: bug.php?op=show&bugid=$attachid");
	}
}

function grab_attachment($attachid) {
	global $q, $STRING;
	
	if (!is_numeric($attachid)) {
		show_text($STRING['bad_attachment'], true);
		return false;
	}
	$ainfo = $q->grab("select a.bug_id, file_name, mime_type, project_id"
	                 ." from ".TBL_ATTACHMENT." a, ".TBL_BUG." b"
			 ." where attachment_id = $attachid and a.bug_id = b.bug_id");
	if ($q->num_rows() != 1) {
		show_text($STRING['bad_attachment'], true);
		return false;
	}
	$filename = join('/',array(INSTALL_PATH, ATTACHMENT_PATH, 
		$ainfo['project_id'], "{$ainfo['bug_id']}-{$ainfo['file_name']}"));
	if (!is_readable($filename)) {
		show_text($STRING['bad_attachment'], true);
		return false;
	}
	return array($filename, $ainfo['mime_type']);
}

function add_attachment($bugid, $description) {
	global $q, $HTTP_POST_FILES, $now, $u, $STRING, $t;
	
	if (!isset($HTTP_POST_FILES['attachment']) || 
		$HTTP_POST_FILES['attachment']['tmp_name'] == 'none') {
		show_attachment_form($bugid, $STRING['give_attachment']);
		return;
	}

	// Check the upload size.  If the size was greater than the max in
	// php.ini, the file won't even be set and will fail at the check above
	if ($HTTP_POST_FILES['attachment']['size'] > ATTACHMENT_MAX_SIZE) {
		show_attachment_form($bugid, $STRING['attachment_too_large']);
		return;
	}
	
	$projectid = $q->grab_field("select project_id from ".TBL_BUG." where bug_id = $bugid");
	if (!$projectid) {
		show_text($STRING['nobug'], true);
		return;
	}

	// Check for a previously-uploaded attachment with the same name, bug, and project
	$q->query("select a.bug_id, project_id from ".TBL_ATTACHMENT." a, ".TBL_BUG." b"
	         ." where file_name = '{$HTTP_POST_FILES['attachment']['name']}' and a.bug_id = b.bug_id");
	while ($ainfo = $q->grab()) {
		if ($bugid == $ainfo['bug_id'] && $projectid == $ainfo['project_id']) {
			show_attachment_form($bugid, $STRING['dupe_attachment']);
			return;
		}
	}
	
	$filepath = INSTALL_PATH.'/'.ATTACHMENT_PATH;
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
	$q->query("insert into ".TBL_ATTACHMENT." (attachment_id, bug_id, file_name, description, file_size, mime_type, created_by, created_date) values (".$q->nextid(TBL_ATTACHMENT).", $bugid, '{$HTTP_POST_FILES['attachment']['name']}', '$description', {$HTTP_POST_FILES['attachment']['size']}, '{$HTTP_POST_FILES['attachment']['type']}', $u, $now)");
	$t->set_file('content', 'bugattachmentsuccess.html');
	$t->set_var('bugid', $bugid);
}

function show_attachment_form($bugid, $error = '') {
	global $q, $t, $STRING;
	
	$t->set_file('content', 'bugattachmentform.html');
	if (!is_numeric($bugid)) { 
		show_text($STRING['nobug'], true);
		return;
	}
	
	$bugexists = $q->grab_field("select count(*) from ".TBL_BUG." where bug_id = $bugid");
	if (!$bugexists) { 
		show_text($STRING['nobug'], true);
		return;
	}
	
	$t->set_var(array(
		'error' => $error,
		'bugid' => $bugid,
		'description' => isset($description) 
			? htmlspecialchars(stripslashes($description)) : '',
		'max_size' => ini_get('upload_max_filesize') < ATTACHMENT_MAX_SIZE 
			? number_format(ini_get('upload_max_filesize'))
			: number_format(ATTACHMENT_MAX_SIZE)
		));
}		

$t->set_file('wrap','wrap.html');
if (isset($_gv['del'])) {
	if (!$perm->have_perm('Administrator')) {
		show_text($STRING['bad_permission']);
	} else {
		del_attachment($_gv['del']);
	}
} elseif (isset($HTTP_POST_FILES['attachment'])) {
	$perm->check('Editbug');
	add_attachment($_pv['bugid'],	$_pv['description']);
} elseif (isset($_gv['attachid'])) {
	if (list($filename, $mimetype) = grab_attachment($_gv['attachid'])) {
		header("Content-type: $mimetype");
		@readfile($filename);
		exit;
	}
} else {
	$perm->check('Editbug');
	show_attachment_form($_gv['bugid']);
}

$t->pparse('main',array('content','wrap','main'));

?>
