# Database creation script (MySQL)
# If you change the database name, make sure you change it in dbclass in 
# include.php.  Make sure you edit the User insert below.

create database BugTracker;
use BugTracker;

#
# Table structure for table 'db_sequence'
#

DROP TABLE IF EXISTS db_sequence;
CREATE TABLE db_sequence (
  seq_name varchar(127) DEFAULT '' NOT NULL,
  nextid int(10) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (seq_name)
);

#
# Table structure for table 'User'
#

DROP TABLE IF EXISTS User;
CREATE TABLE User (
  UserID int(10) unsigned DEFAULT '0' NOT NULL,
  FirstName char(40) DEFAULT '' NOT NULL,
  LastName char(40) DEFAULT '' NOT NULL,
  Email char(60) DEFAULT '' NOT NULL,
  Password char(40) DEFAULT '' NOT NULL,
  UserLevel tinyint(3) unsigned DEFAULT '1' NOT NULL,
  CreatedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (UserID)
);

# -- EDIT THIS --
insert into User values (1, 'System', 'Admin', 'root@your.com', 'somepassword', 15, unix_timestamp(now()));
insert into db_sequence values ('User', 1);

#
# Table structure for table 'Bug'
#

DROP TABLE IF EXISTS Bug;
CREATE TABLE Bug (
  BugID int(10) unsigned DEFAULT '0' NOT NULL,
  Title varchar(30) DEFAULT '' NOT NULL,
  Description text DEFAULT '' NOT NULL,
  URL varchar(255) DEFAULT '' NOT NULL,
  Severity tinyint(3) unsigned DEFAULT '0' NOT NULL,
  Priority tinyint(3) unsigned DEFAULT '0' NOT NULL,
  Status tinyint(3) unsigned DEFAULT '0' NOT NULL,
  Resolution tinyint(3) unsigned DEFAULT '0' NOT NULL,
  AssignedTo int(10) unsigned DEFAULT '0' NOT NULL,
  CreatedBy int(10) unsigned DEFAULT '0' NOT NULL,
  CreatedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  LastModifiedBy int(10) unsigned DEFAULT '0' NOT NULL,
  LastModifiedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  Project int(10) unsigned DEFAULT '0' NOT NULL,
  Version varchar(5) DEFAULT '' NOT NULL,
  Component int(10) unsigned DEFAULT '0' NOT NULL,
  OS tinyint(3) unsigned DEFAULT '0' NOT NULL,
  BrowserString varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (BugID)
);

#
# Table structure for table 'Comment'
#

DROP TABLE IF EXISTS Comment;
CREATE TABLE Comment (
  CommentID int(10) unsigned DEFAULT '0' NOT NULL,
  BugID int(10) unsigned DEFAULT '0' NOT NULL,
  Text text DEFAULT '' NOT NULL,
  CreatedBy int(10) unsigned DEFAULT '0' NOT NULL,
  CreatedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (CommentID)
);

#
# Table structure for table 'Component'
#

DROP TABLE IF EXISTS Component;
CREATE TABLE Component (
  ComponentID int(10) unsigned DEFAULT '0' NOT NULL,
  ProjectID int(10) unsigned DEFAULT '0' NOT NULL,
  Name varchar(30) DEFAULT '' NOT NULL,
  Description text DEFAULT '' NOT NULL,
  Owner int(10) unsigned DEFAULT '0' NOT NULL,
  Active char(1) binary DEFAULT '1' NOT NULL,
  CreatedBy int(10) unsigned DEFAULT '0' NOT NULL,
  CreatedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  LastModifiedBy int(10) unsigned DEFAULT '0' NOT NULL,
  LastModifiedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (ComponentID)
);

#
# Table structure for table 'OS'
#

DROP TABLE IF EXISTS OS;
CREATE TABLE OS (
  OSID int(10) unsigned DEFAULT '0' NOT NULL,
  Name char(30) DEFAULT '' NOT NULL,
  SortOrder tinyint(3) unsigned DEFAULT '0' NOT NULL,
  Regex char(40) DEFAULT '' NOT NULL,
  PRIMARY KEY (OSID)
);

#
# Dumping data for table 'OS'
#

