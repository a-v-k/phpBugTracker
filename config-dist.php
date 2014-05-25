<?php
// config-dist.php - Set up configuration options.  This file is copied
// to config.php in a normal install.
//
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
// $Id: config-dist.php,v 1.41 2008/09/20 21:22:09 brycen Exp $

// Database Config
define ('DB_TYPE', '{db_type}');  // using PEAR::DB naming (mysql, pgsql, etc.)
define ('DB_HOST', '{db_host}'); // hostname of database server
define ('DB_PORT', '{db_port}'); // hostname of database server
define ('DB_DATABASE', '{db_database}'); // database name
define ('DB_USER', '{db_user}'); // username for database connection
define ('DB_PASSWORD', '{db_pass}'); // password for database connection

// Database Table Config
// you can change either the prefix of the table names or each table name individually
define ('CUR_DB_VERSION', 7);  // The version of the database. Copy this to your config.php for upgrades.
define ('TBL_PREFIX', '{tbl_prefix}');	// the prefix for all tables, leave empty to use the old style

define ('TBL_ACTIVE_SESSIONS', TBL_PREFIX.'active_sessions');
define ('TBL_DB_SEQUENCE',     TBL_PREFIX.'db_sequence');
define ('TBL_ATTACHMENT',      TBL_PREFIX.'attachment');
define ('TBL_AUTH_GROUP',      TBL_PREFIX.'auth_group');
define ('TBL_AUTH_PERM',       TBL_PREFIX.'auth_perm');
define ('TBL_AUTH_USER',       TBL_PREFIX.'auth_user');
define ('TBL_BUG',             TBL_PREFIX.'bug');
define ('TBL_BUG_CC',          TBL_PREFIX.'bug_cc');
define ('TBL_BUG_DEPENDENCY',  TBL_PREFIX.'bug_dependency');
define ('TBL_BUG_GROUP',       TBL_PREFIX.'bug_group');
define ('TBL_BUG_HISTORY',     TBL_PREFIX.'bug_history');
define ('TBL_BUG_VOTE',        TBL_PREFIX.'bug_vote');
define ('TBL_COMMENT',         TBL_PREFIX.'comment');
define ('TBL_COMPONENT',       TBL_PREFIX.'component');
define ('TBL_CONFIGURATION',   TBL_PREFIX.'configuration');
define ('TBL_GROUP_PERM',      TBL_PREFIX.'group_perm');
define ('TBL_OS',              TBL_PREFIX.'os');
define ('TBL_PROJECT',         TBL_PREFIX.'project');
define ('TBL_RESOLUTION',      TBL_PREFIX.'resolution');
define ('TBL_SAVED_QUERY',     TBL_PREFIX.'saved_query');
define ('TBL_SEVERITY',        TBL_PREFIX.'severity');
define ('TBL_STATUS',          TBL_PREFIX.'status');
define ('TBL_USER_GROUP',      TBL_PREFIX.'user_group');
define ('TBL_USER_PERM',       TBL_PREFIX.'user_perm');
define ('TBL_USER_PREF',       TBL_PREFIX.'user_pref');
define ('TBL_VERSION',         TBL_PREFIX.'version');
define ('TBL_PROJECT_GROUP',   TBL_PREFIX.'project_group');
define ('TBL_PROJECT_PERM',    TBL_PREFIX.'project_perm');
define ('TBL_DATABASE',	       TBL_PREFIX.'database_server');
define ('TBL_SITE',            TBL_PREFIX.'site');
// New stuff for database DB_VERSION 5
define ('TBL_BOOKMARK',	       TBL_PREFIX.'bookmark');
define ('TBL_PRIORITY',        TBL_PREFIX.'priority');

// Constants
define('STRICT_ERROR_MODE', 0);
define ('ONEDAY', 86400);
define('PEAR_PATH', 'inc/pear/'); // Blank, or path to Pear::DB library directory containing DB.php (See http://pear.php.net/)
require_once (dirname(__FILE__).'/inc/auth.php');

// Mail parameters
define('SMTP_EMAIL', false);
define('SMTP_HOST', "localhost");
define('SMTP_PORT', 25);
define('SMTP_HELO', null);
define('SMTP_AUTH', false);
define('SMTP_AUTH_USER', "");
define('SMTP_AUTH_PASS', "");
define('RETURN_PATH', null);

date_default_timezone_set('Europe/Moscow');
