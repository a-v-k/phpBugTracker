<?php

// css.php - Kick out the CSS
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
// $Id: css.php,v 1.8 2002/04/03 01:00:52 bcurtis Exp $

$colors['default'] = array(
	'link' => '#006699',
	'vlink' => '#006699',
	'body' => '#000000',
	'body-bg' => '#ffffff',
	'td' => '',
	'tr.alt-bg' => '#dddddd',
	'td.head-bg' => '#eeeeee',
	'td.head-selected-bg' => '#bbbbbb',
	'th-bg' => '#eeeeee',
	'th' => ''
	);

$colors['black'] = array(
	'link' => '#cecece',
	'vlink' => '#cecece',
	'body' => '#ffffff',
	'body-bg' => '#000000',
	'td' => '#ffffff',
	'tr.alt-bg' => '#4e4e4e',
	'td.head-bg' => '#4a4a4a',
	'td.head-selected-bg' => '#6a6a6a',
	'th-bg' => '#4a4a4a',
	'th' => '#ffffff'
	);
	
$color = $colors['default'];

?>
body { 
	font-size: 12px; 
	color: <?php echo $color['body'] ?>;
	background-color: <?php echo $color['body-bg'] ?>;
	}

a:link      { text-decoration: none; color: <?php echo $color['link'] ?>; }
a:visited   { text-decoration: none; color: <?php echo $color['vlink'] ?>; }   
a:active    { text-decoration: none }
a:hover     { text-decoration: underline; }

td { 
  font-family: "Arial","Helvetica","MS Sans Serif","Sans-Serif"; 
  font-size: 12px; 
	color: <?php echo $color['td'] ?>;
	}
	
tr.alt {
	background-color: <?php echo $color['tr.alt-bg'] ?>;
	}
	
td.head {
	font-weight: bold; 
	text-align: center;
	background-color: <?php echo $color['td.head-bg'] ?>;
	}
	
td.center {
	text-align: center;
	}
		
th { 
  font-family: "Arial","Helvetica","MS Sans Serif","Sans-Serif"; 
  font-size: 12px;
	color: <?php echo $color['th'] ?>;
	background-color: <?php echo $color['th-bg'] ?>;
	}
	
th.selected {
	font-weight: bold; 
	text-align: center;
	background-color: <?php echo $color['td.head-selected-bg'] ?>;
	}

select {
  font-family: "Arial","Helvetica","MS Sans Serif","Sans-Serif"; 
	font-size: 12px;
	}
	
input {
	font-size: 12px;
	}

input[type="file"] {
  font-family: "Arial","Helvetica","MS Sans Serif","Sans-Serif"; 
	font-size: 12px;
	}
	
input[type="submit"] {
  font-family: "Arial","Helvetica","MS Sans Serif","Sans-Serif"; 
	}
	
input[type="reset"] {
  font-family: "Arial","Helvetica","MS Sans Serif","Sans-Serif"; 
	}
	
textarea {
	font-size: 12px;
	}
	
.navfont { 
  font-family: "Verdana","Arial","Helvetica","MS Sans Serif","Sans-Serif";
  font-size: 10px; 
	}

.login-box {
	vertical-align: text-bottom; 
	font-size: 10px; 
	}

input[type="text"].login-box { 
	padding-top: 0.1em; 
	padding-left: 0.2em; 
	padding-right: 0.2em; 
	}

.banner { 
	text-align: center; 
	font-weight: bold;
	padding: 1.5em;
	font-size: 16px;
}
	
.error {
	text-align: center;
	font-weight: bold;
	color: #ff0000;
	padding-bottom: 0.1em;
	}

.result {
	text-align: center;
	font-weight: bold;
	color: #0000ff;
	padding-bottom: 0.1em;
	}

a.sidenavlink {
	color: #000000;
	text-decoration: none;
	}
	
.nav {
	vertical-align: middle;
	background: #9999ff;
	color: #ffffff;
	font-family: "Arial", sans-serif;
	font-size: 16px;
	font-weight: bold;
	border-width: 0px 0px 3px 0px;
	border-color: #000000;
	border-style: solid;
	}
	
.navinput {
	vertical-align: middle;
	background: #ffffff;
	color: #00000;
	font-family: "Arial", sans-serif;
	font-size: 12px;
	}
	
.sidenav {
	vertical-align: top;
	background: #9999ff;
	color: #ffffff;
	font-family: "Verdana", "Tahoma", "Helvetica", sans-serif;
	font-size: 14px;
	font-weight: bold;
	}
	
.bottomnav {
	vertical-align: middle;
	background: #ffffcc;
	color: #000000;
	font-family: "Arial", sans-serif;
	font-size: 10px;
	font-weight: bold;
	}
	
.bottomnavinput {
	vertical-align: middle;
	color: #00000;
	font-family: "Arial", sans-serif;
	font-size: 10px;
	}
	
.topnavbox {
	vertical-align: middle;
	background: #9999ff;
	color: #ffffff;
	font-family: "Arial", sans-serif;
	font-size: 14px;
	font-weight: bold;
	border-width: 1px 0px 1px 0px;
	border-color: #ffffff;
	border-style: solid;
	padding: 5px;
	cursor: pointer;
	cursor: hand;
	}
	
.navbox {
	vertical-align: middle;
	background: #9999ff;
	color: #ffffff;
	font-family: "Arial", sans-serif;
	font-size: 14px;
	font-weight: bold;
	border-width: 0px 0px 1px 0px;
	border-color: #ffffff;
	border-style: solid;
	padding: 5px;
	cursor: pointer;
	cursor: hand;
	}
	
.navboxselected {
	vertical-align: middle;
	background: #ffffcc;
	color: #ffffff;
	font-family: "Arial", sans-serif;
	font-size: 14px;
	font-weight: bold;
	border-width: 0px 0px 1px 0px;
	border-color: #ffffff;
	border-style: solid;
	padding: 5px;
	cursor: pointer;
	cursor: hand;
	}
	
.maincontent {
	vertical-align: top;
	background: #ffffff;
	color: #000000;
	font-family: "Arial", sans-serif;
	font-size: 12px;
	padding: 5px;
	}
	
.info {
	font-size: 10px;
	color: <?php echo $color['th'] ?>;
	background:  <?php echo $color['th-bg'] ?>;
	border-width: 1px 1px 1px 1px;
	border-color: #000000;
	border-style: solid;
	padding: 5px;
	text-align: center;
}

.header-col { font-weight: bold; }
.center-col { text-align: center; }
