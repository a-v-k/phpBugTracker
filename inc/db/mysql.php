<?php
$QUERY = array(
	'admin-list-groups' => 'select ag.group_id, group_name, locked, '.
		'count(ug.group_id) as count '.
		'from '.TBL_AUTH_GROUP.' ag '.
		'left join '.TBL_USER_GROUP.' ug using (group_id) '.
		'left join '.TBL_AUTH_USER.' using (user_id) '.
		'group by ag.group_id, group_name, locked '.
		'order by %s %s',
	'admin-list-oses' => 'select s.os_id, os_name, regex, sort_order, '.
		'count(bug_id) as bug_count '.
		'from '.TBL_OS.' s '.
		'left join '.TBL_BUG.' using (os_id) '.
		'group by s.os_id, os_name, regex, sort_order '.
		'order by %s %s',
	'admin-show-version' => 'select v.*, p.project_name as project_name '.
		'from '.TBL_VERSION.' v left join '.TBL_PROJECT.' p using(project_id) '.
		'where version_id = \'%s\'',
	'admin-show-component' => 'select c.*, p.project_name as project_name '.
		'from '.TBL_COMPONENT.' c  left join '.TBL_PROJECT.' p using (project_id) '.
		'where component_id = \'%s\'',
	'admin-list-resolutions' => 'select s.resolution_id, resolution_name, '.
		'resolution_desc, sort_order, count(bug_id) as bug_count '.
		'from '.TBL_RESOLUTION. ' s left join '.TBL_BUG.' using (resolution_id) '.
		'group by s.resolution_id, resolution_name, resolution_desc, sort_order '.
		'order by %s %s',
	'admin-list-severities' => 'select s.severity_id, severity_name, '.
		'severity_desc, severity_color, sort_order, count(bug_id) as bug_count '.
		'from '.TBL_SEVERITY. ' s left join '.TBL_BUG.' using (severity_id) '.
		'group by s.severity_id, severity_name, severity_desc, severity_color, '.
		'sort_order '.
		'order by %s %s',
	'admin-list-databases' => 'select d.database_id, database_name, '.
		'database_version, sort_order, count(bug_id) as bug_count '.
		'from '.TBL_DATABASE. ' d left join '.TBL_BUG.' using (database_id) '.
		'group by d.database_id, database_name, database_version, sort_order '.
		'order by %s %s',
	'admin-list-sites' => 'select s.site_id, site_name, sort_order, '.
		'count(bug_id) as bug_count from '.TBL_SITE. ' s left join '.
		TBL_BUG.' using (site_id) group by s.site_id, site_name, sort_order '.
		'order by %s %s',
	'admin-list-statuses' => 'select s.status_id, status_name, status_desc, '.
		'sort_order, count(bug_id) as bug_count '.
		'from '.TBL_STATUS.' s left join '. TBL_BUG.' using (status_id) '.
		'group by s.status_id, status_name, status_desc, sort_order '.
		'order by %s %s',
	'admin-user-groups' => 'select ug.group_id '.
		'from '.TBL_USER_GROUP.' ug left join '.TBL_AUTH_GROUP.' g using (group_id) '.
		'where user_id = %s and group_name <> \'User\'',
	'bug-history' => 'select bh.*, login '.
		'from '.TBL_BUG_HISTORY.' bh '.
		'left join '. TBL_AUTH_USER.' on bh.created_by = user_id '.
		'where bug_id = %s',
	'bug-cc-list' => 'select email '.
		'from '.TBL_BUG_CC.' left join '. TBL_AUTH_USER.' u using(user_id), '.
		TBL_USER_PREF.' p '.
		'where bug_id = %s and u.user_id = p.user_id and email_notices = 1',
	'bug-printable' => 'select b.*, reporter.login as reporter, '.
		'owner.login as owner, project_name, component_name, version_name, '.
		'severity_name, os_name, status_name, resolution_name '.
		'from '.TBL_BUG.' b '.
		'left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id '.
		'left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id '.
		'left join '.TBL_RESOLUTION.' r on b.resolution_id = r.resolution_id, '.
		TBL_SEVERITY.' sv, '.TBL_STATUS.' st, '.TBL_OS.' os, '. TBL_VERSION.' v, '.
		TBL_COMPONENT.' c, '.TBL_PROJECT.' p '.
		'where bug_id = %s and b.project_id not in (%s) '.
		'and b.severity_id = sv.severity_id '.
		'and b.os_id = os.os_id and b.version_id = v.version_id '.
		'and b.component_id = c.component_id and b.project_id = p.project_id '.
		'and b.status_id = st.status_id',
	'bug-prev-next' => 'select bug_id, reporter.login as reporter, '.
		'owner.login as owner '.
		'from '.TBL_BUG.' b '.
		'left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id '.
		'left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id '.
		'left join '.TBL_AUTH_USER.' lastmodifier on b.last_modified_by = lastmodifier.user_id '.
		'left join '.TBL_RESOLUTION.' resolution on b.resolution_id = resolution.resolution_id, '.
		TBL_SEVERITY.' severity, '.TBL_STATUS.' status, '.TBL_OS.' os, '.
		TBL_VERSION.' version, '.TBL_COMPONENT.' component, '.
		TBL_PROJECT.' project, '.TBL_SITE.' site '.
		'where b.severity_id = severity.severity_id '.
		'and b.status_id = status.status_id and b.os_id = os.os_id '.
		'and b.version_id = version.version_id '.
		'and b.component_id = component.component_id '.
		'and b.project_id = project.project_id and %s '.
		'and b.site_id = site.site_id '.
		'and bug_id <> %s '.
		'order by %s %s, bug_id asc',
	'bug-show-bug' =>  'select b.*, reporter.login as reporter, '.
		'owner.login as owner, status_name, resolution_name '.
		'from '.TBL_BUG.' b '.
		'left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id '.
		'left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id '.
		'left join '.TBL_RESOLUTION.' r on b.resolution_id = r.resolution_id, '.
		TBL_SEVERITY.' sv, '.TBL_STATUS.' st, '.TBL_SITE.' site '.
		'where bug_id = %s and b.project_id not in (%s) '.
		'and b.site_id = site.site_id '.
		'and b.severity_id = sv.severity_id and b.status_id = st.status_id',
	'functions-bug-cc' => 'select b.user_id, login '.
		'from '.TBL_BUG_CC.' b left join '. TBL_AUTH_USER.' using(user_id) '.
		'where bug_id = %s',
	'functions-project-js' => 'select p.project_id, project_name '.
		'from '.TBL_PROJECT. ' p '.
		'left join '.TBL_PROJECT_GROUP.' pg using(project_id) '.
		'where active = 1 and (pg.project_id is null or pg.group_id in (%s)) '.
		'group by p.project_id, p.project_name '.
		'order by project_name',
	'include-template-owner' => "SELECT sum(CASE WHEN s.status_id ".
		"in (".OPEN_BUG_STATUSES.") THEN 1 ELSE 0 END ) , ".
		"sum(CASE WHEN s.status_id ".
		"not in (".OPEN_BUG_STATUSES.") THEN 1 ELSE 0 END ) ".
		"from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) ".
		"where assigned_to = %s",
	'include-template-reporter' => "SELECT sum(CASE WHEN s.status_id in (".OPEN_BUG_STATUSES.") THEN 1 ELSE 0 END ) , ".
		"sum(CASE WHEN s.status_id not in (".OPEN_BUG_STATUSES.") THEN 1 ELSE 0 END ) ".
		"from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) ".
		"where created_by = %s",
	'index-projsummary-1' => 'select project_name as "Project", '.
		'sum(case when resolution_id = 0 then 1 else 0 end) as "Open"',
	'index-projsummary-2' => "select resolution_name, ",
	'index-projsummary-3' => "', sum(case when resolution_id = '",
	'index-projsummary-4' => "' then 1 else 0 end) as \"'",
	'index-projsummary-5' => " from ".TBL_RESOLUTION,
	'index-projsummary-6' => '%s, count(bug_id) as "Total" '.
		'from '.TBL_BUG. ' b left join '.TBL_PROJECT.' p using (project_id) '.
		'where b.project_id not in (%s) group by b.project_id, project_name '.
		'order by project_name',
	'query-list-bugs-count' => 'select count(*) '.
		'from '.TBL_BUG.' b '.
		'left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id '.
		'left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id where ',
	'query-list-bugs' => 'select %s '.
		'from '.TBL_BUG.' b '.
		'left join '.TBL_AUTH_USER.' owner on b.assigned_to = owner.user_id '.
		'left join '.TBL_AUTH_USER.' reporter on b.created_by = reporter.user_id '.
		'left join '.TBL_AUTH_USER.' lastmodifier on b.last_modified_by = lastmodifier.user_id '.
		'left join '.TBL_RESOLUTION.' resolution on b.resolution_id = resolution.resolution_id '.
		'left join '.TBL_DATABASE.' on b.database_id = '.TBL_DATABASE.'.database_id '.
		'left join '.TBL_VERSION.' version2 on b.to_be_closed_in_version_id = version2.version_id '.
		'left join '.TBL_VERSION.' version3 on b.closed_in_version_id = version3.version_id, '.
		TBL_SEVERITY.' severity, '.TBL_STATUS.' status, '.TBL_OS.' os, '.TBL_SITE.' site, '.
		TBL_VERSION.' version, '.TBL_COMPONENT.' component, '.TBL_PROJECT.' project '.
		'where b.severity_id = severity.severity_id '.
		'and b.status_id = status.status_id and b.os_id = os.os_id '.
		'and b.site_id = site.site_id and b.version_id = version.version_id '.
		'and b.component_id = component.component_id '.
		'and b.project_id = project.project_id %s '.
		'order by %s %s, bug_id asc',
	'report-resbyeng-1' => 'select email as "Assigned To", '.
		'sum(case when resolution_id = 0 then 1 else 0 end) as "Open"',
	'report-resbyeng-2' => "select resolution_name, ",
	'report-resbyeng-3' => "', sum(case when resolution_id = '",
	'report-resbyeng-4' => "' then 1 else 0 end) as \"'",
	'report-resbyeng-5' => " from ".TBL_RESOLUTION,
	'report-resbyeng-6' => '%s, count(bug_id) as "Total" '.
		'from '.TBL_BUG. ' b '.
		'left join '.TBL_AUTH_USER.' u on assigned_to = user_id %s '.
		'group by assigned_to, u.email',
	'join-where' => 'where',
	'admin-list-components' => 'select c.component_id, component_name, '.
		'c.created_date, active, count(bug_id) as bug_count '.
		'from '.TBL_COMPONENT.' c left join '.TBL_BUG.' b using(component_id) '.
		'where c.project_id = %s '.
		'group by c.component_id, c.component_name, c.created_date, c.active',
	'admin-list-versions' => 'select v.version_id, version_name, '.
		'v.created_date, active, count(bug_id) as bug_count '.
		'from '.TBL_VERSION.' v left join '.TBL_BUG.' b using(version_id) '.
		'where v.project_id = %s '.
		'group by v.version_id, v.version_name, v.created_date, v.active',
	);

?>
