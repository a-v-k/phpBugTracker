<?php

// index.php - Front page
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

include 'include.php';

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
	global $STRING;
	
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
	
	if (!$totalbugs) {
		return $STRING['nobugs'];
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
	$p1->value->SetFormat("%d%%");
	$p1->value->Show();
	$p1->SetLegends($legend);
	$p1->SetCSIMTargets($targ,$alts);
	$p1->SetCenter(0.25);
	$graph->Add($p1);
	$graph->Stroke('jpgimages/'.GenImgName());

	return $graph->GetHTMLImageMap("myimagemap").
		"<img align=\"right\" src=\"jpgimages/".GenImgName()."\" ISMAP USEMAP=\"#myimagemap\" border=0>";
}

// Show the overall bug stats
if (USE_JPGRAPH) {
	if (!is_writeable('jpgimages')) {
		$t->assign('summary_image', $STRING['image_path_not_writeable']);
	} else {
		$t->assign('summary_image', build_image($restricted_projects));
	}
} else {
	$t->assign('stats', grab_data($restricted_projects));
}

if (SHOW_PROJECT_SUMMARIES) {
	$querystring = $QUERY['index-projsummary-1'];
	$resfields = array('Project','Open');

	// Grab the resolutions from the database
	$rs = $db->query($QUERY['index-projsummary-2'].
		db_concat($QUERY['index-projsummary-3'], 'resolution_id', 
			$QUERY['index-projsummary-4'], 'resolution_name', "'\"' ").
		$QUERY['index-projsummary-5']);
	while (list($fieldname, $countquery) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
		$resfields[] = $fieldname;
		$querystring .= $countquery;
	}
	$resfields[] = 'Total';
	
	$db->setOption('optimize', 'performance'); // For Oracle to do this loop
	$t->assign(array(
		'resfields' => $resfields,
		'projects' => $db->getAll(sprintf($QUERY['index-projsummary-6'], 
			$querystring, $restricted_projects))
		));
	$db->setOption('optimize', 'portability'); 
}

// Show the recently added and closed bugs
$t->assign('recentbugs', 
	$db->getAll($db->modifyLimitQuery("select bug_id, title, project_name from ".TBL_BUG.
		' b, '.TBL_PROJECT." p where b.project_id not in ($restricted_projects)".
		' and b.project_id = p.project_id order by b.created_date desc', 0, 5)));

$t->assign('closedbugs', 
	$db->getAll($db->modifyLimitQuery('select b.bug_id, title, project_name from '.TBL_BUG.' b, '.
		TBL_BUG_HISTORY.' h, '.TBL_PROJECT.' p'.
		" where b.project_id not in ($restricted_projects) and b.bug_id = h.bug_id".
		" and changed_field = 'status' and new_value = 'Closed'".
		' and b.project_id = p.project_id order by h.created_date desc', 0, 5)));

if ($u != 'nobody') {
    $pref = $db->GetOne('select saved_queries from '.TBL_USER_PREF." where user_id='".$u."'");
    if ((isset($pref['saved_queries'])) && ($pref['saved_queries'])) {
	// Grab the saved queries if there are any and user wants them
	$t->assign('queries', 
	    $db->getAll("select * from ".TBL_SAVED_QUERY." where user_id = '$u'"));
    }
}

$t->wrap('index.html', 'home');

?>
