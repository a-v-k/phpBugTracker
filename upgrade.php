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
// $Id: upgrade.php,v 1.12 2002/03/18 15:54:39 bcurtis Exp $

define ('NO_AUTH', 1);
include 'include.php';

function upgrade() {
	global $db;
	
	$upgraded = $db->getOne('select count(*) from '.TBL_BUG.'_seq');
	if (!$upgraded or DB::isError($upgraded)) {
		// Convert the sequences
		if (DB_TYPE == 'mysql') {
			// Just in case we have someone who started using phpbt a long time ago...
			$db->query('update '.TBL_DB_SEQUENCE.' set seq_name = lower(seq_name)');
		}
		$rs = $db->query("select * from ".TBL_DB_SEQUENCE);
		if (DB_TYPE == 'pgsql') {
			while ($rs->fetchInto($row)) {
				$db->query("create sequence {$row['seq_name']}_seq start {$row['nextid']}");
			}
		} else {
			while ($rs->fetchInto($row)) {
				$db->query("create table {$row['seq_name']}_seq (id int unsigned auto_increment not null primary key)");
				$db->query("insert into {$row['seq_name']}_seq values ({$row['nextid']})");
			}
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
