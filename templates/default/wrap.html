<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<META HTTP-EQUIV="Expires" CONTENT="-1">
	<title><?php echo $page_title ?> - phpBugTracker</title>

    <link rel="stylesheet" type="text/css" href="styles/<?php echo $STYLE; ?>.css">
    <link rel="stylesheet" type="text/css" href="styles/print.css" media="print">

    <link rel="alternate stylesheet" type="text/css" href="styles/default.css" title="default">
    <link rel="alternate stylesheet" type="text/css" href="styles/black.css"   title="black">
    <link rel="alternate stylesheet" type="text/css" href="styles/print.css"   title="print">

	<?php if (defined('CHARSET')) echo '<META http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">'; ?>
</head>
<body>
<div class="NavBar">
<div class="nav FixedWidth">
    <div class="header_line1">
	<div class="header_image">
                <div class="PhpBugTracker">phpBugTracker</div>
	</div>
	<div class="header_search">
		<form action="bug.php">
			<input type="hidden" name="op" value="show">
			<?php echo translate("Find Bug") ?>
			<input name="bugid" type="text" id="bugid" size="5">
			&nbsp;
		</form>
	</div>
    </div>
	<ul class="tabnav">
		<li><a href="index.php" style="border: none;"><?php echo translate("Home"); ?></a></li>
		<li><a href="bug.php?op=add"><?php echo translate("Add a New Bug"); ?></a></li>
		<li><a href="query.php?op=query"><?php echo translate("Basic Query"); ?></a></li>
		<li><a href="query.php?op=query&amp;form=advanced"><?php echo translate("Advanced Query"); ?></a></li>
		<li><a href="report.php"><?php echo translate("View Reports"); ?></a></li>
		<?php if (!NEW_ACCOUNTS_DISABLED && empty($_SESSION['uid'])) { ?><li><a href="newaccount.php"><?php echo translate("Create a New Account"); ?></a></li><?php } ?>
		<li><a href="docs/html/userguide.html"><?php echo translate("Read Documentation"); ?></a></li>
		<?php if (isset($perm) && $perm->have_perm_proj()) { ?><li><a href="admin/project.php"><?php echo translate("Administration Tools"); ?></a></li><?php } ?>
	</ul>
</div>
</div>
<div class="ContentBar">
<?php if (basename($_SERVER['SCRIPT_NAME']) != 'newaccount.php') { ?>
<div class="personalarea FixedWidth">
<form method="post" action="<?php echo $_SERVER['SCRIPT_NAME'].(isset($_SERVER['REQUEST_STRING']) ? '?'.$_SERVER['REQUEST_STRING'] : '') ?>">
&nbsp;
<?php if (empty($_SESSION['uid'])) { ?>
	<?php if (EMAIL_IS_LOGIN) $loginlabel = translate("Email");
		else $loginlabel = translate("Login");
	?>
	<?php echo !empty($loginerror) ? $loginerror : ''; ?>
	<?php echo $loginlabel ?>: <input type="text" name="username" class="bottomnavinput" value="<?php echo (isset($_COOKIE['phpbt_user']) ? $_COOKIE['phpbt_user'] : '') ?>">
	<?php echo translate("Password") ?>: <input type="password" name="password" class="bottomnavinput">
	<input type="hidden" name="dologin" value="1">
	<input type="submit" value="<?php echo translate("Login"); ?>" class="bottomnavinput">
	<input type="submit" name="sendpass" value="<?php echo translate("Email Password"); ?>" class="bottomnavinput" title="<?php echo translate("Forgot your password?  Have it sent to you") ?>">
	<?php if (RECALL_LOGIN) { ?>
		<input type="checkbox" name="savecookie" value="1" <?php if (!empty($_COOKIE['phpbt_user'])) echo 'checked' ?> class="bottomnavinput" title="<?php printf(translate('Remember %s for next time'), $loginlabel) ?>"> <?php echo translate("Remember me"); ?>
	<?php } ?>
<?php } else { ?>
<?php if (isset($perm) && $perm->have_perm('Assignable') || $owner_open || $owner_closed) { ?>
	<?php echo translate("Bugs assigned to me"); ?>: <a href="query.php?op=mybugs&amp;assignedto=1&amp;open=1" title="Open"><?php echo $owner_open ?></a> / <a href="query.php?op=mybugs&amp;assignedto=1&amp;open=0" title="Closed"><?php echo $owner_closed ?></a> |
<?php } ?>
	<?php echo translate("Bugs reported by me"); ?>: <a href="query.php?op=mybugs&amp;reportedby=1&amp;open=1" title="Open"><?php echo $reporter_open ?></a> / <a href="query.php?op=mybugs&amp;reportedby=1&amp;open=0" title="Closed"><?php echo $reporter_closed ?></a>
	| <?php echo translate("Bookmarked bugs"); ?>: <a href="query.php?op=mybugs&amp;bookmarked=1&amp;open=1" title="Open"><?php echo $bookmarks_open ?></a> / <a href="query.php?op=mybugs&amp;bookmarked=1&amp;open=0" title="Closed"><?php echo $bookmarks_closed ?></a>
	| <a href="user.php"><?php echo translate("Personal Page"); ?></a>
	| <a href="logout.php"><?php echo translate("Logout"), " ", $_SESSION["uname"]; ?></a>
<?php } ?>
&nbsp;
</form>
</div>
<?php } ?>
<?php
if (get_magic_quotes_gpc() == 1) {
    echo ("<div class=\"FixedWidth\" style=\"color:red;\">Warning: magic_quotes_gpc is ON </div>");
}
if (get_magic_quotes_runtime() == 1) {
    echo ("<div class=\"FixedWidth\" style=\"color:red;\">Warning: magic_quotes_runtime is ON </div>");
}
if (!in_array(DB_TYPE, array('mysql','mysqli'))) {
    echo ("<div class=\"FixedWidth\" style=\"color:red;\">Your Database type (" . DB_TYPE . ") unsupported. Please convert to mysql with utf8 codepage or use older version of phpBugTracker </div>");
}
?>
<div class="FixedWidth" style="padding: 5px;">
	<?php 
        if (substr($content_template, -4) == '.tpl') {
            $this->smarty->display($content_template);
        } else {
            include($content_template);
        }

        ?>
</div>
</div>

</body>
</html>
