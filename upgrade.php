<?php

// upgrade.php -- Upgrade from the previous version
// ------------------------------------------------------------------------
// Copyright (c) 2001 - 2004 The phpBugTracker Group
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
// $Id: upgrade.php,v 1.41 2005/07/19 19:25:37 ulferikson Exp $

define ('NO_AUTH', 1);
define ('RAWERROR', true);
define ('THEME', 'default');
$upgrading = true;
include 'include.php';

$comment_text = "";
$log_text = "";
$num_errors = 0;

// Handle a database error
function handle_upgrade_error(&$obj) {
	global $log_text;

	$log_text .= "<div class=\"error\">";
	$log_text .= htmlentities($obj->message).'<br>'.htmlentities($obj->userinfo);
	$log_text .= "</div>\n";
}

function log_query($str) {
	global $db, $log_text, $num_errors;

	$log_text .= "SQL: " . $str . "<br>\n";
	$result = $db->query($str);
	if (DB::isError($result)) {
		$num_errors = $num_errors + 1;
	}
	$log_text .= "<br>\n";
}

function upgrade() {
	global $db, $comment_text, $log_text, $num_errors;

	$thisvers = 0;

	$tmp_log = "Using the following information from config.php:<br>\n";
	$tmp_log .= "DB_TYPE: '".DB_TYPE."'<br>\n";
	$tmp_log .= "DB_HOST: '".DB_HOST."'<br>\n";
	$tmp_log .= "DB_DATABASE: '".DB_DATABASE."'<br>\n";
	$tmp_log .= "DB_USER: '".DB_USER."'<br>\n";
	$tmp_log .= "DB_PASSWORD: '".DB_PASSWORD."'<br>\n";
	$tmp_log .= "<br>\n";

	$db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_upgrade_error");

	$query = 'select count(*) from '.TBL_CONFIGURATION;
	$tmp_log .= "SQL: ".$query."<br>\n";
	$count = $db->getOne($query);
	if (DB::isError($count)) {
		$tmp_log .= $log_text;
		$log_text = "";
		$count = 0;
	}

	$tmp_log .= "<p>count = ".$count."</p>\n";

	if ($count > 30) {
		$query = 'select varvalue from '.TBL_CONFIGURATION.' where varname = \'DB_VERSION\'';
		$tmp_log .= "SQL: ".$query."<br>\n";
		$thisvers = $db->getOne($query);
		if (DB::isError($thisvers)) {
			$tmp_log .= $log_text;
			$log_text = "";
			$thisvers = 0;
		}
	}

	$tmp_log .= "<p>DB_VERSION = ".$thisvers."</p>\n";

	$comment_text .= "Current DB version = $thisvers<br>\n";
	$comment_text .= "Upgrading to version = ".CUR_DB_VERSION."<br>\n";
	$comment_text .= "<br>\n";

	$log_text = $tmp_log;

	if ($thisvers == CUR_DB_VERSION) $upgraded = 1;
	if (!$upgraded) {
		switch(DB_TYPE) {
			case 'pgsql' :
				log_query("create table ".TBL_PROJECT_PERM." ( project_id INT4 NOT NULL DEFAULT '0', user_id INT4 NOT NULL DEFAULT '0' )");
				if ($thisvers < 2) {
					log_query("alter table ".TBL_AUTH_GROUP." ADD assignable INT2");
					log_query("alter table ".TBL_AUTH_GROUP." alter assignable set DEFAULT 0");
					log_query("update ".TBL_AUTH_GROUP." set assignable = 0");
					log_query("alter table ".TBL_AUTH_GROUP." alter assignable set NOT NULL");
				}
				if ($thisvers < 3) {
					log_query("ALTER TABLE ".TBL_USER_PREF." ADD def_results INT4");
					log_query("ALTER TABLE ".TBL_USER_PREF." alter def_results set DEFAULT 20");
					log_query("update ".TBL_USER_PREF." set def_results = 20");
					log_query("ALTER TABLE ".TBL_USER_PREF." alter def_results set NOT NULL");
				}
				if ($thisvers < 4) {
					log_query('ALTER TABLE '.TBL_STATUS.' ADD bug_open INT2');
					log_query("ALTER TABLE ".TBL_STATUS." alter bug_open set DEFAULT 1");
					log_query("update ".TBL_STATUS." set bug_open = 1");
					log_query("ALTER TABLE ".TBL_STATUS." alter bug_open set NOT NULL");
				}
				break;
			case 'mysqli' :
			case 'mysql' :
				log_query("create table if not exists ".TBL_PROJECT_PERM." ( project_id int(11) NOT NULL default '0', user_id int(11) NOT NULL default '0' )");
				if ($thisvers < 2) {
					log_query("alter table ".TBL_AUTH_GROUP." ADD assignable TINYINT DEFAULT 0 NOT NULL AFTER locked");
				}
				if ($thisvers < 3) {
					log_query("ALTER TABLE ".TBL_USER_PREF." ADD def_results INT DEFAULT '20' NOT NULL");
				}
				if ($thisvers < 4) {
					log_query('ALTER TABLE '.TBL_STATUS.' ADD bug_open TINYINT DEFAULT \'1\' NOT NULL');
				}
				break;
			case 'oci8' :
				if (true) {
					$comment_text .= "<div class=\"error\">Oracle is not supported in version 1.0</div>";

					$comment_text .= "<p>An attempt to restore Oracle support has been made (by copy-paste-and-edit), but it is completely UNTESTED! Proceed At Your Own Risk...</p>";
					$comment_text .= "<p>Don't forget to report your success (or failure) story to <a href=\"mailto:phpbt-dev@lists.sourceforge.net\">phpbt-dev@lists.sourceforge.net</a> if you do proceed. We have interest in detailed error reports and patches from people who use Oracle.</p>";
					include 'templates/default/upgrade-finished.html';
					exit;
				}

				log_query("create table ".TBL_PROJECT_PERM." ( project_id number(10) default '0' NOT NULL, user_id number(10) default '0' NOT NULL )");
				if ($thisvers < 2) {
					log_query("alter table ".TBL_AUTH_GROUP." ADD (assignable number(1) default '0' NOT NULL)");
				}
				if ($thisvers < 3) {
					log_query("ALTER TABLE ".TBL_USER_PREF." ADD (def_results number(10) default '20' NOT NULL)");
				}
				if ($thisvers < 4) {
					log_query("ALTER TABLE ".TBL_STATUS." ADD (bug_open number(1) default '1' NOT NULL)");
				}
				break;
		}

		/** Database-independent changes */
		if ($thisvers < 2) {
			log_query("DELETE FROM ".TBL_CONFIGURATION." WHERE varname = 'GROUP_ASSIGN_TO'");
			log_query("UPDATE ".TBL_AUTH_GROUP." SET assignable = 1 WHERE group_id = 3");
			log_query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('EMAIL_DISABLED', '0', 'Whether to disable all mail sent from the system', 'bool');");
			/* add db-version attribute */
			log_query("INSERT INTO ".TBL_CONFIGURATION." VALUES ('DB_VERSION', '".(int)CUR_DB_VERSION."', 'Database Version <b>Warning:</b> Changing this might make things go horribly wrong.', 'string')");
		}

		if ($thisvers < 4) {
			log_query('DELETE FROM '.TBL_CONFIGURATION.' WHERE varname = \'BUG_CLOSED\'');
			$comment_text .= "You must set your Statuses to either open or closed. Default settings should be modified so that \"resolved\", \"closed\", and \"verified\" are shown as being closed, and all other statuses are set to open.<br><br>\n";
			log_query("INSERT INTO ".TBL_AUTH_PERM." (perm_id, perm_name) VALUES (3, 'EditAssignment')");
		}

		/* update to current DB_VERSION */
		log_query("UPDATE ".TBL_CONFIGURATION." SET varvalue = '".CUR_DB_VERSION."' WHERE varname = 'DB_VERSION'");
		if ($num_errors == 0) {
			$comment_text .= "Success!<br>\n";
		}
		else {
			$comment_text .= "Done, but with ".$num_errors." error(s)<br>\n";
		}
	}
	else {
		$comment_text .= "Nothing to do...<br>\n";
	}

	include 'templates/default/upgrade-finished.html';
}

if (isset($_GET['doit'])) {
	upgrade();
} else {
	include 'templates/default/upgrade.html';
}

?>
