Optional plugins
=========================================

For the record to understand how these get linked with SSMS, both database entry's should point to the same table/username/password as SSMS.

Crashlogger
-----------

The Crashlogger is based on DarthNinja's version. You have to add a line called "crashlogger" into your database for this to work.
You can change the database name but then you would have to edit the crashlogger.sp to do so.

Tables that are needed for this come with the sql from SSMS. And if not present it should create them.

Adminlogging
------------

The adminlogging will check if a person is a admin or not and just log pretty much everything (including chat) of the player to the logs.
Do take a mental note that if you have 30 servers it can consume some table spaces.

If you want to use the admin logging with a different database then "default" change the line:

        SQL_TConnect(GotDatabase, "default");

To a entry in your databases.cfg, if you just use the .smx your database.cfg needs to have a "default" one where it writes too (assuming that
a database one and not sqlite)

ServerAds
---------

The server ads works in the same concepts of http://forums.alliedmods.net/showthread.php?t=150674 , only difference is that this one is 1 plugin
that gets the data from the ssms database. Afterall, if we pull the servers for data.. there is no need to do it again. So make sure you poll like
once a minute (which is the intetion of ssms) if you run this plugin. It will check the gametype and only show "also availible" lines on your screen
for that type. If you go to your settings per server you can say "show server in motd" if this is set to "No" it wont show it in the list.

If your database is called anything else than "ssms" change the line in the config called:

	SQL_TConnect(SQLT_Connect, "ssms");

To what you want from you databases.cfg. Dont forget to place the txt file into your translation folder and adjust accordingly. (so change the
lethal-zone part for instance) some other values can be changed there too.
