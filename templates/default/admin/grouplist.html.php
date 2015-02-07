<script language="JavaScript">
<!--
	var me = '<?php echo $_SERVER['SCRIPT_NAME']; ?>';
		
	function popupGroup(id) {
		window.open(me + '?op=edit<?php echo $do_group ? '' : '-role'?>&use_js=1&group_id='+id, 'ewin', 'dependent=yes,width=250,height=250,scrollbars=1');
	}
// -->
</script>
<table border="0" width="100%">
  <tr>
    <td valign="top">
      <b><?php echo $page_title; ?></b>
	<?php if ($do_group) { ?>
	 - <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?op=edit<?php echo $do_group ? '' : '-role'?>&group_id=0" onClick="popupGroup(0); return false;"><?php echo translate("Add new group"); ?></a>
	<?php } ?>
      <hr size="1">
      <table class="bordertable" align="center">
        <tr>
          <th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Name"); ?></a></th>
          <th class="<?php echo $headers['count']['class']; ?>"><a href="<?php echo $headers['count']['url']; ?>"><?php echo translate("Users"); ?></a></th>
					<th>&nbsp;</th>
        </tr>
        <?php for ($i = 0, $count = count($groups); $i < $count; $i++) { ?>
        <tr>
          <td><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?op=edit<?php echo $do_group ? '' : '-role'?>&group_id=<?php echo $groups[$i]['group_id']; ?>" onClick="popupGroup(<?php echo $groups[$i]['group_id']; ?>); return false;"><?php echo stripslashes($groups[$i]['group_name']); ?></a></td>
          <td align="center"><?php echo $groups[$i]['count']; ?></td>
					<td align="center">
						<?php if($groups[$i]['locked']) { 
                            echo translate("Locked"); 
                        } else { ?>
							<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?op=del<?php echo $do_group ? '' : '-role'?>&group_id=<?php echo $groups[$i]['group_id']; ?>" onClick="return confirm('<?php echo translate("This will remove all user assignments to this group and the group itself.  Continue?"); ?>')"><?php echo translate("Delete"); ?></a> | 
							<a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?op=purge<?php echo $do_group ? '' : '-role'?>&group_id=<?php echo $groups[$i]['group_id']; ?>" onClick="return confirm('<?php echo translate("This will remove all user assignments to this group.  Continue?"); ?>')"><?php echo translate("Purge"); ?></a>
						<?php } ?>
					</td>
        </tr>
        <?php } ?>
		</table>
        <?php include('pagination.html'); ?>
    </td>
	</tr>
</table>
