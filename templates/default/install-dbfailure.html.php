<html>
    <head>
        <title><?php echo translate("DB Test Failure"); ?></title>
        <link rel="StyleSheet" href="styles/default.css" type="text/css">
    </head>
    <body>
        <br>
        <div class="error">
            <?php echo translate("DB Test Failure"); ?>
        </div>
        <br>
        <div align="center">
            <?php printf(translate("<br>The installation script could not connect to the database <b>%s</b> on the host <b>%s</b> using the specified username and password.<br><br>Please check these details are correct and that the database already exists then retry. "), $params['db_database'], $params['db_host']); ?>
            <br>
            <br>
            <?php
            echo $error_message . '<br>' . $error_info . "\n";
            if ($testonly) {
                echo '<br><br><a href="javascript:window.close()">' . translate("Close window") . '</a>';
            }
            ?>
        </div>
        <!--

        <?php echo htmlspecialchars($trace) ?>

        -->
    </body>
</html>
