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
// $Id: upgrade.php,v 1.25 2002/09/23 20:10:05 bcurtis Exp $

define ('NO_AUTH', 1);
define ('STYLE', 'default');

include 'include.php';

function upgrade() {
	global $db;

	$upgraded = $db->getOne('select varname from '.TBL_CONFIGURATION.' where varname = \'BUG_UNCONFIRMED\'');
	if (!$upgraded or DB::isError($upgraded)) {
		if (!@is_writeable('c_templates')) {
			include('templates/default/base/templatesperm.html');
			exit;
		}
		switch(DB_TYPE) {
			case 'pgsql' :
				$db->query("alter table ".TBL_USER_PREF." add saved_queries int2");
				$db->query("alter table ".TBL_USER_PREF." alter saved_queries set default 1");
				$db->query("update ".TBL_USER_PREF." set saved_queries = 1");
				break;
			case 'mysql' :
				$db->query("alter table ".TBL_USER_PREF." add saved_queries tinyint(1) not null default '1' after email_notices");
				$db->query("alter table ".TBL_BUG_HISTORY." change changed_field changed_field varchar(30) not null");
				$db->query("alter table ".TBL_BUG." add database_id int not null after resolution_id, add site_id int not null after database_id, add closed_in_version_id int not null after version_id, add to_be_closed_in_version_id int not null after closed_in_version_id");
				$db->query("create table ".TBL_SITE." (site_id int unsigned NOT NULL default '0', site_name varchar(50) NOT NULL default '', sort_order tinyint(3) unsigned NOT NULL default '0', PRIMARY KEY (site_id))");
				$db->query("create table ".TBL_SITE."_seq (id int unsigned auto_increment not null primary key)");
				$db->query("insert into ".TBL_SITE."_seq values (4)");
				$db->query("create table ".TBL_DATABASE." (database_id int(10) unsigned NOT NULL default '0', database_name varchar(40) NOT NULL default '', sort_order tinyint(3) unsigned NOT NULL default '0', PRIMARY KEY (database_id))");
				$db->query("create table ".TBL_DATABASE."_seq (id int unsigned auto_increment not null primary key)");
				$db->query("insert into ".TBL_DATABASE."_seq values (3)");
				break;
			case 'oci8' :
				break;
		}

		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('SEND_MIME_EMAIL', '1', 'Whether to use MIME quoted-printable encoded emails or not', 'bool')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('BUG_UNCONFIRMED', '1', 'The status to assign a bug when it is first submitted.', 'multi')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('BUG_PROMOTED', '2', 'The status to assign a bug when it is promoted (if enabled).', 'multi')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('BUG_ASSIGNED', '3', 'The status to assign a bug when it is assigned.', 'multi')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('BUG_REOPENED', '4', 'The status to assign a bug when it is reopened.', 'multi')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('BUG_CLOSED', '7', 'The status to assign a bug when it is closed.', 'multi')");
		$db->query("INSERT INTO ".TBL_SITE." VALUES (0,'All',1), (1,'Development',2), (2,'Testing',3), (3,'Staging',4), (4,'Production',5)");
		$db->query("INSERT INTO ".TBL_DATABASE." VALUES (1,'Oracle 8.1.7',1), (2,'MySQL 3.23.49',2), (3,'PostgreSQL 7.1.3',3");
	}
	include 'templates/default/upgrade-finished.html';
}

if (isset($_gv['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
