<?php

// configure.php - Interface for configuration options
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
// $Id: configure.php,v 1.12 2002/11/18 14:32:28 bcurtis Exp $

chdir('..');
define('TEMPLATE_PATH', 'admin');
include 'include.php';

$perm->check('Admin');

if (isset($_pv['submit'])) {
	foreach ($_pv as $k => $v) {
		// Check the jpgraph path to make sure it has a trailing /
		if ($k == 'JPGRAPH_PATH' and strlen($v) and substr($v, -1) != '/') $v .= '/';

		$db->query('update '.TBL_CONFIGURATION." set varvalue = '$v' where varname = '$k'");

		// Refresh the template variable now instead of waiting for the next page load.
		if ($k == 'STYLE') {
			$t->assign('STYLE', $v);
		}
	}
}

$t->assign('vars',  $db->getAll('select * from '.TBL_CONFIGURATION));
$t->wrap('admin/configure.html', 'configuration');

?>


