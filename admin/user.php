<?php

include '../include.php';

page_open(array('sess' => 'usess', 'auth' => 'uauth', 'perm' => 'uperm'));

function do_form($userid = 0) {
  global $q, $me, $ffirstname, $flastname, $femail, $fpassword, $usertype;
  
  // Validation
  //if ($error) { show_form($id, $error); return; }
  
	if (!$userid) {
    $q->query("insert into User (UserID, FirstName, LastName, Email, 
			Password, UserLevel, CreatedDate) values (".$q->nextid('User').", 
			'$ffirstname', '$flastname', '$femail', '$fpassword', $usertype, ".time().")");
  } else {
    $q->query("update User set FirstName='$ffirstname', LastName='$flastname', 
			Email='$femail', Password='$fpassword', 
			UserLevel=$usertype where UserID = '$userid'");
  }
  header("Location: $me?");
}  

function show_form($userid = 0, $error = '') {
  global $q, $me, $t, $firstname, $lastname, $email, $password, $usertype;
  
  #$t->set_file('content','userform.html');
  if ($userid && !$error) {
    $row = $q->grab("select * from User where UserID = '$userid'");
    $t->set_var(array(
			'action' => 'Edit',
      'fuserid' => $row[UserID],
      'ffirstname' => $row[FirstName],
      'flastname' => $row[LastName],
      'femail' => $row[Email],
      'fpassword' => $row[Password],
      'usertype' => build_select('authlevels',$row[UserLevel]),
      'createddate' => $row[CreatedDate]));
  } else {
    $t->set_var(array(
      'action' => $userid ? 'Edit' : 'Add new',
      'error' => $error,
      'fuserid' => $userid,
      'ffirstname' => $firstname,
      'flastname' => $lastname,
      'femail' => $email,
      'fpassword' => $password ? $password : genpassword(10),
      'usertype' => build_select('authlevels',$usertype),
      'createddate' => $createddate));
  }
}


function list_items($userid = 0, $error = '') {
  global $me, $q, $t, $selrange, $order, $sort, $select;
        
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
                
  $q->query("select UserID, concat(FirstName,' ',LastName) as FullName, Email, 
		CreatedDate, UserLevel from User order by $order $sort 
		limit $llimit, $selrange");
        
  if (!$q->num_rows()) {
    $t->set_var('rows','<tr><td>Oops!</td></tr>');
    return;
  }

  $headers = array(
    'userid' => 'UserID',
    'name' =>  'LastName',
    'login' => 'Email',
    'password' => 'Password',
    'userlevel' => 'UserLevel',
    'date' => 'CreatedDate');

  sorting_headers($me, $headers, $order, $sort);
        
  while ($row = $q->grab()) {
    $t->set_var(array(
      'bgcolor' => (++$i % 2 == 0) ? '#dddddd' : '#ffffff',
      'userid' => $row[UserID],
      'name' => $row[FullName],
      'email' => $row[Email],
      'userlevel' => $select['authlevels'][$row[UserLevel]],
      'date' => date(DATEFORMAT,$row[CreatedDate])));
    $t->parse('rows','row',true);
  }
	
	show_form($userid, $error);
	$t->set_var('TITLE','Users');
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
