<?php

// index.php - Front page

include 'include.php';

page_open(array('sess' => 'usess'));

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
	$q->query("select * from Status order by SortOrder");
	while ($row = $q->grab()) {
		$stats[$row['StatusID']]['Name'] = $row['Name'];
	}
	$q->query("select Status, count(Status) as Count from Bug group by Status");
	while ($row = $q->grab()) {
		$stats[$row['Status']]['Count'] = $row['Count'];
	}
	foreach ($stats as $stat) {
		$t->set_var(array(
			'status' => $stat['Name'],
			'count' => $stat['Count'] ? $stat['Count'] : 0
			));
		$total += $stat['Count'];
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
