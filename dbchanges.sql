alter table auth_group add locked tinyint(1) not null default 0 after group_name;
update auth_group set locked = 1;
insert into db_sequence values('auth_group', 3); 

create table project_group (
  project_id int(10) unsigned NOT NULL default '0',
  group_id int(10) unsigned NOT NULL default '0',
  created_by int(10) unsigned NOT NULL default '0',
  created_date bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (project_id,group_id),
  KEY group_id (group_id)
) TYPE=MyISAM;
