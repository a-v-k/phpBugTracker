#!/usr/bin/python
#
#PROGRAM: gnats-to-phpbt.py
#
#DESCRIPTION: Converts a single GNATS bug tracking database entry to phpbt and
#inserts it; designed to be run in loop on all GNATS entries for a given
#project.  Do the following before running:
#
#1. Fill out the configuration setting
#2. Transfer over any user accounts manually; make sure to configure a
#default user account.
#3. Create the project that the GNATS bugs are for.
#4. Create the following status types: Feedback, Suspended, and Analyzed.
#
#USAGE: gnats-to-phpbt.py <filename>
#
#AUTHOR: Karen Pease (karen-pease@uiowa.edu)
#
#LICENSE: GPL
#

import sys,re,string,MySQLdb,time

##############################
#CONFIGURATION
#
db_host='my.host.name'
db_user='username'
db_passwd='password'
db_name='bug_tracker'
#
database_id="2"
#1=Oracle, 2=Mysql, 3=Postgres (should always be 2)
#
site_id="0"
#0=All, 1=Production 1, 2=Production 2, 3=Test 1, 4=Test 2
#
default_username="unknown"
#
project='Foobar'
version='2.0'
component="Default"
#
os='Linux'
#
##############################

False=0
True=1

if len(sys.argv)!=2:
	print "ERROR: This script takes one argument (the C++ file to process)"
	sys.exit(1)

filename=sys.argv[1]
fp=None
try:
	fp=open(filename,'r')
except:
	print "ERROR: File %s not found." % filename

data=fp.read()
fp.close()

data=string.replace(data,"'","''")
lines2=string.split(data,'\n')
lines=[]
for line in lines2:
	lines.append(line+"\n")

vars={}
started=False
last_var=None
for line in lines:
	if line[0]=='>' or line[:14]=='State-Changed-':
		started=True
		if line[0]=='>':
			line=line[1:]
		split=string.split(line,':',1)
		vars[split[0]]=split[1][1:]
		if last_var!=None:
			vars[last_var]=vars[last_var][:-1]	#Strip the trailing newline
		last_var=split[0]
	elif started==True:
		vars[last_var]=vars[last_var]+line

for key in vars.keys():
	while len(vars[key])>0 and vars[key][0]==' ':
		vars[key]=vars[key][1:]
	
db=MySQLdb.connect(host=db_host,user=db_user,passwd=db_passwd,db=db_name)
curs=db.cursor()

curs.execute("select id from phpbt_bug_seq")
bug_id_num=curs.fetchall()[0][0]
bug_id="%d" % (bug_id_num+1)

if vars['Priority']=="high":
	priority="2"
elif vars['Priority']=="medium":
	priority="1"
else:
	priority="0"

if priority=="2":
	curs.execute("select severity_id from phpbt_severity where severity_name='Critical'")
if priority=="1":
	curs.execute("select severity_id from phpbt_severity where severity_name='Significant'")
if priority=="0":
	curs.execute("select severity_id from phpbt_severity where severity_name='Annoyance'")
severity_id="%d" % curs.fetchall()[0][0]

state_name="Unconfirmed"
if vars['State']=="open":
	curs.execute("select status_id from phpbt_status where status_name='Assigned'")
elif vars['State']=="closed":
	curs.execute("select status_id from phpbt_status where status_name='Closed'")
elif vars['State']=="analyzed":
	curs.execute("select status_id from phpbt_status where status_name='Analyzed'")
elif vars['State']=="feedback":
	curs.execute("select status_id from phpbt_status where status_name='Feedback'")
elif vars['State']=="suspended":
	curs.execute("select status_id from phpbt_status where status_name='Suspended'")
status_id="%d" % curs.fetchall()[0][0]

resolution_id="1"		#Assign all of them to "Fixed"?

curs.execute("select user_id from phpbt_auth_user where login='"+vars['Responsible']+"'")
try:
	assigned_to="%d" % curs.fetchall()[0][0]
except IndexError:
	curs.execute("select user_id from phpbt_auth_user where login='"+default_username+"'")
	assigned_to="%d" % curs.fetchall()[0][0]
	
curs.execute("select user_id from phpbt_auth_user where login='"+vars['Submitter-Id']+"'")
try:
	created_by="%d" % curs.fetchall()[0][0]
except IndexError:
	curs.execute("select user_id from phpbt_auth_user where login='"+default_username+"'")
	created_by="%d" % curs.fetchall()[0][0]

