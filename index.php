<?php

// index.php - Front page
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

include 'include.php';

$t->set_file(array(
	'wrap' => 'wrap.html',
	'content' => 'index.html'
	));
$t->set_block('content', 'statsblock', 'sblock');
$t->set_block('statsblock','row','rows');
$t->set_block('content', 'recentrow', 'recentrows');
$t->set_block('content', 'closerow', 'closerows');
$t->set_var('TITLE',$TITLE['home']);

function grab_data($restricted_projects) {
	global $q;
	
	// Grab the legend
	$q->query("select status_id, status_name from ".TBL_STATUS." order by sort_order");
	while ($row = $q->grab()) {
		$stats[$row['status_id']]['name'] = $row['status_name'];
	}
	
	// Grab the data
	$q->query("select status_id, count(status_id) as count from ".TBL_BUG.
		" where project_id not in ($restricted_projects) group by status_id");
	while ($row = $q->grab()) {
		$stats[$row['status_id']]['count'] = $row['count'];
	}
	
	return $stats;
}

function build_image($restricted_projects) {
	error_reporting(0); // Force this, just in case
	include_once JPGRAPH_PATH.'jpgraph.php';
	include_once JPGRAPH_PATH.'jpgraph_pie.php';
	
	$stats = grab_data($restricted_projects);
	$totalbugs = 0;
	foreach ($stats as $statid => $stat) {
		if ($stat['count']) {
			$data[] = $stat['count'];
			$legend[] = "{$stat['name']} ({$stat['count']})";
			$targ[] = "query.php?op=doquery&status[]=$statid";
			$alts[] = $stat['name'];
			$totalbugs += $stat['count'];
		}
	}
	
	// Create the Pie Graph. 
	$graph = new PieGraph(350,200,"bug_cat_summary");
	$graph->SetShadow();

	// Set A title for the plot
	$graph->title->Set(sprintf("Bug Summary (%d bug%s)",
		$totalbugs, $totalbugs == 1 ? '' : 's'));
	$graph->title->SetFont(FF_FONT1,FS_BOLD);

	$graph->legend->Pos(0.03, 0.5, 'right', 'center');
	// Create
	$p1 = new PiePlot($data);
	$p1->SetLegends($legend);
	$p1->SetCSIMTargets($targ,$alts);
	$p1->SetCenter(0.25);
	$p1->SetPrecision(0);
	$graph->Add($p1);
	$graph->Stroke('jpgimages/'.GenImgName());

	return $graph->GetHTMLImageMap("myimagemap").
		"<img align=\"right\" src=\"jpgimages/".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>";
}

// Check to see if we have bugs from projects that shouldn't be visible to the user
$restricted_projects = '0';
if (!$perm->have_perm('Admin')) {
	$matching_projects = delimit_list(',', 
		$q->grab_field_set("select project_id from ".TBL_PROJECT_GROUP.
			" where group_id not in (".delimit_list(',', $auth->auth['group_ids']).")"));
	if ($matching_projects) {
		$restricted_projects .= ",$matching_projects";
	}
}

// Show the overall bug stats
if (USE_JPGRAPH) {
	$t->set_var('sblock', build_image($restricted_projects));
} else {
	$stats = grab_data($restricted_projects);
	$total = 0;
	foreach ($stats as $statid => $stat) {
		$t->set_var(array(
			'statid' => $statid,
			'status' => $stat['name'],
			'count' => isset($stat['count']) ? $stat['count'] : 0
			));
		$total += isset($stat['count']) ? $stat['count'] : 0;
		$t->parse('rows','row',true);
	}
	$t->set_var(array(
		'statid' => delimit_list('&status[]=', array_keys($stats)),
		'status' => "<b>{$STRING['totalbugs']}</b>",
		'count' => $total ? "<b>$total</b>" : 0
		));
	$t->parse('rows','row',true);
	$t->parse('sblock', 'statsblock', true);
}

// Show the recently added and closed bugs
$q->query("select bug_id, title from ".TBL_BUG.
	" where project_id not in ($restricted_projects)".
	' order by created_date desc limit 5');
if (!$q->num_rows()) {
	$t->set_var('recentrows', $STRING['nobugs']);
} else {
	while (list($bugid, $title) = $q->grab()) {
		$t->set_var(array(
			'title' => stripslashes($title),
			'bugid' => $bugid
		));
		$t->parse('recentrows', 'recentrow', true);
	}
}
$q->query('select b.bug_id, title from '.TBL_BUG.' b, '.TBL_BUG_HISTORY.
	" h where project_id not in ($restricted_projects) and b.bug_id = h.bug_id".
	" and changed_field = 'Status' and new_value = 'Closed'".
	' order by h.created_date desc limit 5');
if (!$q->num_rows()) {
	$t->set_var('closerows', $STRING['nobugs']);
} else {
	while (list($bugid, $title) = $q->grab()) {
		$t->set_var(array(
			'title' => stripslashes($title),
			'bugid' => $bugid
		));
		$t->parse('closerows', 'closerow', true);
	}
}

	
$t->pparse('main',array('content','wrap','main'));

?>
