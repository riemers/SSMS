<?php
	require_once 'lib/class.growl.php';
        include("config.php");
        include("lib/functions.php");

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

        $config = getsettings();
        $gametypes = gametypes();

$growlip = $config['growlip']['config'];
$growlpass = $config['growlpass']['config'];

$growl = new Growl();
$growl->setAddress($growlip, $growlpass);

        foreach (array_keys($gametypes) as $shortname) {
		$growl->addNotification("$shortname");
	}

// Register with the remote machine.
// You only need to do this once.

$growl->register();
?>
<meta http-equiv="refresh" content="0; URL=ssmsconfig.php">
