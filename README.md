To get started with this bot, add your token to the line $token = ""; then move the file to a directory served by your webserver
The WeatherUnderground token and such are optional so you can remove everything related if you don't plan on using it. I mainly put that in there for personal use and to show an example of how to add things more complex than simple responses.


To get a bot go here https://dev.groupme.com/bots

The Bot ID is your token and you should make the callback url the full URL where the php file is served
After that, just say test or abc to see if it works.  I have only tested this in Debian 9 with Apache and cannot guarantee it works with any other platform.
Some php functions may be disabled if you are on a vps or a plain webhost.  I have only tested this running on my own personal server

I cannot guarantee the safety of using this bot on your system but I have tried making escape sequences in both my name and message to break the bot or escape the shell and read files with no success.
