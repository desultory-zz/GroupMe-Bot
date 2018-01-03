<?php
include 'functions.php';
if (isset($_POST['delete'])) {
	$delete = $_POST['delete'];
	$responses = read_array('responses.php');
	foreach ($delete as $element) {
		array_splice($responses, $element, 1);
	}
	store_array($responses, 'responses.php');
}
if (isset($_POST['find']) && isset($_POST['respond']) && !empty($_POST['find']) && !empty($_POST['respond'])) {
	$responses = read_array('responses.php');
	$responses[count($responses)] = [$_POST['find'], $_POST['respond']];
	store_array($responses, 'responses.php');
}
if (isset($_POST['setting'])) {
	$settings = read_array('settings.php');
	$update = $_POST['setting'];
	foreach ($settings as $key=>$value) {
		if (isset($update[$key])) {
			$settings[$key] = 1;
		} else {
			$settings[$key] = 0;
		}
	}
	store_array($settings, 'settings.php');
}
if (isset($_POST['del_setting'])) {
	$settings = read_array('settings.php');
	$delete = $_POST['del_setting'];
	foreach ($settings as $key=>$value)  {
		if (isset($delete[$key])) {
			unset($settings[$key]);
		}
	}
	store_array($settings, 'settings.php');
}
if (isset($_POST['new_setting']) && !empty($_POST['new_setting'])) {
	$settings = read_array('settings.php');
	$settings[$_POST['new_setting']] = 1;
	store_array($settings, 'settings.php');
}
if (isset($_POST['send']) && !empty($_POST['send'])) {
	send($_POST['send']);
}?>
<html>
<head>
<style>
tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
The top form can be used to add a response. %n will append the user's name to the response. %u will append the user's id to the response.
<form name="add" method="post" action="">
	<input type="text" name="find" placeholder="Text to find">
	<input type="text" name="respond" placeholder="Text to respond with">
	<input type="submit" value="Add">
</form>
<form name="delete" method="post" action="">
<table>
	<tr>
		<th>Find</th>
		<th>Respond</th>
		<th>Delete</th>
	</tr>
	<?php
	$responses = read_array('responses.php');
	$iteration = 0;
	foreach ($responses as $element) {
		echo "<tr>";
		echo "<th>$element[0]</th>";
		echo "<th>$element[1]</th>";
		echo "<th><input type=\"checkbox\" name=\"delete[]\" value=\"$iteration\">";
		echo "</tr>";
		$iteration = $iteration + 1;
	}?>
</table>
<input type="submit" value="Remove">
</form>
<form name="settings" method="post" action="">
<table>
	<tr>
		<th>Name</th>
		<th>State</th>
		<th>Delete</th>
	</tr>
	<?php
	$settings = read_array('settings.php');
	foreach ($settings as $key=>$value) {
		echo "<tr>";
		echo "<th>$key</th>";
		if ($value) {
			echo "<th><input type=\"checkbox\" name=\"setting[$key]\" value=\"1\" checked>";
		} else {
			echo "<th><input type=\"checkbox\" name=\"setting[$key]\" value=\"1\">";
		}
		echo "<th><input type=\"checkbox\" name=\"del_setting[$key]\" value=\"1\">";
		echo "</tr>";
	}?>
	<tr>
		<th>Add setting</th>
		<th><input type="text" name="new_setting" placeholder="Name for new setting"></th>
	</tr>
</table>
<input type="submit" value="update">
<input type="hidden" name="setting[]" value="1">
</form>
<form name="send" method="post" action="">
	<input type="text" name="send" placeholder="Message to send">
	<input type="submit" value="Send">
</form>
