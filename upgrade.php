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
// $Id: upgrade.php,v 1.7 2002/03/07 00:30:28 bcurtis Exp $

define ('NO_AUTH', 1);
include 'include.php';

function upgrade() {
	global $q;
	
	$upgraded = $q->grab_field("select nextid from ". TBL_DB_SEQUENCE.
		' where seq_name = "'.TBL_AUTH_GROUP.'"');
	if (!$upgraded) {
		// Make changes to the auth_group table
		$q->query('alter table '.TBL_AUTH_GROUP.' add locked tinyint(1) not null default 0 after group_name');
		$q->query('update '.TBL_AUTH_GROUP.' set locked = 1');
		$q->query("insert into ".TBL_DB_SEQUENCE." values('".TBL_AUTH_GROUP."', 3)");
		
		// New table
		if (DB_TYPE == 'pgsql') {
			$q->query("CREATE TABLE ".TBL_PROJECT_GROUP." ( project_id INT4  NOT NULL DEFAULT '0', group_id INT4  NOT NULL DEFAULT '0', created_by INT4  NOT NULL DEFAULT '0', created_date INT8  NOT NULL DEFAULT '0', PRIMARY KEY  (project_id,group_id) )"); 
		} else {
			$q->query("create table ".TBL_PROJECT_GROUP." ( project_id int(10) unsigned NOT NULL default '0', group_id int(10) unsigned NOT NULL default '0', created_by int(10) unsigned NOT NULL default '0', created_date bigint(20) unsigned NOT NULL default '0', PRIMARY KEY  (project_id,group_id), KEY group_id (group_id) )");
		}
	}
	include 'templates/default/upgrade-finished.html';
}

if (isset($_gv['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
