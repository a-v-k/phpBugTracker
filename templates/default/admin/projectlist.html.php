<?php
if (!defined('PHPBT_VERSION')) {
    die('not in phpbt');
}
?>
<table border="0" width="100%">
    <tr>
        <td valign="top">
            <b>&nbsp;<?php echo $page_title; ?></b>
            <?php
            if (isset($perm) and $perm->have_perm('Admin'))
                echo " - <a href=\"{$_SERVER['SCRIPT_NAME']}?op=add\">" . translate("Add new project") . "</a>";
            ?>
            <hr size="1">
            <table class="bordertable" align="center">
                <tr>
                    <th class="<?php echo $headers['name']['class']; ?>"><a href="<?php echo $headers['name']['url']; ?>"><?php echo translate("Project"); ?></a></th>
                    <th class="<?php echo $headers['createddate']['class']; ?>"><a href="<?php echo $headers['createddate']['url']; ?>"><?php echo translate("Created Date"); ?></a></th>
                    <th class="<?php echo $headers['active']['class']; ?>"><a href="<?php echo $headers['active']['url']; ?>"><?php echo translate("Active"); ?></a></th>
                </tr>
                <?php for ($i = 0, $count = count($projects); $i < $count; $i++) { ?>
                    <?php if (isset($perm) and ($perm->have_perm('Admin') or $perm->have_perm_proj($projects[$i]['project_id']))) { ?>
                        <tr>
                            <td>
                                <?php if (isset($perm) and ($perm->have_perm('Admin') or $perm->have_perm_proj($projects[$i]['project_id']))) { ?>
                                    <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>?op=edit&id=<?php echo $projects[$i]['project_id']; ?>"><?php echo stripslashes($projects[$i]['project_name']); ?></a>
                                    <?php
                                } else {
                                    echo stripslashes($projects[$i]['project_name']);
                                }
                                ?>
                            </td>
                            <td align="center"><?php echo date(DATE_FORMAT, $projects[$i]['created_date']); ?></td>
                            <td align="center"><?php echo $projects[$i]['active'] ? translate("Yes") : translate("No"); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </table>
            <?php //include('pagination.html'); ?>
        </td>
    </tr>
</table>
