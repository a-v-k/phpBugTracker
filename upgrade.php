<?php

// upgrade.php -- Upgrade from the previous version
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
// $Id: upgrade.php,v 1.3 2001/11/06 04:41:14 bcurtis Exp $

define ('NO_AUTH', 1);
include 'include.php';

function upgrade() {
	global $q;
	
	$upgraded = $q->grab_field("select count(*) from ". TBL_CONFIGURATION.
		" where varname = 'STRICT_UPDATING'");
	if (!$upgraded) {
		// Move the support tables to use the table prefix
		// (if the table prefix is non-empty)
		if (strlen(TBL_PREFIX)) {
			$q->Halt_On_Error = 'no'; // We're going to ignore errors 
			$db_sess_table = ereg_replace(TBL_PREFIX, '', TBL_ACTIVE_SESSIONS);
			$db_seq_table = ereg_replace(TBL_PREFIX, '', TBL_DB_SEQUENCE);
			$q->query("alter table $db_sess_table rename to ". TBL_ACTIVE_SESSIONS);
			$q->query("alter table $db_seq_table rename to ". TBL_DB_SEQUENCE);
			$q->Halt_On_Error = 'yes'; // Stop ignoring errors
		} 

		// New configuration options
		$q->query('insert into '. TBL_CONFIGURATION.
			" (varname, varvalue, description, vartype) values ('STRICT_UPDATING', '0', 
			'Only the bug reporter, bug owner, managers, and admins can change a bug', 
			'bool')");
		$q->query('insert into '. TBL_CONFIGURATION.
			" (varname, varvalue, description, vartype) values 
			('NEW_ACCOUNTS_DISABLED', '0', 
			'Only admins can create new user accounts - newaccount.php is disabled', 
			'bool')");
	}
	include 'templates/default/upgrade-finished.html';
}

if ($doit) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
