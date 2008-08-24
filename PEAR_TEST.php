<?php

//  A small standalone test file, to check if pear:db is working
//  Part of php bug tracker

//  If you get "DB Error: extension not found" you are missing the
//  proper PHP database driver for your database.  Install the driver
//  and restart Apache.

echo "Your php include path is: \"" . ini_get('include_path') . "\"<br/>";

define('PEAR_PATH', '');
if (@include_once(PEAR_PATH.'DB.php')) {
    echo 'Pear DB module loaded OK!<br/>';
    } else {
    echo 'Error: Failed to load the PEAR DB.php module from the path. Check for the file "DB.php" in the path.<br/>';
    }

$dsn = array(
'phptype'   => 'mysqli',
#'phptype'   => 'pgsql',
'hostspec'  => 'localhost',
'database'  => 'bug_tracker',
'username'  => '',
'password'  => ''
);

$db = DB::Connect($dsn);
if (DB::isError($db)) {
    echo 'Failed to connect to the database: '. $db->getMessage() .'<br/>';
    print_r($dsn);
    echo '<br/>';
    } else {
    echo 'Database opened OK!<br/>';

    $result =& $db->query("SELECT bug_id,title,created_date from phpbt_bug");
    if (DB::isError ($result))
     die ("SELECT failed: " . $result->getMessage () . "\n");
    printf("Result set contains %d rows and %d columns<br/>\n",
        $result->numRows (), $result->numCols ());
    printf("<table border=1>\n");
    while ($row =& $result->fetchRow ()) {
        printf("<tr><td>%s</td><td>%s</td><td>%s</td>\n", $row[0], $row[1], $row[2]);
        }
    $result->free();
    printf("</table>\n");
    }


//  Uncomment the next line for lots of information about php setup
//  phpinfo(8+4);
?>
