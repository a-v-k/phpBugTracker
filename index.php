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
	global $db;
	
	// Grab the legend
	$rs = $db->query("select status_id, status_name from ".TBL_STATUS." order by sort_order");
	while ($rs->fetchInto($row)) {
		$stats[$row['status_id']]['name'] = $row['status_name'];
	}
	
	// Grab the data
	$rs = $db->query("select status_id, count(status_id) as count from ".TBL_BUG.
		" where project_id not in ($restricted_projects) group by status_id");
	while ($rs->fetchInto($row)) {
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

// Show the overall bug stats
if (USE_JPGRAPH) {
	if (!is_writeable('jpgimages')) {
		$t->set_var('sblock', $STRING['image_path_not_writeable']);
	} else {
		$t->set_var('sblock', build_image($restricted_projects));
	}
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

// Project summaries
$t->set_block('content', 'projectsummaryblock', 'projblock');
$t->set_block('projectsummaryblock', 'projectrow', 'prows');
$t->set_block('projectrow', 'col', 'cols');
if (SHOW_PROJECT_SUMMARIES) {
	$querystring = 'select project_name as "Project", sum(case when resolution_id = 0 then 1 else 0 end) as "Open"';
	$resfields = array('Project','Open');

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

	$rs = $db->query("$querystring, count(bug_id) as \"Total\" from ".TBL_BUG.
		" b left join ".TBL_PROJECT." p using (project_id)".
		" where b.project_id not in ($restricted_projects) group by b.project_id".
		" order by project_name");
	if (!$rs->numRows()) {
		$t->set_var('projblock', '');
	} else {
		foreach ($resfields as $col) {
			$t->set_var('coldata', stripslashes($col));
			$t->set_var('colclass', 'header-col');
			$t->parse('cols', 'col', true);
		}
		$t->set_var('bgcolor', '#eeeeee');
		$t->parse('prows', 'projectrow', true);
		$t->set_var('cols', '');
		$i = 0;
		$db->setOption('optimize', 'performance'); // For Oracle to do this loop
		while ($rs->fetchInto($row)) {
			foreach ($resfields as $col) {
				$t->set_var(array(
					'coldata' => stripslashes($row[$col]),
					'colclass' => $col == 'Project' ? '' : 'center-col'
					));
				$t->parse('cols', 'col', true);
			}
			$t->set_var('trclass', $i % 2 ? 'alt' : '');
			$i++;
			$t->parse('prows', 'projectrow', true);
			$t->set_var('cols', '');
			//for header default
			$t->set_var('trclass','alt');
		}
		$t->parse('projblock', 'projectsummaryblock', true);
		$rs->free();
		$db->setOption('optimize', 'portability'); 
	}
} else {
	$t->set_var('projblock', '');
}

// Show the recently added and closed bugs
$rs = $db->limitQuery("select bug_id, title, project_name from ".TBL_BUG.
	' b, '.TBL_PROJECT." p where b.project_id not in ($restricted_projects)".
	' and b.project_id = p.project_id order by b.created_date desc', 0, 5);
if (DB::isError($rs) or !$rs->numRows()) {
	$t->set_var('recentrows', $STRING['nobugs']);
} else {
	while (extract($rs->fetchRow())) {
		$t->set_var(array(
			'title' => stripslashes($title),
			'bugid' => $bugid,
			'project' => stripslashes($project_name)
		));
		$t->parse('recentrows', 'recentrow', true);
	}
}
$rs->free();
$rs = $db->limitQuery('select b.bug_id, title, project_name from '.TBL_BUG.' b, '.
	TBL_BUG_HISTORY.' h, '.TBL_PROJECT.' p'.
	" where b.project_id not in ($restricted_projects) and b.bug_id = h.bug_id".
	" and changed_field = 'status' and new_value = 'Closed'".
	' and b.project_id = p.project_id order by h.created_date desc', 0, 5);
if (DB::isError($rs) or !$rs->numRows()) {
	$t->set_var('closerows', $STRING['nobugs']);
} else {
	while (extract($rs->fetchRow())) {
		$t->set_var(array(
			'title' => stripslashes($title),
			'bugid' => $bugid,
			'project' => stripslashes($project_name)
		));
		$t->parse('closerows', 'closerow', true);
	}
}
$rs->free();

	
$t->pparse('main',array('content','wrap','main'));

?>
