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
// $Id: upgrade.php,v 1.21 2002/04/03 18:18:02 bcurtis Exp $

define ('NO_AUTH', 1);
include 'include.php';

function upgrade() {
	global $db;
	
	// Note, no upgrades for oracle since we didn't support oracle before 0.8.0
	$upgraded = $db->getOne('select varname from '.TBL_CONFIGURATION.' where varname = \'FORCE_LOGIN\'');
	if (!$upgraded or DB::isError($upgraded)) {
		if (!@include('Smarty.class.php')) { // Template class
			die('<br><br>
			<div align="center">The Smarty templates class is not in your include path.
			Without this class being available, phpBugTracker will not be able to work.
			Please visit <a href="http://www.phpinsider.com/php/code/Smarty/">the smarty
			website</a> and install the package.  Please reload this page when smarty 
			has been installed.</div>
			');
		}
		if (!@is_writeable('c_templates')) {
			die('<br><br>
			<div align="center">The "c_templates" subdirectory is not writeable by the 
			web process.  This needs to be corrected before the upgrade can proceed 
			so the templates can be compiled by smarty.  Please reload this page when 
			this has been corrected.</div>
			');
		}
		// Convert the sequences
		if (DB_TYPE == 'mysql') {
			// Just in case we have someone who started using phpbt a long time ago...
			$db->query('update '.TBL_DB_SEQUENCE.' set seq_name = lower(seq_name)');
		}
		$rs = $db->query("select * from ".TBL_DB_SEQUENCE);
		if (DB_TYPE == 'pgsql') {
			// Set up the user prefs table
			$db->query("CREATE TABLE ".TBL_USER_PREF." ( user_id INT4  NOT NULL DEFAULT '0', email_notices INT2  NOT NULL DEFAULT '1', PRIMARY KEY  (user_id) )");
			$db->query("insert into ".TBL_USER_PREF." (user_id) select user_id from ".TBL_AUTH_USER);
			// Move the sequences
			while ($rs->fetchInto($row)) {
				$db->query("create sequence {$row['seq_name']}_seq start {$row['nextid']}");
			}
		} else {
			// Set up the user prefs table
			$db->query("CREATE TABLE ".TBL_USER_PREF." ( user_id int(11) NOT NULL default '0', email_notices tinyint(1) NOT NULL default '1', PRIMARY KEY  (user_id) )");
			$db->query("insert into ".TBL_USER_PREF." (user_id) select user_id from ".TBL_AUTH_USER);
			// Move the sequences
			while ($rs->fetchInto($row)) {
				$db->query("create table {$row['seq_name']}_seq (id int unsigned auto_increment not null primary key)");
				$db->query("insert into {$row['seq_name']}_seq values ({$row['nextid']})");
			}
		}
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('RECALL_LOGIN','0','Enable use of cookies to store username between logins','bool')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('SHOW_PROJECT_SUMMARIES', '1', 'Itemize bug stats by project on the home page', 'bool')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('FORCE_LOGIN', '0', 'Force users to login before being able to use the bug tracker', 'bool')");
		$db->query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('STYLE', 'default', 'The CSS file to use (color scheme)', 'multi')");
	}
	include 'templates/default/upgrade-finished.html';
}

if (isset($_gv['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
