create table auth_group (group_id int unsigned not null, group_name varchar(80) not null, created_by int unsigned not null, created_date bigint unsigned not null, last_modified_by int unsigned not null, last_modified_date bigint unsigned not null, primary key (group_id));
create table auth_perm (perm_id int unsigned not null, perm_name varchar(80) not null, created_by int unsigned not null, created_date bigint unsigned not null, last_modified_by int unsigned not null, last_modified_date bigint unsigned not null, primary key (perm_id));
create table user_group (user_id int unsigned not null, group_id int unsigned not null, primary key (user_id, group_id), key (group_id));
create table user_perm (user_id int unsigned not null, perm_id int unsigned not null, primary key (user_id, perm_id), key (perm_id));
create table group_perm (group_id int unsigned not null, perm_id int unsigned not null, primary key (group_id, perm_id), key (perm_id));
create table bug_group (bug_id int unsigned not null, group_id int unsigned not null, primary key (bug_id, group_id), key (group_id));

# Start off with two user levels...
insert into auth_group (group_id, group_name) values (1, 'admin');
insert into auth_group (group_id, group_name) values (2, 'user');

# ... and only two permissions (how quaint)
insert into auth_perm (perm_id, perm_name) values (1, 'admin');
insert into auth_perm (perm_id, perm_name) values (2, 'editbug');

# Admins can do all the admin stuff and users can edit bugs
insert into group_perm (group_id, perm_id) values (1, 1);
insert into group_perm (group_id, perm_id) values (2, 2);

# And user_id 1 is the admin
insert into user_group (user_id, group_id) values (1, 1);

# You can use these queries to convert the post 0.2.x / pre 0.3.0 schema
alter table user rename auth_user;
alter table auth_user change user_level active tinyint unsigned not null;
update auth_user set active = 1 where active > 0;
 
