<?php

// report.php - Generate reports on various bug activities
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
// $Id: report.php,v 1.18 2002/01/16 11:12:33 javyer Exp $  

include 'include.php';

function resolution_by_engineer($projectid = 0) {
	global $q, $t;
	
	$t->set_block('content', 'row', 'rows');
	$t->set_block('row', 'col', 'cols');
	$t->set_var('reporttitle', 'Bug resolutions');
	
	// Start off our query
	$querystring = 'select email as "Assigned To", sum(case when resolution_id = 0 then 1 else 0 end) as "Open"';
	$resfields = array('Assigned To','Open');

	// Grab the resolutions from the database
	$q->query("select resolution_name, ".$q->concat("', sum(case when resolution_id = '", 'resolution_id', "' then 1 else 0 end) as \"'", 'resolution_name' ,"'\"'")." from ".TBL_RESOLUTION);
	while (list($fieldname, $countquery) = $q->grab()) {
		$resfields[] = $fieldname;
		$querystring .= $countquery;
	}
	$resfields[] = 'Total';
	
	if ($projectid && is_numeric($projectid)) {
		$projectquery = "where project_id = $projectid";
	} else {
		$projectquery = '';
	}
	
	$q->query("$querystring, count(bug_id) as \"Total\" from ".TBL_BUG." b left join ".TBL_AUTH_USER." u on assigned_to = user_id $projectquery group by assigned_to, u.email");
	if (!$q->num_rows()) {
		$t->set_var('rows', 'No data to display');
	} else {
		foreach ($resfields as $col) {
			$t->set_var('coldata', stripslashes($col));
			$t->set_var('colclass', 'header-col');
			$t->parse('cols', 'col', true);
		}
		$t->set_var('bgcolor', '#eeeeee');
		$t->parse('rows', 'row', true);
		$t->set_var('cols', '');
		$i = 0;
		while ($row = $q->grab()) {
			foreach ($resfields as $col) {
				if (!isset($row[$col]) || $row[$col] == '') {
					$coldata = 'Unassigned';
				} elseif ($col == 'Assigned To') {
					$coldata = maskemail($row[$col]);
				} else {
					$coldata = stripslashes($row[$col]);
				}
				$t->set_var(array(
					'coldata' => $coldata,
					'colclass' => $col == 'Assigned To' ? '' : 'center-col'
					));
				$t->parse('cols', 'col', true);
			}
			$t->set_var('trclass', $i % 2 ? 'alt' : '');
			$i++;
			$t->parse('rows', 'row', true);
			$t->set_var('cols', '');
			//for header default
			$t->set_var('trclass','alt');
		}
	}
}

$projectid = isset($_gv['projectid']) ? $_gv['projectid'] : 0;
$t->set_file('wrap','wrap.html');
$t->set_file('content','report.html');
$t->set_var(array(
	'projects' => build_select('project', $projectid),
	'TITLE' => $TITLE['reporting']
	));

resolution_by_engineer($projectid);

$t->pparse('main',array('content','wrap','main'));

?>
