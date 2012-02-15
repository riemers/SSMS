Replay Crawler for youtube
=========================================

These files will help with the crawling of youtube. Server have a specific matchid per map round. We use this matchid to search on youtube for a specific field to match the movie to one of the servers in the list of SSMS.

Install
--------

Make sure you install the correct tables (should be in the main .sql file of ssms)
For the servers in question (default is no) turn the replay to "on" in the configuration tab of the server.

	Please note, if your sv_tags doesn't include "replays" it will not show this option!

Add a crontab entry to run every 2 minutes. It will check multiple times up to the duration setup in the mani configuration
in ssms. If you set this any higher then 30 days it will stress out more to youtube and perhaps be faulty too (use on your on risk)

Example crontab:

	*/2 * * * * cd /home/lethal/public_html/admin/replay && php crawler.php > /dev/null 2>&1

Please note that this needs the Zend package with the google library in order to work (thats why it is included)

	Change the youtube account data and api key in the config.php accordingly!

Also if you dont want to use this in a youtube channel, remove the following line:

	$yt->insertEntry( $videoEntry, $yt->getUserFavorites( "LethalZone" )->getSelfLink()->href );

But if you do want it, then change the "LethalZone" to whatever your channel name is.
