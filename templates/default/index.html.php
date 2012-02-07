<table border=0 width="100%">
	<tr>
		<td valign="top">
			<b><?php echo translate("Most recently changed bugs") ?></b>
			<br>
			<?php 
				if ($count = count($recentbugs)) {
					for ($i = 0; $i < $count; $i++) { 
						echo '<a href="bug.php?op=show&bugid='.$recentbugs[$i]['bug_id'].'">'.stripslashes($recentbugs[$i]['title']).'</a> &nbsp;&nbsp;(<i>'.stripslashes($recentbugs[$i]['project_name']).'</i>)<br>';
					}
				} else {
					echo translate("No bugs found");
				}
			?>
			<br><br>
			<b><?php echo translate("Most recently closed bugs") ?></b>
			<br>
			<?php 
				if ($count = count($closedbugs)) {
					for ($i = 0; $i < $count; $i++) { 
						echo '<a href="bug.php?op=show&bugid='.$closedbugs[$i]['bug_id'].'">'.stripslashes($closedbugs[$i]['title']).'</a> &nbsp;&nbsp;(<i>'.stripslashes($closedbugs[$i]['project_name']).'</i>)<br>';
					}
				} else {
					echo translate("No bugs found");
				}
			?>
			<?php if (isset($queries) && count($queries)) { ?>
				<br><br>
				<b><?php echo translate("Saved Queries") ?></b>
				<br>
				<?php for ($i = 0, $count = count($queries); $i < $count; $i++) { 
					echo '<a href="query.php?'.$queries[$i]['saved_query_string'].'">'.$queries[$i]['saved_query_name'].'</a><br>';
				}
			} ?>
		</td>
		<td valign="top" align="right"> 
			<?php if (USE_JPGRAPH) {
				if (!is_writeable('jpgimages')) {
					echo translate("The image path 'jpgimages' is not writeable");
				} else {
					echo build_image($restricted_projects);
				}
			} else { ?>
				<b><?php echo translate("Quick Stats"); ?></b>
				<br><br>
				<table border="1" cellspacing="0" cellpadding="2">
					<tr>
						<th><?php echo translate("Status"); ?></th>
						<th><?php echo translate('# bugs'); ?></th>
					</tr>
					<?php $stats = grab_data($restricted_projects); ?>
					<?php foreach ($stats as $statid => $info) { ?>
						<tr>
							<td><a href="query.php?op=doquery&status[]=<?php echo $statid ?>"><?php echo $info['name'] ?></a></td>
							<td align="center"><?php echo isset($info['count']) && $info['count'] ? $info['count'] : 0 ?></td>
						</tr>
					<?php } ?>
				</table>
			<?php } ?>
		</td>
	</tr>
</table>
<br/>
<?php if (SHOW_PROJECT_SUMMARIES) { ?>
	<table class="bordertable" align="center">
		<tr>
		<?php foreach ($resfields as $field) echo "<th>$field</th>"; ?>
		</tr>
		<?php for ($i = 0, $count = count($projects); $i < $count; $i++) { ?>
			<tr<?php if ($i % 2 != 0) echo ' class="alt"'?>>
			<?php foreach ($projects[$i] as $var => $val) echo '<td'.($var != 'Project' ? ' align="center"' : '').'>'.stripslashes($val).'</td>'; ?>
			</tr>
		<?php } ?>
	</table>
<?php } ?>
<?php if ($fastlinks1) { ?>
<b><?php echo translate("Add a new bug"); ?></b><br/><?php echo $fastlinks1 ?><br/>
<br/>
<b><?php echo translate("Basic Query"); ?></b><br/><?php echo $fastlinks2 ?><br/>
<?php } ?>
