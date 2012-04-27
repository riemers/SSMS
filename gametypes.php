<?php
	
	include("config.php");
	include("lib/functions.php");
	require_once 'lib/steam-condenser/lib/steam-condenser.php';
	
	$start = head();
	
	mysql_connect($host, $user, $pass) or die(mysql_error());
	mysql_select_db($table) or die(mysql_error());
	
        $gametype = $_GET['gametype'];
        $update = $_GET['update'];

	if ($update == "yes") { 
		$longname = $_GET['longname'];
		mysql_query("UPDATE games SET longname = '$longname' WHERE shortname = '$gametype'");
		echo '<b>Gametype ' . $gametype . ' Updated longname correctly</b><br/>';
	}

        if ($update == "players") {
                $minplayers = $_GET['minplayers'];
                      if ($minplayers == "NULL") {
                		mysql_query("UPDATE games SET minplayers = NULL WHERE shortname = '$gametype'");
                      } else {
                		mysql_query("UPDATE games SET minplayers = '$minplayers' WHERE shortname = '$gametype'");
                      }
                echo '<b>Gametype ' . $gametype . ' Updated playercount restart correctly</b><br/>';
        }

        if ($update == "optional") {
                mysql_query("UPDATE servers SET restartsend='optional',cmdtosend='optional' WHERE type = '$gametype'");
                echo '<b>Gametype ' . $gametype . ' Optional restart send</b><br/>';
        }

        if ($update == "update") {
                mysql_query("UPDATE servers SET restartsend='update' WHERE shortname = '$gametype'");
                echo '<b>Gametype ' . $gametype . ' Updated longname correctly</b><br/>';
        }

	$gametypes = gametypes();

	if ($update) echo '<br/>';
	
	echo '<form action="#">';
	echo '<select name="gametype" onchange="this.form.submit()">';
	echo '<option value="">Select Game</option>';
	foreach (array_keys($gametypes) as $shortname) {
		if ($gametype == $shortname)  { echo '<option value="' . $shortname . '" selected="selected">' . $gametypes["$shortname"]['longname'] . '</option>';}
		else { echo '<option value="'. $shortname . '">' . $gametypes["$shortname"]['longname'] . '</option>';}
        }
	echo '</select>';	
	echo '</form><br/>';
	if ($gametype) {
	echo "<img src=images/stockimages/$gametype.jpg align=left><br/><br/><br/><br/>";

	echo '<fieldset>';
    echo "<legend>Gametype configuration for " . $gametypes["$gametype"]['longname'] . "</legend>";
		echo "&nbsp;&nbsp;&nbsp;<b>Current version is: " . $gametypes["$gametype"]['version'] . "</b><br/>";
		echo "&nbsp;&nbsp;&nbsp;<b>Shortname: " . $gametype . "</b><br/>";
		echo "&nbsp;&nbsp;&nbsp;<b>Out of date?: " . $gametypes["$gametype"]['expired'] ."<br/>";
		echo "&nbsp;&nbsp;&nbsp;<b>AppCode: <a href=http://store.steampowered.com/app/" . $gametypes["$gametype"]['appid'] . "/>" . $gametypes["$gametype"]['appid'] . "</a></b>";
	echo '<fieldset>';
		echo '<legend>Options</legend>';
		echo '<form>';
		echo 'Long name description <input type=text size=50 name="longname" value="'. $gametypes["$gametype"]['longname'] . '">';
		echo '<input type="hidden" name="gametype" value="'. $gametype . '">';
		echo '<input type="hidden" name="update" value="yes">&nbsp;&nbsp;&nbsp;';
		echo '<input type="submit" value="Update">';
		echo '</form>';
		echo '<form>';
                                echo 'Maximum numbers of players present for optional update &nbsp;<select size="1" name="minplayers" id="minplayers">';
                                $players=1;
				$maxplayers=20;
                                if ($gametypes["$gametype"]['minplayers'] == NULL) {
                                        echo '<option value=NULL id=minplayers selected=selected>Dont Check</option>';
                                } else {
                                        echo '<option value=NULL id=minplayers>Dont Check</option>';
                                }
                                while($players<=$maxplayers) {
                                        if ($gametypes["$gametype"]['minplayers'] == $players) {
                                                echo "<option value=\"" . $players . "\" id=\"minplayers\" selected>" . $players . " " . ( $players == "1" ? "Player" : "Players") . "</option>";
                                        }       else  {
                                                echo "<option value=\"" . $players . "\" id=\"minplayers\">" . $players . " " . ( $players == "1" ? "Player" : "Players") . "</option>";
                                        }
                                $players++;
                                }
                echo '<input type="hidden" name="gametype" value="'. $gametype . '">';
                echo '<input type="hidden" name="update" value="players">&nbsp;&nbsp;&nbsp;';
                echo '<input type="submit" value="Update">';
                echo '</form><br/>';


		// add a call towards the singlerestart for the shutdown/quit command

                echo '<form>';
		echo 'An optional update means that Valve updated the files but it is not a required update<br/>';
		echo "Press below button to restart all servers for type " . $gametypes["$gametype"]['longname'];
		if ( $gametypes["$gametype"]['minplayers'] == NULL) { echo ", we DONT check for users present!!<br/>";}
		else { echo " when usercount is below " . $gametypes["$gametype"]['minplayers'] . " player(s)<br/>"; }
                echo '<input type="hidden" name="gametype" value="'. $gametype . '">';
                echo '<input type="hidden" name="update" value="optional">&nbsp;&nbsp;&nbsp;';
                echo '<input type="submit" value="Run Optional update">';
                echo '</form>';

                echo '<form>';
                echo 'A Forced update means that a required update came out but the master servers were not updated yet. Because of this it didn\'t go automatically, below button will fake a update so it will restart all servers which will update. This will <b>NOT</b> check for playercounts on the server but will send out a message to the server\'s players!<br/>';
                echo "Press below button to restart all servers for type " . $gametypes["$gametype"]['longname'];
                echo '<br/><input type="hidden" name="gametype" value="'. $gametype . '">';
                echo '<input type="hidden" name="update" value="update">&nbsp;&nbsp;&nbsp;';
                echo '<input type="submit" value="Force update">';
                echo '</form>';


	echo '</fieldset>';
	echo '</fieldset>';
	echo '<fieldset><legend>Recent updates (Feed from Valve)</legend>';
	getupdates($gametypes["$gametype"]['appid'],'style');
	echo '</fieldset>';
	} else {
	
?>
<div id="piechart1">
    <?php

    $sql = "SELECT * FROM servers";
		$result = mysql_query($sql);
             $data = array();
             while ($row = mysql_fetch_array($result)) {
				$type = $row['type'];
                $current[$type] += (int)$row['currentplayers'];
				
				$total = $total + (int)$row['currentplayers'];
				
                $bots[$type] = $bots[$type] + (int)$row['currentbots'];
                $max[$type] = $max[$type] + (int)$row['maxplayers'];
				//echo $current[$type][current] .' ';
             }
             $data = json_encode($bots);// <<----------------- add this line
			
			$percent['cur'] = 100 / $total;
			
			$i = 1;
			$len = count($current);
			
			?>
		</div>
			
<script type="text/javascript">
var chart;
$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'piechart1',
         defaultSeriesType: 'bar'
      },
      title: {
         text: 'Overall Game Type Activity'
      },
      xAxis: {
         categories: [<?php
		 	$graph1Active = '{name:\'Active\', data:[';
		 	$graph1Bots = '{name:\'Bots\', data:[';
		 	
		 	$first = true;
         	foreach(array_keys($current) as $type) {
				if (!$first) {
					echo ',';
					$graph1Active .= ',';
					$graph1Bots .= ',';
				} else {
					$first = false;
				}
					
				echo '\''. $gametypes[$type]['longname'] .'\'';
				
				$graph1Active .= $current[$type];
				$graph1Bots .= $bots[$type];
			}
			$graph1Active .= ']}';
		 	$graph1Bots .= ']}';
		 ?>]
      },
      yAxis: {
         min: 0,
         title: {
            text: 'Players'
         }
      },
      legend: {
         backgroundColor: Highcharts.theme.legendBackgroundColorSolid || '#FFFFFF',
		 reversed: true
      },
      tooltip: {
         formatter: function() {
            return ''+
                this.series.name +': '+ this.y +'';
         }
      },
      plotOptions: {
         series: {
            stacking: 'normal'
         }
      },
           series: [<?php echo $graph1Bots; ?>,
		   <?php echo $graph1Active; ?>]
   });
   
   
});
             </script>
		
