<?php
	if (!isset($site_name))  $site_name = '';
	if (!isset($sort_order)) $sort_order = '';
	if (!isset($site_id))    $site_id = '';
?>
<script type="text/javascript" language="JavaScript">
    var nameString = '<?php echo translate("Please enter a name"); ?>';

    function checkForm(frm) {
        if (frm.site_name.value == '') {
            alert(nameString);
            frm.site_name.focus();
            return false;
        }
        return true;
    }
</script>
<b><?php echo $page_title; ?>&nbsp;</b> 
<hr size="1">
<form method="post" onsubmit="return checkForm(this)">
    <table border='0'>
        <?php if($error) { ?>
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
                <input type="text" size="20" maxlength="50" name="site_name" value="<?php echo stripslashes(htmlspecialchars($site_name)); ?>">
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
                <input type="submit" name="<?php echo translate("Submit"); ?>"> 
                <input type="hidden" name="site_id" value="<?php echo $site_id; ?>"> 
                <input type="hidden" name="use_js" value="<?php echo $_REQUEST['use_js']; ?>">
                <input type="hidden" name="op" value="save">
            </td>
        </tr>
    </table>
</form>


