<script language="JavaScript">
<!--
    function popupSite(id) {
        window.open('site.php?op=edit&use_js=1&site_id=' + id, 'ewin', 'dependent=yes,width=350,height=300,scrollbars=1');
    }
// -->
</script>
<table border="0" width="100%">
    <tr>
        <td valign="top">
            <b>&nbsp;<?php echo $page_title; ?></b> - <a href="site.php?op=edit&site_id=0" onClick="popupSite(0);
                    return false;"><?php echo translate("Add new site"); ?></a>
            <hr size="1">
            <table class="bordertable" align="center">
                <tr>
                    <th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Name"); ?></a></th>
                    <th class="<?php echo $headers['sortorder']['class']; ?>"><a href="<?php echo $headers['sortorder']['url']; ?>"><?php echo translate("Sort Order"); ?></a></th>
                    <th><?php echo translate("Delete"); ?></th>
                </tr>
                <?php for ($i = 0, $count = count($sites); $i < $count; $i++) { ?>
                    <tr>
                        <td>
                            <?php if ($sites[$i]['site_id']) { ?>
                                <a href="site.php?op=edit&site_id=<?php echo $sites[$i]['site_id']; ?>" onClick="popupSite(<?php echo $sites[$i]['site_id']; ?>);
                                                return false;"><?php echo htmlspecialchars($sites[$i]['site_name']); ?></a>
                               <?php } else { ?>
                                   <?php echo htmlspecialchars($sites[$i]['site_name']); ?>
                               <?php } ?>
                        </td>
                        <td align="center"><?php echo $sites[$i]['sort_order']; ?></td>
                        <td align="center">
                            <?php if (!$sites[$i]['bug_count'] && $sites[$i]['site_id']) { ?>
                                <a href="site.php?op=del&site_id=<?php echo $sites[$i]['site_id']; ?>" onClick="return confirm('<?php echo translate("Are you sure you want to delete this item?"); ?>')"><?php echo translate("Delete"); ?></a>
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
