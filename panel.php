<html>
<head>
<?php
include 'functions.php';

if (file_exists('config.php')) {
	if (isset($_POST['delete'])) {
		del_response_bynum($_POST['delete']);
	}
	if (isset($_POST['find']) && isset($_POST['respond']) && !empty($_POST['find']) && !empty($_POST['respond'])) {
		add_response($_POST['find'], $_POST['respond']);
	}
	if (isset($_POST['users'])) {
		if (isset($_POST['admins'])) {
			update_admins($_POST['admins']);
		} else {
			file_put_contents('admins.php', "<?php\n[]");
		}
		if (isset($_POST['ignore'])) {
			update_ignore($_POST['ignore']);
		} else {
			file_put_contents('ignore.php', "<?php\n[]");
		}
	}
	if (isset($_POST['setting'])) {
		update_settings($_POST['setting']);
	}
	if (isset($_POST['del_setting']) && !empty($_POST['del_setting'])) {
		del_setting_bynum($_POST['del_setting']);
	}
	if (isset($_POST['new_setting']) && !empty($_POST['new_setting'])) {
		add_setting($_POST['new_setting']);
	}
	if (isset($_POST['send']) && !empty($_POST['send'])) {
		send($_POST['send']);
	}?>
	<title>PHP GroupMe Bot</title>
<style>
tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
When adding a response, %n can be used to mention a user by name and %u will be replace by their user id
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
		$iteration++;
	}?>
</table>
<input type="submit" value="Remove">
</form>
<form name="Users" method="post" action="">
<table>
	<tr>
		<th>Name</th>
		<th>Admin</th>
		<th>Ignored</th>
	</tr>
	<?php
	$admins = read_array('admins.php');
	$ignore = read_array('ignore.php');
	$users = get_users();
	foreach ($users as $user) {
		$name = $user["name"];
		$userid = $user["userid"];
		$avatar = $user["avatar"];
		echo "<tr>";
		echo "<th>$name ($userid)</th>";
		if (in_array($userid, $admins)) {
			echo "<th><input type=\"checkbox\" name=\"admins[$userid]\" value=\"1\" checked>";
		} else {
			echo "<th><input type=\"checkbox\" name=\"admins[$userid]\" value=\"1\">";
		}
		if (in_array($userid, $ignore)) {
			echo "<th><input type=\"checkbox\" name=\"ignore[$userid]\" value=\"1\" checked>";
		} else {
			echo "<th><input type=\"checkbox\" name=\"ignore[$userid]\" value=\"1\">";
		}
		echo "</tr>";
	}?>
	</table>
		<input type="submit" value="update">
		<input type="hidden" name="users[]" value="1">
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
	</form><?php
} else if (is_writeable('./')) {
	if (!empty($_POST)) {
		$error = 0;
		if (!empty($_POST['apitoken']) && !empty($_POST['bottoken'])) {
			$apitoken = $_POST['apitoken'];
			$bottoken = $_POST['bottoken'];
			$config = "<?php\n\$apitoken = '$apitoken';\n\$bottoken = '$bottoken';\n";
			if (!empty($_POST['wutoken'])) {
				if (!empty($_POST['wuloc'])) {
					$wutoken = $_POST['wutoken'];
					$wuloc = $_POST['wuloc'];
					$config .= "\$wutoken = '$wutoken';\n\$wuloc = '$wuloc';\n";
				} else {
					$error = 1;
					echo "You must specify a WeatherUnderground Location if you specify a token";
				}
			}
			$config .= "\$log = '1';\n";
			if (!empty($_POST['logdir'])) {
				$config .= "\$logdir = " . $_POST['logdir'] . ";\n";
			} else {
				$config .= "\$logdir = 'logs';\n";
			}
			if (!empty($_POST['logname'])) {
				$config .= "\$logfile = " . $_POST['logname'] . ";\n";
			} else {
				$config .= "\$logfile = 'log';\n";
			}
			if (!empty($_POST['logchmod'])) {
				$config .= "\$logdirchmod = " . $_POST['logchmod'] . ";\n";
			} else {
				$config .= "\$logdirchmod = '0755';";
			}
		} else {
			$error = 1;
			echo "You must specify an api token and bot token";
		}
		if (!$error) {
			$me = json_decode(file_get_contents("https://api.groupme.com/v3/users/me?token=$apitoken"));
			$id = $me->response->id;
			$admins = "<?php\n[\"$id\"]";
			if (!file_exists('admins.php')) {
				file_put_contents('admins.php', $admins);
			}
			if (!file_exists('ignore.php')) {
				file_put_contents('ignore.php', "<?php\n[]");
			}
			if (!file_exists('responses.php')) {
				file_put_contents('responses.php', "<?php\n[[\"test\",\"It works!\"]]");
			}
			if (!file_exists('settings.php')) {
				if (isset($wutoken) && isset($wuloc)) {
					file_put_contents('settings.php', "<?php\n{\"weather\":1,\"bitcoin\":1,\"ethereum\":1,\"litecoin\":1,\"lights\":0}");
				} else {
					file_put_contents('settings.php', "<?php\n{\"weather\":0,\"bitcoin\":1,\"ethereum\":1,\"litecoin\":1,\"lights\":0}");
				}
			}
			file_put_contents('config.php', $config);
			sleep(1);
			header("Refresh:0");
		}
	}
?>
	<title>PHP GroupMe Bot Setup</title>
</head>
<form name="setup" method="post" action="">
	<input type="text" style="width: 50%;" name="apitoken" placeholder="Your GroupMe API token"><br>
	<input type="text" style="width: 50%;" name="bottoken" placeholder="Your GroupMe bot token"><br>
	<input type="text" style="width: 50%;" name="wutoken" placeholder="Your WeatherUnderground API token"><br>
	<input type="text" style="width: 50%;" name="wuloc" placeholder="Your WeatherUnderground Location Code"><br>
	<input type="text" style="width: 50%;" name="logdir" placeholder="Log directory, logs is the default"><br>
	<input type="text" style="width: 50%;" name="logname" placeholder="Log name, log is the default"><br>
	<input type="text" style="width: 50%;" name="logchmod" placeholder="Log chmod, 0755 is the default"><br>
<input type="submit" value="generate"><br><?php
} else {
	echo "Working directory is not writeable, either chown it to the webserver user and group or allow write permissions to everyone";
}
