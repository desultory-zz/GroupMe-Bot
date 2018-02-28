This is a simple groupme bot I made using PHP

It has the ability to respond to certain phrases, mention users, give the weather, give current cryptocurrency prices, and do a lot more things pretty easily by simply adding things to the bot.php file.

First, you need to go to https://dev.groupme.com/bots and register a bot there, you should be asked for a callback url, the callback url is the url where the bot.php will be sitting.  An example is https://your.website/botdiectory/bot.php

I recommend running each instance of this bot in it's own subdirectory as it will be using it's own database (db.sqlite)

By default, logs will be made added to the database and displayed in the log section of the site

Once you have creted your bot, go to the directory where the bot is running in a web browser.  Fill in all details, wunderground data is optionsl.

 The  panel will be a way to manage responses, settings, and send messages from the bot.

By default, the bot will only reapond to "test".  You can add and remove responses by going to the panel and changing the responses.

You can make the bot mention a user in a response by adding %n to the response and make it list their userid with %u

I have only tested this with Debian 9, Apache2.4, and PHP 7.0.  I cannot guarantee that it runs on any other system but it should.
Some php functions used may not work on a VPS, Webhost, or server slot.  I have only tested them on a virual machine in ESXi.
I cannot guarantee the security of this bot, I have tested some escape sequences but I cannot guarantee that someone in your groupme won't be able to get root access on your system using this bot.  The good news is that if they do, you'll see them doing it in the chat.

