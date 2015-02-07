<?php
        if (!isset($email))         $email = '';
        if (!isset($first_name))    $first_name = '';
        if (!isset($last_name))     $last_name = '';
        if (!isset($active))        $active = '';
        if (!isset($email_notices)) $email_notices = '';
        if (!isset($user_id))       $user_id = '';
?>
<script type="text/javascript" language="JavaScript">
<!--
    var hadAdmin = <?php echo $hadadmin; ?>;
    var numAdmins = <?php echo $numadmins; ?>;

    function checkAdmin(slct) {
        var adminSelected = false;

        if (hadAdmin && numAdmins == 1) {
            for (current = 0; current < slct.options.length; current++ ) {
                if (slct.options[current].selected && slct.options[current].value == 1) {
                    adminSelected = true;
                }
            }
            if (!adminSelected) {
                alert('This is the only admin user for the system.  Removing this user from the Admin group would be unwise.');
            }
        }
    }
// -->
</script>
<b><?php echo $page_title; ?>&nbsp;</b> 
<hr size="1">
<form action="user.php" method="post">
    <table border='0'>
        <?php if(isset($error)) { ?>
        <tr>
            <td colspan="2" class="error">
                <?php echo $error; ?>
            </td>
        </tr>
        <?php } ?><?php if(!EMAIL_IS_LOGIN) { ?>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Login"); ?>:
            </td>
            <td>
                <input type="text" size="20" maxlength="40" name="login" value="<?php echo stripslashes(htmlspecialchars($login)); ?>">
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Email"); ?>:
            </td>
            <td>
                <input type="text" size="35" maxlength="40" name="email" value="<?php echo $email; ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("First Name"); ?>:
            </td>
            <td>
                <input type="text" size="20" maxlength="40" name="first_name" value="<?php echo stripslashes(htmlspecialchars($first_name)); ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Last Name"); ?>:
            </td>
            <td>
                <input type="text" size="20" maxlength="40" name="last_name" value="<?php echo stripslashes(htmlspecialchars($last_name)); ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Password"); ?>:
            </td>
            <td>
                <input type="text" size="20" maxlength="40" name="password" value="<?php echo $password; ?>">
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Group<br>Membership"); ?>:
            </td>
            <td>
                <select name="fusergroup[]" size="5" multiple onclick="checkAdmin(this)">
                    <?php build_select('group', $user_groups); ?>
                </select> 
            </td>
        </tr>
        <tr>
            <td></td><td><?php echo translate("A user may gain more<br>permissions based<br>on their bug role."); ?>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Active"); ?>:
            </td>
            <td>
                <input type="checkbox" name="active" value="1" <?php echo $active ? 'checked' : ''; ?>>
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                <?php echo translate("Email Notify"); ?>:
            </td>
            <td>
                <input type="checkbox" name="fe_notice" value="1" <?php echo $email_notices ? 'checked' : ''; ?>>
            </td>
        </tr>
        <tr>
            <td align="right" valign="top">
                &nbsp;
            </td>
            <td>
                <input type='submit' name='submit' value='<?php echo translate("Submit"); ?>'>
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"> 
                <input type="hidden" name="filter" value="<?php echo (isset($_REQUEST['filter']) ? $_REQUEST['filter'] : ''); ?>"> 
                <input type="hidden" name="use_js" value="<?php echo (isset($_REQUEST['use_js']) ? $_REQUEST['use_js'] : ''); ?>"> 
                <input type="hidden" name="op" value="save"> 
            </td>
        </tr>
    </table>
</form>