INSERT INTO OS VALUES (1,'All',1,'');
INSERT INTO OS VALUES (2,'Windows 3.1',2,'/Mozilla.*\\(Win16.*\\)/');
INSERT INTO OS VALUES (3,'Windows 95',3,'/Mozilla.*\\(.*;.*; 32bit.*\\)/');
INSERT INTO OS VALUES (4,'Windows 98',4,'/Mozilla.*\\(Win98.*\\)/');
INSERT INTO OS VALUES (5,'Windows ME',5,'');
INSERT INTO OS VALUES (6,'Windows 2000',6,'/Mozilla.*Windows NT 5.*\\)/');
INSERT INTO OS VALUES (7,'Windows NT',7,'/Mozilla.*\\(Windows.*NT/');
INSERT INTO OS VALUES (8,'Mac System 7',8,'');
INSERT INTO OS VALUES (9,'Mac System 7.5',9,'');
INSERT INTO OS VALUES (10,'Mac System 7.6.1',10,'');
INSERT INTO OS VALUES (11,'Mac System 8.0',11,'');
INSERT INTO OS VALUES (12,'Mac System 8.5',12,'/Mozilla.*\\(.*;.*; 68K.*\\)/');
INSERT INTO OS VALUES (13,'Mac System 8.6',13,'/Mozilla.*\\(.*;.*; PPC.*\\)/');
INSERT INTO OS VALUES (14,'Mac System 9.0',14,'');
INSERT INTO OS VALUES (15,'Linux',15,'/Mozilla.*\\(.*;.*; Linux.*\\)/');
INSERT INTO OS VALUES (16,'BSDI',16,'/Mozilla.*\\(.*;.*; BSD\\/OS.*\\)/');
INSERT INTO OS VALUES (17,'FreeBSD',17,'/Mozilla.*\\(.*;.*; FreeBSD.*\\)/');
INSERT INTO OS VALUES (18,'NetBSD',18,'');
INSERT INTO OS VALUES (19,'OpenBSD',19,'');
INSERT INTO OS VALUES (20,'AIX',20,'/Mozilla.*\\(.*;.*; AIX.*\\)/');
INSERT INTO OS VALUES (21,'BeOS',21,'');
INSERT INTO OS VALUES (22,'HP-UX',22,'/Mozilla.*\\(.*;.*; HP-UX.*\\)/');
INSERT INTO OS VALUES (23,'IRIX',23,'/Mozilla.*\\(.*;.*; IRIX.*\\)/');
INSERT INTO OS VALUES (24,'Neutrino',24,'');
INSERT INTO OS VALUES (25,'OpenVMS',25,'');
INSERT INTO OS VALUES (26,'OS/2',26,'');
INSERT INTO OS VALUES (27,'OSF/1',27,'/Mozilla.*\\(.*;.*; OSF.*\\)/');
INSERT INTO OS VALUES (28,'Solaris',28,'/Mozilla.*\\(.*;.*; SunOS 5.*\\)/');
INSERT INTO OS VALUES (29,'SunOS',29,'/Mozilla.*\\(.*;.*; SunOS.*\\)/');
INSERT INTO OS VALUES (30,'other',30,'');

insert into db_sequence values ('OS', 30);

#
# Table structure for table 'Project'
#

DROP TABLE IF EXISTS Project;
CREATE TABLE Project (
  ProjectID int(10) unsigned DEFAULT '0' NOT NULL,
  Name varchar(30) DEFAULT '' NOT NULL,
  Description text DEFAULT '' NOT NULL,
  Active char(1) binary DEFAULT '1' NOT NULL,
  CreatedBy int(10) unsigned DEFAULT '0' NOT NULL,
  CreatedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (ProjectID)
);

#
# Table structure for table 'Resolution'
#

DROP TABLE IF EXISTS Resolution;
CREATE TABLE Resolution (
  ResolutionID int(10) unsigned DEFAULT '0' NOT NULL,
  Name varchar(30) DEFAULT '' NOT NULL,
  Description text DEFAULT '' NOT NULL,
  SortOrder tinyint(3) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (ResolutionID)
);

#
# Dumping data for table 'Resolution'
#

INSERT INTO Resolution VALUES (1,'Fixed','Bug was eliminated',1);
INSERT INTO Resolution VALUES (2,'Not a bug','It\'s not a bug -- it\'s a feature!',2);
INSERT INTO Resolution VALUES (3,'Won\'t Fix','This bug will stay',3);
INSERT INTO Resolution VALUES (4,'Deferred','We\'ll get around to it later',4);
INSERT INTO Resolution VALUES (5,'Works for me','Can\'t replicate the bug',5);
INSERT INTO Resolution VALUES (6,'Duplicate','',6);

