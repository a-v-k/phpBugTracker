<?php
if (!isset($status_name)) {
    $status_name = '';
}
if (!isset($status_desc)) {
    $status_desc = '';
}
if (!isset($sort_order)) {
    $sort_order = '';
}
if (!isset($status_id)) {
    $status_id = '';
}
?>
<script type="text/javascript" language="JavaScript">
    var nameString = '<?php echo translate("Please enter a name"); ?>';
    var descString = '<?php echo translate("Please enter a description"); ?>';

    function checkForm(frm) {
        if (frm.status_name.value == '') {
            alert(nameString);
            frm.status_name.focus();
            return false;
        }
        if (frm.status_desc.value == '') {
            alert(descString);
            frm.status_desc.focus();
            return false;
        }
        return true;
    }
</script>
<b><?php echo $page_title; ?>&nbsp;</b> 
<hr size="1">
<form method="post" onsubmit="return checkForm(this)">
    <table border='0'>
        <?php if ($error) { ?>
            <tr>
                <td colspan="2" class="error">
                    <?php echo $error; ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Name"); ?>:
            </td>
            <td>
                <input type="text" size="20" maxlength="40" name="status_name" value="<?php echo stripslashes(htmlspecialchars($status_name)); ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Description"); ?>:
            </td>
            <td>
                <textarea name="status_desc" cols="20" rows="5"><?php echo stripslashes(htmlspecialchars($status_desc)); ?></textarea>
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Sort Order"); ?>:
            </td>
            <td>
                <input type="text" size="3" maxlength="3" name="sort_order" value="<?php echo $sort_order; ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Open/Closed"); ?>:
            </td>
            <td>
                <input type="radio" name="bug_open" value="1" <?php echo $bug_open ? ' checked="checked"' : ''; ?>> <?php echo translate("Open"); ?> <input type="radio" name="bug_open" value="0" <?php echo $bug_open ? '' : ' checked="checked"'; ?>> <?php echo translate("Closed"); ?>
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                &nbsp;
            </td>
            <td>
                <input type='submit' name='submit' value='<?php echo translate("Submit"); ?>'>
                <input type="hidden" name="status_id" value="<?php echo $status_id; ?>"> 
                <input type="hidden" name="use_js" value="<?php echo $_REQUEST['use_js']; ?>"> 
                <input type="hidden" name="op" value="save"> 
            </td>
        </tr>
    </table>
</form>

