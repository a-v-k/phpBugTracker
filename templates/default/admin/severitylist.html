<script language="JavaScript">
<!--
	function popupSeverity(id) {
		window.open('severity.php?op=edit&use_js=1&severity_id='+id, 'ewin', 'dependent=yes,width=350,height=300,scrollbars=1');
	}
// -->
</script>
<table border="0" width="100%">
	<tr>
		<td valign="top">
			<b>&nbsp;<?php echo $page_title; ?></b> - <a href="severity.php?op=edit&severity_id=0" onClick="popupSeverity(0); return false;"><?php echo translate("Add new severity"); ?></a>
			<hr size="1">
			<table class="bordertable" align="center">
				<tr>
					<th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Name"); ?></a></th>
					<th class="<?php echo $headers['description']['class']; ?>"><a href="<?php echo $headers['description']['url']; ?>"><?php echo translate("Description"); ?></a></th>
					<th class="<?php echo $headers['sortorder']['class']; ?>"><a href="<?php echo $headers['sortorder']['url']; ?>"><?php echo translate("Sort Order"); ?></a></th>
					<th><?php echo translate("Delete"); ?></th>
				</tr>
				<?php for ($i = 0, $count = count($severities); $i < $count; $i++) { ?>
                <tr <?php if ($severities[$i]['severity_color']) echo 'style="background-color: ', $severities[$i]['severity_color'],  ' ;"' ?>>
					<td><a href="severity.php?op=edit&severity_id=<?php echo $severities[$i]['severity_id']; ?>" onClick="popupSeverity(<?php echo $severities[$i]['severity_id']; ?>); return false;"><?php echo stripslashes(htmlspecialchars($severities[$i]['severity_name'])); ?></a></td>
					<td>&nbsp;<?php echo $severities[$i]['severity_desc']; ?></td>
					<td align="center"><?php echo $severities[$i]['sort_order']; ?></td>
					<td align="center">
						<?php if(!$severities[$i]['bug_count']) { ?>
							<a href="severity.php?op=del&severity_id=<?php echo $severities[$i]['severity_id']; ?>" onClick="return confirm('<?php echo translate("Are you sure you want to delete this severity?"); ?>')"><?php echo translate("Delete"); ?></a>
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
			</table>
            <?php include('pagination.html'); ?>
			<br>
			<div class="info">
				<?php echo translate("Items with a Sort Order = 0 will not be selectable by users."); ?>
                <br>
                <?php echo translate("Only those items that have no bugs referencing them can be deleted."); ?>
			</div>
		</td>
	</tr>
</table>
