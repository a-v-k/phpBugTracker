<table border="0" cellpadding="0" cellspacing="0" width="600">
    <tr>
        <td width="300" valign="top">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="do" value="changepassword">
                <table border="0" cellpadding="0" cellspacing="0" width="300">
                    <tr>
                        <td colspan="2" width="300"><b><?php echo translate("Change Password"); ?></b></td>
                    </tr>
                    <?php if ($error) { ?>
                        <tr>
                            <td colspan="2" width="300" class="error"><?php echo $error; ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="2" width="300"><?php echo translate("Enter new password"); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo translate("Password"); ?>: </td>
                        <td><input type="password" name="pass1"></td>
                    </tr><tr>
                        <td><?php echo translate("Verify password"); ?>: </td>
                        <td><input type="password" name="pass2"></td>
                    </tr><tr>
                        <td colspan="2" align="center"><br>
                            <input type="reset"> <input type="submit" value="<?php echo translate("Submit"); ?>"></td>
                    </tr>
                </table></form></td>
        <td width="300" valign="top"><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="hidden" name="do" value="changeprefs">
                <table border="0" cellpadding="0" cellspacing="0" width="300">
                    <tr>
                        <td colspan="2" width="300"><b><?php echo translate("Change Preferences"); ?></b></td>
                    </tr><tr>
                        <td colspan="2" width="300" class="error"></td>
                    </tr>
                    <?php for ($i = 0, $count = count($preferences); $i < $count; $i++) { ?>
                        <tr>
                            <td><?php echo $preferences[$i]['label']; ?>: </td>
                            <td><input type="checkbox" name="preferences[]" value="<?php echo $preferences[$i]['pref']; ?>" <?php if ($preferences[$i]['checked']) echo 'checked'; ?>></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><?php echo translate("Number of results per page"); ?></td>
                        <td><input type="text" name="def_results" value="<?php echo $def_results; ?>" size="4"></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center"><br>
                            <input type="reset"> <input type="submit" value="<?php echo translate("Submit"); ?>"></td>
                    </tr>
                </table></form></td>
    </tr>
</table>
<br>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <input type="hidden" name="do" value="changecolumnlist">
    <table border="0" cellpadding="0" cellspacing="0" width="300">
        <tr>
            <td width="300"><table border="0" cellpadding="0" cellspacing="0" width="300">
                    <tr>
                        <td width="300" colspan="2"><b><?php echo translate("Bug List Columns"); ?></b></td>
                    </tr><tr>
                        <td width="600" colspan="2"><?php echo translate("Choose the fields you want to see in the bug list"); ?></td>
                    </tr>
                    <?php foreach ($field_titles as $var => $val) { ?>
                        <tr valign="baseline">
                            <td><input type="checkbox" name="column_list[]" value="<?php echo $var; ?>" <?php if (in_array($var, $my_fields)) echo 'checked'; ?>></td><td><?php echo $val; ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td align="center" colspan="2"><br>
                            <input type="reset"> <input type="submit" value="Submit"></td>
                    </tr>
                </table></td>
            <td valign="top">
                <?php if ($vcount = count($votes)) { ?>
                    <b><?php echo translate("Votes"); ?></b>
                    <table border="0" cellpadding="1" cellspacing="1" width="300">
                        <tr>
                            <th><?php echo translate("Bug"); ?></th>
                            <th><?php echo translate("When"); ?></th>
                            <th>&nbsp;</th>
                        </tr>
                        <?php for ($i = 0; $i < $vcount; $i++) { ?>
                            <tr<?php if ($i % 2) echo ' class="alt"'; ?>>
                                <td align="center"><a href="bug.php?op=show&bugid=<?php echo $votes[$i]['bug_id']; ?>">#<?php echo $votes[$i]['bug_id']; ?></a></td>
                                <td align="center"><?php echo date(DATE_FORMAT, $votes[$i]['created_date']); ?></td>
                                <td align="center"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?op=delvote&bugid=<?php echo $votes[$i]['bug_id']; ?>" onClick="return confirm('<?php echo translate("Are you sure you want to delete this vote?"); ?>');"><?php echo translate("Delete"); ?></a></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            </td>
        </tr>
    </table>
</form>
