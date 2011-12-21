<?
	include("../lib/functions.php");
	include("config.php");
	
	head();
	
	$connectsourcebans = mysql_connect($host_sourcebans, $user_sourcebans, $pass_sourcebans) or die(mysql_error());
	mysql_select_db($table_sourcebans) or die(mysql_error());
	
	$sbAdmins = "SELECT user,authid,srv_group from `sb_admins` ORDER BY `sb_admins`.`srv_group` DESC";
	$query_sbAdmins = mysql_query($sbAdmins);
	$total = 0;
	
	echo time();
	
	echo "<table class=\"listtable\" align=\"left\">";
	echo "	<tr class=\"headers\">
				<td>User</td><td>Steam ID</td><td>Group</td><td>Forum User ID</td><td>Forum Name</td><td>Email</td><td>Lastest Post</td><td>Last Forum Visit</td><td>Last Server Connect</td><td>Tools</td>
			</tr>";
	
	
	
	while ($row = mysql_fetch_assoc($query_sbAdmins)) {
		
		$user = $row["user"];
		$authid = $row["authid"];
		$srv_group = $row["srv_group"];
		
		if($srv_group == '') {
		
		} else {
		
			$total++;
		
			echo "<tr class=\"elements\"><td>$user</td><td>$authid</td><td>$srv_group</td>";
			
			mysql_close($connectsourcebans);
			
			$connectslethalsite = mysql_connect($host_lz, $user_lz, $pass_lz) or die(mysql_error());
			mysql_select_db($table_lz) or die(mysql_error());
		
			$forumId = "SELECT user_id from `phpbb_profile_fields_data` where pf_steam_id = '$authid'";
			$query_forumId = mysql_query($forumId);
			
			$row = mysql_fetch_assoc($query_forumId);
			
			$user_id = $row["user_id"];
			
			echo "<td>$user_id</td>";
			
			$forumInfo = "SELECT username,user_email,user_lastpost_time,user_lastvisit from phpbb_users where user_id = '$user_id'";
			$query_forumInfo = mysql_query($forumInfo);
			
			$row = mysql_fetch_assoc($query_forumInfo);
			
			$username = $row["username"];
			$useremail = $row["user_email"];
			$userLastPost = $row["user_lastpost_time"];
			$userLastVisit = $row["user_lastvisit"];
			
			echo "<td>$username</td><td>$useremail</td><td>$userLastPost</td><td>$userLastVisit</td>";
			
			mysql_close($connectslethalsite);
			
			$connectshlstats = mysql_connect($host_hlstats, $user_hlstats, $pass_hlstats) or die(mysql_error());
			mysql_select_db($table_hlstats) or die(mysql_error());
			
			// steeam id used in hlstats db
			$uniqueId = substr($authid, 8);
			
			$hlplayerid = "SELECT playerId from hlstats_PlayerUniqueIds where uniqueId = '$uniqueId'";
			$query_hlplayerid = mysql_query($hlplayerid);
			
			$total = 0;
			
			while($row = mysql_fetch_array($query_hlplayerid)) {
			
				$hlPlayerId = $row["playerId"];
				$connect = "SELECT last_event FROM `hlstats_Players` where playerId = '$hlPlayerId'";
				$query_connect = mysql_query($connect);
				
				$row = mysql_fetch_assoc($query_connect);
			
				$connectTime = $row["last_event"];
				
				if($connectTime > $total) {
				
					$total = $connectTime;
				
				}	
				
			
			}
			
			
									
			// More than 30 days formula for unix time:
			// if, current - playertime > 26294744 
			// then more than 30 days
			
			$current = time();
			//$time = $current - $connectTime;

			
			$theTime = $current - $total;
			
			
			if ($theTime > 2592000) {
			
				$uConnectTime = date('M j y', $connectTime);

				echo "<td style='background-color:red;'>$uConnectTime</td><td></td></tr>";
				
			
			} else {
			
			$uConnectTime = date('M j y', $connectTime);
			
			echo "<td>$uConnectTime</td><td></td></tr>";
			

			
			}
						
				
			
			mysql_close($connectshlstats);
			
			
			
		}
		mysql_close($connectslethalsite);
	}
	
	echo "</table>";
	
	echo $total;
	
	
	
	
	bottom();

?>
