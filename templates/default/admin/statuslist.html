<script language="JavaScript">
<!--
	function popupStatus(id) {
		window.open('status.php?op=edit&use_js=1&status_id='+id, 'ewin', 'dependent=yes,width=350,height=300,scrollbars=1');
	}
// -->
</script>
<table border="0" width="100%">
	<tr>
		<td valign="top">
			<b>&nbsp;<?php echo $page_title; ?></b> - <a href="status.php?op=edit&status_id=0" onClick="popupStatus(0); return false;"><?php echo translate("Add new status"); ?></a>
			<hr size="1">
			<table class="bordertable" align="center">
				<tr>
					<th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Name"); ?></a></th>
					<th class="<?php echo $headers['description']['class']; ?>"><a href="<?php echo $headers['description']['url']; ?>"><?php echo translate("Description"); ?></a></th>
					<th class="<?php echo $headers['sortorder']['class']; ?>"><a href="<?php echo $headers['sortorder']['url']; ?>"><?php echo translate("Sort Order"); ?></a></th>
					<th><?php echo translate("Open/Closed"); ?></th>
					<th><?php echo translate("Delete"); ?></th>
				</tr>
				<?php for ($i = 0, $count = count($statuses); $i < $count; $i++) { ?>
				<tr>
					<td><a href="status.php?op=edit&status_id=<?php echo $statuses[$i]['status_id']; ?>" onClick="popupStatus(<?php echo $statuses[$i]['status_id']; ?>); return false;"><?php echo stripslashes(htmlspecialchars($statuses[$i]['status_name'])); ?></a></td>
					<td>&nbsp;<?php echo $statuses[$i]['status_desc']; ?></td>
					<td align="center"><?php echo $statuses[$i]['sort_order']; ?></td>
					<td align="center">
						<?php echo $statuses[$i]['bug_open'] ?  translate("Open") : translate("Closed"); ?>
					</td>
					<td align="center">
						<?php if(!$statuses[$i]['bug_count'] and $statuses[$i]['status_id'] != BUG_UNCONFIRMED) { ?>
						<a href="status.php?op=del&status_id=<?php echo $statuses[$i]['status_id']; ?>" onClick="return confirm('<?php echo translate("Are you sure you want to delete this item?"); ?>')"><?php echo translate("Delete"); ?></a>
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
