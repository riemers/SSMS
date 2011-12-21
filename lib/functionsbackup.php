<?php
	
	function microtime_float ()	{
		list ($msec, $sec) = explode(' ', microtime());
		$microtime = (float)$msec + (float)$sec;
		return $microtime;
	}
	
	function head () {
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Cache-Control" content="no-cache">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Lang" content="en">
		<meta name="author" content="Lethal-Zone">
		<title>Lethal-Zone</title>
		<link rel="stylesheet" type="text/css" href="css/css.css">
	        <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.8.custom.css" rel="Stylesheet">
	        <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
	        <script type="text/javascript" src="js/jquery-ui-1.8.8.custom.min.js"></script>
		<script type="text/javascript" src="js/nicetitle.js"></script>
                <link rel="stylesheet" type="text/css" href="css/hintbox.css">
                <script type="text/javascript" src="js/hintbox.js"></script>
		<link rel="stylesheet" href="css/nicetitle.css">
	</head>
	<body>
		<div id="mainwrapper">
			<a href="http://www.lethal-zone.eu" title="Lethal-Zone - Gaming Community" id="badge"></a>
			<img src="images/logo.png" alt="logo.png" onclick="document.location = 'index.php'">
			<div id="bar">
				<img src="images/bar_end_l.png" alt="bar_end_l.png" style="display:block;float:left;">
				<div class="bar_text"><a href="servers.php">View Servers</a> - <a href="plugins.php">View Plugins</a> - <a href="dbplugins.php">View DB Plugins</a> - <a href="restarts.php">View Restarts</a> - <a href="rules.php">View Default Rules</a> - <a href="adminlogs.php">View admin activity - <a href="gametypes.php">Game Types - <a href="ssmsconfig.php">View Settings</a></div>
				<img src="images/bar_end_r.png" alt="bar_end_r.png" style="display:block;float:left;position:absolute;right:0px;top:0px;">
			</div>
			<div style="clear:both;"></div>
			<div id="content">
				<div id="shadow_left"></div>
				<div id="shadow_right"></div>
				<div id="content-wrapper">
<?php
		
		return microtime_float();
	}
	
	function bottom ( $start ) {
	
?>
				</div>
			 </div>
			 <div id="footer"></div>
			 <p>Script Execution Time: <?php echo round( microtime_float() - $start, 3 ); ?> seconds</p>
		 </div>
	 </body>
 </html>
<?php
	
	}

	function gametypes() {


//        $result = mysql_query("SELECT DISTINCT `games`.`longname` FROM `servers` JOIN `games` ON `servers`.`type` = `games`.`shortname`" ) or die(mysql_error());
        $result = mysql_query( "select distinct games.* from servers, games where servers.type = games.shortname" ) or die(mysql_error());
        while($row = mysql_fetch_array( $result )) {
                $shortname = $row['shortname'];
                $longname = $row['longname'];
                $appid = $row['appID'];
                $version = $row['version'];
                $expired = $row['expired'];
                $rules = $row['defaultrules'];
                $minplayers = $row['minplayers'];

        $gametypes[$shortname] = array ( 'longname' => "$longname",
                                                    'appid' => "$appid",
                                                    'version' => "$version",
                                                    'expired' => "$expired",
                                                    'minplayers' => "$minplayers",
                                                    'rules' => "$rules");

        }

        return($gametypes);
	}

	function getsettings() {
	$result = mysql_query("SELECT * from config") or die (mysql_error());
	while($row = mysql_fetch_array( $result )) {
		$setting = $row['setting'];
		$config = $row['config'];
		$shortname = $row['shortname'];
		$description = $row['description'];

		$reply[$setting] = array ( 'config' => "$config",
					   'description' => "$description",
					   'shortname' => "$shortname");
	}
	
	return($reply);
	}

        function getserver($serverid) {
        $result = mysql_query("SELECT * from servers where serverid = '$serverid'") or die (mysql_error());
	while(($resultArray[] = mysql_fetch_assoc($result)) || array_pop($resultArray)); 
//        while($row = mysql_fetch_assoc( $result )) {
//		foreach($row as $key => $value) { 
//			$$key = $value;
//		}
//        }

        return($resultArray);
        }

