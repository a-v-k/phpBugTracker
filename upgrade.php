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
// $Id: upgrade.php,v 1.11 2002/03/17 01:44:24 bcurtis Exp $

define ('NO_AUTH', 1);
include 'include.php';

function upgrade() {
	global $db;
	
	$upgraded = $db->getOne('select varvalue from '.TBL_CONFIGURATION.
		" where varname = 'PROMOTE_VOTES'");
	if (!$upgraded) {
		// Add the bug_vote table and insert the new configuration options
		if (DB_TYPE == 'pgsql') {
			$db->query("CREATE TABLE ".TBL_BUG_VOTE." ( user_id INT4  NOT NULL DEFAULT '0', bug_id INT4  NOT NULL DEFAULT '0', created_date INT8  NOT NULL DEFAULT '0', PRIMARY KEY  (user_id,bug_id) );"); 
		} else {
			$db->query("create table ".TBL_BUG_VOTE." ( user_id int(10) unsigned NOT NULL default '0', bug_id int(10) unsigned NOT NULL default '0', created_date bigint(20) unsigned NOT NULL default '0', PRIMARY KEY  (user_id, bug_id), KEY bug_id (bug_id) )");
		}
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('PROMOTE_VOTES', 5, 'The number of votes required to promote a bug from Unconfirmed to New (Set to 0 to disable promotions by voting)', 'string')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('MAX_USER_VOTES', 5, 'The maximum number of votes a user can cast across all bugs (Set to 0 to have no limit)', 'string')");
	}
	include 'templates/default/upgrade-finished.html';
}

if (isset($_gv['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
