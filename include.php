<?php

// include.php - Set up global variables
// ------------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
// ------------------------------------------------------------------------
// This file is part of phpBugTracker
//
// phpBugTracker is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// phpBugTracker is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with phpBugTracker; if not, write to the Free Software Foundation,
// Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
// ------------------------------------------------------------------------
// $Id: include.php,v 1.93 2002/01/26 16:46:52 bcurtis Exp $

// Where are we?
define ('INSTALL_PATH', dirname(__FILE__));

if (!@include (INSTALL_PATH.'/config.php')) {
  header("Location: install.php");
  exit();
}
if (!defined('DB_HOST')) { // Installation hasn't been completed
  header("Location: install.php");
  exit();
}

// Grab the global functions
include (INSTALL_PATH.'/inc/functions.php');

class dbclass extends DB_Sql {
  var $classname = 'dbclass';
  var $Host = DB_HOST;
  var $Database = DB_DATABASE;
  var $User = DB_USER;
  var $Password = DB_PASSWORD;
  var $Seq_Table = TBL_DB_SEQUENCE;
  var $Seq_ID_Col    = "nextid";
  var $Seq_Name_Col  = "seq_name";

  // Attempt to handle different limit syntax
  function limit_query($q_string, $limit, $offset = 0) {
    if ($offset) {
      if (DB_TYPE == 'pgsql') {
        $this->query("$q_string limit $limit offset $offset");
      } else {
        $this->query("$q_string limit $offset, $limit");
      }
    } else {
      $this->query("$q_string limit $limit");
    }
  }

  // Handle the different types of concats
  function concat() {
    $pieces = func_get_args();
    if (DB_TYPE == 'pgsql') {
      return delimit_list(' || ', $pieces);
    } else {
      return 'concat('. delimit_list(', ', $pieces).')';
    }
  }

  function grab($q_string = '') {
    if ($q_string) $this->query($q_string);
    $this->next_record();
    return $this->Record;
  }

  function grab_field($q_string = '') {
    list($retval) = $this->grab($q_string);
    return $retval;
  }

  function grab_set($q_string = '') {
    $retary = array();
    if ($q_string) $this->query($q_string);
    while ($row = $this->grab()) { $retary[] = $row; }
    return $retary;
  }

  function grab_field_set($q_string = '') {
    $retary = array();
    if ($q_string) $this->query($q_string);
    while ($item = $this->grab_field()) { $retary[] = $item; }
    return $retary;
  }
  function nextid($seq_name) {
    global $auth;

    if ($seq_name == TBL_SAVED_QUERY) {
      if ($id = $this->grab_field("select max(saved_query_id)+1 from ".TBL_SAVED_QUERY." where user_id = ".$auth->auth['uid'])) {
        return $id;
      } else {
        return 1;
      }
    } else {
      return DB_Sql::nextid($seq_name);
    }
  }
}

$q = new dbclass;

// Set up the configuration variables
$q->query('select varname, varvalue from '.TBL_CONFIGURATION);
while (list($k, $v) = $q->grab()) {
  define($k, $v);
}

// Localization - include the file with the desired language
include INSTALL_PATH.'/languages/'.LANGUAGE.'.php';

$me = $HTTP_SERVER_VARS['PHP_SELF'];
$me2 = $HTTP_SERVER_VARS['REQUEST_URI'];
$selrange = 30;
$now = time();
$_gv =& $HTTP_GET_VARS;
$_pv =& $HTTP_POST_VARS;

$all_db_fields = array(
  'bug_id' => 'ID',
  'title' => 'Title',
  'description' => 'Description',
  'url' => 'URL',
  'severity_name' => 'Severity',
  'priority' => 'Priority',
  'status_name' => 'Status',
  'resolution_name' => 'Resolution',
  'reporter' => 'Reporter',
  'owner' => 'Owner',
  'created_date' => 'Created Date',
  'lastmodifier' => 'Last Modified By',
  'last_modified_date' => 'Last Modified Date',
  'project_name' => 'Project',
  'version_name' => 'Version',
  'component_name' => 'Component',
  'os_name' => 'OS',
  'browser_string' => 'Browser',
  'close_date' => 'Closed Date'
  );

$default_db_fields = array('bug_id', 'title', 'reporter', 'owner',
  'severity_name', 'priority', 'status_name', 'resolution_name');

