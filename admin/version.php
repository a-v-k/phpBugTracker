<?php

include '../include.php';

function do_form($versionid = 0) {
	global $q, $me, $projectid, $version, $active, $STRING, $now, $u;
	
	// Validation
	if (!$version = trim($version)) 
		$error = $STRING['giveversion'];
	if ($error) { show_form($versionid, $error); return; }
	
	if (!$active) $active = 0;
	if (!$versionid) {
		$q->query("insert into Version (VersionID, ProjectID, Name, Active, CreatedBy, CreatedDate) values (".$q->nextid('Version').", $projectid, '$version', '$active', $u, $now)");
	} else {
		$q->query("update Version set ProjectID = $projectid, Name = '$version', Active = '$active' where VersionID = '$versionid'");
	}
	header("Location: project.php?op=edit&id=$projectid");
}	

function show_form($versionid = 0, $error = '') {
	global $q, $me, $t, $projectid, $version, $active, $TITLE;
	
	$t->set_file('content','versionform.html');
	if ($versionid && !$error) {
		$row = $q->grab("select v.*, p.Name as ProjectName from Version v left join Project p using(ProjectID) where VersionID = '$versionid'");
		$t->set_var(array(
			'versionid' => $row['VersionID'],
			'projectid' => $row['ProjectID'],
			'project' => $row['ProjectName'],
			'version' => $row['Name'],
			'active' => $row['Active'] ? 'checked' : '',
			'TITLE' => $TITLE['editversion']));
	} else {
		$t->set_var(array(
			'id' => $id,
			'me' => $me,
			'error' => $error,
			'versionid' => $versionid,
			'projectid' => $projectid,
			'project' => $q->grab_field("select Name from Project where ProjectID = $projectid"),
			'version' => $version,
			'active' => $active ? ' checked' : '',
			'TITLE' => $id ? $TITLE['editversion'] : $TITLE['addversion']));
	}
}

$t->set_file('wrap','wrap.html');

if ($op) switch($op) {
	case 'add' : show_form(); break;
	case 'edit' : show_form($id); break;
} elseif($submit) {		 
	do_form($id);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
