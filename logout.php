<?php

// logout.php - Clear the authentication of a user

include 'include.php';

page_open(array('sess' => 'usess', 'auth' => 'uauth'));

$auth->unauth();

include 'templates/logout.html';

page_close();

?>
