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
// $Id: upgrade.php,v 1.31 2003/04/09 12:11:44 bcurtis Exp $

define ('NO_AUTH', 1);

@include 'include.php';

function upgrade() {
	global $db;

	$upgraded = $db->getOne('select varname from '.TBL_CONFIGURATION.' where varname = \'GROUP_ASSIGN_TO\'');
	if (!$upgraded or DB::isError($upgraded)) {
		if (!@is_writeable('c_templates')) {
			include('templates/default/base/templatesperm.html');
			exit;
		}
		switch(DB_TYPE) {
			case 'pgsql' :
				$db->query("create table ".TBL_PROJECT_PERM." ( project_id INT4 NOT NULL DEFAULT '0', user_id INT4 NOT NULL DEFAULT '0' )");
				break;
			case 'mysql' :
				$db->query("create table ".TBL_PROJECT_PERM." ( project_id int(11) NOT NULL default '0', user_id int(11) NOT NULL default '0' )");
				break;
			case 'oci8' :
				$db->query("create table ".TBL_PROJECT_PERM." ( project_id number(10) default '0' NOT NULL, user_id number(10) default '0' NOT NULL )");
				break;
		}

		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('GROUP_ASSIGN_TO', '3', 'The group to whom bugs can be assigned', 'multi');");
	}
	include 'templates/default/upgrade-finished.html';
}

if (isset($_gv['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
