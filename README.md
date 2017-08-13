This is a simple groupme bot I made that works in php

First, you need to go to https://dev.groupme.com/bots and register a bot there, you should be asked for a callback url, the callback url is the url where the bot.php will be sitting.  An example is https://yout.website/bot/bot.php

I recommend running each instance of this bot in it's own subdirectory as it will be using it's own config, response, and admin file and its own loggig file and directory.

By default, logs will be made under /logs/ so you can restrict access to this directory through your webserver (This way people cant read your chat history if they find the directory)

Once you have your bot registered, edit config.php to add your bot token, if you have a weatherunderground token add it too, if you don't, move on.

I have made a few basic responses in the responses.php file, edit, add or remove them to fit your specs.
If you already have the bot in the appropriate place for groupme callback, you can use the commands /addresponse "catch" "respond" in the chat the bot is in to add a response
You can also use  /delresponse "catch" to delete a response if you want.  

For these commands to work, you must have your ID in the admins.php file.  You can find your id by sending a message and reading the log, it will contain your ID next to your name.


When writing responses through the commands or by editing the responses.php file, you can use things like $name or $id to be put in the response.  You can code in the ability to have custom responses for different users pretty easily.

An example of doing this would be to add a block like this:

if ($name == "dave" && $text == "hey") {
	send("You aren't welcome here, dave", null, null);
}

It is bad practice to do authentication by name but its fine to do responses by name because someone can change their name to get a response but you don't want them copying your name to get admin commands


I have only tested this with Debian 9, Apache2, and PHP 7.0.  I cannot guarantee that it runs on any other system but it should.
Some php functions used may not work on a VPS, Webhost, or server slot.  I have only tested them on a virual machine in ESXi.
I cannot guarantee the security of this bot, I have tested some escape sequences but I cannot guarantee that someone in your groupme won't be able to get root access on your system using this bot.  The good news is that if they do, you'll see them doing it in the chat.


I wrote most of the comments and all of this README at 6AM after coding this all night because I was bored.  If there are spelling errors or grammar errors I might fix them eventually, but probably not.
