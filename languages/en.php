<?php

// strings-en.php - English strings and titles
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
// $Id: en.php,v 1.6 2001/12/04 14:32:24 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'us-ascii',
	'nouser' => 'That user does not exist',
	'dupeofself' => 'A bug can\'t be a duplicate of itself',
	'nobug' => 'That bug does not exist',
	'givesummary' => 'Please enter a summary',
	'givedesc' => 'Please enter a description',
	'noprojects' => 'No projects found',
	'totalbugs' => 'Total Bugs',
	'giveemail' => 'Please enter a valid email address',
	'givelogin' => 'Please enter a login',
	'loginused' => 'That login has already been used',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "Your phpBugTracker password is %s",
	'nobugs' => 'No bugs found',
	'givename' => 'Please enter a name',
	'edit' => 'Edit',
	'addnew' => 'Add new',
	'nooses' => 'No OSes found',
	'giveinitversion' => 'Please enter an initial version for the project',
	'giveversion' => 'Please enter a version',
	'noversions' => 'No versions found',
	'nocomponents' => 'No components found',
	'nostatuses' => 'No statuses found',
	'noseverities' => 'No severities found',
	'givepassword' => 'Please enter a password',
	'nousers' => 'No users found',
	'bugbadperm' => 'You cannot change this bug',
	'bugbadnum' => 'That bug does not exist',
	'datecollision' => 'Someone has updated this bug since you viewed it.	The bug info has been reloaded with the latest changes.',
	'passwordmatch' => 'Those passwords don\'t match -- please try again',
	'nobughistory' => 'There is no history for that bug',
	'logintomodify' => 'You must be logged in to modify this bug',
	'dupe_attachment' => 'That attachment already exists for this bug',
	'give_attachment' => 'Please specify a file to upload',
	'no_attachment_save_path' => 'Couldn\'t find where to save the file!',
	'attachment_path_not_writeable' => 'Couldn\'t create a file in the save path',
	'attachment_move_error' => 'There was an error moving the uploaded file',
	'bad_attachment' => 'That attachment does not exist',
	'attachment_too_large' => 'The file you specified is larger than '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'You do not have the permissions required for that function'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Enter a Bug',
	'editbug' => 'Edit Bug',
	'newaccount' => 'Create a new account',
	'bugquery' => 'Bug Query',
	'buglist' => 'Bug List',
	'addcomponent' => 'Add Component',
	'editcomponent' => 'Edit Component',
	'addproject' => 'Add Project',
	'editproject' => 'Edit Project',
	'addversion' => 'Add Version',
	'editversion' => 'Edit Version',
	'project' => 'Projects',
	'os' => 'Operating Systems',
	'resolution' => 'Resolutions',
	'status' => 'Statuses',
	'severity' => 'Severity',
	'user' => 'Users',
	'home' => 'Home',
	'reporting' => 'Reporting',
	'group' => 'Groups'
	);
	
?>
