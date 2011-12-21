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
		<link rel="stylesheet" type="text/css" href="images/css.css">
	</head>
	<body>
		<div id="mainwrapper">
			<a href="http://www.lethal-zone.eu" title="Lethal-Zone - Gaming Community" id="badge"></a>
			<img src="images/logo.png" onclick="document.location = 'index.php';"/>
			<div id="bar">
				<img src="images/bar_end_l.png" style="display:block;float:left;">
				<div class="bar_text"><a href="servers.php">View Servers</a> - <a href="plugins.php">View Plugins</a> - <a href="restarts.php">View Restarts</a> - <a href="rules.php">View Default Rules</a> - <a href="ssmsconfig.php">View Settings</a></div>
				<img src="images/bar_end_r.png" style="display:block;float:left;position:absolute;right:0px;top:0px;">
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

        $gametypes[$shortname] = array ( 'longname' => "$longname",
                                                    'appid' => "$appid",
                                                    'version' => "$version",
                                                    'expired' => "$expired",
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

	
?>
