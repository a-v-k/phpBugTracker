<?php
if (!isset($os_name)) {
    $os_name = '';
}
if (!isset($regex)) {
    $regex = '';
}
if (!isset($sort_order)) {
    $sort_order = '';
}
if (!isset($os_id)) {
    $os_id = '';
}
?>
<script type="text/javascript" language="JavaScript">
    var nameString = '<?php echo translate("Please enter a name"); ?>';

    function checkForm(frm) {
        if (frm.os_name.value == '') {
            alert(nameString);
            frm.os_name.focus();
            return false;
        }
        return true;
    }
</script>
<b><?php echo $page_title; ?></b> 
<hr size="1">
<form method="post" onsubmit="return checkForm(this)">
    <table border='0'>
        <?php if (!empty($error)) { ?>
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
                <input type="text" size="20" maxlength="40" name="os_name" value="<?php echo htmlspecialchars($os_name); ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Regex"); ?>:
            </td>
            <td>
                <input type="text" size="20" maxlength="40" name="regex" value="<?php echo htmlspecialchars($regex); ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Sort Order"); ?>:
            </td>
            <td>
                <input type="text" size="3" maxlength="3" name="sort_order" value="<?php echo htmlspecialchars($sort_order); ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                &nbsp;
            </td>
            <td>
                <input type='submit' name='submit' value='<?php echo translate("Submit"); ?>'> 
                <input type="hidden" name="op" value="save">
                <input type="hidden" name="os_id" value="<?php echo $os_id; ?>"> 
                <input type="hidden" name="use_js" value="<?php echo $useJs; ?>">
            </td>
        </tr>
    </table>
</form>
