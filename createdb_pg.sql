BEGIN;

-- Host: localhost    Database: bugtracker
----------------------------------------------------------
-- $Id: createdb_pg.sql,v 1.3 2001/08/30 20:44:40 bcurtis Exp $
-- Table structure for table 'active_sessions'
--

CREATE TABLE "active_sessions" (
  "sid" varchar(32) NOT NULL DEFAULT '',
  "name" varchar(32) NOT NULL DEFAULT '',
  "val" text,
  "changed" varchar(14) NOT NULL DEFAULT '',
  PRIMARY KEY  (name,sid)
);

-- Table structure for table 'attachment'
--

CREATE TABLE "attachment" (
  "attachment_id" INT4  NOT NULL DEFAULT '0',
  "bug_id" INT4  NOT NULL DEFAULT '0',
  "file_name" char(255) NOT NULL DEFAULT '',
  "description" char(255) NOT NULL DEFAULT '',
  "file_size" INT8  NOT NULL DEFAULT '0',
  "mime_type" char(30) NOT NULL DEFAULT '',
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0',
  PRIMARY KEY  (attachment_id)
);

--
-- Table structure for table 'bug'
--

CREATE TABLE "bug" (
  "bug_id" INT4  NOT NULL DEFAULT '0',
  "bug_title" varchar(100) NOT NULL DEFAULT '',
  "bug_desc" "text" NOT NULL,
  "bug_url" varchar(255) NOT NULL DEFAULT '',
  "severity_id" INT2  NOT NULL DEFAULT '0',
  "priority" INT2  NOT NULL DEFAULT '0',
  "status_id" INT2  NOT NULL DEFAULT '0',
  "resolution_id" INT2  NOT NULL DEFAULT '0',
  "assigned_to" INT4  NOT NULL DEFAULT '0',
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0',
  "last_modified_by" INT4  NOT NULL DEFAULT '0',
  "last_modified_date" INT8  NOT NULL DEFAULT '0',
  "project_id" INT4  NOT NULL DEFAULT '0',
  "version_id" INT4  NOT NULL DEFAULT '0',
  "component_id" INT4  NOT NULL DEFAULT '0',
  "os_id" INT2  NOT NULL DEFAULT '0',
  "browser_string" varchar(255) NOT NULL DEFAULT '',
  "close_date" INT8  NOT NULL DEFAULT '0',
  PRIMARY KEY  (bug_id)
);

--
-- Table structure for table 'bug_history'
--

CREATE TABLE "bug_history" (
  "bug_id" INT4  NOT NULL DEFAULT '0',
  "changed_field" char(20) NOT NULL DEFAULT '',
  "old_value" char(255) NOT NULL DEFAULT '',
  "new_value" char(255) NOT NULL DEFAULT '',
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0'
);


--
-- Table structure for table 'comment'
--

CREATE TABLE "comment" (
  "comment_id" INT4  NOT NULL DEFAULT '0',
  "bug_id" INT4  NOT NULL DEFAULT '0',
  "comment_text" "text" NOT NULL,
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0',
  PRIMARY KEY  (comment_id)
);


--
-- Table structure for table 'component'
--

CREATE TABLE "component" (
  "component_id" INT4  NOT NULL DEFAULT '0',
  "project_id" INT4  NOT NULL DEFAULT '0',
  "component_name" varchar(30) NOT NULL DEFAULT '',
  "component_desc" "text" NOT NULL,
  "owner" INT4  NOT NULL DEFAULT '0',
  "active" INT2 NOT NULL DEFAULT '1',
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0',
  "last_modified_by" INT4  NOT NULL DEFAULT '0',
  "last_modified_date" INT8  NOT NULL DEFAULT '0',
  PRIMARY KEY  (component_id)
);


--
-- Table structure for table 'db_sequence'
--

CREATE TABLE "db_sequence" (
  "seq_name" varchar(127) NOT NULL DEFAULT '',
  "nextid" INT4  NOT NULL DEFAULT '0',
  PRIMARY KEY  (seq_name)
);

--
-- Dumping data for table 'db_sequence'
--

INSERT INTO "db_sequence" VALUES ('User',2);
INSERT INTO "db_sequence" VALUES ('OS',30);
INSERT INTO "db_sequence" VALUES ('Resolution',6);
INSERT INTO "db_sequence" VALUES ('Severity',7);
INSERT INTO "db_sequence" VALUES ('Status',7);
INSERT INTO "db_sequence" VALUES ('project',1);
INSERT INTO "db_sequence" VALUES ('version',1);
INSERT INTO "db_sequence" VALUES ('component',1);

--
-- Table structure for table 'os'
--

