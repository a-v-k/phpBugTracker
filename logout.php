<?php

// logout.php - Clear the authentication of a user

include 'include.php';

$auth->unauth();

include 'templates/'.THEME.'logout.html';

page_close();

?>
