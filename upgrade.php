<?php

// upgrade.php -- Upgrade from the previous version
// ------------------------------------------------------------------------
// Copyright (c) 2001, 2002 The phpBugTracker Group
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
// $Id: upgrade.php,v 1.33 2003/04/19 18:12:37 kennyt Exp $

define ('NO_AUTH', 1);

@include 'include.php';

function upgrade() {
	global $db;

	$thisvers = $db->getOne('select varvalue from '.TBL_CONFIGURATION.' where varname = \'DB_VERSION\'');
	if ($thisvers == DB_VERSION) $upgraded = 1;
	if (!$upgraded or DB::isError($thisvers)) {
		if (!@is_writeable('c_templates')) {
			include('templates/default/base/templatesperm.html');
			exit;
		}
		switch(DB_TYPE) {
			case 'pgsql' :
				$db->query("create table ".TBL_PROJECT_PERM." ( project_id INT4 NOT NULL DEFAULT '0', user_id INT4 NOT NULL DEFAULT '0' )");
				//! TBL_AUTH_GROUP
				break;
			case 'mysql' :
				$db->query("create table if not exists ".TBL_PROJECT_PERM." ( project_id int(11) NOT NULL default '0', user_id int(11) NOT NULL default '0' )");
				if ($thisvers < 2)
					$db->query("alter table ".TBL_AUTH_GROUP." ADD assignable TINYINT DEFAULT 0 NOT NULL AFTER locked");
				break;
			case 'oci8' :
				$db->query("create table ".TBL_PROJECT_PERM." ( project_id number(10) default '0' NOT NULL, user_id number(10) default '0' NOT NULL )");
				//! TBL_AUTH_GROUP
				break;
		}

		if ($thisvers < 2) {
			$db->query("DELETE FROM ".TBL_CONFIGURATION." WHERE varname = 'GROUP_ASSIGN_TO'");
			$db->query("UPDATE ".TBL_AUTH_GROUP." SET assignable = 1 WHERE group_id = 3");
			$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('EMAIL_DISABLED', '0', 'Whether to disable all mail sent from the system', 'bool');");
			/* add db-version attribute */
			$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('DB_VERSION', '".DB_VERSION."', 'Database Version <b>Warning:</b> Changing this might make things go horribly wrong.', 'string')");
		}

		if ($thisvers < 3) {

		}

		/* update to current DB_VERSION */
		$db->query("UPDATE ".TBL_CONFIGURATION." SET varvalue = '".DB_VERSION."' WHERE varname = 'DB_VERSION'");

	}
	include 'templates/default/upgrade-finished.html';
}

if (isset($_gv['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>