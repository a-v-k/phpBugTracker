<?php

// report.php - Generate reports on various bug activities

include 'include.php';

page_open(array('sess' => 'usess', 'auth' => 'uauth', 'perm' => 'uperm'));
$u = $auth->auth['uid'];

function resolution_by_engineer($projectid = 0) {
	global $q, $t;
	
	$t->set_block('content', 'row', 'rows');
	$t->set_block('row', 'col', 'cols');
	$t->set_var('reporttitle', 'Status of Assigned bugs');
	
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
	
	if ($projectid && is_numeric($projectid)) 
		$projectquery = "and Project = $projectid";
		
	$q->query("$querystring, count(BugID) as Total from Bug b, User u where AssignedTo = UserID $projectquery group by AssignedTo");
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
				$t->set_var(array(
					'coldata' => $col == 'Assigned To' 
						? sprintf("<a href='mailto:%s'>%s</a>", stripslashes($row[$col]),
							stripslashes($row[$col]))
						: stripslashes($row[$col]),
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
$t->set_var('projects', build_select('Project', $projectid));

resolution_by_engineer($projectid);

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
