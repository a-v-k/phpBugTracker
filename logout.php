<?php

// logout.php - Clear the authentication of a user
// --------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
// ---------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// ---------------------------------------------------------------------

include 'include.php';

$auth->unauth();

include 'templates/'.THEME.'logout.html';

page_close();

?>
