<?php
if (!defined('PHPBT_VERSION')) {
    die('not in phpbt');
}

$project_id = 0;
if (isset($_GET['projects']) && is_numeric($_GET['projects'])) {
    $project_id = (int) $_GET['projects'];
}
$mass_update = false;
if (!empty($_SESSION['uid']) && isset($perm) && $perm->have_perm_proj($project_id)) {
    $mass_update = true;
}
?>
<form action="bug.php" method="post">
    <input type="hidden" name="op" value="mass_update">
    <input type="hidden" name="last_modified_date" value="<?php echo empty($now) ? time() : $now; ?>">
    <table class="bordertable" align="center" style="width: 100%">
        <tr>
            <?php
            for ($i = 0, $colcount = count($db_fields); $i < $colcount; $i++) {
                echo "<th class='{$headers[$i]['class']}' bgcolor='{$headers[$i]['color']}'><a  href='{$headers[$i]['url']}'>{$field_titles[$i]}</a></th>";
            }
            ?>
        </tr>
        <?php if (!$bugcount = count($bugs)) { ?>
            <td colspan="<?php echo $colcount ?>" align="center">
                <?php echo translate("No bugs found"); ?>
            </td>
        <?php } else { ?>
            <?php for ($i = 0; $i < $bugcount; $i++) { ?>
                <?php if (USE_SEVERITY_COLOR_LINE) { ?>
                    <tr class="bugrow" bgcolor="<?php echo $bugs[$i]['severity_color']; ?>"> 
                    <?php } else if (USE_PRIORITY_COLOR_LINE) { ?>
                    <tr class="bugrow" bgcolor="<?php echo $bugs[$i]['priority_color']; ?>"> 
                    <?php } else { ?> 
                    <tr class="bugrow<?php echo ($i % 2 != 0) ? ' alt' : ''; ?>">
                    <?php } ?> 
                    <?php
                    $n = 1;
                    foreach ($bugs[$i] as $var => $val) {
                        if ($var == 'bug_link_id')
                            $bugid = $val;
                        elseif ($var == 'severity_color'
                                || $var == 'project_id'
                                || $var == 'priority_color') {
                            //hidden cols - do nothing
                        } else {
                            $class = '';
                            if ($n == 1) {
                                $class .= ' nowrap';
                            }

                $colorStyle = '';
                if (($var == 'priority_name') && USE_PRIORITY_COLOR) {
                    $colorStyle = ' color:' . $bugs[$i]['priority_color'] . '; ';
                }
                if (($var == 'severity_name') && USE_SEVERITY_COLOR) {
                    $colorStyle = ' color:' . $bugs[$i]['severity_color'] . '; ';
                }

                echo "<td class=\"$class\" style=\"$colorStyle\">";
                            if ($mass_update && $n == 1) {
                                echo "<input type=\"checkbox\" name=\"bugids[]\" value=\"$bugid\">&nbsp;";
                            }
                            echo format_bug_col($val, $var, $bugid, $i, $bugs[$i]);
                            echo '</td>';
                            $n = $n + 1;
                        }
                    }
                    ?>
                </tr>
            <?php } ?>
        <?php } ?>
    </table>

    <?php include('admin/pagination.html'); ?>

    <div class="noprint">
        <?php if ($has_excel) { ?>
            <div align="center">
                <a href="query.php?xl=1"><?php echo translate("Download to spreadsheet"); ?></a>
            </div>
        <?php } ?>

        <div align="left">
            <a href="query.php?op=edit"><?php echo translate("Edit this query"); ?></a>
        </div>
        <?php if (false) { // Was $mass_update. Disabled until fixed  ?>
            <hr>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                    <?php if ($project_id > 0) { ?>
                        <td><?php echo translate("Component"); ?>:
                            <select name="component_id"><option value="-1" selected>No Change</option><?php build_select('component', -1, $project_id) ?></select></td>
                        <td><?php echo translate("Version"); ?>:
                            <select name="version_id"><option value="-1" selected>No Change</option><?php build_select('version', -1, $project_id) ?></select></td>
                    </tr><tr>
                    <?php } ?>
                    <td><?php echo translate("Priority"); ?>:
                        <select name="priority"><option value="-1" selected>No Change</option><?php build_select('priority', -1) ?></select></td>
                    <td><?php echo translate("Status"); ?>:
                        <select name="status_id"><option value="-1" selected>No Change</option><?php build_select('status', -1) ?></select></td>
                </tr><tr>
                    <td><?php echo translate("Severity"); ?>:
                        <select name="severity_id"><option value="-1" selected>No Change</option><?php build_select('severity', -1) ?></select></td>
                    <td><?php echo translate("Resolution"); ?>:
                        <select name="resolution_id"><option value="-1" selected>No Change</option><option value="0"><?php echo translate("None"); ?></option><?php build_select('resolution', -1) ?></select></td>
                </tr><tr>
                    <td><?php echo translate("Assigned to"); ?>:
                        <?php if (isset($perm) && ($perm->have_perm('EditAssignment') or $perm->have_perm_proj($project_id))) { ?>
                            <select name="assigned_to"><option value="-1" selected>No Change</option><option value="0"><?php echo translate("None"); ?></option><?php build_select('owner', -1) ?></select></td>
                    <?php } else { ?>

                        <?php echo lookup('assigned_to', $assigned_to); ?>
                    <input type="hidden" name="assigned_to" value="<?php echo $assigned_to ?>">
                    </td>
                <?php } ?>
                </tr>
                <tr class="noprint">
                    <td valign="top" colspan=2><?php echo translate("Additional comments"); ?>:<br>
                        <textarea name="comments" rows="6" cols="55" <?php echo $disabled ?>><?php echo isset($_POST['comments']) ? $_POST['comments'] : ''; ?></textarea>
                        <br><br>
                        <div align="left">
                            <?php echo translate("Supress notification email"); ?> <input type="checkbox" name="suppress_email" value="1">
                            <input type="submit" value="Submit">
                        </div>
                    </td>
                </tr>
            </table>
        <?php } ?>

    </div>
</form>
