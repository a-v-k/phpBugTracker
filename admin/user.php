<?php

include '../include.php';

page_open(array('sess' => 'usess', 'auth' => 'uauth', 'perm' => 'uperm'));

function do_form($userid = 0) {
	global $q, $me, $ffirstname, $flastname, $femail, $fpassword, $usertype, $STRING, $now;
	
	// Validation
	if (!valid_email($femail))
		$error = $STRING['giveemail'];
	elseif (!$fpassword = trim($fpassword))
		$error = $STRING['givepassword'];
	if ($error) { list_items($userid, $error); return; }
	
	if (!$userid) {
		if (ENCRYPTPASS) $mpassword = md5($fpassword);
		else $mpassword = $fpassword;
		$q->query("insert into User (UserID, FirstName, LastName, Email, Password, UserLevel, CreatedDate) values (".$q->nextid('User').", '$ffirstname', '$flastname', '$femail', '$mpassword', $usertype, $now)");
	} else {
		if (ENCRYPTPASS) {
			$oldpass = $q->grab_field("select Password from User where UserID = $userid");
			if ($oldpass != $fpassword) {
				$pquery = ", Password = '".md5($fpassword)."'";
			} else {
				$pquery = '';
			}
		} else {
			$pquery = ", Password = '$fpassword'";
		}
		$q->query("update User set FirstName = '$ffirstname', LastName = '$flastname', Email = '$femail', $pquery UserLevel = $usertype where UserID = '$userid'");
	}
	header("Location: $me?");
}	

function show_form($userid = 0, $error = '') {
	global $q, $me, $t, $firstname, $lastname, $email, $password, $usertype, $STRING;
	
	if ($userid && !$error) {
		$row = $q->grab("select * from User where UserID = '$userid'");
		$t->set_var(array(
			'action' => $STRING['edit'],
			'fuserid' => $row['UserID'],
			'ffirstname' => stripslashes($row['FirstName']),
			'flastname' => stripslashes($row['LastName']),
			'femail' => $row['Email'],
			'fpassword' => $row['Password'],
			'usertype' => build_select('authlevels',$row['UserLevel']),
			'createddate' => $row['CreatedDate']));
	} else {
		$t->set_var(array(
			'action' => $userid ? $STRING['edit'] : $STRING['addnew'],
			'error' => $error,
			'fuserid' => $userid,
			'ffirstname' => stripslashes($firstname),
			'flastname' => stripslashes($lastname),
			'femail' => $email,
			'fpassword' => $password ? $password : genpassword(10),
			'usertype' => build_select('authlevels',$usertype),
			'createddate' => $createddate));
	}
}

function list_items($userid = 0, $error = '') {
	global $me, $q, $t, $selrange, $order, $sort, $select, $STRING, $TITLE, $page;
				
	$t->set_file('content','userlist.html');
	$t->set_block('content','row','rows');
				
	if (!$order) { $order = '1'; $sort = 'asc'; }
	$nr = $q->grab_field("select count(*) from User");

	list($selrange, $llimit, $npages, $pages) = multipages($nr,$page,
		"order=$order&sort=$sort");
								
	$t->set_var(array(
		'pages' => '[ '.$pages.' ]',
		'first' => $llimit+1,
		'last' => $llimit+$selrange > $nr ? $nr : $llimit+$selrange,
		'records' => $nr));
								
	$q->query("select UserID, concat(FirstName,'&nbsp;',LastName) as FullName, Email, 
		CreatedDate, UserLevel from User order by $order $sort 
		limit $llimit, $selrange");
				
	if (!$q->num_rows()) {
		$t->set_var('rows',"<tr><td>{$STRING['nousers']}</td></tr>");
		return;
	}

	$headers = array(
		'userid' => 'UserID',
		'name' =>	'LastName',
		'login' => 'Email',
		'password' => 'Password',
		'userlevel' => 'UserLevel',
		'date' => 'CreatedDate');

	sorting_headers($me, $headers, $order, $sort);
				
	while ($row = $q->grab()) {
		$t->set_var(array(
			'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
			'userid' => $row['UserID'],
			'name' => stripslashes($row['FullName']),
			'email' => $row['Email'],
			'userlevel' => $select['authlevels'][$row['UserLevel']],
			'date' => date(DATEFORMAT,$row['CreatedDate'])));
		$t->parse('rows','row',true);
	}
	
	show_form($userid, $error);
	$t->set_var('TITLE',$TITLE['user']);
}

$t->set_file('wrap','wrap.html');

$perm->check('Administrator');

if ($op) switch($op) {
	case 'add' : list_items(); break;
	case 'edit' : list_items($id); break;
} elseif($submit) {		 
	do_form($id);
} else list_items();

$t->pparse('main',array('content','wrap','main'));

page_close();

?>
