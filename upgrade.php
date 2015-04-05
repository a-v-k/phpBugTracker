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
// $Id: upgrade.php,v 1.54 2008/09/29 04:12:32 brycen Exp $

define('NO_AUTH', 1);
define('THEME', 'default');
$upgrading = true;
include 'include.php';

$comment_text = "";
$log_text = "";
$num_errors = 0;

//// Handle a database error
//function handle_upgrade_error(&$obj) {
//    global $log_text;
//
//    $log_text .= "<div class=\"error\">";
//    $log_text .= htmlentities($obj->message) . '<br>' . htmlentities($obj->userinfo);
//    $log_text .= "</div>\n";
//}

function log_query($str) {
    global $db, $log_text, $num_errors;

    $log_text .= "SQL: " . $str . "<br>\n";
    try {
        $db->query($str);
    } catch (Exception $exc) {
        $num_errors = $num_errors + 1;
        $log_text .= "<div class=\"error\">";
        $log_text .= htmlentities(get_class($exc) . ':' . $exc->getCode() . ' ' . $exc->getMessage());
        $log_text .= "</div>\n";
    }
    $log_text .= "<br>\n";
}

function upgrade() {
    global $db, $comment_text, $log_text, $num_errors;

    $thisvers = false;

    $tmp_log = "Using the following information from config.php:<br>\n";
    $tmp_log .= "DB_TYPE: '" . DB_TYPE . "'<br>\n";
    $tmp_log .= "DB_HOST: '" . DB_HOST . "'<br>\n";
    $tmp_log .= "DB_DATABASE: '" . DB_DATABASE . "'<br>\n";
    //$tmp_log .= "DB_USER: '".DB_USER."'<br>\n";
    //$tmp_log .= "DB_PASSWORD: '".DB_PASSWORD."'<br>\n";
    $tmp_log .= "<br>\n";

    //$db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_upgrade_error");
//    $query = 'select count(*) from ' . TBL_CONFIGURATION;
//    $tmp_log .= "<p>SQL: " . $query . "<br>\n";
//    $count = $db->getOne($query);
//    if (DB::isError($count)) {
//        $tmp_log .= $log_text;
//        $log_text = "";
//        $count = 0;
//    }
//    $tmp_log .= "count = " . $count . "</p>\n";
    //if ($count > 30) {
    $query = 'select varvalue from ' . TBL_CONFIGURATION . ' where varname = \'DB_VERSION\'';
    $tmp_log .= "<p>SQL: " . $query . "<br>\n";
    try {
        $thisvers = $db->getOne($query);
    } catch (Exception $exc) {
        $tmp_log .= "<div class=\"error\">";
        $tmp_log .= "Error while getting current DB version from DB: ";
        $tmp_log .= htmlentities(get_class($exc) . ':' . $exc->getCode() . ' ' . $exc->getMessage());
        $tmp_log .= "</div>\n";
    }
    //}

    if (!is_numeric($thisvers)) {
        die('failed to detect current database version');
    }
    $tmp_log .= "DB_VERSION = " . $thisvers . "</p>\n";

    $upgradeTo = $thisvers + 1;
    $comment_text .= "Current DB version = $thisvers<br>\n";
    $comment_text .= "Release DB version = " . CUR_DB_VERSION . "<br>\n";
    if ($thisvers < CUR_DB_VERSION) {
        $comment_text .= "Upgrading to version = " . $upgradeTo . "<br>\n";
    }
    $comment_text .= "<br>\n";

    $log_text = $tmp_log;

    $needUpdate = 1;
    if ($thisvers >= CUR_DB_VERSION) {
        $needUpdate = 0;
    }
    if ($needUpdate == 1) {
        switch (DB_TYPE) {
            case 'pgsql' :
                if (false) {
                    $comment_text .= "<div class=\"error\">Upgrading of old installs is still unsupported</div>";

                    $comment_text .= "<p>An upgrade script has been written, but it is completely UNTESTED! Proceed At Your Own Risk...</p>";
                    $comment_text .= "<p>Don't forget to report your success (or failure) story to sourceforge. We have interest in detailed error reports and patches from people who use PostgreSQL.</p>";
                    include 'templates/default/upgrade-finished.html';
                    exit;
                }

                if ($thisvers < 2) {
                    log_query("alter table " . TBL_AUTH_GROUP . " ADD assignable INT2");
                    log_query("alter table " . TBL_AUTH_GROUP . " alter assignable set DEFAULT 0");
                    log_query("update " . TBL_AUTH_GROUP . " set assignable = 0");
                    log_query("alter table " . TBL_AUTH_GROUP . " alter assignable set NOT NULL");
                }
                if ($thisvers < 3) {
                    log_query("ALTER TABLE " . TBL_USER_PREF . " ADD def_results INT4");
                    log_query("ALTER TABLE " . TBL_USER_PREF . " alter def_results set DEFAULT 20");
                    log_query("update " . TBL_USER_PREF . " set def_results = 20");
                    log_query("ALTER TABLE " . TBL_USER_PREF . " alter def_results set NOT NULL");
                }
                if ($thisvers < 4) {
                    log_query('ALTER TABLE ' . TBL_STATUS . ' ADD bug_open INT2');
                    log_query("ALTER TABLE " . TBL_STATUS . " alter bug_open set DEFAULT 1");
                    log_query("update " . TBL_STATUS . " set bug_open = 1");
                    log_query("ALTER TABLE " . TBL_STATUS . " alter bug_open set NOT NULL");
                }
                if ($thisvers < 5) {
                    log_query("create table " . TBL_PRIORITY . "( priority_id INT4  NOT NULL DEFAULT '0', priority_name varchar(30) NOT NULL DEFAULT '', priority_desc TEXT DEFAULT '' NOT NULL, sort_order INT2  NOT NULL DEFAULT '0', priority_color varchar(10) NOT NULL DEFAULT '#FFFFFF', PRIMARY KEY  (priority_id) )");
                    log_query("create table " . TBL_BOOKMARK . "( user_id INT4  NOT NULL DEFAULT '0', bug_id INT4  NOT NULL DEFAULT '0' )");

                    log_query("ALTER TABLE " . TBL_COMPONENT . " ADD sort_order INT2");
                    log_query("ALTER TABLE " . TBL_COMPONENT . " alter sort_order set DEFAULT 0");
                    log_query("ALTER TABLE " . TBL_COMPONENT . " alter sort_order set NOT NULL");
                    log_query("ALTER TABLE " . TBL_VERSION . " ADD sort_order INT2");
                    log_query("ALTER TABLE " . TBL_VERSION . " alter sort_order set DEFAULT 0");
                    log_query("ALTER TABLE " . TBL_VERSION . " alter sort_order set NOT NULL");
                    log_query("CREATE SEQUENCE " . TBL_PRIORITY . "_seq START 6");
                    log_query("ALTER TABLE " . TBL_AUTH_GROUP . " ADD is_role INT2");
                    log_query("ALTER TABLE " . TBL_AUTH_GROUP . " alter is_role set DEFAULT 0");
                    log_query("ALTER TABLE " . TBL_AUTH_GROUP . " alter is_role set NOT NULL");
                    log_query("DROP SEQUENCE " . TBL_AUTH_GROUP . "_seq");
                    log_query("CREATE SEQUENCE " . TBL_AUTH_GROUP . "_seq START 10");

                    log_query("create table " . TBL_PROJECT_PERM . " ( project_id INT4 NOT NULL DEFAULT '0', user_id INT4 NOT NULL DEFAULT '0' )");
                }
                break;
            case 'mysqli' :
            case 'mysql' :
                if (false) {
                    $comment_text .= "<div class=\"error\">Upgrading of old installs is probably available.</div>";

                    $comment_text .= "<p>An upgrade script has been written, but it is lightly tested! Proceed At Your Own Risk...</p>";
                    $comment_text .= "<p>Don't forget to report your success (or failure) story to sourceforge.net";
                    include 'templates/default/upgrade-finished.html';
                    exit;
                }

                if ($thisvers < 2) {
                    log_query("alter table " . TBL_AUTH_GROUP . " ADD assignable TINYINT DEFAULT 0 NOT NULL AFTER locked");
                }
                if ($thisvers < 3) {
                    log_query("ALTER TABLE " . TBL_USER_PREF . " ADD def_results INT DEFAULT '20' NOT NULL");
                }
                if ($thisvers < 4) {
                    log_query('ALTER TABLE ' . TBL_STATUS . ' ADD bug_open TINYINT DEFAULT \'1\' NOT NULL');
                }
                if ($thisvers < 5) {
                    log_query("create table if not exists " . TBL_PRIORITY . " ( priority_id int(10) unsigned NOT NULL default '0', priority_name varchar(30) NOT NULL default '', priority_desc text NOT NULL, sort_order tinyint(3) unsigned NOT NULL default '0', priority_color varchar(10) NOT NULL default '#FFFFFF', PRIMARY KEY  (priority_id) )");
                    log_query("create table if not exists " . TBL_BOOKMARK . " ( user_id int(10) unsigned NOT NULL default '0', bug_id int(10) unsigned NOT NULL default '0' )");
                    log_query("alter table " . TBL_COMPONENT . " ADD sort_order tinyint(3) unsigned NOT NULL default '0' AFTER active");
                    log_query("alter table " . TBL_VERSION . " ADD sort_order tinyint(3) unsigned NOT NULL default '0' AFTER active");
                    log_query("CREATE TABLE IF NOT EXISTS " . TBL_PRIORITY . "_seq (id int unsigned auto_increment not null primary key)");
                    log_query("INSERT INTO " . TBL_PRIORITY . "_seq values (5)");
                    log_query("alter table " . TBL_AUTH_GROUP . " ADD is_role tinyint(1) unsigned NOT NULL default '0'");
                    log_query("DROP TABLE " . TBL_AUTH_GROUP . "_seq");
                    log_query("CREATE TABLE IF NOT EXISTS " . TBL_AUTH_GROUP . "_seq (id int unsigned auto_increment not null primary key)");
                    log_query("INSERT INTO " . TBL_AUTH_GROUP . "_seq values (9)");
                    log_query("create table if not exists " . TBL_PROJECT_PERM . " ( project_id int(11) NOT NULL default '0', user_id int(11) NOT NULL default '0' )");
                }
                if ($thisvers < 6) {
                    log_query("create INDEX bug_id_attachment ON " . TBL_ATTACHMENT . " (bug_id);");
                    log_query("create INDEX bug_id_bookmark   ON " . TBL_BOOKMARK . " (bug_id);");
                    log_query("create INDEX bug_id_comment    ON " . TBL_COMMENT . " (bug_id);");
                }
                if ($upgradeTo == 8) {
                    log_query("alter table " . TBL_ATTACHMENT . " engine=InnoDB;");
                }
                if ($upgradeTo == 9) {
                    log_query("alter table " . TBL_ATTACHMENT . " add column bytes longblob after file_name;");
                }
                if ($upgradeTo == 10) {
                    log_query("alter table " . TBL_BUG . " engine=InnoDB;");
                }
                if ($upgradeTo == 11) {
                    log_query("alter table " . TBL_COMMENT . " engine=InnoDB;");
                }
                if ($upgradeTo == 12) {
                    log_query("alter table " . TBL_BOOKMARK . " engine=InnoDB;");
                }
                if ($upgradeTo == 13) {
                    //log_query("alter table " . TBL_ACTIVE_SESSIONS . " engine=InnoDB;");
                    //log_query("alter table " . TBL_DB_SEQUENCE . " engine=InnoDB;");
                    log_query("alter table " . TBL_AUTH_GROUP . " engine=InnoDB;");
                    log_query("alter table " . TBL_AUTH_PERM . " engine=InnoDB;");
                    log_query("alter table " . TBL_AUTH_USER . " engine=InnoDB;");
                }
                if ($upgradeTo == 14) {
                    log_query("alter table " . TBL_BUG_CC . " engine=InnoDB;");
                    log_query("alter table " . TBL_BUG_DEPENDENCY . " engine=InnoDB;");
                    log_query("alter table " . TBL_BUG_GROUP . " engine=InnoDB;");
                    log_query("alter table " . TBL_BUG_HISTORY . " engine=InnoDB;");
                    log_query("alter table " . TBL_BUG_VOTE . " engine=InnoDB;");
                }
                if ($upgradeTo == 15) {
                    log_query("alter table " . TBL_COMPONENT . " engine=InnoDB;");
                    log_query("alter table " . TBL_CONFIGURATION . " engine=InnoDB;");
                    log_query("alter table " . TBL_GROUP_PERM . " engine=InnoDB;");
                    log_query("alter table " . TBL_OS . " engine=InnoDB;");
                    log_query("alter table " . TBL_PROJECT . " engine=InnoDB;");
                    log_query("alter table " . TBL_RESOLUTION . " engine=InnoDB;");
                }
                if ($upgradeTo == 16) {
                    log_query("alter table " . TBL_SAVED_QUERY . " engine=InnoDB;");
                    log_query("alter table " . TBL_SEVERITY . " engine=InnoDB;");
                    log_query("alter table " . TBL_STATUS . " engine=InnoDB;");
                    log_query("alter table " . TBL_USER_GROUP . " engine=InnoDB;");
                    log_query("alter table " . TBL_USER_PERM . " engine=InnoDB;");
                    log_query("alter table " . TBL_USER_PREF . " engine=InnoDB;");
                }
                if ($upgradeTo == 17) {
                    log_query("alter table " . TBL_VERSION . " engine=InnoDB;");
                    log_query("alter table " . TBL_PROJECT_GROUP . " engine=InnoDB;");
                    log_query("alter table " . TBL_PROJECT_PERM . " engine=InnoDB;");
                    log_query("alter table " . TBL_DATABASE . " engine=InnoDB;");
                    log_query("alter table " . TBL_SITE . " engine=InnoDB;");
                    log_query("alter table " . TBL_PRIORITY . " engine=InnoDB;");
                }
                if ($upgradeTo == 20) {
                    log_query("alter table " . TBL_VERSION . " engine=InnoDB;"); // just for upgrade testing
                }
                if ($upgradeTo == 21) {
                    log_query("alter table " . TBL_AUTH_USER . " modify column password char(60) not null default '';"); // for future use password_hash
                }

                break;
            case 'oci8' :
                if (true) {
                    $comment_text .= "<div class=\"error\">Upgrading of old installs is still unsupported</div>";

                    $comment_text .= "<p>An Oracle upgrade script has been written, but it is completely UNTESTED! Proceed At Your Own Risk...</p>";
                    $comment_text .= "<p>Don't forget to report your success (or failure) story to sourceforge if you do proceed. We have interest in detailed error reports and patches from people who use Oracle.</p>";
                    include 'templates/default/upgrade-finished.html';
                    exit;
                }

                log_query("create table " . TBL_PROJECT_PERM . " ( project_id number(10) default '0' NOT NULL, user_id number(10) default '0' NOT NULL )");
                if ($thisvers < 2) {
                    log_query("alter table " . TBL_AUTH_GROUP . " ADD (assignable number(1) default '0' NOT NULL)");
                }
                if ($thisvers < 3) {
                    log_query("ALTER TABLE " . TBL_USER_PREF . " ADD (def_results number(10) default '20' NOT NULL)");
                }
                if ($thisvers < 4) {
                    log_query("ALTER TABLE " . TBL_STATUS . " ADD (bug_open number(1) default '1' NOT NULL)");
                }
                if ($thisvers < 5) {
                    log_query("create table " . TBL_PRIORITY . " ( priority_id number(3)   default '0' NOT NULL, priority_name varchar2(30)  default '' NOT NULL, priority_desc varchar2(4000) NOT NULL, sort_order number(3)   default '0' NOT NULL, priority_color varchar2(10)  default '#FFFFFF' NOT NULL, PRIMARY KEY  (priority_id) )");
                    log_query("create table " . TBL_BOOKMARK . " ( user_id number(10) default '0' NOT NULL, bug_id number(10) default '0' NOT NULL )");
                    log_query("ALTER TABLE " . TBL_COMPONENT . " ADD ( sort_order number(3) default '0' NOT NULL )");
                    log_query("ALTER TABLE " . TBL_VERSION . " ADD ( sort_order number(3) default '0' NOT NULL )");
                    log_query("CREATE SEQUENCE " . TBL_PRIORITY . "_seq START WITH 6 NOCACHE");
                    log_query("ALTER TABLE " . TBL_AUTH_GROUP . " ADD ( is_role number(1) default '0' NOT NULL )");
                    log_query("DROP SEQUENCE " . TBL_AUTH_GROUP . "_seq");
                    log_query("CREATE SEQUENCE " . TBL_AUTH_GROUP . "_seq START WITH 10 NOCACHE");
                }
                break;
        }

        /** Database-independent changes */
        if ($thisvers < 2) {
            log_query("DELETE FROM " . TBL_CONFIGURATION . " WHERE varname = 'GROUP_ASSIGN_TO'");
            log_query("UPDATE " . TBL_AUTH_GROUP . " SET assignable = 1 WHERE group_id = 3");
            log_query("INSERT INTO " . TBL_CONFIGURATION . " VALUES ('EMAIL_DISABLED', '0', 'Whether to disable all mail sent from the system', 'bool');");
            /* add db-version attribute */
            log_query("INSERT INTO " . TBL_CONFIGURATION . " VALUES ('DB_VERSION', '" . (int) CUR_DB_VERSION . "', 'Database Version <b>Warning:</b> Changing this might make things go horribly wrong.', 'string')");
        }

        if ($thisvers < 4) {
            log_query('DELETE FROM ' . TBL_CONFIGURATION . ' WHERE varname = \'BUG_CLOSED\'');
            $comment_text .= "You must set your Statuses to either open or closed. Default settings should be modified so that \"resolved\", \"closed\", and \"verified\" are shown as being closed, and all other statuses are set to open.<br><br>\n";
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (3, 'EditAssignment')");
        }

        if ($thisvers < 5) {
            //log_query("DELETE FROM ".TBL_AUTH_GROUP." WHERE 1"); // Note mysqlism
            //log_query("DELETE FROM ".TBL_AUTH_PERM."  WHERE 1"); // Note mysqlism
            //log_query("DELETE FROM ".TBL_GROUP_PERM." WHERE 1"); // Note mysqlism
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, locked) VALUES (1, 'Admin', 1)");
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, locked) VALUES (2, 'User', 1)");
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, locked) VALUES (3, 'Developer', 0)");
            //log_query("INSERT INTO ".TBL_AUTH_GROUP." (group_id, group_name, locked) VALUES (4, 'Manager', 1)");
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, is_role, locked) VALUES (5, 'Guest', 1, 1)");
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, is_role, locked) VALUES (6, 'User',  1, 1)");  // Magic don't edit
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, is_role, locked) VALUES (7, 'Reporter', 1, 1)");
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, is_role, locked) VALUES (8, 'Assignee', 1, 1)");
            log_query("INSERT INTO " . TBL_AUTH_GROUP . " (group_id, group_name, is_role, locked) VALUES (9, 'Owner', 1, 1)");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (1, 'Admin')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (2, 'AddBug')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (3, 'EditAssignment')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (4, 'Assignable')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (5, 'EditBug')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (6, 'CloseBug')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (7, 'CommentBug')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (8, 'EditPriority')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (9, 'EditStatus')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (10, 'EditSeverity')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (11, 'EditResolution')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (12, 'EditProject')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (13, 'EditComponent')");
            log_query("INSERT INTO " . TBL_AUTH_PERM . " (perm_id, perm_name) VALUES (14, 'ManageBug')");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (1, 1)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (5, 7)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (6, 2)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (7, 5)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (7, 10)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (3, 4)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (3, 5)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (8, 8)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (8, 9)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (8, 11)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (4, 3)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (4, 6)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (4, 12)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (4, 13)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (4, 14)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (9, 6)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (9, 7)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (9, 8)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (9, 9)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (9, 10)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (9, 11)");
            log_query("INSERT INTO " . TBL_GROUP_PERM . " (group_id, perm_id) VALUES (9, 13)");
            log_query("INSERT INTO " . TBL_CONFIGURATION . " VALUES ('USE_PRIORITY_COLOR','0','Should the query list use the priority colors as the row background color','bool')");
            log_query("INSERT INTO " . TBL_PRIORITY . " VALUES (1,'Low','Fix if possible',1,'#dadada')");
            log_query("INSERT INTO " . TBL_PRIORITY . " VALUES (2,'Medium Low','Must fix before final',2,'#dad0d0')");
            log_query("INSERT INTO " . TBL_PRIORITY . " VALUES (3,'Medium','Fix before next milestone (alpha, beta, etc.)',3,'#dac0c0')");
            log_query("INSERT INTO " . TBL_PRIORITY . " VALUES (4,'Medium High','Fix as soon as possible',4,'#dab0b0')");
            log_query("INSERT INTO " . TBL_PRIORITY . " VALUES (5,'High','Fix immediately',5,'#daaaaa')");
            log_query("INSERT INTO " . TBL_CONFIGURATION . " VALUES ('NEW_ACCOUNTS_GROUP', 'User', 'The group assigned to new user accounts', 'string')");
            log_query("UPDATE " . TBL_AUTH_USER . " SET bug_list_fields=null"); // Incompatible change, blow away user settings here
            log_query("INSERT INTO " . TBL_AUTH_USER . " (user_id, login, first_name, last_name, email, password, active, bug_list_fields) values (0, 'Anonymous User', 'Anonymous', 'User', '', '', 0, null)");
        }

        if ($thisvers < 7) {
            log_query("INSERT INTO " . TBL_CONFIGURATION . " VALUES ('USE_SEVERITY_COLOR_LINE','0','Should the query list use the severity colors as the row background color (like SourceForge)','bool');");
            log_query("INSERT INTO " . TBL_CONFIGURATION . " VALUES ('USE_PRIORITY_COLOR_LINE','0','Should the query list use the priority colors as the row background color','bool');");
            log_query("update " . TBL_CONFIGURATION . " set varvalue = 1, description = 'Should the query list use the severity colors as the field background color' where varname = 'USE_SEVERITY_COLOR'");
            log_query("update " . TBL_CONFIGURATION . " set varvalue = 1, description = 'Should the query list use the priority colors as the field background color' where varname = 'USE_PRIORITY_COLOR'");
        }

        if ($num_errors == 0) {
            /* update to current DB_VERSION */
            log_query("UPDATE " . TBL_CONFIGURATION . " SET varvalue = '" . $upgradeTo . "' WHERE varname = 'DB_VERSION'");
            $comment_text .= "Success!<br>\n";
        } else {
            $comment_text .= "Done, but with " . $num_errors . " error(s) <br>\n";
            $comment_text .= "You need check upgrade.php script and your db to resolve errors <br>\n";
        }
    } else {
        $comment_text .= "Nothing to do...<br>\n";
    }

    $comment_text .= "<br/><br/><font size='-1'>$log_text</font>";

    require('templates/default/upgrade-finished.html.php');
}

if (filter_input(INPUT_GET, 'doit') == 1) {
    upgrade();
} else {
    $new_db_rev = CUR_DB_VERSION;
    require('templates/default/upgrade.html');
}
