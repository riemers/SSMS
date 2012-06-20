<?php
        include("config.php");
        include("lib/functions.php");
	require_once 'lib/boxcar/boxcar_api.php';

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());

        $config = getsettings();

	$boxemail = $config['boxemail']['config'];

if (!function_exists('curl_init')) {
        trigger_error('CURL must be enabled for boxcar_api to function', E_USER_ERROR);
}

$b = new boxcar_api($boxcarapi,$boxcarsec);

$emails = explode(",",$boxemail);
foreach ($emails as $boxalert) {
        try {
		$b->notify($boxalert, 'SUCCES', 'Your first notification for SSMS, you are a champ!');
        } catch(Exception $e) {
		echo "Seems that something went wrong, did you type in the correct email? And did you also subscribed to the SSMS feed in boxcar?";
		echo "Double check that, you will be send back to the config page in 10 seconds. Otherwise just press the back button.";
		echo '<meta http-equiv="refresh" content="10; URL=ssmsconfig.php">';
		echo $e;
		die;
        }
}

?>
<meta http-equiv="refresh" content="0; URL=ssmsconfig.php">

