<script language="JavaScript">
<!--
    function popupPriority(id) {
        window.open('priority.php?op=edit&use_js=1&priority_id=' + id, 'ewin', 'dependent=yes,width=350,height=300,scrollbars=1');
    }
// -->
</script>
<table border="0" width="100%">
    <tr>
        <td valign="top">
            <b>&nbsp;<?php echo $page_title; ?></b> - <a href="priority.php?op=edit&priority_id=0" onClick="popupPriority(0);
                    return false;"><?php echo translate("Add new priority"); ?></a>
            <hr size="1">
            <table class="bordertable" align="center">
                <tr>
                    <th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Name"); ?></a></th>
                    <th class="<?php echo $headers['description']['class']; ?>"><a href="<?php echo $headers['description']['url']; ?>"><?php echo translate("Description"); ?></a></th>
                    <th class="<?php echo $headers['sortorder']['class']; ?>"><a href="<?php echo $headers['sortorder']['url']; ?>"><?php echo translate("Sort Order"); ?></a></th>
                    <th><?php echo translate("Delete"); ?></th>
                </tr>
                <?php for ($i = 0, $count = count($priorities); $i < $count; $i++) { ?>
                    <tr <?php if ($priorities[$i]['priority_color']) echo 'style="background-color: ', $priorities[$i]['priority_color'], ' ;"' ?>>
                        <td><a href="priority.php?op=edit&priority_id=<?php echo $priorities[$i]['priority_id']; ?>" onClick="popupPriority(<?php echo $priorities[$i]['priority_id']; ?>);
                                    return false;"><?php echo stripslashes(htmlspecialchars($priorities[$i]['priority_name'])); ?></a></td>
                        <td>&nbsp;<?php echo htmlspecialchars($priorities[$i]['priority_desc']); ?></td>
                        <td align="center"><?php echo $priorities[$i]['sort_order']; ?></td>
                        <td align="center">
                            <?php if (!$priorities[$i]['bug_count']) { ?>
                                <a href="priority.php?op=del&priority_id=<?php echo $priorities[$i]['priority_id']; ?>&ak=<?php echo make_action_key(); ?>" onClick="return confirm('<?php echo translate("Are you sure you want to delete this priority?"); ?>')"><?php echo translate("Delete"); ?></a>
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
