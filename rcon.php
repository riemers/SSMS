<?php
	
	include( "config.php" );
	include( "lib/functions.php" );
	require_once 'steam-condenser/lib/steam-condenser.php';
	
	error_reporting( E_ALL ); //} no error will slip past unnoticed
	ini_set( "display_errors", 1) ;
	
	mysql_connect( $host, $user, $pass ) or die( mysql_error() );
	mysql_select_db( $table ) or die( mysql_error() );
	
	if( !isset( $_GET[ 'serverid' ] ) ) die( "Server not set." );
	
	if( isset( $_GET[ 'command' ] ) ) {

        $settings = getsettings();
        $servercfg = getserver($_GET['serverid']);
        $servercfg = $servercfg['0'];

		
		$serverIP = $servercfg['ip'];
		$server = new SourceServer($serverIP, $servercfg['port']);

	 		
		try {
			$server->rconAuth($servercfg['rconpass']);
		} catch(Exception $e) { $output = "> Unable to connect to Server"; }
		if (empty($output)) {
			try { $output = $server->rconExec($_GET[ 'command' ]); } catch(Exception $e) { $output = "> Server not Responding";}	
	        }	
		if( isset( $_GET[ 'init' ] ) ) {
			$output = explode( "\n\n", $output );
			$output = $output[ 0 ] . "\n";
		}
			
		echo $output;
		
		mysql_close();	
		
		die();
		
	}
	
	$start = head();
	
	echo '<h2 style="margin-top: 0;">RCON: ' . mysql_result( mysql_query( "SELECT servername FROM servers where serverid = " . $_GET[ 'serverid' ] ), 0, "servername") . '</h2>';
	
?>
<script type="text/javascript">

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
				this.print( '\n> ' + command + '\n' );
			
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
			
			xmlhttp.onreadystatechange = rcon.handle;
			xmlhttp.open( 'GET', '?serverid=<?php echo $_GET[ 'serverid' ]; ?>&command=' + command, true );
			xmlhttp.send( null );
			
			return false;
			
		}
		
		
	};
	
	window.onload = function() {
		rcon.send( "status&init", true );
		document.getElementById( "input" ).focus();
	};

</script>
<pre id="terminal">Requesting server status...
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
<?php
	
	
	bottom($start);
	
?>
