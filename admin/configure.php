<?php

// configure.php - Interface for configuration options
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
// $Id: configure.php,v 1.2 2001/11/13 03:53:04 bcurtis Exp $

define('INCLUDE_PATH', '../');
include INCLUDE_PATH.'include.php';

function save_options() {
	global $q, $HTTP_POST_VARS;
	
	foreach ($HTTP_POST_VARS as $k => $v) {
		$q->query('update '.TBL_CONFIGURATION." set varvalue = '$v' where varname = '$k'");
	}
}

function list_options() {
	global $q, $t;
	
	$t->set_file('content', 'configure.html');
	$t->set_block('content', 'row', 'rows');
	$t->set_block('row', 'inputblock', 'input');
	$t->set_block('row', 'selectblock', 'select');
	$t->set_block('row', 'radioblock', 'radio');
	
	$q->query('select * from '.TBL_CONFIGURATION);
	while ($row = $q->grab()) {
		$t->set_var($row);
		$t->set_var('trclass', ++$i % 2 ? '' : 'alt');
		
		switch ($row['vartype']) {
			case 'multi' :
				$t->set_var(array(
					'options' => build_select($row['varname'], $row['varvalue']),
					'input' => '',
					'radio' => ''
					));
				$t->parse('select', 'selectblock', true);
				break;
			case 'bool' :
				$t->set_var(array(
					'yes' => $row['varvalue'] ? ' checked' : '',
					'no' => $row['varvalue'] ? '' : ' checked',
					'input' => '',
					'select' => ''
					));
				$t->parse('radio', 'radioblock', true);
				break;
			default :
				$t->set_var(array(
					'input_type' => 'text',
					'checked' => '',
					'select' => '',
					'radio' => ''
					));
				$t->parse('input', 'inputblock', true);
				break;
		}
		$t->parse('rows', 'row', true);
		$t->set_var(array(
			'input' => '',
			'select' => '',
			'radio' => ''
			));
	}
}

$t->set_file('wrap','wrap.html');

$perm->check('Admin');

if ($submit) {
	save_options();
} 
list_options();

$t->pparse('main',array('content','wrap','main'));

?>


