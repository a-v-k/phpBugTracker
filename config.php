<?php

// config.php - Set up configuration options
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
// $Id: config.php,v 1.6 2001/09/01 15:44:20 mohni Exp $

define ('INSTALLPATH', '/home/bcurtis/public_html/phpbt');
define ('INSTALLURL', 'http://localhost/~bcurtis/phpbt');

define ('PHPLIBPATH', ''); // If not in the include path
define ('JPGRAPH_PATH', ''); // If not in the include path

//Database Config
define ('DB_TYPE', 'mysql');  //using PHPlib file naming
define ('DB_HOST', 'localhost');
define ('DB_DATABASE', 'BugTracker');
define ('DB_USER', 'root');
define ('DB_PASSWORD', '');

//Database Table Config
//you can change either the prefix of the table names or each table name individualy
//define ('TBL_PREFIX', 'phpbt_');	//the prefix for all tables
define ('TBL_PREFIX', '');	//the prefix for all tables, leave empty to use the old style
define ('TBL_ATTACHMENT',    TBL_PREFIX.'attachment');
define ('TBL_BUG',           TBL_PREFIX.'bug');
define ('TBL_BUG_HISTORY',   TBL_PREFIX.'bug_history');
define ('TBL_COMMENT',       TBL_PREFIX.'comment');
define ('TBL_COMPONENT',     TBL_PREFIX.'component');
define ('TBL_PROJECT',       TBL_PREFIX.'project');
define ('TBL_RESOLUTION',    TBL_PREFIX.'resolution');
define ('TBL_SAVED_QUERY',   TBL_PREFIX.'saved_query');
define ('TBL_SEVERITY',      TBL_PREFIX.'severity');
define ('TBL_STATUS',        TBL_PREFIX.'status');
define ('TBL_AUTH_USER',     TBL_PREFIX.'auth_user');
define ('TBL_VERSION',       TBL_PREFIX.'version');
define ('TBL_OS',            TBL_PREFIX.'os');
define ('TBL_AUTH_GROUP',    TBL_PREFIX.'auth_group');
define ('TBL_AUTH_PERM',     TBL_PREFIX.'auth_perm');
define ('TBL_USER_GROUP',    TBL_PREFIX.'user_group');
define ('TBL_USER_PERM',     TBL_PREFIX.'user_perm');
define ('TBL_GROUP_PERM',    TBL_PREFIX.'group_perm');
define ('TBL_BUG_GROUP',     TBL_PREFIX.'bug_group');
define ('TBL_PROJECT_GROUP', TBL_PREFIX.'project_group');

define ('ADMINEMAIL', 'phpbt@bencurtis.com');
define ('ENCRYPTPASS', 0);  // Whether to store passwords encrypted
define ('THEME', 'default/'); // Which set of templates to use
define ('USE_JPGRAPH', 0); // Whether to show images or not
define ('MASK_EMAIL', 1); // Should email addresses be plainly visible?
define ('HIDE_EMAIL', 1); // Should email addresses be hidden for those not logged in?
// Should the query list use the severity colors as the row background color (like SourceForge)
define ('USE_SEVERITY_COLOR', 1);
define ('EMAIL_IS_LOGIN', 1); // Whether to use email addresses as logins

// Sub-dir of the INSTALLPATH - Needs to be writeable by the web process
define ('ATTACHMENT_PATH', 'attachments');
// Maximum size (in bytes) of an attachment
// This will not override the settings in php.ini if php.ini has a lower limit
define ('ATTACHMENT_MAX_SIZE', 2097152);
define ('ONEDAY', 86400);
define ('DATEFORMAT', 'm-d-Y');
define ('TIMEFORMAT', 'g:i A');

require PHPLIBPATH.'db_'.DB_TYPE.'.inc';
require PHPLIBPATH.'ct_sql.inc';
require PHPLIBPATH.'session.inc';
require PHPLIBPATH.'auth.inc';
require PHPLIBPATH.'perm.inc';
require PHPLIBPATH.'page.inc';
require PHPLIBPATH.'template.inc';

// Localization - include the file with the desired language
include INSTALLPATH.'/languages/en.php';

?>
