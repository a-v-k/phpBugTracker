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
// $Id: config.php,v 1.3 2001/08/23 02:10:18 bcurtis Exp $

define ('INSTALLPATH', '/home/bcurtis/public_html/phpbt');
define ('INSTALLURL', 'http://localhost/~bcurtis/phpbt');

define ('PHPLIBPATH', ''); // If it's not in the include path
define ('JPGRAPH_PATH', ''); // If it's not in the include path

//Database Config
define ('DB_TYPE', 'mysql');  //using PHPlib file naming
define ('DB_HOST', 'localhost');
define ('DB_DATABASE', 'BugTracker');
define ('DB_USER', 'root');
define ('DB_PASSWORD','');

define ('ADMINEMAIL', 'phpbt@bencurtis.com');
define ('ENCRYPTPASS', 0);  // Whether to store passwords encrypted
define ('THEME', 'default/'); // Which set of templates to use
define ('USE_JPGRAPH', 0); // Whether to show images or not
define ('MASK_EMAIL', 1); // Should email addresses be plainly visible?
define ('HIDE_EMAIL', 1); // Should email addresses be hidden for those not logged in?
// Should the query list use the severity colors as the row background color (like SourceForge)
define ('USE_SEVERITY_COLOR', 1);

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
