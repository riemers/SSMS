SSMS - Sourcemod Server Management System
=========================================

This tool should help you in the everlasting battle against plugin versions automatic updates and other handy things
when it comes down to handling sourced game servers. At first i got the idea from the great [tool] nemrun but this would
not work with windows based machines and used the same technique to query the master server for updates which is used
by another great tool [steam condenser]

Download
--------

	git clone git://github.com/Snelvuur/SSMS.git
	cd into your folder where you have it copied to or downloaded in.
	git submodule update --init

It is important to understand it uses submodules, so the init will download those files then too, otherwise they are empty.

Install
-------

First make a database, and fill it with the data of ssms.sql.

Copy over all the files to your folder, update the config.php.example and rename it to config.php
to fit your needs (dont fill in what you dont have/need) You can import your servers if you have
them in hlstats or just use the gui to do a addserver in the start.

!! Make sure the "cache" folder is writable for your webserver! (or just chmod 777 if you want)

To update it automaticly dont forget to add a crontab, as example: 

	*/1 * * * * cd /home/lethal/public_html/admin/ && php servers.php -u > /dev/null 2>&1


For updates on the plugin id's and version matching do this (only do it once! and if possible change the time a bit random)

	58 05 * * * cd /home/lethal/public_html/admin/ && php dbplugins.php -u > /dev/null 2>&1

The DB plugin checker is experimental, so its still a bit buggy, feel free to tune it up a bit more.

	IMPORTANT PART BELOW FOR PEOPLE THAT COMPLAIN AND CANNOT READ

Since this piece of php/html/etc could be filled with "security holes" instead of me finding out the wheel its best for you
to just create a .htpasswd and .htpasswd in your folder (read apache google for that) since you wont hand this out to everybody
anyway :-)

Features
--------

* Automaticly updates your server without the needs to have a tool running locally. This is done by simply sending a _restart towards the server in question (so it can be either windows or linux machines)
* Will warn 1 minute in advance to all players online with a popup or any command you would like to send out to the server before it goes down.
* Overview for all your extensions/metamod/sourcemod plugins nicely in 1 big screen. This way you can easilly see which plugins you have installed and where something is different compared to your other servers.
* Daily restarts set by time and even with a min/max player count
* Alerting of servers that are down via Email, Growl and twitter. 
* Valve Updates for servers get alerted the same way.
* Use the server overview to do quick access things like see the players, restart a server (if empty), use rcon access.

Optional Features
-----------------
* Server crash statistics, you would need to install the plugin that came with ssms on all your servers and add a line to databases.cfg for this to work and set "yes" on the config to allow it to show.
* Admin logging, ever wanted to know if your admins just slap around all the time? Now you can, install the optional admin logging plugin on all your servers, setup databases.cfg and your off.

If you want to use the admin logging with a different database then "default" change the line:

	SQL_TConnect(GotDatabase, "default");

To a entry in your databases.cfg

Server overview for your website/community
------------------------------------------

You can have the servers just like in the list of the admin menu on your own webpage/forum/etc, there is a folder called serverstatus which has a minimum set of files. Copy this over to your favorite location and your done :)

Dont forget to set your database settings in the serverstatus.php file.

Credits
-------
1. Nemrun for his great [tool]
2. Koraktor for helping out and debugging issues and ofcourse his [steam condenser]
3. Multiple people from irc mostly from Alliedmodders for helping/ideas.
4. Several people from our community (Pablo/Alias/etc)
5. DarthNinja for creating the first [crashlogger] plugin.
6. Author of admin logging plugin part (mentioned in the source of supplied plugin)

[tool]: http://nephyrin.net/tools/nemrun/latest/
[steam condenser]: https://github.com/koraktor/steam-condenser
[crashlogger]: http://forums.alliedmods.net/showthread.php?p=1050025
