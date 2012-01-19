
Install
-------

Copy the files to a public folder (on the web)

Open up the tf2image.php and adjust the location of the font (it needs the full path) dont ask me why.
Change the username/password/server/database settings.

Install a crontab entry, example:

	*/5 * * * * cd /home/lethal/public_html/motd/tf2 && php tf2image.php > /dev/null 2>&1

The reason why it is done like this is because the image will be created every 5 minutes.
If we would let it create it on each motd visit it would hammer the database/server more.

Ofcourse you can also use this with any other server types (cstrike/l4d/l4d2/etc) you can do this by looking up:

	$result = mysql_query( "SELECT * FROM servers where type='tf' and showmotd='yes' order by servername" );

As you can see this is only for a list of tf2 servers. It also doesn't show the server if you have it set to no in your server config in SSMS.
