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