class templateclass extends Template {
  function pparse($target, $handle, $append = false) {
    global $auth, $perm, $q;

    $u = isset($auth->auth['uid']) ? $auth->auth['uid'] : 0;
    $this->set_block('wrap', 'logoutblock', 'loblock');
    $this->set_block('wrap', 'loginblock', 'liblock');
    $this->set_block('wrap', 'adminnavblock', 'anblock');
    if ($u) {
      list($owner_open, $owner_closed) = $q->grab("SELECT sum(CASE WHEN status_name in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END ) ,"
        ."sum(CASE WHEN status_name not in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END )"
        ."from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) where assigned_to = $u");
      list($reporter_open, $reporter_closed) = $q->grab("SELECT sum(CASE WHEN status_name in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END ) ,"
        ."sum(CASE WHEN status_name not in ('Unconfirmed','New','Assigned','Reopened') THEN 1 ELSE 0 END )"
        ."from ".TBL_BUG." b left join ".TBL_STATUS." s using(status_id) where created_by = $u");
      $this->set_var(array(
        'loggedinas' => $auth->auth['uname'],
        'liblock' => '',
        'owner_open' => $owner_open ? $owner_open : 0,
        'owner_closed' => $owner_closed ? $owner_closed : 0,
        'reporter_open' => $reporter_open ? $reporter_open : 0,
        'reporter_closed' => $reporter_closed ? $reporter_closed : 0
        ));
      $this->parse('loblock', 'logoutblock', true);
    } else {
      $this->set_var(array(
        'loggedinas' => '',
        'loblock' => '',
        'loginlabel' => EMAIL_IS_LOGIN ? 'Email' : 'Login'
        ));
      $this->parse('liblock', 'loginblock', true);
    }
    if (isset($perm) && $perm->have_perm('Administrator')) {
      $this->parse('anblock', 'adminnavblock', true);
    } else {
      $this->set_var('anblock', '');
    }
    print $this->finish($this->parse($target, $handle, $append));
    return false;
  }
}

if (defined('TEMPLATE_PATH')) {
  $t = new templateclass(INSTALL_PATH.'/templates/'.THEME.'/'.TEMPLATE_PATH, 'keep');
	$t->set_var('template_path', '../templates/'.THEME.'/'.TEMPLATE_PATH);
} else {
  $t = new templateclass(INSTALL_PATH.'/templates/'.THEME, 'keep');
	$t->set_var('template_path', 'templates/'.THEME);
}

$t->set_var(array(
  'TITLE' => '',
  'me' => $me,
  'me2' => $me2,
  'error' => '',
  'loginerror' => ''));

// End classes -- Begin page

if (!defined('NO_AUTH')) {
  session_start();
  $_sv =& $HTTP_SESSION_VARS;
  $auth = new uauth;
  $perm = new uperm;
  $u = isset($auth->auth['uid']) ? $auth->auth['uid'] : 0;
}

// Check to see if the user is trying to login
if (isset($_pv['dologin'])) {
  if (isset($_pv['sendpass'])) {
    list($email, $password) = $q->grab("select email, password from ".TBL_AUTH_USER." where login = '{$_pv['username']}' and active > 0");
    if (!$q->num_rows()) {
      $t->set_var('loginerror', '<div class="error">Invalid login</div>');
    } else {
      if (ENCRYPT_PASS) {
        $password = genpassword(10);
        $mpassword = md5($password);
        $q->query("update ".TBL_AUTH_USER." set password = '$mpassword' where login = '$username'");
      }
      mail($email, $STRING['newacctsubject'], sprintf($STRING['newacctmessage'],
        $password),  sprintf("From: %s\nContent-Type: text/plain; charset=%s\nContent-Transfer-Encoding: 8bit\n",ADMIN_EMAIL, $STRING['lang_charset']));
      $t->set_var('loginerror',
        '<div class="result">Your password has been emailed to you</div>');
    }
  } else {
    if (!$u = $auth->auth_validatelogin()) {
      $t->set_var('loginerror', '<div class="error">Invalid login</div>');
    }
  }
}

$op = isset($_gv['op']) ? $_gv['op'] : (isset($_pv['op']) ? $_pv['op'] : '');

?>
