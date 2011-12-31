<?php
	
	include( "config.php" );
	include( "lib/functions.php" );
	
	$start = head();
	
	mysql_connect( $host, $user, $pass ) or die( mysql_error() );
	mysql_select_db( $table ) or die( mysql_error() );
	
	error_reporting( E_ALL ); //} no error will slip past unnoticed
	ini_set( "display_errors", 1) ;

	$settings = getsettings();
	
	function array2json( $plugins, $column ) {
		
		$json = '';
		
		foreach( $plugins as $key => &$value ) {	
			
			if( $json != '')
				$json .= ', ';
				
			$json .= $key . ': \'' . mysql_real_escape_string( $value[ $column ] ) . '\'';
			
		}
		
		echo $json;
		
	}
	
	$latest	 	= array();
	$mods		= array();
	$versions 	= array();
	
	if(! isset( $_GET[ 'game' ] ) )
		$_GET[ 'game' ] = 'all';
	
	if(! isset( $_GET[ 'mod' ] ) )
		$_GET[ 'mod' ] = 'source';
	
	$modvars = array(
		"source" => array( 'mods', 'modid', 'filename', 'title' ),
		"meta" => array( 'metamods', 'metaid', 'file', 'description' ),
		"ext" => array( 'extensions', 'extid', 'file', 'name' )
	);
	$modvars = $modvars[ $_GET[ 'mod' ] ];
	
	$result = mysql_query( "SELECT * from " . $modvars[ 0 ] . " ORDER BY " . $modvars[ 2 ] ) or die( mysql_error() ); // retrieve all mods
	
	while( $row = mysql_fetch_array( $result ) ) { // for each result
		
		if(! isset( $mods[ $row[ $modvars[ 2 ] ] ] ) ) //} add the mod if it's not already in the array
			$mods[ $row[ $modvars[ 2 ] ] ] = array(
				"filename" => $row[ $modvars[ 2 ] ],
				"latest" => array(),
				"servers" => array()
			);
		
		$numbers = explode( '.', $row[ 'version' ] ); //} make the versions ready for sorting and store it in the mod's array
		foreach( $numbers as &$number )
			$number = ( strpos( $number, '-' ) === false ? strlen( $number ) : strpos( $number, '-' ) ) . $number;
		$mods[ $row[ $modvars[ 2 ] ] ][ 'latest' ][] = implode( '.', $numbers );
		
		$versions[ $row[ $modvars[ 1 ] ] ] = array( //} keep track of which ID is which version, with it's data
			"version" => $row[ 'version' ],
			"filename" => $row[ $modvars[ 2 ] ],
			"desc" => $row[ $modvars[ 3 ] ],
			"url" => $row[ 'url' ],
		);
		
	}
	
	foreach( $mods as &$mod ) { // for each mod
	
		rsort( $mod['latest'] ); // sort the versions
		
		$numbers = explode( '.', $mod['latest'][0] ); // keep and restore only the latest version
		foreach( $numbers as &$number )
			$number = substr( $number, 1 );
		$mod['latest'] = implode( '.', $numbers );
		
	}

	$result = mysql_query( "SELECT * from servers ORDER BY type DESC" ) or die( mysql_error() ); // retrieve all servers
	while( $row = mysql_fetch_array( $result ) ) // for each result
		if( $_GET[ 'game' ] == "all" || $row[ 'type' ] == $_GET[ 'game' ] ) // if the server matches the gametype selected
			$servers[ $row[ 'serverid' ] ] = array( // dump the server in the array
				"serverid" => $row[ 'serverid' ],
				"servername" => str_replace( $settings['server_prefix']['config'], '', $row[ 'servername' ] ),
			);

	$result = mysql_query( "SELECT * from srv_mods" ) or die( mysql_error() ); // retrieve all links
	while( $row = mysql_fetch_array( $result ) ) // for each result
		if( isset( $versions[ $row[ $modvars[ 1 ] ] ] ) ) // if the mod exist
			if( isset( $servers[ $row[ 'serverid' ] ] ) ) // if the server exist
				$mods[ $versions[ $row[ $modvars[ 1 ] ] ][ 'filename' ] ][ 'servers' ][ $row[ 'serverid' ] ] = $row[ $modvars[ 1 ] ]; // link the server to the modid with that version in the plugin's array
	
	
	
