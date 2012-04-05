<?php

// include.php - Set up global variables
// ------------------------------------------------------------------------
// Copyright (c) 2001 - 2004 The phpBugTracker Group
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
// $Id: include.php,v 1.151 2008/10/06 01:08:59 brycen Exp $

error_reporting(E_ALL);
ini_set('display_errors', true);

define('RAWERROR', true);

define('PHPBT_VERSION', '1.0rc6');
define('DIGICRAFT_TRACKER', '1');
@ini_set("session.save_handler", "files");

// Installation hasn't been completed
if (file_exists('config.php')) {
    if (!include_once('config.php')) {
        header("Location: install.php");
        exit();
    }
}
if (!defined('DB_HOST')) {
    header("Location: install.php");
    exit();
}

// Stupid magic quotes stuff
//@ini_set("magic_quotes_runtime", 0);
//if (DB_TYPE == 'oci8') {
//    @ini_set("magic_quotes_sybase", 1);
//} else {
//    @ini_set("magic_quotes_sybase", 0);
//}
// Grab the global functions
include ('inc/functions.php');

// PEAR::DB
//@ini_set("display_errors", true);
require_once(PEAR_PATH . 'DB.php');
//@ini_restore("display_errors");
$dsn = array(
    'phptype' => DB_TYPE,
    'hostspec' => DB_HOST,
    'database' => DB_DATABASE,
    'username' => DB_USER,
    'password' => DB_PASSWORD
);
/*
 * * Note confusing php5 E_STRICT error, which affects JPGraph use:
 * * "Non-static method DB::isError() should not be called statically "
 * * It's unclear what solution works for both php4 and php5.
 * * the documentation recommends PEAR::isError($db) which is also non-static
 */
$db = DB::Connect($dsn);
if (DB::isError($db)) {
    die($db->message . '<br>' . $db->userinfo);
}
$db->query("set names utf8");
/*
  $db = new DB();
  $db = $db->connect($dsn);
  if ($db->isError($db)) {
  die($db->message.'<br>'.$db->userinfo);
  }
 */
/*
  $db = &DB::connect($dsn);
  if (PEAR::isError($db)) {
  die($db->message.'<br>'.$db->userinfo);
  }
 */
$db->setOption('optimize', 'portability');
$db->setFetchMode(DB_FETCHMODE_ASSOC);
$db->setErrorHandling(PEAR_ERROR_CALLBACK, "handle_db_error");

if (empty($upgrading)) {
    // Set up the configuration variables
    $rs = $db->query('select varname, varvalue from ' . TBL_CONFIGURATION);
    while (list($k, $v) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
        if (!defined($k))
            define($k, $v);
    }
    define('OPEN_BUG_STATUSES', join(', ', $db->getCol("select status_id from " . TBL_STATUS . " where bug_open = 1")));

    // Set up translation and character set
    include_once('languages/' . LANGUAGE . '.php');
    if (!defined('CHARSET')) {
        if (!empty($STRING['charset'])) {
            define('CHARSET', $STRING['charset']);
        } else {
            define('CHARSET', 'utf-8');
        }
    }
} else {
    if (!defined('OPEN_BUG_STATUSES'))
        define('OPEN_BUG_STATUSES', '0');
    if (!defined('CHARSET'))
        define('CHARSET', 'utf-8');
    if (!defined('STYLE'))
        define('STYLE', 'default');
}
require_once ('inc/db/' . DB_TYPE . '.php');

$me = $_SERVER['PHP_SELF'];
$selrange = 30;
$now = time();

$all_db_fields = array(
    'bug_id' => 'ID',
    'title' => 'Title',
    'description' => 'Description',
    'url' => 'URL',
    'severity_name' => 'Severity',
    'priority_name' => 'Priority',
    'status_name' => 'Status',
    'resolution_name' => 'Resolution',
    'closed_in_version_name' => 'Closed in Version',
    'to_be_closed_in_version_name' => 'To be Closed in Version',
    'database_name' => 'Database',
    'site_name' => 'Site',
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
    'close_date' => 'Closed Date',
    'comments' => 'Comments',
    'attachments' => 'Attachments',
    'votes' => 'Votes'
);

$default_db_fields = array('bug_id', 'title', 'reporter', 'owner',
    'severity_name', 'priority_name', 'status_name', 'resolution_name');

require_once './inc/Smarty/libs/Smarty.class.php';

// Template class
class template {

    var $vars;

    function template() {
        $this->vars = array();
    }

    function render($content_template, $page_title, $wrap_file = '', $nowrap = false) {
        //error_reporting(E_ALL ^ E_NOTICE); // Clobber notices in template output
        extract($this->vars);
        $path = defined('TEMPLATE_PATH') ? './templates/' . THEME . '/' . TEMPLATE_PATH . '/' : './templates/' . THEME . '/';
        if ($nowrap) {
            if (substr($content_template, -4) == '.tpl') {
                $this->smarty->display($content_template);
            } else {
                include($this->template_path . '/' . $content_template);
            }
        } else {
            include ($wrap_file ? $this->template_path . '/' . $wrap_file : $this->template_path . '/' . 'wrap.html');
        }

        //include($nowrap ? $path . $content_template : ($wrap_file ? $path . $wrap_file : $path . 'wrap.html'));
    }

    function fetch($content_template) {
        if (substr($content_template, -4) == '.tpl') {
            return $this->smarty->fetch($content_template);
        } else {
            ob_start();
            $this->render($content_template, '', '', true);
            $rettext = ob_get_contents();
            ob_end_clean();
            return $rettext;
        }
    }