insert into db_sequence values ('Resolution', 6);

#
# Table structure for table 'SavedQuery'
#

DROP TABLE IF EXISTS SavedQuery;
CREATE TABLE SavedQuery (
   SavedQueryID int(10) unsigned NOT NULL auto_increment,
   UserID int(10) unsigned DEFAULT '0' NOT NULL,
   SavedQueryName varchar(40) NOT NULL,
   SavedQueryString text NOT NULL,
   PRIMARY KEY (SavedQueryID, UserID)
);

#
# Table structure for table 'Severity'
#

DROP TABLE IF EXISTS Severity;
CREATE TABLE Severity (
  SeverityID int(10) unsigned DEFAULT '0' NOT NULL,
  Name varchar(30) DEFAULT '' NOT NULL,
  Description text DEFAULT '' NOT NULL,
  SortOrder tinyint(3) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (SeverityID)
);

#
# Dumping data for table 'Severity'
#

INSERT INTO Severity VALUES (1,'Unassigned','Default bug creation',1);
INSERT INTO Severity VALUES (2,'Idea','Ideas for further development',2);
INSERT INTO Severity VALUES (3,'Feature Request','Requests for specific features',3);
INSERT INTO Severity VALUES (4,'Annoyance','Cosmetic problems or bugs not affecting performance',4);
INSERT INTO Severity VALUES (5,'Content','Non-functional related bugs, such as text content',5);
INSERT INTO Severity VALUES (6,'Significant','A bug affecting the intended performance of the product',6);
INSERT INTO Severity VALUES (7,'Critical','A bug severe enough to prevent the release of the product',7);

insert into db_sequence values ('Severity', 7);

#
# Table structure for table 'Status'
#

DROP TABLE IF EXISTS Status;
CREATE TABLE Status (
  StatusID int(10) unsigned DEFAULT '0' NOT NULL,
  Name varchar(30) DEFAULT '' NOT NULL,
  Description text DEFAULT '' NOT NULL,
  SortOrder tinyint(3) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (StatusID)
);

#
# Dumping data for table 'Status'
#

INSERT INTO Status VALUES (1,'Unconfirmed','Reported but not confirmed',1);
INSERT INTO Status VALUES (2,'New','A new bug',2);
INSERT INTO Status VALUES (3,'Assigned','Assigned to a developer',3);
INSERT INTO Status VALUES (4,'Reopened','Closed but opened again for further inspection',4);
INSERT INTO Status VALUES (5,'Resolved','',5);
INSERT INTO Status VALUES (6,'Verified','Confirmed as a bug',6);
INSERT INTO Status VALUES (7,'Closed','',7);

insert into db_sequence values ('Status', 7);

#
# Table structure for table 'Version'
#

DROP TABLE IF EXISTS Version;
CREATE TABLE Version (
  VersionID int(10) unsigned DEFAULT '0' NOT NULL,
  ProjectID int(10) unsigned DEFAULT '0' NOT NULL,
  Name char(10) DEFAULT '' NOT NULL,
  Active char(1) binary DEFAULT '' NOT NULL,
  CreatedBy int(10) unsigned DEFAULT '0' NOT NULL,
  CreatedDate bigint(20) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (VersionID)
);

#
# Table structure for table 'active_sessions'
#

DROP TABLE IF EXISTS active_sessions;
CREATE TABLE active_sessions (
  sid varchar(32) DEFAULT '' NOT NULL,
  name varchar(32) DEFAULT '' NOT NULL,
  val text,
  changed varchar(14) DEFAULT '' NOT NULL,
  PRIMARY KEY (name,sid),
  KEY changed (changed)
);

CREATE TABLE `BugHistory` (
  `BugID` int(10) unsigned NOT NULL default '0',
  `ChangedField` char(20) NOT NULL default '',
  `OldValue` char(255) NOT NULL default '',
  `NewValue` char(255) NOT NULL default '',
  `CreatedBy` int(10) unsigned NOT NULL default '0',
  `CreatedDate` bigint(20) unsigned NOT NULL default '0'
);
