<?php

// images.php - Draw graphs using jpgraph
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

define ('NO_AUTH',1);
include 'include.php';
include JPGRAPH_PATH.'jpgraph.php';

function bug_cat_summary() {
	global $q;
	
	include_once JPGRAPH_PATH.'jpgraph_pie.php';
	
	// Grab the legend
	$q->query("select status_id, status_name from ".TBL_STATUS." order by sort_order");
	while ($row = $q->grab()) {
		$stats[$row['status_id']]['name'] = $row['status_name'];
	}
	
	// Grab the data
	$q->query("select status_id, count(status_id) as count from ".TBL_BUG." group by status_id");
	while ($row = $q->grab()) {
		$stats[$row['status_id']]['count'] = $row['count'];
	}
	$totalbugs = 0;
	foreach ($stats as $stat) {
		if ($stat['count']) {
			$data[] = $stat['count'];
			$legend[] = "{$stat['name']} ({$stat['count']})";
			$totalbugs += $stat['count'];
		}
	}
	
	// Create the Pie Graph. 
	$graph = new PieGraph(300,200,"bug_cat_summary");
	$graph->SetShadow();

	// Set A title for the plot
	$graph->title->Set(sprintf("Bug Summary (%d bug%s)",
		$totalbugs, $totalbugs == 1 ? '' : 's'));
	$graph->title->SetFont(FONT1,FS_BOLD);

	$graph->legend->Pos(0.03, 0.5, 'right', 'center');
	// Create
	$p1 = new PiePlot($data);
	$p1->SetLegends($legend);
	$p1->SetCenter(0.25);
	$p1->SetPrecision(0);
	$graph->Add($p1);
	$graph->Stroke();

}

switch($pic) {
	default : bug_cat_summary();
}

?>
