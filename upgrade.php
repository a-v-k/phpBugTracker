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
// $Id: upgrade.php,v 1.36 2003/07/25 19:22:26 kennyt Exp $

define ('NO_AUTH', 1);

@include 'include.php';

function upgrade() {
	global $db;

	$thisvers = $db->getOne('select varvalue from '.TBL_CONFIGURATION.' where varname = \'DB_VERSION\'');
	if ($thisvers == CUR_DB_VERSION) $upgraded = 1;
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
				if ($thisvers < 2) {
					$db->query("alter table ".TBL_AUTH_GROUP." ADD assignable TINYINT DEFAULT 0 NOT NULL AFTER locked");
				}
				if ($thisvers < 3) {
					$db->query("ALTER TABLE ".TBL_USER_PREF." ADD def_results INT DEFAULT '20' NOT NULL");
				}
				if ($thisvers < 4) {
					$db->query('ALTER TABLE '.TBL_STATUS.' ADD bug_open TINYINT DEFAULT \'1\' NOT NULL');
				}
				break;
			case 'pgsql' :
				//! Missing Alter/Create's
			case 'oci8' :
				$db->query("create table ".TBL_PROJECT_PERM." ( project_id number(10) default '0' NOT NULL, user_id number(10) default '0' NOT NULL )");
				//! TBL_AUTH_GROUP
				//! TBL_USER_PERM.def_results (see mysql)
				break;
		}

		/** Database-independent changes */
		if ($thisvers < 2) {
			$db->query("DELETE FROM ".TBL_CONFIGURATION." WHERE varname = 'GROUP_ASSIGN_TO'");
			$db->query("UPDATE ".TBL_AUTH_GROUP." SET assignable = 1 WHERE group_id = 3");
			$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('EMAIL_DISABLED', '0', 'Whether to disable all mail sent from the system', 'bool');");
			/* add db-version attribute */
			$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('DB_VERSION', '".CUR_DB_VERSION."', 'Database Version <b>Warning:</b> Changing this might make things go horribly wrong.', 'string')");
		}

		if ($thisvers < 4) {
			$db->query('DELETE FROM '.TBL_CONFIGURATION.' WHERE varname = \'BUG_CLOSED\'');
			echo 'You must set your Statuses to either open or closed. Default settings should be modified so that "resolved", "closed", and "verified" are shown as being closed, and all other statuses are set to open.';
		}

		/* update to current DB_VERSION */
		$db->query("UPDATE ".TBL_CONFIGURATION." SET varvalue = '".CUR_DB_VERSION."' WHERE varname = 'DB_VERSION'");

	}
	include 'templates/default/upgrade-finished.html';
}

if (isset($_gv['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