    function assign($var, $value = '') {

        if ($var == 'template_path') {
            $this->template_path = $value;
        }

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $this->vars[$k] = $v;
            }
        } else {
            $this->vars[$var] = $value;
        }
        $this->smarty->assign($var, $value);
    }

}

$t = new template();
$sm = new Smarty();
$t->smarty = &$sm;
$t->assign('STYLE', STYLE);
$sm->assign('STYLE', STYLE);


//$sm->f

if (defined('TEMPLATE_PATH')) {
    $t->assign('template_path', './templates/' . THEME . '/' . TEMPLATE_PATH);
    $sm->template_dir = './templates/' . THEME . '/' . TEMPLATE_PATH;
    $sm->setTemplateDir('./templates/' . THEME . '/' . TEMPLATE_PATH)
            ->setCompileDir('./tmp/templates_c')
            ->setCacheDir('./tmp/sm_cache');
} else {
    $t->assign('template_path', 'templates/' . THEME);
    $sm->template_dir = 'templates/' . THEME;
    $sm->setTemplateDir('templates/' . THEME)
            ->setCompileDir('tmp/templates_c')
            ->setCacheDir('tmp/sm_cache');
}



// End classes -- Begin page

if (!defined('NO_AUTH')) {
    session_start();
    $auth = new uauth;
    $perm = new uperm;
    $u = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
}

// Check to see if the user is trying to login
if (isset($_POST['dologin'])) {
    if (!empty($_POST['sendpass'])) {
        $username = $_POST['username'];
        list($email, $password) = $db->getRow("select email, password from " . TBL_AUTH_USER . " where login = '{$_POST['username']}' and active > 0", null, DB_FETCHMODE_ORDERED);
        if (!$email) {
            $t->assign('loginerror', '<div class="error">' . translate("Invalid login") . '</div>');
        } else {
            if (ENCRYPT_PASS) {
                $password = genpassword(10);
                $mpassword = md5($password);
                $db->query("update " . TBL_AUTH_USER . " set password = '$mpassword' where login = '$username'");
            }
            if (defined('EMAIL_DISABLED') and EMAIL_DISABLED) {
                $t->assign('loginerror', '<div class="result">' . translate("Your password has not been mailed to you because all system email has been disabled.") . '</div>');
            } else {
                qp_mail($email, translate("phpBugTracker Login"), sprintf(translate("Your phpBugTracker password is %s"), $password), ADMIN_EMAIL);
                $t->assign('loginerror', '<div class="result">' . translate("Your password has been emailed to you") . '</div>');
                $emailsuccess = true;
            }
        }
    } else {
        // If Invalid Login
        if (!$u = $auth->auth_validatelogin()) {
            $t->assign('loginerror', '<div class="error">' . translate("Invalid login") . '</div>');
            $username = $_POST['username'];
        }
        // If good login with saved URL: redirect user
        if (isset($_POST['redirect_url'])) {
            header("location: $_POST[redirect_url]");
            exit(0);
        }
    }

    // "Remember me" handling
    if (RECALL_LOGIN) {
        if (!empty($_POST["savecookie"])) {
            setcookie('phpbt_user', $_POST["username"], $now + 18144000); // 3 week expiration
        } elseif (!empty($_COOKIE['phpbt_user'])) {
            // Clear the cookie if the cookie is populated and the box wasn't checked
            setcookie('phpbt_user');
        }
    }
}

if (!empty($u)) {
//	echo "<h1>I'm here too</h1>"; //test
//	var_dump($QUERY['include-template-owner']);
    list($owner_open, $owner_closed) =
            $db->getRow(sprintf($QUERY['include-template-owner'], $u), DB_FETCHMODE_ORDERED);
    list($reporter_open, $reporter_closed) =
            $db->getRow(sprintf($QUERY['include-template-reporter'], $u), DB_FETCHMODE_ORDERED);
    list($bookmarks_open, $bookmarks_closed) =
            $db->getRow(sprintf($QUERY['include-template-bookmark'], $u), DB_FETCHMODE_ORDERED);
    $t->assign(array(
        'owner_open' => $owner_open ? $owner_open : 0,
        'owner_closed' => $owner_closed ? $owner_closed : 0,
        'reporter_open' => $reporter_open ? $reporter_open : 0,
        'reporter_closed' => $reporter_closed ? $reporter_closed : 0,
        'bookmarks_open' => $bookmarks_open ? $bookmarks_open : 0,
        'bookmarks_closed' => $bookmarks_closed ? $bookmarks_closed : 0,
        'perm' => $perm,
    ));
}

if (defined('FORCE_LOGIN') and FORCE_LOGIN and empty($u) and !defined('NO_AUTH')) {
    // Save URL for after login
    $incoming_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $t->assign('incoming_url', $incoming_url);

    // Basic template
    include('templates/' . THEME . '/login.html');
    exit;
}

$op = !empty($_REQUEST['op']) ? $_REQUEST['op'] : '';

if (!defined('NO_AUTH')) {
    // Check to see if we have projects that shouldn't be visible to the user
    $restricted_projects = '0';
    if (!$perm->have_perm('Admin')) {
        $viewable_projects = @join(',', $db->getCol("select project_id from " . TBL_PROJECT_GROUP . " where group_id in (" . delimit_list(',', $_SESSION['group_ids']) . ")"));
        $viewable_projects = $viewable_projects ? $viewable_projects : '0';
        $matching_projects = delimit_list(',', $db->getCol("select project_id from " . TBL_PROJECT_GROUP . " where project_id not in ($viewable_projects) group by project_id"));
        if ($matching_projects) {
            $restricted_projects .= ",$matching_projects";
        }
    } else {
        $viewable_projects = @join(',', $db->getCol("select project_id from " . TBL_PROJECT));
    }
}
