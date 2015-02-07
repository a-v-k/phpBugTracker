<?php
	if (!isset($database_name)) $database_name = '';
	if (!isset($sort_order))    $sort_order = '';
	if (!isset($database_id))   $database_id = '';
?>
<script type="text/javascript" language="JavaScript">
    var nameString = '<?php echo translate("Please enter a name"); ?>';

    function checkForm(frm) {
        if (frm.database_name.value == '') {
            alert(nameString);
            frm.database_name.focus();
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
                <input type="text" size="20" maxlength="30" name="database_name" value="<?php echo stripslashes(htmlspecialchars($database_name)); ?>">
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
                &nbsp;
            </td>
            <td>
                <input type='submit' name='submit' value='<?php echo translate("Submit"); ?>'>
                <input type="hidden" name="database_id" value="<?php echo $database_id; ?>"> 
                <input type="hidden" name="use_js" value="<?php echo $_REQUEST['use_js']; ?>"> 
                <input type="hidden" name="op" value="save">
            </td>
        </tr>
    </table>
</form>
