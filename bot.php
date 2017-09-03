<?php
//Includes all functions and parses the post data into appropriate variables
include 'functions.php';
$callback = json_decode(file_get_contents('php://input'));
$attachments = $callback->attachments;
$avatar = $callback->avatar_url;
$name = $callback->name;
$type = $callback->sender_type;
$text = $callback->text;
$userid = $callback->user_id;
//If logging is enables in the config, this logs the chat to specified file and directory
logging($userid, $name, $text);
//Only handles messages from users to prevent infinite loops
$ignored = read_array('ignore.php');
if ($type == 'user' && !in_array($userid, $ignored)) {
	//Reads the admins array for authentication when running commands
	$admins = read_array('admins.php');
	if ($text[0] !== '/') {
		//Basic response is a simple response to a found phrase
		basic_response($text, $name, $userid);
		//If the Weather Underground API token and location are set, this will return a forecast if someone says "weather"
		weather_response($text);
		//If anyone says "ethereum" this will return the price in USD and BTC
		btc_response($text);
		eth_response($text);
	} elseif (in_array($userid, $admins)) {
		//Parses command arguments into array commands
		$command = parse_cmd($text);
		if ($text == '/help') {
			disp_help();
		} elseif ($text == '/ignorelist') {
			list_ignored();
		} elseif (strpos($text, '/ignore') !== FALSE && isset($command[0])) {
			send(add_ignore($command[0]));
		} elseif (strpos($text, '/unignore') !== FALSE && isset($command[0])) {
			send(del_ignore($command[0]));
		} elseif ($text == '/responses') {
			list_responses();
		} elseif (strpos($text, '/addresponse') !== FALSE && isset($command[0]) && isset($command[1])) {
			send(add_response($command[0], $command[1]));
		} elseif (strpos($text, '/delresponse') !== FALSE && isset($command[0])) {
			send(del_response($command[0]));
		} elseif ($text == '/admins') {
			list_admins();
		} elseif (strpos($text, '/getuserid') !== FALSE && isset($command[0])) {
			send("$command[0]'s User ID is " . get_user_id($command[0]));
		} elseif (strpos($text, '/addadmin') !== FALSE && isset($command[0])) {
			send(add_admin($command[0]));
		} elseif (strpos($text, '/deladmin') !== FALSE && isset($command[0])) {
			send(del_admin($command[0]));
		} else {
			send('Invalid Command');
		}
	}
}
