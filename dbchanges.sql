create table bug_dependency (bug_id int not null, depends_on int not null, primary key (bug_id, depends_on));
