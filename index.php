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
$t->set_var('TITLE',$TITLE['home']);

if (USE_JPGRAPH) {
	$t->set_var('sblock', '<img src="images.php" align="right">');
} else {
	$q->query("select * from status order by sort_order");
	while ($row = $q->grab()) {
		$stats[$row['status_id']]['name'] = $row['status_name'];
	}
	$q->query("select status_id, count(status_id) as count from bug group by status_id");
	while ($row = $q->grab()) {
		$stats[$row['status_id']]['count'] = $row['count'];
	}
	foreach ($stats as $stat) {
		$t->set_var(array(
			'status' => $stat['name'],
			'count' => $stat['count'] ? $stat['count'] : 0
			));
		$total += $stat['count'];
		$t->parse('rows','row',true);
	}
	$t->set_var(array(
		'status' => "<b>{$STRING['totalbugs']}</b>",
		'count' => $total ? "<b>$total</b>" : 0
		));
	$t->parse('rows','row',true);
	$t->parse('sblock', 'statsblock', true);
}
	
$t->pparse('main',array('content','wrap','main'));

page_close();

?>
