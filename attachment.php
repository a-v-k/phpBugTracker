<?php

// attachment.php - Adding, deleting, and displaying attachments
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
// $Id: attachment.php,v 1.26 2005/08/27 13:14:28 ulferikson Exp $

include 'include.php';

function del_attachment($attachid) {
	global $db;

	if (list($filename, $mimetype) = grab_attachment($attachid)) {
		$db->query("delete from ".TBL_ATTACHMENT." where attachment_id = ".$db->quote($attachid));
		unlink($filename);
		header("Location: {$_SERVER['HTTP_REFERER']}");
	}
}

function grab_attachment($attachid) {
	global $db;

	if (!is_numeric($attachid)) {
		show_text(translate("That attachment does not exist"), true);
		return false;
	}
	$ainfo = $db->getRow("select a.bug_id, file_name, mime_type, project_id"." from ".TBL_ATTACHMENT." a, ".TBL_BUG." b"." where attachment_id = $attachid and a.bug_id = b.bug_id");
	if (empty($ainfo)) {
		show_text(translate("That attachment does not exist"), true);
		return false;
	}
	$filename = join('/',array(ATTACHMENT_PATH,
		$ainfo['project_id'], "{$ainfo['bug_id']}-{$ainfo['file_name']}"));
	if (!is_readable($filename)) {
		show_text(translate("That attachment does not exist"), true);
		return false;
	}
	return array($filename, $ainfo['mime_type']);
}

function add_attachment($bugid, $description) {
	global $db, $now, $u, $t;

	if (!isset($_FILES['attachment'])) {
		show_attachment_form($bugid, translate("Please specify a file to upload"));
		return;
	}
	
	if ($_FILES['attachment']['tmp_name'] == 'none') {
		if (empty($_FILES['attachment']['name'])) {
			show_attachment_form($bugid, translate("Please specify a file to upload"));
		} else {
			show_attachment_form($bugid, sprintf(translate("The file you specified is larger than %s bytes"), number_format(ATTACHMENT_MAX_SIZE)));
		}
		return;
	}

	// Check the upload size.  If the size was greater than the max in
	// php.ini, the file won't even be set and will fail at the check above
	if ($_FILES['attachment']['size'] > ATTACHMENT_MAX_SIZE) {
		show_attachment_form($bugid, printf(translate("The file you specified is larger than %s bytes"), number_format(ATTACHMENT_MAX_SIZE)));
		return;
	}

	$projectid = $db->getOne("select project_id from ".TBL_BUG." where bug_id = $bugid");
	if (!$projectid) {
		show_text(translate("That bug does not exist"), true);
		return;
	}

	// Check for a previously-uploaded attachment with the same name, bug, and project
	$rs = $db->query("select a.bug_id, project_id from ".TBL_ATTACHMENT." a, ".TBL_BUG." b where file_name = ".$db->quote($_FILES['attachment']['name'])." and a.bug_id = b.bug_id");
	while ($rs->fetchInto($ainfo)) {
		if ($bugid == $ainfo['bug_id'] && $projectid == $ainfo['project_id']) {
			show_attachment_form($bugid, translate("That attachment already exists for this bug"));
			return;
		}
	}

	$filepath = ATTACHMENT_PATH;
	$tmpfilename = $_FILES['attachment']['tmp_name'];
	$filename = "$bugid-{$_FILES['attachment']['name']}";

	if (!is_dir($filepath)) {
		show_attachment_form($bugid, translate("Couldn't find where to save the file!"));
		return;
	}

	if (!is_writeable($filepath)) {
		show_attachment_form($bugid, translate("Couldn't create a file in the save path"));
		return;
	}

	if (!is_dir("$filepath/$projectid")) {
		@mkdir("$filepath/$projectid", 0775);
	}

	if (!@move_uploaded_file($_FILES['attachment']['tmp_name'],
		"$filepath/$projectid/$filename")) {
		show_attachment_form($bugid, translate("There was an error moving the uploaded file"));
		return;
	}

	@chmod("$filepath/$projectid/$filename", 0766);
	$db->query("insert into ".TBL_ATTACHMENT." (attachment_id, bug_id, file_name, description, file_size, mime_type, created_by, created_date) values (".join(', ', array($db->nextId(TBL_ATTACHMENT), $db->quote($bugid), $db->quote($_FILES['attachment']['name']), $db->quote(stripslashes($description)), $db->quote($_FILES['attachment']['size']), $db->quote($_FILES['attachment']['type']), $u, $now)).")");

	if ($_POST['use_js']) {
		$t->render('admin/edit-submit.html');
	} else {
		header("Location: bug.php?op=show&bugid=$bugid");
	}
}

function show_attachment_form($bugid, $error = '') {
	global $db, $t;

	if (!is_numeric($bugid)) {
		show_text(translate("That bug does not exist"), true);
		return;
	}

	$bugexists = $db->getOne("select count(*) from ".TBL_BUG." where bug_id = $bugid");
	if (!$bugexists) {
		show_text(translate("That bug does not exist"), true);
		return;
	}

	$t->assign(array(
		'error' => $error,
		'bugid' => $bugid,
		'description' => isset($description)
			? htmlspecialchars(stripslashes($description)) : '',
		'max_size' => ini_get('upload_max_filesize') < ATTACHMENT_MAX_SIZE
			? number_format(ini_get('upload_max_filesize'))
			: number_format(ATTACHMENT_MAX_SIZE)
		));
	$t->render('bugattachmentform.html', translate("Add Attachment"), 
		!empty($_REQUEST['use_js']) ? 'wrap-popup.html' : 'wrap.html');
}

if (isset($_GET['del'])) {
	if (!$perm->have_perm('Admin')) {
		show_text(translate("You do not have the permissions required for that function"));
	} else {
		del_attachment($_GET['del']);
	}
} elseif (isset($_POST['submit'])) {
	$perm->check('Editbug');
	add_attachment($_POST['bugid'], $_POST['description']);
} elseif (isset($_GET['attachid'])) {
	if (list($filename, $mimetype) = grab_attachment($_GET['attachid'])) {
		$base = basename($filename);
		header("Content-Disposition: attachment; filename=\"$base\"");
		header("Content-Type: $mimetype");
		header("Connection: close");
//		header("Pragma: nocache");
//		header("Expires: 0 ");
		header("Cache-Control: max-age=60");
		@readfile($filename);
		exit;
	}
} elseif (isset($_GET['bugid'])) {
	$perm->check('Editbug');
	show_attachment_form($_GET['bugid']);
} else {
	show_text(translate("You tried to post an attachment that is larger than the server's maximum upload file size."));
}

?>