<!--
		
<div id="piechart2">
    <?php

    $sql = "SELECT * FROM servers";
		$result = mysql_query($sql);
             $data = array();
             while ($row = mysql_fetch_array($result)) {
				$type = $row['type'];
                $current[$type]['current'] = $current[$type][current] + (int)$row['currentplayers'];
				$current[$type]['max'] = $current[$type][max] + (int)$row['maxplayers'];
				$totalcur = $totalcur + (int)$row['currentplayers'];
				$totalmax = $totalmax + (int)$row['maxplayers'];
				
                $bots[$type] = $bots[$type] + (int)$row['currentbots'];
                $max[$type] = $max[$type] + (int)$row['maxplayers'];
             }
             $data = json_encode($bots);// <<----------------- add this line
			
			$percentcur = 100 / $totalcur;
			$percentmax = 100 / $totalmax;
			
			?>	

<script type="text/javascript">
		
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'piechart2',
						margin: [50, 0, 0, 0],
						plotBackgroundColor: 'transparent',
						plotBorderWidth: null,
						backgroundColor : 'transparent',
						plotShadow: false				
					},
					title: {
						text: 'Maximum Players/Active Players'
					},
					subtitle: {
						text: 'Inner Circle: Maximum Players || Outer Circle: Active Players'
					},
					tooltip: {
						formatter: function() {
							return '<b>'+ this.series.name +'</b><br/>'+ 
								this.point.name +': '+ this.y +' %';
						}
					},
				    series: [{
						// Inner Ring
						type: 'pie',
						name: 'Maximum Players',
						size: '45%',
						innerSize: '20%',
						data: [
						<?
							$i = 1;
							$len = count($current);
							foreach (array_keys($current) as $type) {
							$piece = round($current[$type][max] * $percentmax);
							// $piece = $current[$type][max];
							if ($i != $len) {
								echo "['" . $gametypes[$type][longname] . "'," . $piece . "],\n";
							} else {
								echo "['" . $gametypes[$type][longname] . "'," . $piece . "]\n";
							}
								$i++;
							}
						?>
						],
						dataLabels: {
							enabled: false
						}
					}, {
						// Outer Ring
						type: 'pie',
						name: 'Active Players',
						innerSize: '45%',
						data: [
						<?
							$i = 1;
							$len = count($current);
							foreach (array_keys($current) as $type) {
							$piece = round($current[$type][current] * $percentcur);
							// echo "$current[$type][current] $percentcur"; 
							// $piece = $current[$type][current];
							if ($i != $len) {
								echo "['" . $gametypes[$type][longname] . "'," . $piece . "],\n";
							} else {
								echo "['" . $gametypes[$type][longname] . "'," . $piece . "]\n";
							}
								$i++;
							}
						?>
						],
						dataLabels: {
							enabled: true,
							color: '#000000',
							connectorColor: '#000000'
						}
					}]
				});
				
				
			});
				
		</script>		
		
