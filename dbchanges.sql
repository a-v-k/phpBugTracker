alter table auth_group add locked tinyint(1) not null default 0 after group_name;
update auth_group set locked = 1;
insert into db_sequence values('auth_group', 3); 