date_str=vars['Arrival-Date']
date_str=date_str[:-10]+date_str[-4:]
created_date=time.mktime(time.strptime(date_str))
created_date="%d" % created_date

try:
	curs.execute("select user_id from phpbt_auth_user where login='"+vars['State-Changed-By']+"'")
	try:
		last_modified_by="%d" % curs.fetchall()[0][0]
	except IndexError:
		curs.execute("select user_id from phpbt_auth_user where login='"+default_username+"'")
		last_modified_by="%d" % curs.fetchall()[0][0]
except KeyError:
	last_modified_by="NULL"

try:
	date_str=vars['Last-Modified']
	date_str=date_str[:-10]+date_str[-4:]
	last_modified_date=time.mktime(time.strptime(date_str))
except KeyError:
	last_modified_date=0
last_modified_date="%d" % last_modified_date

curs.execute("select project_id from phpbt_project where project_name='"+project+"'")
project_id="%d" % curs.fetchall()[0][0]

curs.execute("select version_id from phpbt_version where version_name='"+version+"' and project_id="+project_id)
version_id="%d" % curs.fetchall()[0][0]

curs.execute("select component_id from phpbt_component where component_name='"+component+"'")
component_id="%d" % curs.fetchall()[0][0]

curs.execute("select os_id from phpbt_os where os_name='"+os+"'")
os_id="%d" % curs.fetchall()[0][0]

browser_string="''"

try:
	date_str=vars['Closed-Date']
	date_str=date_str[:-10]+date_str[-4:]
	close_date=time.mktime(time.strptime(date_str))
except KeyError:
	close_date=0
close_date="%d" % close_date
	
#print {"bug_id":filename,"title":vars['Synopsis'],\
#"description":vars['Description'],"severity_id":severity_id,\
#"priority":priority,"status_id":status_id,"resolution_id":resolution_id,\
#"database_id":database_id,"site_id":site_id,"assigned_to":assigned_to,\
#"created_by":created_by,"created_date":created_date,\
#"last_modified_by":last_modified_by,"last_modified_date":last_modified_date,\
#"project_id":project_id,"version_id":version_id,"component_id":component_id,\
#"os_id":os_id,"browser_string":browser_string,"close_date":close_date,\
#"closed_in_version_id":closed_in_version_id,
#"to_be_closed_version_id":to_be_closed_version_id}
#
#print "insert into phpbt_bug (bug_id, title, description, url, severity_id,\
#priority, status_id, resolution_id, database_id, site_id, assigned_to, created_by,\
#created_date, last_modified_by, last_modified_date, project_id, version_id,\
#component_id, os_id, browser_string, close_date, closed_in_version_id,\
#to_be_closed_in_version_id) values ("+filename+",'"+vars['Synopsis']+"','"+\
#vars['Description']+"','',"+severity_id+","+priority+","+status_id+","+\
#resolution_id+","+database_id+","+site_id+","+assigned_to+","+created_by+","+\
#created_date+","+last_modified_by+","+last_modified_date+","+project_id+","+\
#version_id+","+component_id+","+os_id+","+browser_string+","+close_date+","+\
#version_id+","+version_id+")"

curs.execute("insert into phpbt_bug (bug_id, title, description, url, severity_id,\
priority, status_id, resolution_id, database_id, site_id, assigned_to, created_by,\
created_date, last_modified_by, last_modified_date, project_id, version_id,\
component_id, os_id, browser_string, close_date, closed_in_version_id,\
to_be_closed_in_version_id) values ("+bug_id+",'"+vars['Synopsis']+"','"+\
vars['Description']+"','',"+severity_id+","+priority+","+status_id+","+\
resolution_id+","+database_id+","+site_id+","+assigned_to+","+created_by+","+\
created_date+","+last_modified_by+","+last_modified_date+","+project_id+","+\
version_id+","+component_id+","+os_id+","+browser_string+","+close_date+","+\
version_id+","+version_id+")")

curs.execute("update phpbt_bug_seq set id=%d" % (bug_id_num+1))

#curs.execute("select max(comment_id) from phpbt_comment")
#comment_id_num=curs.fetchall()[0][0]
#comment_id="%d" % (comment_id_num+1)
#
#curs.execute("insert into phpbt_comment (comment_id, bug_id, comment_text,\
#created_by,created_date) values ("+comment_id+","+bug_id+","+data+","+\
#created_by+","+created_date+")")

curs.close()
db.close()

