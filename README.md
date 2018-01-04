This is a simple groupme bot I made using PHP

It has the ability to respond to certain phrases, mention users, give the weather, give current cryptocurrency prices, and do a lot more things pretty easily by simply adding things to the bot.php file.

First, you need to go to https://dev.groupme.com/bots and register a bot there, you should be asked for a callback url, the callback url is the url where the bot.php will be sitting.  An example is https://your.website/botdiectory/bot.php

I recommend running each instance of this bot in it's own subdirectory as it will be using it's own config, response, and admin file and its own logging file and directory.

By default, logs will be made under /logs/ so you can restrict access to this directory through your webserver (This way people cant read your chat history if they find the directory)

Once you have creted your bot, go to the directory where the bot is running and open "panel.php" in a web browser.  Input your API token and bot token.  Everything else is optional and logging information will be set to the default if you leave it blank.  This script will automatically add you to the admins list.  The config file created will be config.php.  If you want to change anything you can either delete the config.php file and open the panel again or you can edit it manueally.  After the config file is created, the panel will be a way to manage responses, settings, and send messages from the bot.

By default, the bot will only reapond to "test".  You can add and remove responses by going to "panel.php" in a web browser or in the chat by using the commands below.

For these commands to work, your user ID must be in the admins list.  The creator of the bot will be the first admin.
Admin commands are as follows:
/help displays information about all commands
/ignorelist lists all usera that are ignored
/ignore -"userid" ignores a specifc user so they will not trigger the bot
/unignore -"userid" removes an ignore
/responses lists all resposnes
/addresponse -"find" -"response" responds with a response every time it finds a certain string
/delresponse -"find" deletes a response
/admins lists current user IDs with admin status
/getuserid -"name" finds someones userID using the groupme api
/addadmin -"userid" adds an admin to the admins file
/deladmin -"userid" removes an admin from the admins file
/enable -"(weather|bitcoin|ethereum|litecoin)" enables the special responses for weather, bitcoin, or ethereum
/disable -"(weather|bitcoin|ethereum|liecoin)" disables the special responses for weather, bitcoin, or ethereum
/status displays the special response status for all special responses

You can make the bot mention a user in a response by adding %n to the response and make it list their userid with %u

I have only tested this with Debian 9, Apache2.4, and PHP 7.0.  I cannot guarantee that it runs on any other system but it should.
Some php functions used may not work on a VPS, Webhost, or server slot.  I have only tested them on a virual machine in ESXi.
I cannot guarantee the security of this bot, I have tested some escape sequences but I cannot guarantee that someone in your groupme won't be able to get root access on your system using this bot.  The good news is that if they do, you'll see them doing it in the chat.


You need php-curl for this to work, to get that on debian do apt install php-curl. EZ.
