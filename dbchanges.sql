CREATE TABLE `attachment` (
  `attachment_id` int(10) unsigned NOT NULL default '0',
  `bug_id` int(10) unsigned NOT NULL default '0',
  `file_name` char(255) NOT NULL default '',
  `description` char(255) NOT NULL default '',
  `file_size` bigint(20) unsigned NOT NULL default '0',
  `mime_type` char(30) NOT NULL default '',
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`attachment_id`)
);
insert into attachment select * from Attachment;

CREATE TABLE `bug` (
  `bug_id` int(10) unsigned NOT NULL default '0',
  `bug_title` varchar(100) NOT NULL default '',
  `bug_desc` text NOT NULL,
  `bug_url` varchar(255) NOT NULL default '',
  `severity_id` tinyint(3) unsigned NOT NULL default '0',
  `priority` tinyint(3) unsigned NOT NULL default '0',
  `status_id` tinyint(3) unsigned NOT NULL default '0',
  `resolution_id` tinyint(3) unsigned NOT NULL default '0',
  `assigned_to` int(10) unsigned NOT NULL default '0',
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0',
  `last_modified_by` int(10) unsigned NOT NULL default '0',
  `last_modified_date` bigint(20) unsigned NOT NULL default '0',
  `project_id` int(10) unsigned NOT NULL default '0',
  `version_id` int(10) unsigned NOT NULL default '0',
  `component_id` int(10) unsigned NOT NULL default '0',
  `op_sys_id` tinyint(3) unsigned NOT NULL default '0',
  `browser_string` varchar(255) NOT NULL default '',
	`close_date` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`bug_id`)
);
insert into bug select *, 0 from Bug;

CREATE TABLE `bug_history` (
  `bug_id` int(10) unsigned NOT NULL default '0',
  `changed_field` char(20) NOT NULL default '',
  `old_value` char(255) NOT NULL default '',
  `new_value` char(255) NOT NULL default '',
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0'
);
insert into bug_history select * from BugHistory;

CREATE TABLE `comment` (
  `comment_id` int(10) unsigned NOT NULL default '0',
  `bug_id` int(10) unsigned NOT NULL default '0',
  `comment_text` text NOT NULL,
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`comment_id`)
);
insert into comment select * from Comment;

CREATE TABLE `component` (
  `component_id` int(10) unsigned NOT NULL default '0',
  `project_id` int(10) unsigned NOT NULL default '0',
  `component_name` varchar(30) NOT NULL default '',
  `component_desc` text NOT NULL,
  `owner` int(10) unsigned NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1',
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0',
  `last_modified_by` int(10) unsigned NOT NULL default '0',
  `last_modified_date` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`component_id`)
);
insert into component select * from Component;

CREATE TABLE `os` (
  `os_id` int(10) unsigned NOT NULL default '0',
  `os_name` char(30) NOT NULL default '',
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  `regex` char(40) NOT NULL default '',
  PRIMARY KEY  (`os_id`)
);
insert into os select * from OS;

CREATE TABLE `project` (
  `project_id` int(10) unsigned NOT NULL default '0',
  `project_name` varchar(30) NOT NULL default '',
  `project_desc` text NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0',
  `last_modified_by` int(10) unsigned NOT NULL default '0',
  `last_modified_date` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`project_id`)
);
insert into project select *, CreatedBy, CreatedDate from Project;

CREATE TABLE `resolution` (
  `resolution_id` int(10) unsigned NOT NULL default '0',
  `resolution_name` varchar(30) NOT NULL default '',
  `resolution_desc` text NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`resolution_id`)
);
insert into resolution select * from Resolution;

CREATE TABLE `saved_query` (
  `saved_query_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `saved_query_name` varchar(40) NOT NULL default '',
  `saved_query_string` text NOT NULL,
  PRIMARY KEY  (`saved_query_id`,`user_id`)
);
insert into saved_query select * from SavedQuery;

CREATE TABLE `severity` (
  `severity_id` int(10) unsigned NOT NULL default '0',
  `severity_name` varchar(30) NOT NULL default '',
  `severity_desc` text NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  `severity_color` varchar(10) NOT NULL default '#FFFFFF',
  PRIMARY KEY  (`severity_id`)
);
insert into severity select *, null from Severity;
update severity set severity_color = '#dadada' where severity_id = '1';
update severity set severity_color = '#dad0d0' where severity_id = '2';
update severity set severity_color = '#dacaca' where severity_id = '3';
update severity set severity_color = '#dac0c0' where severity_id = '4';
update severity set severity_color = '#dababa' where severity_id = '5';
update severity set severity_color = '#dab0b0' where severity_id = '6';
update severity set severity_color = '#daaaaa' where severity_id = '7';

CREATE TABLE `status` (
  `status_id` int(10) unsigned NOT NULL default '0',
  `status_name` varchar(30) NOT NULL default '',
  `status_desc` text NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`status_id`)
);
insert into status select * from Status;

CREATE TABLE `auth_user` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `first_name` char(40) NOT NULL default '',
  `last_name` char(40) NOT NULL default '',
  `email` char(60) NOT NULL default '',
  `password` char(40) NOT NULL default '',
  `active` tinyint(3) unsigned NOT NULL default '1',
	`bug_list_fields` char(255) NOT NULL default '',
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0',
  `last_modified_by` int(10) unsigned NOT NULL default '0',
  `last_modified_date` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
);
insert into auth_user select UserID, FirstName, LastName, Email, Password, if (UserLevel > 0, 1, 0), '', 0, CreatedDate, 0, CreatedDate from User;

CREATE TABLE `version` (
  `version_id` int(10) unsigned NOT NULL default '0',
  `project_id` int(10) unsigned NOT NULL default '0',
  `version_name` char(10) NOT NULL default '',
  `active` tinyint(1) NOT NULL default '1',
  `created_by` int(10) unsigned NOT NULL default '0',
  `created_date` bigint(20) unsigned NOT NULL default '0',
  `last_modified_by` int(10) unsigned NOT NULL default '0',
  `last_modified_date` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`version_id`)
);
insert into version select *, CreatedBy, CreatedDate from Version;
