<?php

// auth.php - Authentication and permission objects
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
// Based on and/or directly from PHPlib, which is
// Copyright (c) 1998-2000 NetUSE AG -- Boris Erdmann, Kristian Koehntopp

class uauth {

    var $lifetime = 0; // In minutes -- 0 for no expiration until browser closed
    var $classname = 'uauth';

    function __construct() {
        //global $group_ids, $uname, $db_fields, $group, $perms,
        //$uid, $exp;

        if (!isset($_SESSION['group_ids'])) {
            $_SESSION['group_ids'] = array(0);
        }

        if ($this->is_authenticated()) {
            if ($_SESSION['uid']) {
                $_SESSION['exp'] = time() + (60 * $this->lifetime);
            }
        }
    }

    function is_authenticated() {

        if (isset($_SESSION['uid']) && $_SESSION['uid'] && ($this->lifetime <= 0 || time() < $_SESSION['exp'])) {
            return $_SESSION['uid'];
        } else {
            return false;
        }
    }

    function force_auth() {
        if (!$this->is_authenticated()) {
            show_text('You must be logged in to use this page');
            exit;
        }
    }

    function auth_validatelogin() {
        global $db; //, $select, $emailpass, $emailsuccess, $uid;

        $role = array();
        $roles = $db->getAll("select group_id, group_name from " . TBL_AUTH_GROUP . " ag where ag.is_role=1");

        foreach ($roles as $r) {
            $role[$r['group_name']] = $r['group_id'];
        }

        $_SESSION['group'] = array();
        $_SESSION['group_ids'] = array(0);
        $_SESSION['perms'] = array();
        $_SESSION['uname'] = null;

        //extract($_POST);
        //generate_inputs_die($_POST);
        //array (size=3)
        //  'username' => string 'login' (length=16)
        //  'password' => string 'pass' (length=8)
        //  'dologin' => string '1' (length=1)
        $username = trim(get_post_val('username', null));
        $password = trim(get_post_val('password', null));
        //$dologin = get_post_int('dologin', null);

        if ($username == '') {
            return 0;
        }
        if (ENCRYPT_PASS) {
            $password = md5($password);
        }

        $u = $db->getRow("select * from " . TBL_AUTH_USER . " where login = " . $db->quote($username) . " and password = " . $db->quote($password) . " and active > 0");
        if (($u === false) || (!isset($u['login']))) {
            return 0;
        } else {
            $_SESSION['uname'] = $username;
            $_SESSION['db_fields'] = @unserialize($u['bug_list_fields']);

            // Grab group assignments and permissions based on groups
            $rs = $db->query("select u.group_id, group_name from " . TBL_USER_GROUP . " u, " . TBL_AUTH_GROUP . " a where user_id = " . $db->quote($u['user_id']) . " and u.group_id = a.group_id");
            while (list($groupid, $groupname) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
                $_SESSION['group_ids'][] = $groupid;
                $_SESSION['group'][$groupname] = true;
            }
            $_SESSION['group_ids'][] = $role['User'];
            $_SESSION['group']['User'] = true;

            $perms = $db->getCol("select perm_name from " . TBL_AUTH_PERM . " ap, " . TBL_GROUP_PERM . " gp where group_id in (" . @join(',', $_SESSION['group_ids']) . ") and gp.perm_id = ap.perm_id");
            foreach ($perms as $perm) {
                $_SESSION['perms'][$perm] = true;
            }
            $_SESSION['uid'] = $u['user_id'];
            $projs = $db->getCol("select project_id from " . TBL_PROJECT_PERM . " where user_id = " . $_SESSION['uid']);
            foreach ($projs as $proj) {
                $_SESSION['projs'][$proj] = true;
            }

            return $u['user_id'];
        }
    }

    function unauth() {

        $_SESSION['uid'] = 0;
        $_SESSION['perms'] = array();
        $_SESSION['exp'] = 0;
        $_SESSION['group'] = array();
        $_SESSION['group_ids'] = array(0);
        $_SESSION['db_fields'] = array();
        $_SESSION['queryinfo'] = array();
        $_SESSION = array();
    }

}

class uperm {

    var $classname = 'uperm';
    var $permissions = array();

    function isAdmin() {
        return isset($_SESSION['perms']['Admin']) && ($_SESSION['perms']['Admin'] == true);
    }

    function check($p, $proj = 0) {

        if (!$this->have_perm($p, $proj)) {
            if (!isset($_SESSION['perms'])) {
                $_SESSION['perms'] = '';
            }
            $this->perm_invalid($_SESSION['perms'], $p);
            exit();
        }
    }

    function check_proj($project_id = -1) {

        if ($this->have_perm_proj($project_id)) {
            return true;
        } else {
            $this->perm_invalid($_SESSION['perms']);
            exit();
        }
    }

    function have_perm_proj($project_id = -1) {

        if ($this->isAdmin()) {
            return true;
        }

        if ($project_id == -1) {
            if (isset($_SESSION['projs'])) {
                return true;
            } else {
                return false;
            }
        }

        if (isset($_SESSION['projs'][$project_id])) {
            return true;
        } else {
            return false;
        }
    }

    function check_auth($auth_var, $reqs, $proj = 0) {

        // Administrators always pass
        if ($this->isAdmin()) {
            return true;
        }

        if (isset($proj) && !empty($proj) && $this->have_perm_proj($proj)) {
            return true;
        }

        if (is_array($reqs)) {
            foreach ($reqs as $req) {
                if (!isset($_SESSION[$auth_var][$req]) &&
                        ($auth_var != 'perms' || !isset($this->permissions[$req]))) {
                    return false;
                }
            }
        } else {
            if (!isset($_SESSION[$auth_var][$reqs]) &&
                    ($auth_var != 'perms' || !isset($this->permissions[$reqs]))) {
                return false;
            }
        }

        // Didn't fail on any requirements?  Then the user passes the check
        return true;
    }

    function in_group($req_groups) {
        return $this->check_auth('group', $req_groups);
    }

    function have_perm($req_perms, $proj = 0) {
        return $this->check_auth('perms', $req_perms, $proj);
    }

    function perm_invalid($actual_perms, $required_perms = 0) {
        global $t;

        $t->render('badperm.html', '');
    }

    function check_group($group) {
        global $t;

        if (!$this->check_auth('group', $group)) {
            $t->assign('group', $group);
            $t->render('badgroup.html', 'Group Failure');
            exit();
        }
    }

    function add_role($arole) {
        global $db;

        $perms = $db->getCol("select perm_name from " . TBL_AUTH_PERM . " ap, " . TBL_GROUP_PERM . " gp, " . TBL_AUTH_GROUP . " ag where ag.group_name='$arole' and ag.group_id=gp.group_id and gp.perm_id = ap.perm_id");
        if ($perms !== false) {
            foreach ($perms as $p) {
                $this->permissions[$p] = true;
            }
        }
    }

}

//
