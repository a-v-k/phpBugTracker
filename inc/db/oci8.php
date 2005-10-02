<?php

// oci8.php - Oracle queries

$QUERY = array(
	'admin-list-components' =>
		'select '.
			'c.component_id, '.
			'component_name, '.
			'c.created_date, '.
			'active, '.
			'sort_order, '.
			'count(bug_id) as bug_count '.
		'from '.
			TBL_COMPONENT.' c, '.
			TBL_BUG.' b '.
		'where '.
			'c.project_id = %s '.
			'and c.component_id = b.component_id(+) '.
		'group by '.
			'c.component_id, '.
			'c.component_name, '.
			'c.created_date, '.
			'c.active',
	'admin-list-databases' =>
		'select '.
			'd.database_id, '.
			'database_name, '.
			'sort_order, '.
			'count(bug_id) as bug_count '.
		'from '.
			TBL_DATABASE.' d, '.
			TBL_BUG.' b '.
		'where '.
			'd.database_id = b.database_id(+) '.
		'group by '.
			'd.database_id, '.
			'database_name, '.
			'sort_order '.
		'order by '.
			'%s %s',
	'admin-list-groups' =>
		'select '.
			'ag.group_id, '.
			'ag.group_name, '.
			'ag.locked, '.
			'count(ug.group_id) as count '.
		'from '.
			TBL_AUTH_GROUP.' ag, '.
			TBL_USER_GROUP.' ug, '.
			TBL_AUTH_USER.' au '.
		'where '.
			'ag.group_id = ug.group_id(+) '.
			'and ug.user_id = au.user_id(+) '.
		'group by '.
			'ag.group_id, '.
			'ag.group_name, '.
			'ag.locked '.
		'order by '.
			'%s %s',
	'admin-list-oses' =>
		'select '.
			's.os_id, '.
			's.os_name, '.
			's.regex, '.
			's.sort_order, '.
			'count(b.bug_id) as bug_count '.
		'from '.
			TBL_OS.' s, '.
			TBL_BUG.' b '.
		'where '.
			's.os_id = b.os_id(+) '.
		'group by '.
			's.os_id, '.
			's.os_name, '.
			's.regex, '.
			's.sort_order '.
		'order by '.
			's.%s %s',
	'admin-list-priorities' =>
		'select '.
			'p.priority_id, '.
			'p.priority_name, '.
			'p.priority_desc, '.
			'p.priority_color, '.
			'p.sort_order, '.
			'count(b.bug_id) as bug_count '.
		'from '.
			TBL_PRIORITY.' p, '.
			TBL_BUG.' b '.
		'where '.
			'p.priority_id = b.priority(+) '.
		'group by '.
			'p.priority_id, '.
			'p.priority_name, '.
			'p.priority_desc, '.
			'p.priority_color, '.
			'p.sort_order '.
		'order by '.
			'p.%s %s',
	'admin-list-resolutions' =>
		'select '.
			's.resolution_id, '.
			'resolution_name, '.
			'resolution_desc, '.
			'sort_order, '.
			'count(bug_id) as bug_count '.
		'from '.
			TBL_RESOLUTION.' s, '.
			TBL_BUG.' b '.
		'where '.
			's.resolution_id = b.resolution_id(+) '.
		'group by '.
			's.resolution_id, '.
			'resolution_name, '.
			'resolution_desc, '.
			'sort_order '.
		'order by '.
			'%s %s',
	'admin-list-severities' =>
		'select '.
			's.severity_id, '.
			'severity_name, '.
			'severity_desc, '.
			'severity_color, '.
			'sort_order, '.
			'count(bug_id) as bug_count '.
		'from '.
			TBL_SEVERITY.' s, '.
			TBL_BUG.' b '.
		'where '.
			's.severity_id = b.severity_id(+) '.
		'group by '.
			's.severity_id, '.
			'severity_name, '.
			'severity_desc, '.
			'severity_color, '.
			'sort_order '.
		'order by '.
			'%s %s',
	'admin-list-sites' =>
		'select '.
			's.site_id, '.
			'site_name, '.
			'sort_order, '.
			'count(bug_id) as bug_count '.
		'from '.
			TBL_SITE.' s, '.
			TBL_BUG.' b '.
		'where '.
			's.site_id = b.site_id(+) '.
		'group by '.
			's.site_id, '.
			'site_name, '.
			'sort_order '.
		'order by '.
			'%s %s',
	'admin-list-statuses' =>
		'select '.
			's.status_id, '.
			'status_name, '.
			'status_desc, '.
			'sort_order, '.
			'bug_open, '.
			'count(bug_id) as bug_count '.
		'from '.
			TBL_STATUS.' s, '.
			TBL_BUG.' b '.
		'where '.
			's.status_id = b.status_id(+) '.
		'group by '.
			's.status_id, '.
			'status_name, '.
			'status_desc, '.
			'sort_order, '.
			'bug_open '.
		'order by '.
			'%s %s',
	'admin-list-versions' =>
		'select '.
			'v.version_id, '.
			'version_name, '.
			'v.created_date, '.
			'active, '.
			'sort_order, '.
			'count(bug_id) as bug_count '.
		'from '.
			TBL_VERSION.' v, '.
			TBL_BUG.' b '.
		'where '.
			'v.project_id = %s '.
			'and v.version_id = b.version_id(+) '.
		'group by '.
			'v.version_id, '.
			'v.version_name, '.
			'v.created_date, '.
			'v.active',
	'admin-show-component' =>
		'select '.
			'c.*, '.
			'p.project_name as project_name '.
		'from '.
			TBL_COMPONENT.' c, '.
			TBL_PROJECT.' p '.
		'where '.
			'p.project_id = c.project_id(+) '.
			'and component_id = \'%s\'',
	'admin-show-version' =>
		'select '.
			'v.*, '.
			'p.project_name as project_name '.
		'from '.
			TBL_VERSION.' v, '.
			TBL_PROJECT.' p '.
		'where '.
			'p.project_id = v.project_id(+) '.
			'and version_id = \'%s\'',
	'admin-user-groups' =>
		'select '.
			'ug.group_id '.
		'from '.
			TBL_USER_GROUP.' ug, '.
			TBL_AUTH_GROUP.' g '.
		'where '.
			'g.group_id = ug.group_id(+) '.
			'and user_id = %s ',
	'bug-cc-list' =>
		'select '.
			'email '.
		'from '.
			TBL_BUG_CC.' bc, '.
			TBL_AUTH_USER.' u, '.
			TBL_USER_PREF.' p '.
		'where '.
			'u.user_id = bc.user_id(+) '.
			'and u.user_id = p.user_id '.
			'and email_notices = 1 '.
			'and bug_id = %s',
	'bug-history' =>
		'select '.
			'bh.*, '.
			'login '.
		'from '.
			TBL_BUG_HISTORY.' bh, '.
			TBL_AUTH_USER.' '.
		'where '.
			'user_id = bh.created_by(+) '.
			'and bug_id = %s '.
		'order by '.
			'bh.created_date',
	'bug-prev-next' =>
		'select '.
			'b.bug_id, '.
			'reporter.login as reporter, '.
			'owner.login as owner, '.
			'count(distinct comment.comment_id) as comments, '.
			'count(distinct attachment.attachment_id) as attachments, '.
			'count(distinct vote.user_id) as votes '.
		'from '.
			TBL_BUG.' b, '.
			TBL_AUTH_USER.' owner, '.
			TBL_AUTH_USER.' reporter, '.
			TBL_AUTH_USER.' lastmodifier, '.
			TBL_COMMENT.' comment, '.
			TBL_ATTACHMENT.' attachment, '.
			TBL_BUG_VOTE.' vote, '.
			TBL_BOOKMARK.' bookmark, '.
			TBL_RESOLUTION.' resolution, '.
			TBL_DATABASE.' database, '.
			TBL_VERSION.' version2, '.
			TBL_VERSION.' version3, '.
			TBL_SEVERITY.' severity, '.
			TBL_STATUS.' status, '.
			TBL_OS.' os, '.
			TBL_PRIORITY.' priority, '.
			TBL_VERSION.' version, '.
			TBL_COMPONENT.' component, '.
			TBL_PROJECT.' project, '.
			TBL_SITE.' site '.
		'where '.
			'b.assigned_to = owner.user_id(+) '.
			'and b.created_by = reporter.user_id(+) '.
			'and b.last_modified_by = lastmodifier.user_id(+) '.
			'and b.bug_id = comment.bug_id(+) '.
			'and b.bug_id = attachment.bug_id(+) '.
			'and b.bug_id = vote.bug_id(+) '.
			'and b.bug_id = bookmark.bug_id(+) '.
			'and b.resolution_id = resolution.resolution_id(+) '.
			'and b.database_id = database.database_id(+) '.
			'and b.to_be_closed_in_version_id = version2.version_id(+) '.
			'and b.closed_in_version_id = version3.version_id(+) '.
			'and b.severity_id = severity.severity_id '.
			'and b.priority = priority.priority_id '.
			'and b.status_id = status.status_id '.
			'and b.os_id = os.os_id '.
			'and b.version_id = version.version_id '.
			'and b.component_id = component.component_id '.
			'and b.project_id = project.project_id '.
			'and %s '.
			'and b.site_id = site.site_id '.
			'and b.bug_id <> %s '.
		'group by '.
			'b.bug_id '.
		'order by '.
			'%s %s, '.
			'bug_id asc',
	'bug-printable' =>
		'select '.
			'b.*, '.
			'reporter.login as reporter, '.
			'owner.login as owner, '.
			'p.project_name, '.
			'c.component_name, '.
			'v.version_name, '.
			's.severity_name, '.
			'o.os_name, '.
			's.status_name, '.
			'r.resolution_name '.
		'from '.
			TBL_BUG.' b, '.
			TBL_AUTH_USER.' owner, '.
			TBL_AUTH_USER.' reporter, '.
			TBL_BOOKMARK.' bookmark, '.
			TBL_RESOLUTION.' r, '.
			TBL_SEVERITY.' sv, '.
			TBL_STATUS.' st, '.
			TBL_OS.' os, '.
			TBL_VERSION.' v, '.
			TBL_PRIORITY.' priority, '.
			TBL_COMPONENT.' c, '.
			TBL_PROJECT.' p '.
		'where '.
			'b.assigned_to = owner.user_id(+) '.
			'and b.created_by = reporter.user_id(+) '.
			'and b.bug_id = bookmark.bug_id(+) '.
			'and b.resolution_id = r.resolution_id(+) '.
			'and b.bug_id = %s '.
			'and b.project_id not in (%s)'.
			'and b.severity_id = sv.severity_id '.
			'and b.priority = priority.priority_id '.
			'and b.os_id = os.os_id '.
			'and b.version_id = v.version_id '.
			'and b.component_id = c.component_id '.
			'and b.project_id = p.project_id '.
			'and b.status_id = st.status_id ',
	'bug-show-bug' =>
		'select '.
			'b.*, '.
			'reporter.login as reporter, '.
			'owner.login as owner, '.
			'st.status_name, '.
			'r.resolution_name '.
		'from '.
			TBL_BUG.' b, '.
			TBL_AUTH_USER.' owner, '.
			TBL_AUTH_USER.' reporter, '.
			TBL_BOOKMARK.' bookmark, '.
			TBL_RESOLUTION.' r, '.
			TBL_SEVERITY.' sv, '.
			TBL_STATUS.' st, '.
			TBL_SITE.' site, '.
			TBL_PRIORITY.' prio '.
		'where '.
			'b.resolution_id = r.resolution_id(+) '.
			'and b.assigned_to = owner.user_id(+) '.
			'and b.created_by = reporter.user_id(+) '.
			'and b.bug_id = bookmark.bug_id(+) '.
			'and b.bug_id = %s '.
			'and b.project_id not in (%s) '.
			'and b.site_id = site.site_id '.
			'and b.severity_id = sv.severity_id '.
			'and b.status_id = st.status_id '.
			'and b.priority = prio.priority_id',
	'functions-bug-cc' =>
		'select '.
			'b.user_id, '.
			'login '.
		'from '.
			TBL_BUG_CC.' b, '.
			TBL_AUTH_USER.' '.
		'where '.
			'phpbt_auth_user.user_id = b.user_id(+) '.
			'and bug_id = %s',
	'functions-project-js' =>
		'select '.
			'p.project_id, '.
			'project_name '.
		'from '.
			TBL_PROJECT.' p, '.
			TBL_PROJECT_GROUP.' pg '.
		'where '.
			'p.project_id = pg.project_id(+) '.
			'and active = 1 '.
			'and (pg.project_id is null or pg.group_id in (%s)) '.
		'group by '.
			'p.project_id, '.
			'p.project_name '.
		'order by '.
			'project_name',
	'include-template-bookmark' =>
		"SELECT ".
			"sum(decode( s.status_id in (".OPEN_BUG_STATUSES.") , 1, 1, 0 )), ".
			"sum(decode( s.status_id not in (".OPEN_BUG_STATUSES.") , 1, 1, 0 )), ".
		"from ".
			TBL_BUG." b, ".
			TBL_STATUS." s, ".
			TBL_BOOKMARK." w ".
		"where ".
			"b.status_id = s.status_id (+) ".
			"AND w.user_id=%s ".
			"AND w.bug_id = b.bug_id",
	'include-template-owner' =>
		"SELECT ".
			"sum(decode( s.status_id in (".OPEN_BUG_STATUSES.") , 1, 1, 0 )), ".
			"sum(decode( s.status_id not in (".OPEN_BUG_STATUSES.") , 1, 1, 0 )), ".
		'from '.
			TBL_BUG.' b, '.
			TBL_STATUS.' s '.
		'where '.
			'b.status_id = s.status_id (+) '.
			'and b.assigned_to = %s',
	'include-template-reporter' =>
		"SELECT ".
			"sum(decode( s.status_id in (".OPEN_BUG_STATUSES.") , 1, 1, 0 )) , ".
			"sum(decode( s.status_id not in (".OPEN_BUG_STATUSES.") , 1, 1, 0 )) ".
		'from '.
			TBL_BUG.' b, '.
			TBL_STATUS.' s '.
		'where '.
			'b.status_id = s.status_id (+) '.
			'and b.created_by = %s',
	'index-projsummary-1' =>
		'select b.project_id, p.project_name as "Project", '.
		'sum(decode( b.resolution_id, 0, 1, 0)) as "Open"',
	'index-projsummary-2' =>
		"select b.resolution_name, ",
	'index-projsummary-3' =>
		"', sum(decode( b.resolution_id, '",
	'index-projsummary-4' =>
		"', 1, 0)) as \"'",
	'index-projsummary-5' =>
		"from ".TBL_RESOLUTION." b",
	'index-projsummary-6' =>
		"%s, count(bug_id) as \"Total\" ".
		'from '.TBL_BUG.' b, '.TBL_PROJECT.' p '.
		'where b.project_id = p.project_id(+) '.
		'and b.project_id not in (%s) '.
		'group by b.project_id, p.project_name '.
		'order by p.project_name',
	'join-where' =>
		'and',
	'query-list-bugs' =>
		'select '.
			'%s '.
		'from '.
			TBL_BUG.' b, '.
			TBL_AUTH_USER.' owner, '.
			TBL_AUTH_USER.' reporter, '.
			TBL_AUTH_USER.' lastmodifier, '.
			TBL_COMMENT.' comment, '.
			TBL_ATTACHMENT.' attachment, '.
			TBL_BUG_VOTE.' vote, '.
			TBL_BOOKMARK.' bookmark, '.
			TBL_RESOLUTION.' resolution, '.
			TBL_DATABASE.' database, '.
			TBL_VERSION.' version2, '.
			TBL_VERSION.' version3, '.
			TBL_SEVERITY.' severity, '.
			TBL_STATUS.' status, '.
			TBL_OS.' os, '.
			TBL_SITE.' site, '.
			TBL_VERSION.' version, '.
			TBL_COMPONENT.' component, '.
			TBL_PROJECT.' project, '.
			TBL_PRIORITY.' priority '.
		'where '.
			'b.assigned_to = owner.user_id(+) '.
			'and b.created_by = reporter.user_id(+) '.
			'and b.last_modified_by = lastmodifier.user_id(+) '.
			'and b.bug_id = comment.bug_id(+) '.
			'and b.bug_id = attachment.bug_id(+) '.
			'and b.bug_id = vote.bug_id(+) '.
			'and b.bug_id = bookmark.bug_id(+) '.			
			'and b.resolution_id = resolution.resolution_id(+) '.
			'and b.database_id = database.database_id(+) '.
			'and b.to_be_closed_in_version_id = version2.version_id(+) '.
			'and b.closed_in_version_id = version3.version_id(+) '.
			'and b.severity_id = severity.severity_id '.
			'and b.priority = priority.priority_id '.
			'and b.status_id = status.status_id '.
			'and b.os_id = os.os_id '.
			'and b.site_id = site.site_id '.
			'and b.version_id = version.version_id '.
			'and b.component_id = component.component_id '.
			'and b.project_id = project.project_id %s '.
		'group by '.
			'b.bug_id '.
		'order by '.
			'%s %s, '.
			'b.bug_id asc',
	'query-list-bugs-count' =>
		'select '.
			'count(*) '.
		'from '.
			TBL_BUG.' b, '.
			TBL_AUTH_USER.' owner, '.
			TBL_AUTH_USER.' reporter, '.
			TBL_BOOKMARK.' bookmark '.
		'where '.
			'b.assigned_to = owner.user_id(+) '.
			'and b.created_by = reporter.user_id(+) '.
			'and b.bug_id = bookmark.bug_id(+) ',
	'query-list-bugs-count-join' =>
		'and ',
	'report-resbyeng-1' =>
		'select email as "Assigned To", '.
		'sum(decode( b.resolution_id, 0, 1, 0)) as "Open"',
	'report-resbyeng-2' =>
		"select b.resolution_name, ",
	'report-resbyeng-3' =>
		"', sum(decode( b.resolution_id, '",
	'report-resbyeng-4' =>
		"', 1, 0)) as \"'",
	'report-resbyeng-5' =>
		"from ".TBL_RESOLUTION." b",
	'report-resbyeng-6' =>
		'%s, count(bug_id) as "Total" '.
		'from '.TBL_BUG.' b, '.TBL_AUTH_USER.' u '.
		'where b.assigned_to = u.user_id(+) %s '.
		'group by assigned_to, u.email',
	);

?>
