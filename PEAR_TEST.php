<?php

//  A small standalone test file, to check if pear:db is working
//  Part of php bug tracker.
//  You must edit the connection details for this to work.
//  Don't leave your password in this script, else it may be visible externally.

//  If you get "DB Error: extension not found" you are missing the
//  proper PHP database driver for your database.  Install the driver
//  and restart Apache.

//  For more info see http://pear.php.net/package/DB

//  For oracle see http://www.exzilla.net/docs/pear/peardb-oci8-checklist.php

echo "Your php include path is: \"" . ini_get('include_path') . "\"<br/>\n";

define('PEAR_PATH', '');
if (@include_once(PEAR_PATH.'DB.php')) {
    echo 'Pear DB module loaded OK!<br/>';
    } else {
    echo 'Error: Failed to load the PEAR DB.php module from the path. Check for the file "DB.php" in the path.<br/>';
    }

$dsn = array(
#'phptype'   => 'mysqli',
'phptype'   => 'pgsql',
'hostspec'  => 'localhost',
'database'  => 'bug_tracker',
'username'  => 'postgres',
'password'  => ''
);
echo "Connecting to the database with your supplied information: \n";
print_r($dsn);
echo "<br/>\n";

$db = DB::Connect($dsn);
var_dump($db);
if (DB::isError($db)) {
    echo 'Failed to connect to the database with error code:<br/>\''. $db->getMessage() . '\'<br/>\''. $db->getUserInfo().'\'<br/>';
    echo '<br/>';
} else {
    echo 'Database opened OK:<br/>';
    echo $db->toString() . '<br/>';

    $result =& $db->query("SELECT bug_id,title,created_date FROM phpbt_bug LIMIT 5");
    if (DB::isError($result)) {
        echo "Select failed (this is expected if the bug database is not present):<br>\n";
        echo $result->getMessage() . '<br/>';
        echo $result->getUserInfo(). '<br/>';
    } else {
        printf("Test result set contains %d rows and %d columns<br/>\n",$result->numRows (), $result->numCols ());
        printf("<table border=1>\n");
        while ($row =& $result->fetchRow ()) {
            printf("<tr><td>%s</td><td>%s</td><td>%s</td>\n", $row[0], $row[1], $row[2]);
            }
        $result->free();
        printf("</table>\n");
        }
    }

//  Uncomment the next line for lots of information about php setup
//  phpinfo(8+4);
?>
