<?php
if (file_exists('config.php')) {
echo "config.php already exists, delete it if you want to generate a new one with this script";
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
			$config .= "\$logdirchmod = '0755';\n";
		}
	} else {
		$error = 1;
		echo "You must specify an api token and bot token";
	}
	if (!$error) {
		$me = json_decode(file_get_contents("https://api.groupme.com/v3/users/me?token=$apitoken"));
		echo var_dump($me);
		$id = $me->response->id;
		$admins = "<?php\n[\"$id\"]";
		file_put_contents('admins.php', $admins);
		file_put_contents('ignore.php', "<?php\n[]");
		file_put_contents('responses.php', "<?php\n[[\"test\",\"It works!\"]]");
		file_put_contents('config.php', $config);
		sleep(1);
		header("Refresh:0");
	}
}
echo '<html>';
echo '<head>';
echo '<title>PHP GroupMe Bot Setup</title>';
echo '</head>';
echo '<form name="setup" method="post" action="">';
echo '<input type="text" style="width: 50%;" name="apitoken" placeholder="Your GroupMe API token"><br>';
echo '<input type="text" style="width: 50%;" name="bottoken" placeholder="Your GroupMe bot token"><br>';
echo '<input type="text" style="width: 50%;" name="wutoken" placeholder="Your WeatherUnderground API token"><br>';
echo '<input type="text" style="width: 50%;" name="wuloc" placeholder="Your WeatherUnderground Location Code"><br>';
echo '<input type="text" style="width: 50%;" name="logdir" placeholder="Log directory, logs is the default"><br>';
echo '<input type="text" style="width: 50%;" name="logname" placeholder="Log name, log is the default"><br>';
echo '<input type="text" style="width: 50%;" name="logchmod" placeholder="Log chmod, 0755 is the default"><br>';
echo '<input type="submit" value="generate"><br>';
} else {
	echo "Working directory is not writeable, either chown it to the webserver user and group or allow write permissions to everyone";
}
