<?php

// auth.php - Authentication and permission objects
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
// Based on and/or directly from PHPlib, which is 
// Copyright (c) 1998-2000 NetUSE AG -- Boris Erdmann, Kristian Koehntopp

class uauth {
	var $lifetime = 0; // In minutes -- 0 for no expiration until browser closed
	var $classname = 'uauth';
	var $auth = array();
	
	function uauth() {
		global $HTTP_SESSION_VARS;
		
		if (!session_is_registered('auth')) {
			session_register('auth');
			$HTTP_SESSION_VARS['auth'] = array();
		}
		
		$a =& $HTTP_SESSION_VARS['auth'];
		$this->auth =& $a->auth;

		
		if (!isset($this->auth['group_ids'])) {
			$this->auth['group_ids'] = array(0);
		}
		
		if ($this->is_authenticated()) {
			if ($this->auth['uid']) {
				$this->auth['exp'] = time() + (60 * $this->lifetime);
			}
		}
	}

	function is_authenticated() {
		if (isset($this->auth['uid']) && $this->auth['uid'] && 
			($this->lifetime <= 0 || time() < $this->auth['exp'])) {
			return $this->auth['uid'];
		} else {
			return false;
		}
	}
	
	function auth_validatelogin() {
    global $_pv, $q, $select, $emailpass, $emailsuccess, $STRING;

		extract($_pv);
    if (!$username) return 0;
    $this->auth['uname'] = $username;
    if (ENCRYPT_PASS) {
      $password = md5($password);
    }
    $u = $q->grab("select * from ".TBL_AUTH_USER." where login = '$username' and password = '$password' and active > 0");
    if (!$q->num_rows()) {
      return 0;
    } else {
			$this->auth['db_fields'] = @unserialize($u['bug_list_fields']);

      // Grab group assignments and permissions based on groups
      $q->query("select gp.group_id, group_name, perm_name"
      	." from ".TBL_AUTH_PERM." ap, ".TBL_GROUP_PERM." gp, ".TBL_AUTH_GROUP." ag, ".TBL_USER_GROUP." ug"
      	." where ap.perm_id = gp.perm_id and gp.group_id = ag.group_id"
      	."  and ag.group_id = ug.group_id and ug.user_id = {$u['user_id']}");
      while (list($groupid, $group, $perm) = $q->grab()) {
        $this->auth['perm'][$perm] = true;
        $this->auth['uid'] = $u['user_id'];
        $this->auth['group'][$group] = true;
        $this->auth['group_ids'][] = $groupid;
      }

      return $u['user_id'];
    }
  }
	
	function unauth() {
    $this->auth['uid'] = 0;
		$this->auth['perm'] = array();
		$this->auth['exp']   = 0;
    $this->auth['group'] = array();
    $this->auth['db_fields'] = array();
  }
}

class uperm {
  var $classname = 'uperm';
  var $permissions = array ();

  function check($p) {
    global $auth;

    if (!$this->have_perm($p)) {    
      if (!isset($auth->auth['perm']) ) {
        $auth->auth['perm'] = '';
      }
      $this->perm_invalid($auth->auth['perm'], $p);
      exit();
    }
  }

  function check_auth($auth_var, $reqs) {
    global $auth;

    // Administrators always pass
    if (isset($auth->auth[$auth_var]['Admin'])) {
      return true;
    }

    if (is_array($reqs)) {
      foreach ($reqs as $req) {
        if (!isset($auth->auth[$auth_var][$req])) {
          return false;
        }
      }
    } else {
      if (!isset($auth->auth[$auth_var][$reqs])) {
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
    return $this->check_auth('perm', $req_perms);
  }


  function perm_invalid() {
    global $t, $auth;
		
    $t->set_file('content','badperm.html');
    $t->pparse('main',array('content','wrap','main'));
  }

	function check_group($group) {
		global $t;

		if (!$this->check_auth('group', $group)) {		
			$t->set_file('content', 'badgroup.html');
			$t->set_var('group', $group);
			$t->pparse('main',array('content','wrap','main'));
			exit();
		}
	}
}

?>
