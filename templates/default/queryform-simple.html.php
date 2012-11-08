<script type="text/JavaScript">
<!--
versions = new Array();
components = new Array();
closedversions = new Array();
versions['All'] = new Array(new Array('','All'));
components['All'] = new Array(new Array('','All'));
closedversions['All'] = new Array(new Array('','All'));
<?php build_project_js(); ?>

// Saved queries
savedQueries = new Array();
<?php for ($i = 0, $querycount = count($queries); $i < $querycount; $i++) { echo "savedQueries[$i] = '{$queries[$i]['saved_query_name']}'; "; } ?>
	
function updateMenus(f) {
  sel = f.projects[f.projects.selectedIndex].text;
  f.versions.length = versions[sel].length;
  for (var x = 0; x < versions[sel].length; x++) {
    f.versions.options[x].value = versions[sel][x][0];
    f.versions.options[x].text = versions[sel][x][1];
  }
  f.components.length = components[sel].length;
  for (var x = 0; x < components[sel].length; x++) {
    f.components.options[x].value = components[sel][x][0];
    f.components.options[x].text = components[sel][x][1];
  }
}

function checkSavedQueries(frm) {
	if (frm.savedqueryname.value != '') {
		for (i = 0; i < savedQueries.length; i++) {
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
		<tr valign="baseline">
			<td valign="top"><b><?php echo translate("Project"); ?>:</b></td>
			<td valign="top">
				<select name="projects" onChange="updateMenus(this.form)">
					<option value=''><?php echo translate("All"); ?></option>
					<?php build_select('project', $project); ?>
				</select>
			</td>
		</tr>
		<tr valign="baseline">
			<td valign="top"><b><?php echo translate("Version"); ?>:</b></td>
			<td valign="top">
				<select name="versions">
					<option value=''><?php echo translate("All"); ?></option>
					<?php if ($project) build_select('version', $version, $project); ?>
				</select>
			</td>
		</tr>
		<tr valign="baseline">
			<td valign="top"><b><?php echo translate("Component"); ?>:</b></td>
			<td valign="top">
				<select name="components">
					<option value=''><?php echo translate("All"); ?></option>
					<?php if ($project) build_select('component', $component, $project); ?>
				</select>
			</td>
		</tr>
		<tr valign="baseline">
			<td valign="top"><b><?php echo translate("Status"); ?>:</b></td>
			<td valign="top">
				<select name="status[]" multiple size="7">
					<?php build_select('status', $status); ?>
				</select>
			</td>
		</tr>
		<tr valign="baseline">
			<td valign="top"><b><?php echo translate("Sort by"); ?>:</b></td>
			<td valign="top">
				<select name="order">
					<option <?php if (!$order || $order == 'priority') echo "selected"; ?> value="priority_name">
					<?php echo translate("Priority"); ?>
					</option>
					<option <?php if ($order == 'severity.sort_order') echo "selected"; ?> value="severity_name">
					<?php echo translate("Severity"); ?>
					</option>
					<option <?php if ($order == 'status_name') echo "selected"; ?> value="status_name">
					<?php echo translate("Status"); ?>
					</option>
					<option <?php if ($order == 'bug_id') echo "selected"; ?> value="bug_id">
					<?php echo translate("Bug number"); ?>
					</option>
					<option <?php if ($order == 'reporter') echo "selected"; ?> value="reporter">
					<?php echo translate("Reporter"); ?>
					</option>
					<option <?php if ($order == 'owner') echo "selected"; ?> value="owner">
					<?php echo translate("Owner"); ?>
					</option>
				</select>
				<select name="sort">
					<option <?php if (!$sort || $sort == 'asc') echo "selected"; ?> value="asc"><?php echo translate("Ascending"); ?></option>
					<option <?php if ($sort == 'desc') echo "selected"; ?> value="desc"><?php echo translate("Descending"); ?></option>
				</select>
			</td>
		</tr>
	</table>
	<br>
	<br>
	<?php if (!empty($_SESSION['uid'])) { ?>
		<?php echo translate("Save this query as"); ?>: <input maxlength="40" type="text" name="savedqueryname">
		<br><br>
	<?php } else { ?>
                <input type="hidden" name="savedqueryname" value="" />
        <?php } ?>

	<input type="hidden" name="op" value="doquery">
	<input type="hidden" name="savedqueryoverride" value="0">
	<input type="reset" value="<?php echo translate("Reset to default query"); ?>">
	<input type="submit">
    </form>

    <form method="get" name="clear" action="query.php">
        <input type="hidden" name="op" value="query">
        <input type="submit" value="<?php echo translate("Clear All Fields"); ?>">
    </form>
	<?php if ($querycount) { ?>
		<br><br>
		<b><?php echo translate("Saved Queries"); ?></b>
		<br>
		<?php 
			for ($i = 0; $i < $querycount; $i++) {
				echo '<a href="'.$_SERVER['PHP_SELF'].'?'.
					$queries[$i]['saved_query_string'].'">'.
					$queries[$i]['saved_query_name'].'</a> (<a href="'.
					$_SERVER['PHP_SELF'].'?op=delquery&queryid='.
					$queries[$i]['saved_query_id'].'&form=simple" onClick="return confirm(\''.translate("Are you sure you want to delete this saved query?").'\');">'.translate("Delete").'</a>)<br>';
			}
		?>
		
	<?php } ?>
<br>
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?op=query&form=advanced"><?php echo translate("Go to the advanced query page"); ?></a>
