Poor mens webinstaller..<pre>
<?php

$config = "../config.php";

function docheckup() {
$php_version=phpversion();
if($php_version<5)
{
    $error=true;
    $php_error="PHP version is $php_version - too old!";
}

function find_SQL_Version() { 
   $output = shell_exec('mysql -V');    
   preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version); 
   return @$version[0]?$version[0]:-1; 
}

$mysql_version=find_SQL_Version();        
if($mysql_version<5)
{
    if($mysql_version==-1) $mysql_error="MySQL version will be checked at the next step.";
    else $mysql_error="MySQL version is $mysql_version. Version 5 or newer is required.";
}

if(!function_exists('mail'))
{
  $mail_error="PHP Mail function is not enabled!";
}

if( ini_get("safe_mode") )
{
   $error=true;
   $safe_mode_error="Please switch of PHP Safe Mode";
}

if (!function_exists('curl_init')) {
   $curl_error="Curl is not installed, you need this for example boxcar support";
}


echo "\nPHP version newer then version 5: ";
if(empty($php_error)) echo "<span style='color:green;'>$php_version - OK!</span>";
	else
echo "<span style='color:red;'>$php_error</span>";

echo "\nMysql present: ";
if(empty($mysql_error)) echo "<span style='color:green;'>$mysql_version - OK!</span>";
        else
echo "<span style='color:red;'>$mysql_error</span>";

echo "\nMail working in php: "; 
if(empty($mail_error)) echo "<span style='color:green;'>OK!</span>";
        else
echo "<span style='color:red;'>$mail_error</span>";

echo "\nSafe mode turned off: ";
if(empty($safe_mode_error)) echo "<span style='color:green;'>OK!</span>";
        else
echo "<span style='color:red;'>$safe_mode_error</span>";

echo "\nCurl library present: ";
if(empty($curl_error)) echo "<span style='color:green;'>OK!</span>";
        else
echo "<span style='color:red;'>$curl_error</span>";

}

if(count($_POST)==0) {
	docheckup();
?>

<form  action="index.php" method="post">
<table>
<tr><td>Database username:<td><input type="text" name="dbuser"></td></tr>
<tr><td>Database password:<td><input type="text" name="dbpass"></td></tr>
<tr><td>Database host:<td><input type="text" name="dbhost" value="localhost"></td></tr>
<tr><td>Database name:<td><input type="text" name="dbname"></td></tr>
<center><tr><td><input type="submit" name="verder1" value="Next..."></tr></td></center>
</table>
</form>

<?}

if($_POST['verder1']) { //eerst de geposte antwoorden variablen:

$db_error=false;
        
// try to connect to the DB, if not display error
if(!@mysql_connect($_POST['dbhost'],$_POST['dbuser'],$_POST['dbpass']))
{
   $db_error=true;
   $error_msg="Sorry, these details are not correct. 
   Here is the exact error: ".mysql_error();
}
            
if(!$db_error and !@mysql_select_db($_POST['dbname']))
{
   $db_error=true;
   $error_msg="The host, username and password are correct. 
   But something is wrong with the given database.
   Here is the MySQL error: ".mysql_error();
}

if($db_error == "true") {
   print $error_msg;
   echo "\nPlease go back and check again";
   break;
}

$connect_code="<?php
        // default table for location of ssms
        \$host = '".$_POST['dbhost']."';
        \$user = '".$_POST['dbuser']."';
        \$pass = '".$_POST['dbpass']."';
        \$table = '".$_POST['dbname']."';

        // hlstats database (optional)
        \$host_hlstats = 'changeme';
        \$user_hlstats = 'changeme';
        \$pass_hlstats = 'changeme';
        \$table_hlstats = 'changeme';
?>";

if(!is_writable($config))
{
     $error_msg="<p>Sorry, I can't write to <b>$config</b>.
     You will have to edit the file yourself. Here is what you need to insert in that file:<br /><br />
     <textarea rows='15' cols='50' onclick='this.select();'>$connect_code</textarea></p>";
}
else
{
    $fp = fopen($config, 'wb');
    fwrite($fp,$connect_code);
    fclose($fp);
    chmod($config, 0666);
}

if($error_msg) {
    print $error_msg;
} else {
    echo "Correctly written information into <b>$config</b>";
}
?>
<form action="index.php" method="post">
<input type="submit" name="verder2" value="Next...">
</form>

<?}

if($_POST['verder2']) {

	include($config);

        mysql_connect($host, $user, $pass) or die(mysql_error());
        mysql_select_db($table) or die(mysql_error());


	$run = exec("mysql --password=$pass -u $user $table < ssms-1.0.0.sql");
	echo "All done!";
}
?>
</pre>
