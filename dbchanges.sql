create table bug_vote (user_id int not null, bug_id int not null, created_date bigint unsigned not null, primary key(user_id, bug_id));
insert into configuration values ('PROMOTE_VOTES', 5, 'The number of votes required to promote a bug from Unconfirmed to New', 'string');
insert into configuration values ('MAX_USER_VOTES', 5, 'The maximum number of votes a user can cast across all bugs', 'string');