function getstatsurl($statsinfo)
{
	if ( $statsinfo[2] == "BOT") { return; } 
	if ($statsinfo[0] == 'hlxce') { $url = "<a href=\"" . $statsinfo[1] . "/hlstats.php?mode=search&q=" . $statsinfo[2] . "&st=uniqueid&game=\"><img src=images/stats_ico.png align=right></a>"; return $url; } 
	if ($statsinfo[0] == 'gameme') { $url = "<a href=\"" . $statsinfo[1] . "/search?q=" . $statsinfo[2] . "&si=uniqueid&rc=all&x=40&y=8\"><img src=images/stats_ico.png align=right></a>"; return $url; } 
}

function isSteamIdValid($steamId)
{
    $re = '^STEAM_[0-1]:[0-1]:(\d+)^';
    if(preg_match($re, $steamId))
    {
        return(true);
    }
    else
    {
        return(false);
    }
}

function isFriendIdValid($friendId)
{
    if((substr($friendId, 0, 9) == "765611979") && (strlen($friendId) == 17))
    {
        return(true);
    }
    else
    {
        return(false);
    }
}

function getFriendId($steamId)
{
    //Test input steamId for invalid format
    if(!isSteamIdValid($steamId)){return('INVALID');}

    //Example SteamID: "STEAM_X:Y:ZZZZZZZZ"
    $gameType = 0; //This is X.  It's either 0 or 1 depending on which game you are playing (CSS, L4D, TF2, etc)
    $authServer = 0; //This is Y.  Some people have a 0, some people have a 1
    $clientId = ''; //This is ZZZZZZZZ.

    //Remove the "STEAM_"
    $steamId = str_replace('STEAM_', '' ,$steamId);

    //Split steamId into parts
    $parts = explode(':', $steamId);
    $gameType = $parts[0];
    $authServer = $parts[1];
    $clientId = $parts[2];

    //Calculate friendId
    $result = bcadd((bcadd('76561197960265728', $authServer)), (bcmul($clientId, '2')));
    return($result);
}

function getupdates($appid,$style) {
if ($style == "last") {
	$url = "http://api.steampowered.com/ISteamNews/GetNewsForApp/v0001/?appid=$appid&count=1&format=xml";
} else {
	$url = "http://api.steampowered.com/ISteamNews/GetNewsForApp/v0001/?appid=$appid&count=20&format=xml";
}

$xmldata = simplexml_load_file($url);
        foreach($xmldata->newsitems as $newsitems)
        {
                foreach ($newsitems->newsitem as $newsitem) {
                        if ($newsitem->feedname == "steam_updates") {
				if ($style == "style") { 
				echo "<fieldset><legend><b><i>" . date("r",intval($newsitem->date)) . " - " . $newsitem->title . "</i></b></legend>";
                                echo "Content: " . $newsitem->contents . "<br/>";
				echo "</fieldset>";
				} else {
                                echo "Title: " . $newsitem->title . "<br/>";
                                echo "Content: " . $newsitem->contents . "<br/>";
				}
                        }
                }
        }
}


  function sec2hms ($sec, $padHours = false) 
  {

    // start with a blank string
    $hms = "";
    
    // do the hours first: there are 3600 seconds in an hour, so if we divide
    // the total number of seconds by 3600 and throw away the remainder, we're
    // left with the number of hours in those seconds
    $hours = intval(intval($sec) / 3600); 

    // add hours to $hms (with a leading 0 if asked for)
    $hms .= ($padHours) 
          ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
          : $hours. ":";
    
    // dividing the total seconds by 60 will give us the number of minutes
    // in total, but we're interested in *minutes past the hour* and to get
    // this, we have to divide by 60 again and then use the remainder
    $minutes = intval(($sec / 60) % 60); 

    // add minutes to $hms (with a leading 0 if needed)
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

    // seconds past the minute are found by dividing the total number of seconds
    // by 60 and using the remainder
    $seconds = intval($sec % 60); 

    // add seconds to $hms (with a leading 0 if needed)
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    // done!
    return $hms;
    
  }


?>