CREATE TABLE "os" (
  "os_id" INT4  NOT NULL DEFAULT '0',
  "os_name" char(30) NOT NULL DEFAULT '',
  "sort_order" INT2  NOT NULL DEFAULT '0',
  "regex" char(40) NOT NULL DEFAULT '',
  PRIMARY KEY  (os_id)
);

--
-- Dumping data for table 'os'
--

INSERT INTO "os" VALUES (1,'All',1,'');
INSERT INTO "os" VALUES (2,'Windows 3.1',2,'/Mozilla.*\\(Win16.*\\)/');
INSERT INTO "os" VALUES (3,'Windows 95',3,'/Mozilla.*\\(.*;.*; 32bit.*\\)/');
INSERT INTO "os" VALUES (4,'Windows 98',4,'/Mozilla.*\\(Win98.*\\)/');
INSERT INTO "os" VALUES (5,'Windows ME',5,'');
INSERT INTO "os" VALUES (6,'Windows 2000',6,'/Mozilla.*Windows NT 5.*\\)/');
INSERT INTO "os" VALUES (7,'Windows NT',7,'/Mozilla.*\\(Windows.*NT/');
INSERT INTO "os" VALUES (8,'Mac System 7',8,'');
INSERT INTO "os" VALUES (9,'Mac System 7.5',9,'');
INSERT INTO "os" VALUES (10,'Mac System 7.6.1',10,'');
INSERT INTO "os" VALUES (11,'Mac System 8.0',11,'');
INSERT INTO "os" VALUES (12,'Mac System 8.5',12,'/Mozilla.*\\(.*;.*; 68K.*\\)/');
INSERT INTO "os" VALUES (13,'Mac System 8.6',13,'/Mozilla.*\\(.*;.*; PPC.*\\)/');
INSERT INTO "os" VALUES (14,'Mac System 9.0',14,'');
INSERT INTO "os" VALUES (15,'Mac OS X',15,'');
INSERT INTO "os" VALUES (16,'Linux',16,'/Mozilla.*\\(.*;.*; Linux.*\\)/');
INSERT INTO "os" VALUES (17,'BSDI',17,'/Mozilla.*\\(.*;.*; BSD\\/OS.*\\)/');
INSERT INTO "os" VALUES (18,'FreeBSD',18,'/Mozilla.*\\(.*;.*; FreeBSD.*\\)/');
INSERT INTO "os" VALUES (19,'NetBSD',19,'');
INSERT INTO "os" VALUES (20,'OpenBSD',20,'');
INSERT INTO "os" VALUES (21,'AIX',21,'/Mozilla.*\\(.*;.*; AIX.*\\)/');
INSERT INTO "os" VALUES (22,'BeOS',22,'');
INSERT INTO "os" VALUES (23,'HP-UX',23,'/Mozilla.*\\(.*;.*; HP-UX.*\\)/');
INSERT INTO "os" VALUES (24,'IRIX',24,'/Mozilla.*\\(.*;.*; IRIX.*\\)/');
INSERT INTO "os" VALUES (25,'Neutrino',25,'');
INSERT INTO "os" VALUES (26,'OpenVMS',26,'');
INSERT INTO "os" VALUES (27,'OS/2',27,'');
INSERT INTO "os" VALUES (28,'OSF/1',28,'/Mozilla.*\\(.*;.*; OSF.*\\)/');
INSERT INTO "os" VALUES (29,'Solaris',29,'/Mozilla.*\\(.*;.*; SunOS 5.*\\)/');
INSERT INTO "os" VALUES (30,'SunOS',30,'/Mozilla.*\\(.*;.*; SunOS.*\\)/');
INSERT INTO "os" VALUES (31,'other',31,'');

--
-- Table structure for table 'project'
--

CREATE TABLE "project" (
  "project_id" INT4  NOT NULL DEFAULT '0',
  "project_name" varchar(30) NOT NULL DEFAULT '',
  "project_desc" "text" NOT NULL,
  "active" INT2 NOT NULL DEFAULT '1',
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0',
  "last_modified_by" INT4  NOT NULL DEFAULT '0',
  "last_modified_date" INT8  NOT NULL DEFAULT '0',
  PRIMARY KEY  (project_id)
);


CREATE TABLE "resolution" (
  "resolution_id" INT4  NOT NULL DEFAULT '0',
  "resolution_name" varchar(30) NOT NULL DEFAULT '',
  "resolution_desc" "text" NOT NULL,
  "sort_order" INT2  NOT NULL DEFAULT '0',
  PRIMARY KEY  (resolution_id)
);

--
-- Dumping data for table 'resolution'
--

INSERT INTO "resolution" VALUES (1,'Fixed','Bug was eliminated',1);
INSERT INTO "resolution" VALUES (2,'Not a bug','It\'s not a bug -- it\'s a feature!',2);
INSERT INTO "resolution" VALUES (3,'Won\'t Fix','This bug will stay',3);
INSERT INTO "resolution" VALUES (4,'Deferred','We\'ll get around to it later',4);
INSERT INTO "resolution" VALUES (5,'Works for me','Can\'t replicate the bug',5);
INSERT INTO "resolution" VALUES (6,'Duplicate','',6);