?>
<script type="text/javascript">

	var lastPlugin = false;
	
	function show( plugin, el, latest ) {
		
		var text, popup = document.getElementById( 'popup' ), url;
		lastPlugin = plugin;
		if( !latest )
			latest = false;
		
		text = '<b>' + { <?php array2json( $versions, 'filename' ); ?> }[ plugin ] + '</b> ';
		text += { <?php array2json( $versions, 'version' ); ?> }[ plugin ] + '<br/>';
		text += '<span style="font-size: 8pt">' + { <?php array2json( $versions, 'desc' ); ?> }[ plugin ] + '</span><br/>';
		url = { <?php array2json( $versions, 'url' ); ?> }[ plugin ];
		text += '<a href="' + url + "' />" + url + '</a>';
		
		
		popup = document.getElementById( 'popup' );
		popup.innerHTML = text;
		popup.style.left = ( el.offsetLeft + 19 ) + "px";
		popup.style.top = ( el.offsetTop + 82 ) + "px";
		popup.style.display = 'block';
		
	}
	
	function hide( plugin ) {
		
		if( lastPlugin == plugin )
			document.getElementById( 'popup' ).style.display = 'none';
		
	}
	
</script>
<span style="position: absolute; right: 20px;">
	<img src="images/updatepluginsnew.gif" alt="update" style="padding-bottom:20px;">
	<a href="walkserver.php" title ="Update Plugins on the Server(s), will bring you to another page"><img src=images/updateplugins.png></a>
</span>
<form>

	<select name="game" onchange="this.form.submit();">
<?
        $gametypes = gametypes();

	echo '<option value="all">Select Type</option>';
        foreach (array_keys($gametypes) as $shortname) {
                if ($_GET['game'] == $shortname)  { echo '<option value="' . $shortname . '" selected="selected">' . $gametypes["$shortname"]['longname'] . '</option>';}
                else { echo '<option value="'. $shortname . '">' . $gametypes["$shortname"]['longname'] . '</option>';}
        }
?>

	</select>

	<select name="mod" onchange="this.form.submit();">
		<option value="source" <?php if( $_GET[ 'mod' ] == "source" ) echo 'selected'; ?>>SourceMod</option>
		<option value="ext" <?php if( $_GET[ 'mod' ] == "ext" ) echo 'selected'; ?>>SourceMod Extensions</option>
		<option value="meta" <?php if( $_GET[ 'mod' ] == "meta" ) echo 'selected'; ?>>Metamod</option>
		</select>
	
</form>
<div id="popup" style="position: absolute; width: 300px;"></div>
<br/>
<?php
	
	echo "<table class=\"listtable\" align=\"left\">\n";
		
		echo "	<tr>\n		<td>&nbsp;</td>\n";
		if (! empty( $servers )) {
		foreach( $servers as &$server ) // the servers
			echo "		<td><img src=\"image.php?text=" . urlencode( $server[ 'servername' ] ) . "\"/></td>\n";
		echo "	</tr>\n";
		} else { echo "..  No servers yet, so no output yet here..";}
		
		foreach( $mods as &$mod ) { // for each mod
			
			if(! empty( $mod[ 'servers' ] ) ) { // if one of the servers uses it
				echo "	<tr>\n		<td>" . $mod[ 'filename' ] . "</td>\n		";
				foreach( $servers as &$server ) // for each server
					echo isset( $mod[ 'servers' ][ $server[ 'serverid' ] ] ) ? "<td onmouseover=\"show(" . $mod[ 'servers' ][ $server[ 'serverid' ] ] . ", this );\" onmouseout=\"hide(" . $mod[ 'servers' ][ $server[ 'serverid' ] ] . ", this );\" bgcolor=\"" . ( $mod[ 'latest' ] != $versions[ $mod[ 'servers' ][ $server[ 'serverid' ] ] ][ 'version' ] ? 'indianred' : 'lightgreen' ) . "\">" . $versions[ $mod[ 'servers' ][ $server[ 'serverid' ] ] ][ 'version' ] . "</td>" : "<td bgcolor=\"darkgray\">&nbsp;</td>";
				echo "\n	</tr>\n";
			}
			
		}
		
	echo "</table>\n";
	
	
	mysql_close();
	bottom($start);
	
?>
