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
// $Id: attachment.php,v 1.29 2008/01/27 23:19:15 brycen Exp $

include 'include.php';

function del_attachment($attachid) {
    global $db;
    $ainfo = grab_attachment($attachid);
    if (is_array($ainfo)) {
        if (isset($ainfo['fullFileName'])) { // old filesystem attach
            unlink($ainfo['fullFileName']);
        }
        $db->query("delete from " . TBL_ATTACHMENT . " where attachment_id = " . $db->quote($attachid));

        $referer = filter_input(INPUT_SERVER, 'HTTP_REFERER');
        header("Location: {$referer}");
    }
}

function grab_attachment($attachid) {
    global $db;

    if (!is_numeric($attachid)) {
        show_text(translate("That attachment does not exist"), true);
        return false;
    }
    $ainfo = $db->getRow("select a.attachment_id, a.bug_id, file_name, length(bytes) bytes_length, mime_type, project_id" . " from " . TBL_ATTACHMENT . " a, " . TBL_BUG . " b" . " where attachment_id = $attachid and a.bug_id = b.bug_id");
    if (empty($ainfo)) {
        show_text(translate("That attachment does not exist"), true);
        return false;
    }
    if ($ainfo['bytes_length'] == 0) { // old attach in filesystem
        $filename = join('/', array(ATTACHMENT_PATH,
            $ainfo['project_id'], "{$ainfo['bug_id']}-{$ainfo['file_name']}"));
        if (!is_readable($filename)) {
            show_text(translate("That attachment does not exist"), true);
            return false;
        }
        $ainfo['fullFileName'] = $filename;
    }
    return $ainfo;
}

function send_db_attachment($ainfo) {
    global $db;
    $id = $lob = 'null';
    $stmt = $db->getPdo()->prepare("select attachment_id, bytes from " . TBL_ATTACHMENT . " where attachment_id = ? ");
    $stmt->execute(array($ainfo['attachment_id']));
    $stmt->bindColumn(1, $id, PDO::PARAM_INT);
    $stmt->bindColumn(2, $lob, PDO::PARAM_LOB);
    $stmt->fetch(PDO::FETCH_BOUND);
    //$stmt->fetch();
    header("Content-Disposition: attachment; filename=\"$ainfo[file_name]\"");
    header("Content-Type: " . $ainfo['mime_type']);

    //header("Content-Type: " . $mimeType);
    header("Content-Length: " . $ainfo['bytes_length']);

    //fpassthru($lob);
    echo $lob;
    exit();
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

    $projectid = $db->getOne("select project_id from " . TBL_BUG . " where bug_id = $bugid");
    if (!$projectid) {
        show_text(translate("That bug does not exist"), true);
        return;
    }

    // Check for a previously-uploaded attachment with the same name, bug, and project
    $rs = $db->query("select a.bug_id, project_id from " . TBL_ATTACHMENT . " a, " . TBL_BUG . " b where file_name = " . $db->quote($_FILES['attachment']['name']) . " and a.bug_id = b.bug_id");
    $ainfo = array();
    while ($rs->fetchInto($ainfo)) {
        if ($bugid == $ainfo['bug_id'] && $projectid == $ainfo['project_id']) {
            show_attachment_form($bugid, translate("That attachment already exists for this bug"));
            return;
        }
    }

    $filepath = ATTACHMENT_PATH;
    //$tmpfilename = $_FILES['attachment']['tmp_name'];
    //$filename = "$bugid-{$_FILES['attachment']['name']}";

    if (!is_dir($filepath)) {
        show_attachment_form($bugid, translate("Couldn't find where to save the file!" . " (" . $filepath . ")"));
        return;
    }

    if (!is_writeable($filepath)) {
        show_attachment_form($bugid, translate("Couldn't create a file in the save path" . " (" . $filepath . ")"));
        return;
    }

    if (isset($_FILES['attachment']) && $_FILES['attachment']['size'] > 0) {
        $tmpName = $_FILES['attachment']['tmp_name'];
        $fp = fopen($tmpName, 'rb'); // read binary

        try {
            $stmt = $db->getPdo()->prepare(
                    "insert into " . TBL_ATTACHMENT . " (attachment_id, bug_id, file_name, bytes, description, file_size, mime_type, created_by, created_date) values "
                    . "(:attachment_id, :bug_id, :file_name, :bytes, :description, :file_size, :mime_type, :created_by, :created_date)");
            $nextId = $db->nextId(TBL_ATTACHMENT);
            $stmt->bindParam(':attachment_id', $nextId, PDO::PARAM_INT);
            $stmt->bindParam(':bug_id', $bugid, PDO::PARAM_INT);
            $stmt->bindParam(':file_name', $_FILES['attachment']['name'], PDO::PARAM_STR);
            $stmt->bindParam(':bytes', $fp, PDO::PARAM_LOB);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':file_size', $_FILES['attachment']['size'], PDO::PARAM_INT);
            $stmt->bindParam(':mime_type', $_FILES['attachment']['type'], PDO::PARAM_STR);
            $stmt->bindParam(':created_by', $u, PDO::PARAM_STR);
            $stmt->bindParam(':created_date', $now, PDO::PARAM_STR);
            $db->getPdo()->errorInfo();
            $stmt->execute();
        } catch (PDOException $e) {
            //'Error : ' . $e->getMessage();
            throw $e;
        }
        fclose($fp);
    }

