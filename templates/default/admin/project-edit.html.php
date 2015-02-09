<script language="JavaScript">
<!--
    var projectId = '<?php echo $project_id; ?>';
    var nameString = '<?php echo translate("Please enter a name"); ?>';
    var descString = '<?php echo translate("Please enter a description"); ?>';

    function checkForm(frm) {
        if (frm.project_name.value == '') {
            alert(nameString);
            frm.project_name.focus();
            return false;
        }
        if (frm.project_desc.value == '') {
            alert(descString);
            frm.project_desc.focus();
            return false;
        }
        return true;
    }

    function popupComponent(id) {
        window.open('project.php?op=edit_component&project_id=' + projectId + '&use_js=1&id=' + id, 'ewin', 'dependent=yes,width=450,height=300,scrollbars=1');
        return false;
    }

    function popupVersion(id) {
        window.open('project.php?op=edit_version&project_id=' + projectId + '&use_js=1&id=' + id, 'ewin', 'dependent=yes,width=250,height=150,scrollbars=1');
        return false;
    }
// -->
</script>

<form method="post" onSubmit="return checkForm(this)">
    <table border="0" cellpadding="2" cellspacing="2" width="100%">
        <?php if ($error) { ?>
            <tr>
                <td colspan="2" class="error"><?php echo $error; ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td valign="top" width="360">
                <?php echo translate("Name"); ?>:
                <br>
                <input type="text" size="30" maxlength="30" name="project_name" value="<?php echo stripslashes(htmlspecialchars($project_name)); ?>">
            </td>
            <td valign="top" rowspan="3">
                <?php echo translate("Only users in the following groups can see this project"); ?>:
                <br>
                <select name="usergroup[]" size="10" multiple>
                    <?php build_select('group', $project_groups, 1); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td valign="top">
                <?php echo translate("Description"); ?>:
                <br>
                <textarea name="project_desc" cols=40 rows=5 ><?php echo stripslashes(htmlspecialchars($project_desc)); ?></textarea>
            </td>
        </tr>
        <tr>
            <td valign="top">
                <?php echo translate("Active"); ?>:
                <br>
                <input type="checkbox" name="active" value="1" <?php echo ($active == 1) ? 'checked' : ''; ?>>
            </td>
        </tr>
        <?php if (isset($perm) and $perm->have_perm('Admin')) { ?>
            <tr>
                <td>
                    <?php echo translate("These developers can administer this project"); ?>:
                    <br>
                    <select name="useradmin[]" size="10" multiple>
                        <?php build_select('owner', $project_admins); ?>
                    </select>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <td>
                    <?php echo translate("These developers can administer this project"); ?>:
                    <br>
                    <?php
                    for ($i = 0, $count = count($project_admins); $i < $count; $i++) {
                        echo $project_admins[$i] . '<br />';
                    }
                    ?>
                </td>
            </tr>
        <?php } ?>
    </table>
    <input type='submit' name='submit' value='<?php echo translate("Submit"); ?>'>
    <input type="hidden" name="id" value="<?php echo $project_id; ?>">
    <input type="hidden" name="op" value="save_project">
</form>
<br>
<table border="0" width="100%">
    <tr>
        <td width="50%" valign="top">
            <br>
            &nbsp;<b><?php echo translate("Versions"); ?></b> - <a href="project.php?op=edit_version&project_id=<?php echo $project_id; ?>&id=0" onClick="return popupVersion(0);"><?php echo translate("Add new version"); ?></a>
            <hr size="1">
            <table class="bordertable" align="center">
                <tr>
                    <th><?php echo translate("Version"); ?></th>
                    <th><?php echo translate("Active"); ?></th>
                    <th><?php echo translate("Sort Order"); ?></th>
                    <th><?php echo translate("Delete"); ?></th>
                </tr>
                <?php for ($i = 0, $count = count($versions); $i < $count; $i++) { ?>
                    <tr>
                        <td><a href="project.php?op=edit_version&id=<?php echo $versions[$i]['version_id']; ?>" onClick="popupVersion(<?php echo $versions[$i]['version_id']; ?>);
                                    return false;"><?php echo stripslashes(htmlspecialchars($versions[$i]['version_name'])); ?></a></td>
                        <td align="center"><?php echo $versions[$i]['active'] ? translate("Yes") : translate("No"); ?></td>
                        <td align="center"><?php echo $versions[$i]['sort_order']; ?></td>
                        <td align="center"><?php if (!$versions[$i]['bug_count']) { ?><a href="project.php?op=del_version&id=<?php echo $versions[$i]['version_id']; ?>&project_id=<?php echo $project_id; ?>&ak=<?php echo make_action_key(); ?>"><?php echo translate("Delete"); ?></a><?php } ?></td>
                    </tr>
                <?php } ?>
                <?php if (!$count) { ?>
                    <tr>
                        <td colspan="4" align="center"><?php echo translate("No versions found"); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </td>
        <td width="50%" valign="top">
            <br>
            &nbsp;<b><?php echo translate("Components"); ?></b> - <a href="project.php?op=edit_component&project_id=<?php echo $project_id; ?>&id=0" onClick="return popupComponent(0);"><?php echo translate("Add new component"); ?></a>
            <hr size="1">
            <table class="bordertable" align="center">
                <tr>
                    <th><?php echo translate("Component"); ?></th>
                    <th><?php echo translate("Owner"); ?></th>
                    <th><?php echo translate("Active"); ?></th>
                    <th><?php echo translate("Sort Order"); ?></th>
                    <th><?php echo translate("Delete"); ?></th>
                </tr>
                <?php for ($i = 0, $count = count($components); $i < $count; $i++) { ?>
                    <tr>
                        <td><a href="project.php?op=edit_component&id=<?php echo $components[$i]['component_id']; ?>" onClick="popupComponent(<?php echo $components[$i]['component_id']; ?>);
                                    return false;"><?php echo stripslashes(htmlspecialchars($components[$i]['component_name'])); ?></a></td>
                        <td align="center"><?php echo lookup('assigned_to', $components[$i]['owner']); ?></td>
                        <td align="center"><?php echo $components[$i]['active'] ? translate("Yes") : translate("No"); ?></td>
                        <td align="center"><?php echo $components[$i]['sort_order']; ?></td>
                        <td align="center"><?php if (!$components[$i]['bug_count']) { ?><a href="project.php?op=del_component&id=<?php echo $components[$i]['component_id']; ?>&project_id=<?php echo $project_id; ?>&ak=<?php echo make_action_key(); ?>"><?php echo translate("Delete"); ?></a><?php } ?></td>
                    </tr>
                <?php } ?>
                <?php if (!$count) { ?>
                    <tr>
                        <td colspan="4" align="center"><?php echo translate("No components found"); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </td>
    </tr>
</table>
