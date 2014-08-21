<html>
	<head>
		<title><?php echo translate("Upgrade phpBugTracker"); ?></title>
		<style>.error { font-weight: bold; color: #ff0000; } </style>
	</head>
	<body>
		<h3>Upgrade phpBugTracker</h3>
		<p>
		<?php echo $comment_text; ?>
		</p>
		<?php if ($num_errors > 0) { ?>
			<hr>
			<h4>Error log</h4>
			<p>
			<?php echo $log_text; ?>
			</p>
			<hr>
		<?php } else { ?>
		<p>
		<?php echo translate("Your database has been updated."); ?>
		</p>
		<?php } ?>
		<a href="index.php"><?php echo translate("phpBugTracker home"); ?></a>
	</body>
</html>
