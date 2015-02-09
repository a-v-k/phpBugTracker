<script language="text/JavaScript">
<!--
    function popupResolution(id) {
        window.open('resolution.php?op=edit&use_js=1&resolution_id=' + id, 'ewin', 'dependent=yes,width=350,height=300,scrollbars=1');
    }
// -->
</script>
<table border="0" width="100%">
    <tr>
        <td valign="top">
            <b>&nbsp;<?php echo $page_title; ?></b> - <a href="resolution.php?op=edit&resolution_id=0" onClick="popupResolution(0);
                    return false;"><?php echo translate("Add new resolution"); ?></a>
            <hr size="1">
            <table class="bordertable" align="center">
                <tr>
                    <th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Name"); ?></a></th>
                    <th class="<?php echo $headers['description']['class']; ?>"><a href="<?php echo $headers['description']['url']; ?>"><?php echo translate("Description"); ?></a></th>
                    <th class="<?php echo $headers['sortorder']['class']; ?>"><a href="<?php echo $headers['sortorder']['url']; ?>"><?php echo translate("Sort Order"); ?></a></th>
                    <th><?php echo translate("Delete"); ?></th>
                </tr>
                <?php for ($i = 0, $count = count($resolutions); $i < $count; $i++) { ?>
                    <tr>
                        <td><a href="resolution.php?op=edit&resolution_id=<?php echo $resolutions[$i]['resolution_id']; ?>" onClick="popupResolution(<?php echo $resolutions[$i]['resolution_id']; ?>);
                                    return false;"><?php echo htmlspecialchars($resolutions[$i]['resolution_name']); ?></a></td>
                        <td>&nbsp;<?php echo htmlspecialchars($resolutions[$i]['resolution_desc']); ?></td>
                        <td align="center"><?php echo $resolutions[$i]['sort_order']; ?></td>
                        <td align="center">
                            <?php if (!$resolutions[$i]['bug_count']) { ?>
                                <a href="resolution.php?op=del&resolution_id=<?php echo $resolutions[$i]['resolution_id']; ?>&ak=<?php echo make_action_key(); ?>" onClick="return confirm('<?php echo translate("Are you sure you want to delete this resolution?"); ?>')"><?php echo translate("Delete"); ?></a>
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
