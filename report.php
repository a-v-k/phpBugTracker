<?php

// report.php - Generate reports on various bug activities
// --------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
// ---------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// ---------------------------------------------------------------------

include 'include.php';

function resolution_by_engineer($projectid = 0) {
	global $q, $t;
	
	$t->set_block('content', 'row', 'rows');
	$t->set_block('row', 'col', 'cols');
	$t->set_var('reporttitle', 'Bug resolutions');
	
	// Start off our query
	$querystring = 'select Email as "Assigned To", sum(if(Resolution = "0",1,0)) as "Open"';
	$resfields = array('Assigned To','Open');
	// Grab the resolutions from the database
	$q->query("select Name, concat(', sum(if(Resolution = \"',ResolutionID,'\",1,0)) as \"',Resolution.Name,'\"') from Resolution");
	while (list($fieldname, $countquery) = $q->grab()) {
		$resfields[] = $fieldname;
		$querystring .= $countquery;
	}
	$resfields[] = 'Total';
	
	if ($projectid && is_numeric($projectid)) {
		$projectquery = "where Project = $projectid";
	}
	
	$q->query("$querystring, count(BugID) as Total from Bug b left join User u on AssignedTo = UserID $projectquery group by AssignedTo");
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
		while ($row = $q->grab()) {
			foreach ($resfields as $col) {
				if ($row[$col] == '') {
					$coldata = 'Unassigned';
				} elseif ($col == 'Assigned To') {
					$coldata = sprintf("<a href='mailto:%s'>%s</a>", 
						stripslashes($row[$col]), stripslashes($row[$col]));
				} else {
					$coldata = stripslashes($row[$col]);
				}
				$t->set_var(array(
					'coldata' => $coldata,
					'colclass' => $col == 'Assigned To' ? '' : 'center-col'
					));
				$t->parse('cols', 'col', true);
			}
			$t->set_var('bgcolor', (++$i % 2 == 0) ? '#dddddd' : '#ffffff');
			$t->parse('rows', 'row', true);
			$t->set_var('cols', '');
		}
	}
}

$t->set_file('wrap','wrap.html');
$t->set_file('content','report.html');
$t->set_var(array(
	'projects' => build_select('Project', $projectid),
	'TITLE' => $TITLE['reporting']
	));

resolution_by_engineer($projectid);

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
