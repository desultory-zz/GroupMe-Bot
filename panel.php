<html>
<head>
<style>
table {
  border-spacing: 0;
  text-align: center;
  font-size: 16px;
}
th, td {
  height: 100%;
  padding: 10px;
  vertical-align: middle;
}
tr:nth-child(even) {
  background-color: rgba(255, 255, 255, 0.50);
}
tr:nth-child(odd) {
  background-color: rgba(255, 255, 255, 0.25);
}
input {
  width: 100%;
  height: 100%;
  border: 0px;
  color: white;
  text-indent: 0px;
  font-size: 16px;
  background: rgba(0, 0, 0, 0);
  font-family: "Lucida Console", Monaco, monospace;
}
body {
  background: url("https://picload.org/image/dadcrgpl/background.png");
  background-repeat: repeat-y;
  background-size: cover;
  color: white;
  margin: auto;
  left: 0;
  right: 0;
  position: absolute;
  font-size: 16px;
  text-align: center;
  font-family: "Lucida Console", Monaco, monospace;}
</style>
	<title>PHP GroupMe Bot</title>
</head>
<body>
<?php
include 'functions.php';
if (file_exists('config.php')) {
	if (isset($_POST['delete'])) {
		del_responses($_POST['delete']);
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
<h3>%n can be used to mention someone in a response</h3>
<form name="add" method="post" action="">
<table align="center">
	<tr>
		<th><input type="text" name="find" placeholder="Text to find"></th>
		<th><input type="text" name="respond" placeholder="Text to respond with"></th>
		<th><input type="submit" value="Add"></th>
	</tr>
</table>
</form>
<form name="delete" method="post" action="">
<table align="center">
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
		echo "<td>$element[0]</td>";
		echo "<td>$element[1]</td>";
		echo "<td><input type=\"checkbox\" name=\"delete[]\" value=\"$iteration\"></td>";
		echo "</tr>";
		$iteration++;
	}?>
	<tr>
		<th colspan="3"><input type="submit" value="Remove"></th>
	</tr>
</table>
</form>
<form name="Users" method="post" action="">
<table align="center">
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
		echo "<td style=\"text-align: left;\"><img src=\"$avatar\" style=\"width:50px; height:50px; vertical-align: middle;\"> $name ($userid)</td>";
		if (in_array($userid, $admins)) {
			echo "<td><input type=\"checkbox\" name=\"admins[$userid]\" value=\"1\" checked></td>";
		} else {
			echo "<td><input type=\"checkbox\" name=\"admins[$userid]\" value=\"1\"></td>";
		}
		if (in_array($userid, $ignore)) {
			echo "<td><input type=\"checkbox\" name=\"ignore[$userid]\" value=\"1\" checked></td>";
		} else {
			echo "<td><input type=\"checkbox\" name=\"ignore[$userid]\" value=\"1\"></td>";
		}
		echo "</tr>";
	}?>
	<tr>
		<th colspan="3"><input type="submit" value="Update"></th>
	</tr>
</table>
	<input type="hidden" name="users[]" value="1">
</form>
<form name="settings" method="post" action="">
<table align="center">
	<tr>
		<th>Name</th>
		<th>State</th>
		<th>Delete</th>
	</tr>
	<?php
	$settings = read_array('settings.php');
	foreach ($settings as $key=>$value) {
		echo "<tr>";
		echo "<td>$key</td>";
		if ($value) {
			echo "<td><input type=\"checkbox\" name=\"setting[$key]\" value=\"1\" checked></td>";
		} else {
			echo "<td><input type=\"checkbox\" name=\"setting[$key]\" value=\"1\"></td>";
		}
		echo "<td><input type=\"checkbox\" name=\"del_setting[$key]\" value=\"1\"></td>";
		echo "</tr>";
	}?>
	<tr>
		<td>Add setting</td>
		<td colspan="2"><input type="text" name="new_setting" placeholder="Name for new setting"></td>
	</tr>
	<tr>
		<th colspan="3"><input type="submit" value="Update"></th>
	</tr>
</table>
	<input type="hidden" name="setting[]" value="1">
</form>
<form name="send" method="post" action="">
<table align="center">
	<tr>
		<th colspan="3"><input type="text" name="send" placeholder="Message to send"></th>
	</tr>
</table>
		<input type="submit" value="Send" style="display: none">
</form>
<?php
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
<form name="setup" method="post" action="">
	<input type="text" style="width: 50%;" name="user" placeholder="Panel username"><br>
	<input type="text" style="width: 50%;" name="pass" placeholder="Panel password"><br>
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
}?>
</body>
</html>
