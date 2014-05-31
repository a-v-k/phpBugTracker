<?php

// report.php - Generate reports on various bug activities
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
// $Id: editComment.php,v 1.3 2008/04/09 03:27:14 brycen Exp $  

include 'include.php';

$projectid = isset($_GET['projectid']) ? $_GET['projectid'] : 0;
global $db, $t, $restricted_projects, $perm, $QUERY;

if (!$perm->have_perm('Manager')) {
    $t->assign('status', "You do not have permission to edit comments.");
    $t->render('editComment.html', translate("Edit Comment"));
    return;
}

//	Edit a specific comment
$bug_id = check_id($_GET['bugid']);
if (isset($_GET['newComment'])) {
    $new_comment = $_GET['newComment'];
    $new_comment = $db->quoteSmart($new_comment);
    $comment_id = $_GET['commentID'];
    $comment_id = $db->escapeSimple($comment_id);

    // Should we allow changes to be made to this bug by this user?
    /*
      if (STRICT_UPDATING and !($u == $buginfo['assigned_to'] or
      $u == $buginfo['created_by'] or $perm->have_perm('Manager'))) {
      show_bug($bugid,array('status' => translate("You can not change this bug")));
      return;
      }
     */

    // Update database.
    // TODO: create a "revised_on" date column in the database
    $db->query("update " . TBL_COMMENT . " set comment_text=" . $new_comment . " where comment_id=$comment_id");
    $t->assign('status', "Comment #$comment_id updated");
    header("Location: bug.php?op=show&bugid=$bug_id");
}

//	List all comments
//	In the case of an edit, this counts as the confirmation page.
if (is_numeric($bug_id)) {
    $t->assign('comments', $db->getAll('select c.* from ' . TBL_COMMENT . " c where bug_id=$bug_id order by c.created_date"));
    $t->render('editComment.html', translate("Edit Comment"));
}

//