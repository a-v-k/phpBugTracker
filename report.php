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
// $Id: report.php,v 1.21 2002/03/27 18:01:33 bcurtis Exp $  

include 'include.php';

function resolution_by_engineer($projectid = 0) {
	global $db, $t, $restricted_projects, $perm;
	
	$t->set_block('content', 'row', 'rows');
	$t->set_block('row', 'col', 'cols');
	$t->set_var('reporttitle', 'Bug resolutions');
	
	// Start off our query
	$querystring = 'select email as "Assigned To", sum(case when resolution_id = 0 then 1 else 0 end) as "Open"';
	$resfields = array('Assigned To','Open');

	// Grab the resolutions from the database
	$rs = $db->query("select resolution_name, ".
		db_concat("', sum(case when resolution_id = '", 'resolution_id', 
			"' then 1 else 0 end) as \"'", 'resolution_name' ,"'\"'").
		" from ".TBL_RESOLUTION);
	while (list($fieldname, $countquery) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
		$resfields[] = $fieldname;
		$querystring .= $countquery;
	}
	$resfields[] = 'Total';
	
	if ($projectid && is_numeric($projectid)) {
		$projectquery = "where project_id = $projectid";
	} elseif (!$perm->have_perm('Admin')) {
		$projectquery = "where project_id not in ($restricted_projects)";
	} else {
		$projectquery = '';
	}
	
	$rs = $db->query("$querystring, count(bug_id) as \"Total\" from ".TBL_BUG.
		" b left join ".TBL_AUTH_USER." u on assigned_to = user_id $projectquery ".
		"group by assigned_to, u.email");
	if (!$rs->numRows()) {
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
		$db->setOption('optimize', 'performance'); // For Oracle to do this loop
		while ($rs->fetchInto($row)) {
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
		$db->setOption('optimize', 'portability'); 
	}
}

function new_bugs_by_date($date_range) {
	global $db, $t, $now, $_gv;
	
	include ("jpgraph.php");
	include ("jpgraph_bar.php");
	
	$colors = array('red', 'cadetblue', 'gold', 'darkmagenta');
	
	$graph = new Graph(450,300);
	$graph->SetShadow();
	$graph->SetScale("textlin");
	$graph->title->Set("Bug Counts by Date");
	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->img->SetMargin(40,140,40,80);
	if ($date_range > 30) {
		$graph->xaxis->SetTextTickInterval(14);
	} elseif ($date_range > 14) {
		$graph->xaxis->SetTextTickInterval(7);
	} elseif ($date_range > 7) {
		$graph->xaxis->SetTextTickInterval(2);
	}
	
	$dates = array();
	$then = $now - (ONEDAY * $date_range);
	
	// New bugs
	$dates = $db->getCol("select created_date from bug where created_date between $then and $now order by 1");
	if ($date_range == 365) {
		$date_format = 'M Y';
	} else {
		$date_format = 'j M';
		for ($i = $date_range - 1; $i >= 0; $i--) {
			$dates[date($date_format, ($now - (ONEDAY * $i)))] = 0.00000001;
		}
	}
	foreach ($dates as $date) {
		//$date = date($date_format, $date);
		$dates[date($date_format, $date)] += 1;
	}
	foreach ($dates as $date => $count) {
		#echo "$date:$count<br>";
		$xlabel[] = $date;
		$xvalue[] = $count;
	}
	$p1 = new BarPlot($xvalue);
	$p1->SetLegend("Created");
	$p1->SetColor("blue");
	$p1->SetFillColor("blue");
	#$p1->SetCenter();
	$graph->xaxis->SetTickLabels($xlabel);
	#$graph->xaxis->SetLabelAngle(90);
	$graph->SetTickDensity(TICKD_SPARSE);
	#$graph->yscale->SetGrace(50);
	$graph->Add($p1);
	
	// Resolutions
	if (isset($_gv['resolutions'])) {
		$color = 0;
		foreach ($_gv['resolutions'] as $resolution) {
			$stats = array(
				'dates' => array(), 
				'labels' => array(), 
				'values' => array(),
				'plot' => null);
			if ($date_range == 365) {
				$date_format = 'M Y';
			} else {
				$date_format = 'j M';
				for ($i = $date_range - 1; $i >= 0; $i--) {
					$stats['dates'][date($date_format, ($now - (ONEDAY * $i)))] = 0.00000001;
				}
			}
			$dates = $db->getCol("select created_date from bug_history where changed_field = 'resolution' and new_value = '$resolution' and created_date between $then and $now order by 1");
			foreach ($dates as $date) {
				//$date = date($date_format, $date);
				$stats['dates'][date($date_format, $date)] += 1;
			}
			foreach ($stats['dates'] as $date => $count) {
				#echo "$date:$count<br>";
				array_push($stats['labels'], $date);
				array_push($stats['values'], $count);
			}
			$stats['plot'] = new BarPlot($stats['values']);
			$stats['plot']->SetLegend($resolution);
			$stats['plot']->SetColor($colors[$color]);
			$stats['plot']->SetFillColor($colors[$color++]);
			$graph->Add($stats['plot']);
		}
	}
	$graph->Stroke();
}

$projectid = isset($_gv['projectid']) ? $_gv['projectid'] : 0;
$t->set_file('wrap','wrap.html');
$t->set_file('content','report.html');
$t->set_var(array(
	'projects' => build_select('project', $projectid),
	'TITLE' => $TITLE['reporting']
	));

if (isset($_gv['op'])) {
	switch ($_gv['op']) {
		case 'bugsbydate' : 
			new_bugs_by_date(isset($_gv['date_range']) ? $_gv['date_range'] : 7);
			break;
	}
} else {
	resolution_by_engineer($projectid);
}

$t->pparse('main',array('content','wrap','main'));

?>