--
-- Table structure for table 'saved_query'
--

CREATE TABLE "saved_query" (
  "saved_query_id" INT4 NOT NULL DEFAULT '0',
  "user_id" INT4  NOT NULL DEFAULT '0',
  "saved_query_name" varchar(40) NOT NULL DEFAULT '',
  "saved_query_string" "text" NOT NULL,
  PRIMARY KEY  (saved_query_id,user_id)
);


--
-- Table structure for table 'severity'
--

CREATE TABLE "severity" (
  "severity_id" INT4  NOT NULL DEFAULT '0',
  "severity_name" varchar(30) NOT NULL DEFAULT '',
  "severity_desc" "text" NOT NULL,
  "sort_order" INT2  NOT NULL DEFAULT '0',
  "severity_color" varchar(10) NOT NULL DEFAULT '--FFFFFF',
  PRIMARY KEY  (severity_id)
);

--
-- Dumping data for table 'severity'
--

INSERT INTO "severity" VALUES (1,'Unassigned','Default bug creation',1,'#dadada');
INSERT INTO "severity" VALUES (2,'Idea','Ideas for further development',2,'#dad0d0');
INSERT INTO "severity" VALUES (3,'Feature Request','Requests for specific features',3,'#dacaca');
INSERT INTO "severity" VALUES (4,'Annoyance','Cosmetic problems or bugs not affecting performance',4,'#dac0c0');
INSERT INTO "severity" VALUES (5,'Content','Non-functional related bugs, such as text content',5,'#dababa');
INSERT INTO "severity" VALUES (6,'Significant','A bug affecting the intended performance of the product',6,'#dab0b0');
INSERT INTO "severity" VALUES (7,'Critical','A bug severe enough to prevent the release of the product',7,'#daaaaa');

--
-- Table structure for table 'status'
--

CREATE TABLE "status" (
  "status_id" INT4  NOT NULL DEFAULT '0',
  "status_name" varchar(30) NOT NULL DEFAULT '',
  "status_desc" "text" NOT NULL,
  "sort_order" INT2  NOT NULL DEFAULT '0',
  PRIMARY KEY  (status_id)
);

--
-- Dumping data for table 'status'
--

INSERT INTO "status" VALUES (1,'Unconfirmed','Reported but not confirmed',1);
INSERT INTO "status" VALUES (2,'New','A new bug',2);
INSERT INTO "status" VALUES (3,'Assigned','Assigned to a developer',3);
INSERT INTO "status" VALUES (4,'Reopened','Closed but opened again for further inspection',4);
INSERT INTO "status" VALUES (5,'Resolved','Set by engineer with a resolution',5);
INSERT INTO "status" VALUES (6,'Verified','The resolution is confirmed by the reporter',6);
INSERT INTO "status" VALUES (7,'Closed','The bug is officially squashed (QA)',7);

--
-- Table structure for table 'user'
--

CREATE TABLE "user" (
  "user_id" INT4  NOT NULL DEFAULT '0',
  "first_name" char(40) NOT NULL DEFAULT '',
  "last_name" char(40) NOT NULL DEFAULT '',
  "email" char(60) NOT NULL DEFAULT '',
  "password" char(40) NOT NULL DEFAULT '',
  "user_level" INT2  NOT NULL DEFAULT '1',
  "bug_list_fields" char(255) NOT NULL DEFAULT '',
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0',
  "last_modified_by" INT4  NOT NULL DEFAULT '0',
  "last_modified_date" INT8  NOT NULL DEFAULT '0',
  PRIMARY KEY  (user_id)
);

--
-- Dumping data for table 'user'
--

INSERT INTO "user" VALUES (1,'System','Admin','root@your.com','somepassword',15,'',0,998378431,0,998378431);

--
-- Table structure for table 'version'
--

CREATE TABLE "project" (
  "version_id" INT4  NOT NULL DEFAULT '0',
  "project_id" INT4  NOT NULL DEFAULT '0',
  "version_name" char(10) NOT NULL DEFAULT '',
  "active" INT2 NOT NULL DEFAULT '1',
  "created_by" INT4  NOT NULL DEFAULT '0',
  "created_date" INT8  NOT NULL DEFAULT '0',
  "last_modified_by" INT4  NOT NULL DEFAULT '0',
  "last_modified_date" INT8  NOT NULL DEFAULT '0',
  PRIMARY KEY  (version_id)
);

--
-- Indexes for table ACTIVE_SESSIONS
--

CREATE INDEX changed_active_sessions_index ON "active_sessions" ("changed");

COMMIT;
