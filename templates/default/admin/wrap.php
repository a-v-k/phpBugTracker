<?php
if (!defined('PHPBT_VERSION')) {
    die('not in phpbt');
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <META HTTP-EQUIV="Expires" CONTENT="-1">
        <title>phpBugTracker Admin - <?php echo $page_title; ?></title>
        <link rel="stylesheet" href="../styles/<?php echo $STYLE; ?>.css" type="text/css">
        <link rel="stylesheet" type="text/css" href="../styles/print.css" media="print">
        <?php
        if (defined('CHARSET')) {
            echo '<META http-equiv="Content-Type" content="text/html; charset=' . CHARSET . '">';
        }
        ?>
    </head>
    <body>
        <div class="NavBar">
            <div class="nav FixedWidth">
                <div class="header_line1">
                    <div class="header_image">
                        <div class="PhpBugTracker">phpBugTracker</div>
                    </div>
                    <div class="header_search">
                        <form action="../bug.php">
                            <input type="hidden" name="op" value="show">
                            <?php echo translate("Find Bug") ?>
                            <input name="bugid" type="text" id="bugid" size="4">
                            &nbsp;
                        </form>
                    </div>
                </div>
                <ul class="tabnav">
                    <li><a href="configure.php" class="navlink"><?php echo translate("Configuration"); ?></a></li>
                    <li><a href="project.php" class="navlink"><?php echo translate("Projects"); ?></a></li>
                    <li><a href="user.php" class="navlink"><?php echo translate("Users"); ?></a></li>
                    <li><a href="group.php" class="navlink"><?php echo translate("Group Permisions"); ?></a></li>
                    <li><a href="group.php?op=list-roles" class="navlink"><?php echo translate("Role Permisions"); ?></a></li>
                    <li><a href="../docs/html/userguide.html" class="navlink"><?php echo translate("Documentation"); ?></a></li>
                    <li><a href="../index.php" class="navlink"><?php echo translate("User Tools"); ?></a></li>
                </ul>
                <ul class="tabnav">
                    <li><a href="status.php" class="navlink"><?php echo translate("Statuses"); ?></a></li>
                    <li><a href="resolution.php" class="navlink"><?php echo translate("Resolutions"); ?></a></li>
                    <li><a href="severity.php" class="navlink"><?php echo translate("Severities"); ?></a></li>
                    <li><a href="priority.php" class="navlink"><?php echo translate("Priorities"); ?></a></li>
                    <li><a href="os.php" class="navlink"><?php echo translate("Operating Systems"); ?></a></li>
                    <li><a href="database.php" class="navlink"><?php echo translate("Databases"); ?></a></li>
                    <li><a href="site.php" class="navlink"><?php echo translate("Sites"); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="ContentBar">
            <div class="FixedWidth" style="padding: 0px 5px;">
                <?php include($content_template); ?>
            </div>
        </div>

    </body>
</html>
