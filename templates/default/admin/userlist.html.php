<script language="JavaScript">
<!--
	function popupUser(id) {
		window.open('user.php?op=edit&use_js=1&user_id='+id, 'ewin', 'dependent=yes,width=350,height=300,scrollbars=1');
	}
// -->
</script>
<table border="0" width="100%">
	<tr>
		<td valign="top">
			<b>&nbsp;<?php echo $page_title; ?></b> - <a href="user.php?op=edit&user_id=0&userfilter=<?php echo $userfilter; ?>&groupfilter=<?php echo $groupfilter; ?>" onClick="popupUser(0); return false;"><?php echo translate("Add new user"); ?></a>
			<hr size="1">
			<form>
			<div align="center">
				<select name="groupfilter" onChange="document.location.href='user.php?groupfilter=' + this.options[this.selectedIndex].value<?php echo " + '&userfilter=".$userfilter."'"; ?>">
			<?php build_select('group', $groupfilter, 1); ?></select>
				<select name="userfilter" onChange="document.location.href='user.php?<?php echo 'groupfilter='.$groupfilter; ?>&userfilter=' + this.options[this.selectedIndex].value">
			<?php build_select('user_filter', $userfilter); ?></select>
				<input type="submit" value="<?php echo translate("Filter"); ?>">
			</div>
			</form>
			<table class="bordertable" align="center">
				<tr>
					<th class="<?php echo $headers['login']['class']; ?>"><a href="<?php echo $headers['login']['url']; ?>"><?php echo translate("Login"); ?></a></th>
					<th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Name"); ?></a></th>
					<th class="<?php echo $headers['date']['class']; ?>"><a href="<?php echo $headers['date']['url']; ?>"><?php echo translate("Created"); ?></a></th>
					<th class="<?php echo $headers['active']['class']; ?>"><a href="<?php echo $headers['active']['url']; ?>"><?php echo translate("Active"); ?></a></th>
				</tr>
				<?php for ($i = 0, $count = count($users); $i < $count; $i++) { ?>
				<tr>
					<td><a href="user.php?op=edit&user_id=<?php echo $users[$i]['user_id']; ?>&userfilter=<?php echo $userfilter; ?>&groupfilter=<?php echo $groupfilter; ?>" onClick="popupUser(<?php echo $users[$i]['user_id']; ?>); return false;"><?php echo stripslashes(htmlspecialchars($users[$i]['login'])); ?></a></td>
					<td align="center"><?php echo stripslashes(htmlspecialchars($users[$i]['first_name'].' '.$users[$i]['last_name'])); ?></td>
					<td align="center"><?php echo $users[$i]['created_date'] ? date(DATE_FORMAT, $users[$i]['created_date']) : ''; ?></td>
					<td align="center"><?php echo $users[$i]['active'] ? translate("Yes") : translate("No"); ?></td>
				</tr>
				<?php } ?>
			</table>
            <?php include('pagination.html'); ?>
		</td>
	</tr>
</table>
