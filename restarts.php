<?php
	
	include("lib/functions.php");
	include("config.php");
	
	mysql_connect($host,$user,$pass);
	@mysql_select_db($table) or die( "Unable to select database");
	
	if( isset( $_POST[ 'x' ] ) )
		if( !isset( $_POST[ 'delete' ] ) )
			mysql_query( "TRUNCATE TABLE restarts" );
		else
			foreach( $_POST[ 'delete' ] as &$id )
				mysql_query( "DELETE FROM restarts where indexnum = " . $id );
	
	$start = head();
	
	if( isset( $_GET[ 'serverid' ] ) && $_GET[ 'serverid' ] != 'all' ) {
		$server = $_GET['serverid'];
		$servername = mysql_result( mysql_query( "SELECT servername FROM servers where serverid = $server" ), 0, "servername");

	} else {
		$server = 'all';
		$servername = 'all servers';
	}
	
	if( isset( $_GET[ 'date' ] ) )
		$date = $_GET[ 'date' ];
	else
		$date = 'month';
	
	echo "<form method='get' action=''><b>Restarts for <select name=\"serverid\">\n<option value=\"all\">All Servers</option>\n";
	$result = mysql_query( "SELECT * from servers ORDER BY servername" ) or die( mysql_error() ); // retrieve all servers
	while( $row = mysql_fetch_array( $result ) ) {
		$row['servername'] = htmlspecialchars($row['servername']);
		echo "	<option value=\"" . $row['serverid'] . "\"" . ( $row['serverid'] == $server ? ' selected' : '' ) . ">" . $row['servername'] . "</option>\n";
	}
	echo "</select>\nduring\n<select name=\"date\">\n<option value=\"month\">last month</option>\n<option value=\"week\"" . ( isset( $_GET[ 'date' ] ) && $_GET[ 'date' ] == 'week' ? ' selected' : '' ) . ">last week</option>\n<option value=\"day\"" . ( isset( $_GET[ 'date' ] ) && $_GET[ 'date' ] == 'day' ? ' selected' : '' ) . ">last 24 hours</option>\n</select>\n<input type=\"submit\" value=\"Go\"/></b></form><br/>";
	
	$restarts = mysql_query( "SELECT timedate FROM restarts " . ( $server == 'all' ? '' : "where serverid = '$server' " ) . "limit 20" );
	
	if( mysql_num_rows( $restarts ) == 0 )
		echo 'No restart statistics available for ' . $servername;
	else {
																			
		$results = mysql_query( "SELECT COUNT( restarts.serverid ) AS count, DATE_FORMAT( restarts.timedate, '%c-%d' ) as date FROM `restarts` WHERE timedate BETWEEN SUBDATE( CURDATE(), INTERVAL 1 " . $date . " ) AND CURDATE() " . ( $server == 'all' ? '' : 'AND restarts.serverid = ' . $server . ' ' ) . 'GROUP BY date' );
		$result  = mysql_fetch_array( $results );
		
		$curr = strtotime( "-1 " . $date );
		$end = time();
		while( $curr <= $end ) {
				
			$day = date( "m-d", $curr );
						
			if( $result && $result[ 'date' ] == $day ) {
				$result = mysql_fetch_array( $results );
			} else
			
			$curr = strtotime( "+1 day", $curr );
			
		}

		//echo "\n<img src=\"graph.png\" alt=\"graph\" /><br/>\n";
		
		echo "<form method='post' action=''>\n<pre>\n<input type=\"image\" src=\"images/delete.gif\" />&nbsp;<b>Listing last 20 restarts for $servername...</b>\n";
		
		$restarts = mysql_query( "SELECT restarts.indexnum, restarts.timedate, servers.serverid as nrid, servers.servername as server FROM restarts join servers on restarts.serverid = servers.serverid " . ( $server == 'all' ? '' : "where restarts.serverid = $server " ) . "ORDER BY restarts.indexnum DESC limit 20" );
		
		while ( $row = mysql_fetch_array( $restarts ) ) {
			echo '<input type="checkbox" value="' . $row[ 'indexnum' ] . '" style="margin: 0px 2px 0px 2px;" name="delete[]" />&nbsp;';
			if( isset( $_GET[ 'serverid' ] ) && $_GET[ 'serverid' ] != 'all' ) {
				echo $row[ 'timedate' ] . ' @ ' .  $row[ 'server' ]  . "\n";
			}
			else {
				echo $row[ 'timedate' ] . " @ <a href=\"restarts.php?serverid=" . $row[ 'nrid' ] . "\">" .  $row[ 'server' ]  . "</a>\n";
			}
		}
		echo '</pre></form>';
		echo '<div id="restartchart"></div>';
?>
<script language="javascript" type="text/javascript">
<!--
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'restartchart',
         plotBackgroundColor: null,
         plotBorderWidth: null,
         plotShadow: false
      },
      title: {
         text: 'Restarts per day'
      },
      tooltip: {
         formatter: function() {
            return '<b>'+ this.point.name +'</b>: '+ this.y +' restarts';
         }
      },
      plotOptions: {
         pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
               enabled: false
            },
            showInLegend: true
         }
      },
       series: [{
         type: 'pie',
         name: 'Server restarts',
         data: [
		 <?php
		 	$first = true;
			mysql_data_seek($results, 0);
			while ($result = mysql_fetch_array($results)) {
				if (!$first)
					echo ',
					';
				else
					$first = false;
				echo '[\''. $result['date'] .'\',		'. $result['count'] .']';	
			}
		 ?>
         ]
      }]
   });
});
-->
</script>
<?php
	}
	
	mysql_close();
	bottom( $start );
	
?>		