//    if (!is_dir("$filepath/$projectid")) {
//        @mkdir("$filepath/$projectid", 0775);
//    }
//
//    if (!@move_uploaded_file($_FILES['attachment']['tmp_name'], "$filepath/$projectid/$filename")) {
//        show_attachment_form($bugid, translate("There was an error moving the uploaded file"));
//        return;
//    }
//
//    @chmod("$filepath/$projectid/$filename", 0766);
//    $db->query("insert into " . TBL_ATTACHMENT . " (attachment_id, bug_id, file_name, description, file_size, mime_type, created_by, created_date) values (" . join(', ', array($db->nextId(TBL_ATTACHMENT), $db->quote($bugid), $db->quote($_FILES['attachment']['name']), $db->quote($description), $db->quote($_FILES['attachment']['size']), $db->quote($_FILES['attachment']['type']), $u, $now)) . ")");

    if ($_POST['use_js']) {
        $t->render('admin/edit-submit.html');
    } else {
        header("Location: bug.php?op=show&bugid=$bugid");
    }
}

function get_mime_content_type($fileName) {

    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/x-zip-compressed',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $fileName);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}

function show_attachment_form($bugid, $error = '') {
    global $db, $t;

    if (!is_numeric($bugid)) {
        show_text(translate("That bug does not exist"), true);
        return;
    }

    $bugexists = $db->getOne("select count(*) from " . TBL_BUG . " where bug_id = $bugid");
    if (!$bugexists) {
        show_text(translate("That bug does not exist"), true);
        return;
    }

    $t->assign(array(
        'error' => $error,
        'bugid' => $bugid,
        'description' => isset($description) ? htmlspecialchars($description) : '',
        'max_size' => ini_get('upload_max_filesize') < ATTACHMENT_MAX_SIZE ? number_format(ini_get('upload_max_filesize')) : number_format(ATTACHMENT_MAX_SIZE)
    ));
    $t->render('bugattachmentform.html', translate("Add Attachment"), !empty($_REQUEST['use_js']) ? 'wrap-popup.php' : 'wrap.php');
}

if (isset($_GET['del'])) {
    if (!$perm->have_perm('Admin')) {
        show_text(translate("You do not have the permissions required for that function"));
    } else {
        del_attachment($_GET['del']);
    }
} elseif (isset($_POST['submit'])) {
    $perm->check('EditBug');
    add_attachment($_POST['bugid'], $_POST['description']);
} elseif (isset($_GET['attachid'])) {

    $ainfo = grab_attachment($_GET['attachid']);
    if (is_array($ainfo)) {
        if (isset($ainfo['fullFileName'])) { // old filesystem attach
            $base = basename($ainfo['fullFileName']);
            header("Content-Disposition: attachment; filename=\"$ainfo[file_name]\"");
            header("Content-Type: " . $ainfo['mime_type']);
            header("Connection: close");
//		header("Pragma: nocache");
//		header("Expires: 0 ");
            header("Cache-Control: max-age=60");
            @readfile($ainfo['fullFileName']);
            exit;
        } else {
            send_db_attachment($ainfo);
        }
    }
} elseif (isset($_GET['bugid'])) {
    $perm->check('EditBug');
    show_attachment_form(check_id($_GET['bugid']));
} else {
    show_text(translate("You tried to post an attachment that is larger than the server's maximum upload file size."));
}

//