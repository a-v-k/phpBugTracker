<?php
	if (!isset($reporter))    $reporter = '';
	if (!isset($version))     $version = '';
	if (!isset($title))       $title = '';
	if (!isset($description)) $description = '';
	if (!isset($url))         $url = '';
	if (!isset($severity))    $severity = '';
	if (!isset($priority))    $priority = '';
	if (!isset($component))   $component = '';
	if (!isset($database))    $database = '';
	if (!isset($site))        $site = '';
	if (!isset($os))          $os = '';

	$reporter = $reporter ? $reporter : $u
?>
		<form action="bug.php" method="post" enctype="multipart/form-data">
			<table border="0">
				<?php if (!empty($error)) { ?>
				<tr>
					<td colspan="2" class="error">
						<?php echo $error ?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Project"); ?>:
					</td>
					<td>
						<?php echo htmlspecialchars($projectname); ?>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Component"); ?>:
					</td>
					<td>
						<select name="component">
							<?php build_select('component',  $component, $project) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Version"); ?>:
					</td>
					<td>
						<select name="version">
							<?php build_select('version', $version, $project) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Summary"); ?>:
					</td>
					<td>
						<input type="text" size="55" maxlength="100" name="title" value="<?php echo htmlspecialchars($title); ?>">
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Description"); ?>:
					</td>
					<td>
						<textarea name="description" cols="55" rows="8"><?php echo htmlspecialchars($description); ?></textarea>
						<?php echo translate("PRETAGS"); ?>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						URL:
					</td>
					<td>
						<input type="text" size="55" maxlength="255" name="url" value="<?php echo $url ?>">
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Severity"); ?>:
					</td>
					<td>
						<select name="severity">
							<?php build_select('severity', $severity) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Priority"); ?>:
					</td>
					<td>
						<select name="priority">
							<?php build_select('priority', $priority) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Database"); ?>:
					</td>
					<td>
						<select name="database">
							<?php build_select('database', $database) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Site"); ?>:
					</td>
					<td>
						<select name="site">
							<?php build_select('site', $site) ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Operating System"); ?>:
					</td>
					<td>
						<select name="os">
							<?php build_select('os', $os) ?>
						</select>
					</td>
				</tr>
                <!--
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Reporter"); ?>:
					</td>
					<td>
					<?php if (isset($perm) and $perm->have_perm_proj($project)) { ?>
						<select name="reporter">
							<?php build_select('reporter', $reporter) ?>
						</select>
					<?php 	}
						else {
							echo lookup('reporter', $reporter);
						} ?>
						<input type="hidden" name="reporter" value="<?php echo $reporter ?>">
					</td>
				</tr>
                -->
				<tr>
					<td align="right" valign="top">
						<?php echo translate("Attachments"); ?>:
					</td>
					<td>
						<table border="0" align="center">
                                                    <tr>
                                                        <td colspan="2" align="center">
                                                            If you wish to attach a file to this report, please choose a file to upload and enter a one-line description.
                                                            <br>
                                                            <?php
                                                            if (isset($uploadMaxFileSize)) {
                                                                ?>
                                                                Maximum file size: <?php echo number_format($uploadMaxFileSize) ?> bytes
                                                                <br />
                                                                <br />
                                                                <?php
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
							<tr>
								<td>
									File:
								</td>
								<td>
									<input type="file" name="attachment">
								</td>
							</tr>
							<tr>
								<td>
									Description:
								</td>
								<td>
									<input type="text" name="at_description" size="60" maxlength="255" value="">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<input type="submit" name="submit" value="<?php echo translate("Submit"); ?>"> 
			<input type="hidden" name="bugid" value="0"> 
			<input type="hidden" name="project" value="<?php echo htmlspecialchars($project); ?>">
			<input type="hidden" name="op" value="do">
		</form>
