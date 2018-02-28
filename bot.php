<?php
//Includes all functions and parses the post data into appropriate variables
include 'functions.php';
include 'lights.php';

$callback = json_decode(file_get_contents('php://input'));
$attachments = $callback->attachments;
$avatar = $callback->avatar_url;
$name = $callback->name;
$type = $callback->sender_type;
$text = $callback->text;
$userid = $callback->user_id;

$admins = get_admins();
$ignored = get_ignored();
$settings = get_settings();

//If logging is enabled in the config, this logs the chat to the database
logging($userid, $name, $text);

//Only handles messages from users to prevent infinite loops
if ($type == 'user' && !in_array($userid, $ignored) && $text[0] != '/') {
	//Basic response is a simple response to a found phrase
	basic_response($text, $name, $userid);
	//If the Weather Underground API token and location are set and weather has been enabled, this will return a forecast if someone says "weather"
	if ($settings['weather']) {
		weather_response($text);
	}
	//If anyone says "bitcoin" and the bitcoin setting is enabled, this will return the price in USD
	if ($settings['bitcoin']) {
		btc_response($text);
	}
	//If anyone says "ethereum" and the ethereum setting is enabled, this will return the price in USD and BTC
	if ($settings['ethereum']) {
		eth_response($text);
	}
	//If anyone says "litecoin" and the litecoin setting is enabled, this will return the price in USD and BTC
	if ($settings['litecoin']) {
		ltc_response($text);
	}
	if ($settings['lights']) {
		blink($ip, $pins, "50", "20");
	}
}
if (in_array($userid, $admins) && $type == 'user' && $text == '/config') {
	send_configurl($userid);
}
