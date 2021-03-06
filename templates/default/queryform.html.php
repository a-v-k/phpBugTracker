<?php
if (!defined('PHPBT_VERSION')) {
    die('not in phpbt');
}
$emailSearch1 = empty($emailsearch1) ? null : $emailsearch1;
$emailType1 = empty($emailtype1) ? null : $emailtype1;
$emailField1 = empty($emailfield1) ? null : $emailfield1;
$email1 = empty($email1) ? null : $email1;
$title = empty($title) ? null : $title;
$title_type = empty($title_type) ? null : $title_type;
$description = empty($description) ? null : $description;
$description_type = empty($description_type) ? null : $description_type;
$url = empty($url) ? null : $url;
$url_type = empty($url_type) ? null : $url_type;
$start_date = empty($start_date) ? null : $start_date;
$end_date = empty($end_date) ? null : $end_date;
$closed_start_date = empty($closed_start_date) ? null : $closed_start_date;
$closed_end_date = empty($closed_end_date) ? null : $closed_end_date;

$order = empty($order) ? null : $order;
$sort = empty($sort) ? null : $sort;

extract(array(
    'status' => null,
    'resolution' => null,
    'os' => null,
    'priority' => null,
    'severity' => null,
    'database' => null,
    'site' => null,
    'project' => null,
), EXTR_SKIP);
?>
<!--suppress HtmlFormInputWithoutLabel -->
<script type="text/javascript">
    <!--
    versions = new Array();
    closedversions = new Array();
    components = new Array();
    versions['All'] = new Array(new Array('', 'All'));
    closedversions['All'] = new Array(new Array('', 'All'));
    components['All'] = new Array(new Array('', 'All'));
    <?php
    build_project_js();
    ?>

    // Saved queries
    savedQueries = new Array();
    <?php
    for ($i = 0, $querycount = count($queries); $i < $querycount; $i++) {
        echo "savedQueries[$i] = '{$queries[$i]['saved_query_name']}'; ";
    }
    ?>

    function updateMenus(f) {
        var sel = f.projects[f.projects.selectedIndex].text;
        f.versions.length = versions[sel].length;
        var x;
        for (x = 0; x < versions[sel].length; x++) {
            f.versions.options[x].value = versions[sel][x][0];
            f.versions.options[x].text = versions[sel][x][1];
        }
        f.closedinversion.length = closedversions[sel].length;
        for (x = 0; x < closedversions[sel].length; x++) {
            f.closedinversion.options[x].value = closedversions[sel][x][0];
            f.closedinversion.options[x].text = closedversions[sel][x][1];
        }
        f.tobeclosedinversion.length = closedversions[sel].length;
        for (x = 0; x < closedversions[sel].length; x++) {
            f.tobeclosedinversion.options[x].value = closedversions[sel][x][0];
            f.tobeclosedinversion.options[x].text = closedversions[sel][x][1];
        }
        f.components.length = components[sel].length;
        for (x = 0; x < components[sel].length; x++) {
            f.components.options[x].value = components[sel][x][0];
            f.components.options[x].text = components[sel][x][1];
        }
    }

    function checkSavedQueries(frm) {
        if (frm.savedqueryname.value != '') {
            for (var i = 0; i < savedQueries.length; i++) {
                if (frm.savedqueryname.value == savedQueries[i]) {
                    if (confirm('Are you sure you want to override the saved query named "' + frm.savedqueryname.value + '"?')) {
                        frm.savedqueryoverride.value = 1;
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    //-->
</script>
<form method="get" action="query.php" name="query" onSubmit="return checkSavedQueries(this)">
    <table>
        <tr>
            <th align="left"><?php echo translate("Status"); ?>:</th>
            <th align="left"><?php echo translate("Resolution"); ?>:</th>
            <th align="left"><?php echo translate("Operating System"); ?>:</th>
            <th align="left"><?php echo translate("Priority"); ?>:</th>
            <th align="left"><?php echo translate("Severity"); ?>:</th>
            <th align="left"><?php echo translate("Database"); ?>:</th>
            <th align="left"><?php echo translate("Reported on Site"); ?>:</th>
        </tr>
        <tr>
            <td align="left" valign="top">
                <select name="status[]" multiple size="7">
                    <?php echo build_select('status', $status); ?>
                </select>
            </td>
            <td align="left" valign="top">
                <select name="resolution[]" multiple size="7">
                    <option value="0"><?php echo translate("None"); ?></option>
                    <?php build_select('resolution', $resolution); ?>
                </select>
            </td>
            <td align="left" valign="top">
                <select name="os[]" multiple size="7">
                    <?php build_select('os', $os); ?>
                </select>
            </td>
            <td align="left" valign="top">
                <select name="priority[]" multiple size="7">
                    <?php build_select('priority', $priority); ?>
                </select></td>
            <td align="left" valign="top">
                <select name="severity[]" multiple size="7">
                    <?php build_select('severity', $severity); ?>
                </select></td>
            <td align="left" valign="top">
                <select name="database[]" multiple size="7">
                    <?php build_select('database', $database); ?>
                </select></td>
            <td align="left" valign="top">
                <select name="site[]" multiple size="7">
                    <?php build_select('site', $site); ?>
                </select></td>
        </tr>
    </table>
    <br>


    <table>
        <tr>
            <td>
                <table border="1" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <table cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td rowspan="2" valign="top">
                                        <select name="emailsearch1">
                                            <option <?php if (!$emailSearch1 || $emailSearch1 == 'email') echo "selected"; ?>
                                                    value="email"><?php echo translate("Email"); ?></option>
                                            <option <?php if ($emailSearch1 == 'login') echo "selected"; ?>
                                                    value="login"><?php echo translate("Login"); ?></option>
                                        </select>:
                                        + <input name="email1" size="30"
                                                 value="<?php echo $email1; ?>">&nbsp;<?php echo translate("matching as"); ?>
                                        &nbsp;
                                        <select name="emailtype1">
                                            <option <?php if ($emailType1 == 'rlike') echo "selected"; ?>
                                                    value="rlike"><?php echo translate("regexp"); ?></option>
                                            <option <?php if ($emailType1 == 'not rlike') echo "selected"; ?>
                                                    value="not rlike"><?php echo translate("not regexp"); ?></option>
                                            <option <?php if (!$emailType1 || $emailType1 == 'like') echo "selected"; ?>
                                                    value="like"><?php echo translate("substring"); ?></option>
                                            <option <?php if ($emailType1 == '=') echo "selected"; ?>
                                                    value="="><?php echo translate("exact"); ?></option>
                                        </select></td>
                                    <td><input type="checkbox" name="emailfield1[]" value="owner"
                                            <?php if (empty($emailField1) || (is_array($emailField1) && in_array("owner", $emailField1))) echo "checked"; ?>>
                                        <?php echo translate("Assigned To"); ?></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="emailfield1[]" value="reporter"
                                            <?php if (is_array($emailField1) && in_array("reporter", $emailField1)) echo "checked"; ?>>
                                        <?php echo translate("Reporter"); ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="unassigned" value="1"
                                <?php if (!empty($unassigned)) echo "checked"; ?>>
                            <?php echo translate("Unassigned"); ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table border="0">
        <tr>
            <td align="right"><?php echo translate("Summary"); ?>:</td>
            <td><input name="title" size="30" value="<?php echo htmlspecialchars($title); ?>"></td>
            <td><select name="title_type">
                    <option value="like" <?php if (!$title_type || $title_type == 'like') echo "selected"; ?>><?php echo translate("substring"); ?></option>
                    <option value="rlike" <?php if ($title_type == 'rlike') echo "selected"; ?>><?php echo translate("regexp"); ?></option>
                    <option value="not rlike" <?php if ($title_type == 'not rlike') echo "selected"; ?>><?php echo translate("not regexp"); ?></option>
                </select></td>
        </tr>
        <tr>
            <td align="right"><?php echo translate("A description entry"); ?>:</td>
            <td><input name="description" size="30" value="<?php echo htmlspecialchars($description); ?>"></td>
            <td><select name="description_type">
                    <option value="like" <?php if (!$description_type || $description_type == 'like') echo "selected"; ?>><?php echo translate("substring"); ?></option>
                    <option value="rlike" <?php if ($description_type == 'rlike') echo "selected"; ?>><?php echo translate("regexp"); ?></option>
                    <option value="not rlike" <?php if ($description_type == 'not rlike') echo "selected"; ?>><?php echo translate("not regexp"); ?></option>
                </select></td>
        </tr>
        <tr>
            <td align="right"><?php echo translate("URL"); ?>:</td>
            <td><input name="url" size="30" value="<?php echo $url; ?>"></td>
            <td><select name="url_type">
                    <option value="like" <?php if (!$url_type || $url_type == 'like') echo "selected"; ?>><?php echo translate("substring"); ?></option>
                    <option value="rlike" <?php if ($url_type == 'rlike') echo "selected"; ?>><?php echo translate("regexp"); ?></option>
                    <option value="not rlike" <?php if ($url_type == 'not rlike') echo "selected"; ?>><?php echo translate("not regexp"); ?></option>
                </select></td>
        </tr>
        <tr>
            <td align="right">
                <?php echo translate("Created Date Range"); ?>:
            </td>
            <td colspan="2">
                <input type="text" name="start_date" size="11" value="<?php echo htmlspecialchars($start_date); ?>">
                <?php echo translate("to"); ?>
                <input type="text" name="end_date" size="11" value="<?php echo htmlspecialchars($end_date); ?>">
            </td>
        </tr>
        <tr>
            <td align="right">
                <?php echo translate("Closed Date Range"); ?>:
            </td>
            <td colspan="2">
                <input type="text" name="closed_start_date" size="11"
                       value="<?php echo htmlspecialchars($closed_start_date); ?>">
                <?php echo translate("to"); ?>
                <input type="text" name="closed_end_date" size="11"
                       value="<?php echo htmlspecialchars($closed_end_date); ?>">
            </td>
        </tr>
    </table>
    <hr align="left" width="100%">
    <table>
        <tr>
            <td><b><?php echo translate("Project"); ?>:</b></td>
            <td><select name="projects" onChange="updateMenus(this.form)">
                    <option value=''><?php echo translate("All"); ?></option>
                    <?php build_select('project', $project); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><b><?php echo translate("Version"); ?>:</b></td>
            <td><select name="versions">
                    <option value=''><?php echo translate("All"); ?></option>
                    <?php if ($project) build_select('version', $version, $project); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><b><?php echo translate("Closed in Version"); ?>:</b></td>
            <td><select name="closedinversion">
                    <option value=''><?php echo translate("All"); ?></option>
                    <option value='0' <?php echo isset($closedinversion) && $closedinversion != '' && $closedinversion == 0 ? 'selected' : ''; ?>><?php echo translate("Not Set"); ?></option>
                    <?php if ($project) build_select('version', $closedinversion, $project); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><b><?php echo translate("To be Closed in Version"); ?>:</b></td>
            <td><select name="tobeclosedinversion">
                    <option value=''><?php echo translate("All"); ?></option>
                    <option value='0' <?php echo isset($tobeclosedinversion) && $tobeclosedinversion != '' && $tobeclosedinversion == 0 ? 'selected' : ''; ?>><?php echo translate("Not Set"); ?></option>
                    <?php if ($project) build_select('version', $tobeclosedinversion, $project); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><b><?php echo translate("Component"); ?>:</b></td>
            <td><select name="components">
                    <option value=''><?php echo translate("All"); ?></option>
                    <?php if ($project) build_select('component', $component, $project); ?>
                </select>
            </td>
        </tr>
    </table>
    <hr align="left" width="100%">
    <input type="hidden" name="op" value="doquery">
    <input type="hidden" name="form" value="advanced">
    <input type="hidden" name="savedqueryoverride" value="0">
    <b><?php echo translate("Sort By"); ?>:</b>
    <select name="order">
        <option <?php if ($order == 'bug_id') echo "selected"; ?> value="bug_id">
            <?php echo translate("Bug number"); ?>
        </option>
        <option <?php if (!$order || $order == 'severity.sort_order') echo "selected"; ?> value="severity.sort_order">
            <?php echo translate("Severity"); ?>
        </option>
        <option <?php if ($order == 'reporter') echo "selected"; ?> value="reporter">
            <?php echo translate("Reporter"); ?>
        </option>
        <option <?php if ($order == 'status.sort_order') echo "selected"; ?> value="status.sort_order">
            <?php echo translate("Status"); ?>
        </option>
        <option <?php if ($order == 'priority') echo "selected"; ?> value="priority">
            <?php echo translate("Priority"); ?>
        </option>
    </select>
    <select name="sort">
        <option <?php if (!$sort || $sort == 'asc') echo "selected"; ?>
                value="asc"><?php echo translate("Ascending"); ?></option>
        <option <?php if ($sort == 'desc') echo "selected"; ?>
                value="desc"><?php echo translate("Descending"); ?></option>
    </select>
    <br><br>
    <?php if (!empty($_SESSION['uid'])) { ?>
        <?php echo translate("Save this query as"); ?>: <input maxlength="40" type="text" name="savedqueryname">
        <br><br>
    <?php } else { ?>
        <input type="hidden" name="savedqueryname" value=""/>
    <?php } ?>
    <input type="reset" value="<?php echo translate("Reset to default query"); ?>">
    <input type="submit">
</form>

<form method="get" name="clear" action="query.php">
    <input type="hidden" name="op" value="query">
    <input type="hidden" name="form" value="advanced">
    <input type="submit" value="<?php echo translate("Clear All Fields"); ?>">
</form>

<?php if ($querycount) { ?>
    <br><br>
    <b><?php echo translate("Saved Queries"); ?></b>
    <br>
    <?php
    for ($i = 0; $i < $querycount; $i++) {
        echo '<a href="'
            . htmlspecialchars($_SERVER['PHP_SELF'] . '?' . $queries[$i]['saved_query_string']) . '">'
            . $queries[$i]['saved_query_name']
            . '</a> (<a href="'
            . htmlspecialchars($_SERVER['PHP_SELF']
                . '?op=delquery&queryid=' . $queries[$i]['saved_query_id'] . '&form=simple&ak=' . make_action_key())
            . '" onClick="return confirm(\'' . translate("Are you sure you want to delete this saved query?") . '\');">' . translate("Delete") . '</a>)<br>';
    }
    ?>

<?php } ?>
<br>
<a href="query.php?op=query"><?php echo translate("Go to the simple query page"); ?></a>
