<?php
	
	include( "config.php" );
	include( "lib/functions.php" );
	require_once 'lib/steam-condenser/lib/steam-condenser.php';
	
	error_reporting( E_ALL ); //} no error will slip past unnoticed
	ini_set( "display_errors", 1) ;
	
	mysql_connect( $host, $user, $pass ) or die( mysql_error() );
	mysql_select_db( $table ) or die( mysql_error() );
	
	if( isset( $_GET[ 'command' ] ) && isset( $_GET[ 'servers' ] ) ) {
	
		$output = '';
		$list = explode( ',', $_GET[ 'servers' ] );
		$servers = array();
		for( $i = 0; $i < count( $list ); $i++ )
			if( is_numeric( $list[ $i ] ) )
				array_push( $servers, $list[ $i ] );
			else {
				
				$typeServers = mysql_query( "SELECT `serverid` FROM `servers` WHERE `type` = '" . $list[ $i ] . "' AND multic = 'yes'" );
				while( $server = mysql_fetch_array( $typeServers ) )
					array_push( $servers, $server[ 'serverid' ] );
				
			}
		
		for( $i = 0; $i < count( $servers ); $i++ ) {
		
			$settings = getsettings();
			
			$servercfg = getserver( $servers[ $i ] );
			$servercfg = $servercfg['0'];

			
			$serverIP = $servercfg['ip'];
			$server = new SourceServer( $serverIP, $servercfg['port'] );
			
			$output .= "\n>> " . $servercfg['servername'] . "\n";
			try {
				$server->rconAuth($servercfg['rconpass']);
			} catch(Exception $e) { $output .= "> Unable to connect to Server"; }
			
			try { $output .= $server->rconExec( $_GET[ 'command' ] );} catch(Exception $e) { $output .= "> Server not Responding";}
			$output .= "\n";
			
		}
		
		echo $output;
		
		mysql_close();	
		
		die();
		
	}
	
	$start = head();
	$gametypes = gametypes();
	

?>
<script type="text/javascript">
	
	function toggleType( type ) {
		
		for( var i = 0; i < document.targets.elements.length; i++ )
			if( document.targets.elements[ i ].name == type )
				document.targets.elements[ i ].disabled = !document.targets.elements[ i ].disabled;
		
	}
	
	var rcon = {
		
		print: function( str ) {
			
			var terminal = document.getElementById( "terminal" );
			terminal.innerHTML += str;
			terminal.scrollTop = terminal.scrollHeight;

		},
		
		handle: function( ) {
			
			if ( xmlhttp.readyState == 4 ) {
			
				if ( xmlhttp.status == 200 ) {
					
					rcon.print( xmlhttp.responseText );
					
				} else {
				
					rcon.print( "Request failed. HTTP status code: " + xmlhttp.status );
					
				}
				
			}
			
		},
		
		send: function( command, auto ) {
			
			if(! auto )
				this.print( '\n> ' + command );
			
			if ( window.XMLHttpRequest ) { // Mozilla, Safari,...
				xmlhttp = new XMLHttpRequest();
				if ( xmlhttp.overrideMimeType )
					xmlhttp.overrideMimeType( 'text/xml' );
			} else if (window.ActiveXObject) { // IE
				try {
					xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e) {}
				}
			}
			
			if (! xmlhttp ) {
			
				this.print('Cannot create XMLHTTP instance.');
				return false;
				
			}
			
			var servers = new Array();
			for( var i = 0; i < document.targets.elements.length; i++ )
				if( document.targets.elements[ i ].disabled == false && document.targets.elements[ i ].checked == true )
					servers.push( document.targets.elements[ i ].value );
			
			xmlhttp.onreadystatechange = rcon.handle;
			xmlhttp.open( 'GET', '?servers=' + servers.join() + '&command=' + command, true );
			xmlhttp.send( null );
			
			return false;
			
		}
		
		
	};
	
	window.onload = function() {
		
		document.getElementById( "input" ).focus();
		
	};

</script>
<h2 style="margin-top: 0;">Multi Console</h2>
<pre id="terminal">Stand-by...
</pre>
<form onsubmit="rcon.send( this.command.value ); this.command.value = ''; return false;">
	<table border="0" style="width: 100%;">
		<tr>
			<td>
				<input type="text" id="input" style="background-color: lightgray; border: 1px black solid; width:100%;" name="command" >Type Command Here</input>
			</td>
			<td width="1px">
				<input type="submit" value="Send" style="border: 1px black solid; background-color: lightgray;margin-bottom:20px;cursor:pointer;cursor:hand"/>
			</td>
		</tr>
	</table>
</form>
<style type="text/css">
	
	.server-list {
		
		float: left;
		margin-right: 20px;
		
	}

	.server-list:first-line {
		
		font-weight: bold;
		
	}
	
</style>
<br/>
<h3>Targets</h3>
<form name="targets">
<?php
	
	$settings = getsettings();
	$servers = mysql_query( "SELECT `servers`.*, `games`.`longname` FROM `servers` JOIN `games` ON `servers`.`type` = `games`.`shortname` WHERE servers.multic = 'yes' ORDER BY `type`" );
	$currType = false;
	
	while( $server = mysql_fetch_array( $servers ) ) {
		
		if( $server[ 'longname' ] != $currType ) {
			if( $currType !== false )
				echo '</div>';
			$currType = $server[ 'longname' ];
			echo '<div class="server-list"><input type="checkbox" value="' . $server[ 'type' ] . '" onclick="toggleType(\'' . $server[ 'type' ] . '\');"/>' . $currType . '<br/><hr>';
		}
		
		echo '<input type="checkbox" name="' . $server[ 'type' ] . '" value="' . $server[ 'serverid' ] . '"/>' . str_replace( $settings['server_prefix']['config'], '', $server[ 'servername' ] ) . '<br/>';
		
	}
	echo '</div>';
	
	
?>
</form>
<div style="clear:left;">&nbsp;</div>
<?php	
	
	bottom($start);
	
?>
