<?php

// auth.php - Authentication and permission objects
// ------------------------------------------------------------------------
// Copyright (c) 2001, 2002 The phpBugTracker Group
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
	
	function uauth() {
		global $HTTP_SESSION_VARS, $group_ids, $uname, $db_fields, $group, $perms,
			$uid, $exp;
		
		if (!isset($HTTP_SESSION_VARS['group_ids'])) {
			if (phpversion() <= '4.0.6') {
				$group_ids = array(0);
				$uname = '';
				$db_fields = array();
				$group = array();
				$perms = array();
				$uid = 0;
				$exp = 0;
				session_register(array('group_ids', 'uname', 'db_fields', 'group', 
					'perms', 'uid', 'exp'));
			}
			$HTTP_SESSION_VARS['group_ids'] = array(0);
		}
		
		if ($this->is_authenticated()) {
			if ($HTTP_SESSION_VARS['uid']) {
				$HTTP_SESSION_VARS['exp'] = time() + (60 * $this->lifetime);
			}
		}
	}

	function is_authenticated() {
		global $HTTP_SESSION_VARS;
		
		if (isset($HTTP_SESSION_VARS['uid']) && $HTTP_SESSION_VARS['uid'] && 
			($this->lifetime <= 0 || time() < $HTTP_SESSION_VARS['exp'])) {
			return $HTTP_SESSION_VARS['uid'];
		} else {
			return false;
		}
	}
	
	function auth_validatelogin() {
    global $_pv, $db, $select, $emailpass, $emailsuccess, $STRING, 
			$HTTP_SESSION_VARS, $uid;

		extract($_pv);
    if (!$username) return 0;
    $HTTP_SESSION_VARS['uname'] = $username;
    if (ENCRYPT_PASS) {
      $password = md5($password);
    }
    $u = $db->getRow("select * from ".TBL_AUTH_USER." where login = '$username' and password = '$password' and active > 0");
    if (!$u or DB::isError($u)) {
      return 0;
    } else {
			$HTTP_SESSION_VARS['db_fields'] = @unserialize($u['bug_list_fields']);

      // Grab group assignments and permissions based on groups
			$rs = $db->query("select u.group_id, group_name from ".TBL_USER_GROUP.
				" u, ".TBL_AUTH_GROUP." a where user_id = {$u['user_id']} ".
				'and u.group_id = a.group_id');
			while (list($groupid, $groupname) = $rs->fetchRow(DB_FETCHMODE_ORDERED)) {
				$HTTP_SESSION_VARS['group_ids'][] = $groupid;
				$HTTP_SESSION_VARS['group'][$groupname] = true;
			}
			$perms = $db->getCol("select perm_name from ".TBL_AUTH_PERM." ap, ".
				TBL_GROUP_PERM." gp where group_id in (".
				delimit_list(',', $HTTP_SESSION_VARS['group_ids']).") and gp.perm_id = ap.perm_id");
			foreach ($perms as $perm) {				
        $HTTP_SESSION_VARS['perms'][$perm] = true;
      }
			$HTTP_SESSION_VARS['uid'] = $u['user_id'];

      return $u['user_id'];
    }
  }
	
	function unauth() {
		global $HTTP_SESSION_VARS;
		
    $HTTP_SESSION_VARS['uid'] = 0;
		$HTTP_SESSION_VARS['perms'] = array();
		$HTTP_SESSION_VARS['exp']   = 0;
    $HTTP_SESSION_VARS['group'] = array();
    $HTTP_SESSION_VARS['group_ids'] = array(0);
    $HTTP_SESSION_VARS['db_fields'] = array();
  }
}

class uperm {
  var $classname = 'uperm';
  var $permissions = array ();

  function check($p) {
    global $HTTP_SESSION_VARS;

    if (!$this->have_perm($p)) {    
      if (!isset($HTTP_SESSION_VARS['perms']) ) {
        $HTTP_SESSION_VARS['perms'] = '';
      }
      $this->perm_invalid($HTTP_SESSION_VARS['perms'], $p);
      exit();
    }
  }

  function check_auth($auth_var, $reqs) {
    global $HTTP_SESSION_VARS;

    // Administrators always pass
    if (@isset($HTTP_SESSION_VARS[$auth_var]['Admin'])) {
      return true;
    }

    if (is_array($reqs)) {
      foreach ($reqs as $req) {
        if (!@isset($HTTP_SESSION_VARS[$auth_var][$req])) {
          return false;
        }
      }
    } else {
      if (!@isset($HTTP_SESSION_VARS[$auth_var][$reqs])) {
        return false;
      }
    }

    // Didn't fail on any requirements?  Then the user passes the check
    return true;
  }


  function in_group($req_groups) {
    return $this->check_auth('group', $req_groups);
  }


  function have_perm($req_perms) {
    return $this->check_auth('perms', $req_perms);
  }


  function perm_invalid($actual_perms, $required_perms) {
    global $t;
		
    $t->wrap('badperm.html');
  }

	function check_group($group) {
		global $t;

		if (!$this->check_auth('group', $group)) {		
			$t->assign('group', $group);
			$t->wrap('badgroup.html');
			exit();
		}
	}
}

?>