</div>

<div id="chart3">
    <?php

    $sql = "SELECT * FROM servers";
		$result = mysql_query($sql);
             $data = array();
             while ($row = mysql_fetch_array($result)) {
				$type = $row['type'];
                $current[$type]['current'] = $current[$type][current] + (int)$row['currentplayers'];
				$current[$type]['max'] = $current[$type][max] + (int)$row['maxplayers'];
				$totalcur = $totalcur + (int)$row['currentplayers'];
				$totalmax = $totalmax + (int)$row['maxplayers'];
				
                $bots[$type] = $bots[$type] + (int)$row['currentbots'];
                $max[$type] = $max[$type] + (int)$row['maxplayers'];
             }
             $data = json_encode($bots);// <<----------------- add this line
			
			$percentcur = 100 / $totalcur;
			$percentmax = 100 / $totalmax;
			
			?>	
<script type="text/javascript">
		
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'chart3',
						defaultSeriesType: 'bar'
					},
					title: {
						text: 'Overall Game Type Activity'
					},
					xAxis: {
					
						<?
							$i = 1;
							$len = count($current);
							echo "categories:[";
							foreach (array_keys($current) as $type) {
						
							if ($i != $len) {
								echo "'" . $gametypes[$type][longname] . "',\n";
							} else {
								echo "'" . $gametypes[$type][longname] . "']\n";
							}
							
								$i++;
							}
						?>
						//categories: ['1', '2', '3', '4']
					},
					yAxis: {
						<?php
						echo "max: " . $totalcur  . ",";
						?>
						//max: 1500,
						min: 0,
						title: {
							text: 'Players'
						}
					},
					legend: {
						
						reversed: true
					},
					tooltip: {
						formatter: function() {
							return ''+
								 this.series.name +': '+ this.y +'';
						}
					},
					plotOptions: {
						series: {
							stacking: 'normal'
						}
					},
				        series: [{
						name: 'Bots',
						data: [2, 2, 3, 2]
					}, {
						name: 'Active',
						
						<?php
							$j = 1;
							$len = count($current);
							echo "data:[";
							foreach (array_keys($current) as $type) {
						
							if ($j != $len) {
								echo $row['currentplayers'] . ",\n";
							} else {
								echo $row['currentplayers'] . "]\n";
							}
							
								$j++;
							}
						?>
						
						
						//data: [6, 4, 4, 2]
					}]
				});
				
				
			});
				
		</script>


</div>
-->
<?
	}
	mysql_close();
	bottom( $start );
	
?>
