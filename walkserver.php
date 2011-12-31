<?php
	
	//Die('The walker is currently being completely rewritten, hope you don\'t mind.<br/><tt> - Pablosky</tt>');
	
	include("config.php");
	include("lib/functions.php");
	require_once 'lib/steam-condenser.php';

	mysql_connect($host, $user, $pass) or die(mysql_error());
	mysql_select_db($table) or die(mysql_error());

	error_reporting(E_ALL);
	ini_set("display_errors", 1); 

	SteamSocket::setTimeout(50);
	
	function getnumbers($data)
	{
		preg_match("/[0-9]+/i", $data, $value);
		return $value[0];
	}

	function resetplugins( $serverid, $type )
	{
		mysql_query("UPDATE srv_mods SET status = 'inactive' where serverid = '$serverid' and $type <> 0");
	}
	
	function trimv(&$val){return $val = trim($val);}
	
	function go($data,$type,$number,$serverid) {
		global $table, $fp, $global, $errors;
               
		
		$pluginInfo = array( 'Timestamp' => '', 'URL' => '', 'Author' => '' );
		
		// get the mod information
		$is_error = strpos($data,'Load error:') !== FALSE;
		// split up the lines
		$mparray = explode("\n",$data);
		// trim white spaces in beginning
		array_walk($mparray, create_function( '&$v', '$v = trim( $v );' ) );
		// remove last wrong empty line
		//array_pop($mparray);
		// check for errors , wrong loading mods, take them out
		foreach($mparray as $string) {
                    $string = mysql_real_escape_string($string);

                    // get the first name, and the data after that
			$pos = strpos($string,':');        
			if($pos) {
					// the data itself
				$tempname = substr($string,0, $pos);
				$pluginInfo[ $tempname ] = substr($string,$pos+2,strlen($string)-$pos-2);    
                        }
                }

                if (!array_key_exists('Loaded', $pluginInfo))
                        $pluginInfo['Loaded'] = 'Yes';
                
		if(!$is_error && strpos($pluginInfo['Loaded'], 'Yes') !== FALSE){
		// no error, no skip
			if ( $type == "sm" && isset( $pluginInfo[ 'Version' ] ) && $pluginInfo[ 'Version' ] && isset( $pluginInfo[ 'Filename' ] ) && $pluginInfo[ 'Filename' ] ) {
				$Version = $pluginInfo[ 'Version' ]; $Author = $pluginInfo[ 'Author' ]; $Title = $pluginInfo[ 'Title' ]; $URL = $pluginInfo[ 'URL' ]; $Status = $pluginInfo[ 'Status' ]; $Reloads = $pluginInfo[ 'Reloads' ]; $Timestamp = $pluginInfo[ 'Timestamp' ];
				$Filename = preg_replace( '/^.+[\\\\\\/]/', '', $pluginInfo[ 'Filename' ] );
				$result = mysql_query("SELECT modid FROM mods WHERE filename = '$Filename' and version = '$Version'");
				$row = mysql_fetch_array($result);
				$latestid = $row['modid']; 
				if (mysql_num_rows($result)) {
					
					// it exists in the mod db.
					$result2 = mysql_query("SELECT id from srv_mods where modid = (SELECT modid from mods WHERE filename = '$Filename' AND version = '$Version') and serverid = '$serverid'");
					if (mysql_num_rows($result2)) {
						// it already has a entry in srv_mods, update plugin nr.
						mysql_query("UPDATE srv_mods SET pluginnr = '$number', status = 'active' WHERE serverid = '$serverid' AND modid = (SELECT modid from mods WHERE filename = '$Filename' AND version = '$Version')");
						// if it does not, it means it has the same version and need to be added.
					} else {
						mysql_query("INSERT INTO srv_mods (modid,metaid,serverid,status,pluginnr) VALUES($latestid,0,$serverid,'active',$number)");
						fwrite( $fp, "Unlinked plugin $Filename ($Version) found, link added to database.\n" );
					}
				} else {
					$result = mysql_query("SELECT threadid from plugindb where filename = '$Filename'");
					if ( mysql_num_rows( $result ) ) {
						$row = mysql_fetch_array($result);
						mysql_query("INSERT INTO mods (filename, title, author, version, url, status, reloads, timestamp, threadid) VALUES('$Filename', '$Title','$Author','$Version','$URL','$Status','$Reloads','$Timestamp','" . $row['threadid'] . "')");
					} else
						mysql_query("INSERT INTO mods (filename, title, author, version, url, status, reloads, timestamp) VALUES('$Filename', '$Title','$Author','$Version','$URL','$Status','$Reloads','$Timestamp')");
					$lastid = mysql_insert_id();
					// add the refence table
					mysql_query("INSERT INTO srv_mods (modid,extid,metaid,serverid,status,pluginnr) VALUES($lastid,0,0,$serverid,'active',$number)");
					fwrite( $fp, "New plugin $Filename ($Version) found, plugin and link added to database.\n" );
				}
			} elseif ( $type == "mm" && isset( $pluginInfo[ 'Version' ] ) && $pluginInfo[ 'Version' ] && isset( $pluginInfo[ 'File' ] ) && $pluginInfo[ 'File' ] ) {
				$Name = $pluginInfo[ 'Name' ]; $Version = $pluginInfo[ 'Version' ]; $Description = $pluginInfo[ 'Description' ]; $URL = $pluginInfo[ 'URL' ]; $Details = $pluginInfo[ 'Details' ];
				// remove the dirty path in front of the files
				$basefile = preg_replace( '/^.+[\\\\\\/]/', '', $pluginInfo[ 'File' ] );
				$result = mysql_query("SELECT metaid FROM metamods WHERE file = '$basefile' and version = '$Version'");
				$row = mysql_fetch_array($result);
				$latestid = $row['metaid'];
				if (mysql_num_rows($result)) {
					// it exists in the meta db.
					$result2 = mysql_query("SELECT id from srv_mods where metaid = (SELECT metaid from metamods WHERE file = '$basefile' AND version = '$Version') and serverid = '$serverid'");
					if (mysql_num_rows($result2)) {
						// it already has a entry in srv_mods, update plugin nr.
						mysql_query("UPDATE srv_mods SET pluginnr = '$number', status = 'active' WHERE serverid = '$serverid' AND metaid = (SELECT metaid from metamods WHERE file = '$basefile' AND version = '$Version')");
						// if it does not, add it.
					} else {
						mysql_query("INSERT INTO srv_mods (id,modid,metaid,serverid,status,pluginnr) VALUES(NULL,0,$latestid,$serverid,'active',$number)");
						fwrite( $fp, "Unlinked plugin $basefile ($Version) found, link added to database.\n" );
					}
					
				}
				else {
					
					$query = mysql_query("INSERT INTO metamods (metaid, name, version, description, url, details, file) VALUES('NULL', '$Name', '$Version','$Description','$URL','$Details','$basefile')");
										// add the refence table
					$lastid = mysql_insert_id();
					mysql_query("INSERT INTO srv_mods (modid,extid,metaid,serverid,status,pluginnr) VALUES(0,0,$lastid,$serverid,'active',$number)");
					fwrite( $fp, "New plugin $basefile ($Version) found, plugin and link added to database.\n" );
				}

			} elseif ( $type == "ext" && isset( $pluginInfo[ 'Loaded' ] ) && $pluginInfo[ 'Loaded' ] && isset( $pluginInfo[ 'File' ] ) && $pluginInfo[ 'File' ] ) {
                                $Info = $pluginInfo[ 'Binary info' ]; $Name = $pluginInfo[ 'Name' ];
				// remove the dirty path in front of the files
				$basefile = preg_replace( '/^.+[\\\\\\/]/', '', $pluginInfo[ 'File' ] );
				preg_match( '/.*\(version (.*?)\)/', $pluginInfo[ 'Loaded' ], $version ); $Version = $version[ 1 ];
				preg_match( '/(.*)\((.*?)\)/', $pluginInfo[ 'Author' ], $matches ); $Author = $matches[ 1 ]; $Link = $matches[ 2 ];
				$result = mysql_query("SELECT extid FROM extensions WHERE file = '$basefile' and version = '$Version'");
				$row = mysql_fetch_array($result);
				$latestid = $row['extid'];
				if (mysql_num_rows($result)) {
					// it exists in the meta db.
					$result2 = mysql_query("SELECT id from srv_mods where extid = (SELECT extid from extensions WHERE file = '$basefile' AND version = '$Version') and serverid = '$serverid'");
					if (mysql_num_rows($result2)) {
						// it already has a entry in srv_mods, update plugin nr.
						mysql_query("UPDATE srv_mods SET pluginnr = '$number', status = 'active' WHERE serverid = '$serverid' AND extid = (SELECT extid from extensions WHERE file = '$basefile' AND version = '$Version')");
						// if it does not, add it.
					} else {
						mysql_query("INSERT INTO srv_mods (id,modid,metaid,extid,serverid,status,pluginnr) VALUES(NULL,0,0,$latestid,$serverid,'active',$number)");
						fwrite( $fp, "Unlinked SourceMod extension $basefile ($Version) found, link added to database.\n" );
					}
				
				}
				else {
					$query = mysql_query("INSERT INTO extensions (extid, name, author, file, version, url, details) VALUES('NULL', '$Name', '$Author','$basefile','$Version','$Link','$Info')");
					// add the refence table
					$lastid = mysql_insert_id();
					mysql_query("INSERT INTO srv_mods (modid,metaid,extid,serverid,status,pluginnr) VALUES(0,0,$lastid,$serverid,'active',$number)");
					fwrite( $fp, "New SourceMod extension $basefile ($Version) found, extension and link added to database.\n" );
				}
			}
		} else {
			$errors[$type][] = $pluginInfo['File'];
                }
//                return true;
	}
	
	function walker_errorhandler( $errno, $errstr, $errfile, $errline ) {
		
		global $fp;
		fwrite( $fp, "Error: ". $errline ." " . $errstr . "\n" ); // . " on line $errline in file $errfile";
		
	}
	
	if( isset( $_GET[ 'clean' ] ) ) {
		session_start();
		unlink( $_SESSION[ 'walkfile' ] );
		unset( $_SESSION[ 'servers' ] );
	} else if( isset( $_GET[ 'walk' ] ) ) {
		
		set_error_handler("walker_errorhandler");
		
		session_start();
		
		$output = array();
                $errors = array();
		
		if( isset( $_GET[ 'init' ] ) ) {
			
			$_SESSION[ 'servers' ] = array();
			$_SESSION[ 'walkfile' ] = "cache/walk_" . rand() . ".txt";
			$output[ 'file' ] = $_SESSION[ 'walkfile' ];
			$result = mysql_query( "SELECT serverid, servername, ip, port, rconpass from servers" . ( isset( $_GET[ 'id' ] ) ? " WHERE serverid='" . mysql_real_escape_string( $_GET[ 'id' ] ) . "'" : "" ) ) or die( "Error retrieving servers: " . mysql_error() ); // retrieve all servers
			while( $row = mysql_fetch_array( $result ) )
				$_SESSION[ 'servers' ][] = array( $row[ 'serverid' ], $row[ 'servername' ], $row[ 'ip' ], $row[ 'port' ], $row[ 'rconpass' ] );
			
			$output[ 'servers' ] = mysql_num_rows( $result );
			
			$output[ 'next' ] = $_SESSION[ 'servers' ][ 0 ][ 1 ];
			
		} else if( isset( $_GET[ 'next' ] ) ) {
			
			$fp = fopen( $_SESSION[ 'walkfile' ], 'w');
			
			$server = array_shift( $_SESSION[ 'servers' ] );
			$serverid = $server[ 0 ];
			$name 	= $server[ 1 ];
			$ip 	= $server[ 2 ];
			$port 	= $server[ 3 ];
			$rcon 	= $server[ 4 ];
			
			$update = 0;

			$serverIP = $ip;
			$server = new SourceServer( $serverIP, $port );
			
			$error = false;
			try {
				$server->rconAuth($rcon);
			} catch(Exception $e) {
				fwrite( $fp, "Unable to Authenticate to server, perhaps down?" ); 
				$error = $e;
			}

			if( $error )
				fwrite( $fp, $error );
			else {
				
				fwrite( $fp, "Connection to server established.\n" );
				
				// get all the metamod data
				//$res = $srcds_rcon->command("meta list");
				try { $res = $server->rconExec('meta list'); } catch(Exception $e) {}
				if( $res ) {
					resetplugins( $serverid, 'metaid' );
					if( strpos( $res, 'Unknown command' ) === false ) {
						$m = getnumbers( $res );
						fwrite( $fp, $m . " Metamod plugin(s) found, scanning...\n" );
						$m++;
						for( $i = 1; $i < $m; $i++ ) {
							//$mp = $srcds_rcon->command("meta info " . $i );
							try { $mp = $server->rconExec('meta info ' . $i ); } catch(Exception $e) {}
							if( $mp )
								go( $mp, 'mm', $i, $serverid );
						}
						
						// get all the sourcemod data - depends on metamod
						//$res = $srcds_rcon->command("sm plugins list");
						try { $res = $server->rconExec('sm plugins list'); } catch(Exception $e) {}
						
						if( $res ) {
							resetplugins( $serverid, 'modid' );
							if( strpos( $res, 'Unknown command' ) === false ) {
								$m = getnumbers( $res );
								fwrite( $fp, $m . " SourceMod plugin(s) found, scanning...\n" );
								$m++;
								for( $i = 1; $i < $m; $i++ ) {
									//$mp = $srcds_rcon->command("sm plugins info " . $i );
									try { $mp = $server->rconExec('sm plugins info ' . $i ); } catch(Exception $e) {}
									if( $mp )
										go( $mp, 'sm', $i, $serverid );
								}
								
								// get all the extensions data - depends on sourcemod
								//$res = $srcds_rcon->command("sm exts list");
								try { $res = $server->rconExec('sm exts list'); } catch(Exception $e) {}
								if( $res ) {
									resetplugins( $serverid, 'extid' );
									$m = getnumbers( $res );
									fwrite( $fp, $m . " SourceMod extension(s) found, scanning...\n" );
									$m++;
									for( $i = 1; $i < $m; $i++ ) {
										//$mp = $srcds_rcon->command("sm exts info " . $i );
										try { $mp = $server->rconExec('sm exts info ' . $i ); } catch(Exception $e) {}
										if( $mp )
											go( $mp, 'ext', $i, $serverid );
									}
									
									$update = 2;
									
								} else {
									fwrite( $fp, "Failed to retrieve list of SourceMod extensions.\n" );
									$update = 1;
								}
							} else {
								fwrite( $fp, "SourceMod is not installed on this server.\n" );
								$update = 2;
							}
						} else {
							fwrite( $fp, "Failed to retrieve list of SourceMod plugins.\n" );
							$update = 1;
						}
					} else {
						fwrite( $fp, "Metamod is not installed on this server.\n" );
						$update = 2;
					}
				} else
					fwrite( $fp, "Failed to retrieve list of Metamod plugins.\n" );
				
				//$srcds_rcon->disconnect( );
				
			}
			
			$result = mysql_query( "SELECT * FROM srv_mods WHERE status = 'inactive' and serverid = '" . $serverid . "'" );
			while( $row = mysql_fetch_array( $result ) ) {
				if( $row[ 'metaid' ] != 0 ) {
					$re = mysql_query( "SELECT * FROM metamods WHERE metaid = " . $row[ 'metaid' ] );
					$ro = mysql_fetch_array( $re );
					$name = $ro[ 'file' ];
					$version = $ro[ 'version' ];
					$what = 'Metamod plugin';
				} else if( $row[ 'modid' ] != 0 ) {
					$re = mysql_query( "SELECT * FROM mods WHERE modid = " . $row[ 'modid' ] );
					$ro = mysql_fetch_array( $re );
					$name = $ro[ 'filename' ];
					$version = $ro[ 'version' ];
					$what = 'SourceMod plugin';
				} else if( $row[ 'extid' ] != 0 ) {
					$re = mysql_query( "SELECT * FROM extensions WHERE extid = " . $row[ 'extid' ] );
					$ro = mysql_fetch_array( $re );
					$name = $ro[ 'file' ];
					$version = $ro[ 'version' ];
					$what = 'SourceMod extension';
				}
				
				fwrite( $fp, "Linked " . $what . " $name ($version) not found, link removed from database.\n" );
			}
			
			mysql_query( "DELETE FROM srv_mods WHERE status = 'inactive'" );
			
			fwrite( $fp, "Server update " . ( $update == 0 ? "failed" : ( $update == 1 ? "partially " : "" ) . "completed") . ".\n\n" );
			
			if( count( $_SESSION[ 'servers' ] ) == 0 ) {
				
				//$result = mysql_query( "SELECT file, version FROM `metamods` WHERE ( select count( * ) from srv_mods where srv_mods.metaid = metamods.metaid limit 1 ) = 0" );
				//while( $row = mysql_fetch_array( $result ) )
				//	echo "Metamod plugin " . $row[ 'file' ] . " (" . $row[ 'version' ] . ") not found anywhere, plugin removed from database.\n";
				
				mysql_query( "DELETE FROM `metamods` WHERE ( select count( * ) from srv_mods where srv_mods.metaid = metamods.metaid limit 1 ) = 0" );

				//$result = mysql_query( "SELECT filename, version FROM `mods` WHERE ( select count( * ) from srv_mods where srv_mods.modid = mods.modid limit 1 ) = 0" );
				//while( $row = mysql_fetch_array( $result ) )
				//	echo "SourceMod plugin " . $row[ 'filename' ] . " (" . $row[ 'version' ] . ") not found anywhere, plugin removed from database.\n";
								
				mysql_query( "DELETE FROM `mods` WHERE ( select count( * ) from srv_mods where srv_mods.modid = mods.modid limit 1 ) = 0" );
				
			} else
				$output[ 'next' ] = $_SESSION[ 'servers' ][0][1];
			
		}
		
                if (count($errors) > 0) {
                    fwrite($fp, "Following errors encountered:\n");
                    foreach ($errors as $type => $errors2) {
                        foreach ($errors2 as $filename) {
                            fwrite($fp, "\t$type: $filename\n");
                        }
                    }
                }
//                restore_error_handler();
//                fclose($fp);
		mysql_close();
		
		die( json_encode( $output ) );
	}
	
	$start = head();
	
?>
<script type="text/javascript">
	
	function toggle( id ) {
			
		if( lastid !== false ) {
			
			document.getElementById( "log" + lastid ).style.display = "none";
			document.getElementById( "sname" + lastid ).style.fontWeight = "normal";
			
		}
		
		document.getElementById( "log" + id ).style.display = "block";
		document.getElementById( "sname" + id ).style.fontWeight = "bold";
		
		lastid = id;
		
	}
	
	var xmlhttp1, xmlhttp2, lastid = false;
        function getXMLHTTP() {
            var xmlhttp;
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

                    alert('Cannot create XMLHTTP instance.');
                    return false;

            }
            return xmlhttp;
        }
	var walker = {
		
		init: function() {
			
			this.done = true;
			this.server = false;
			this.servers = false;
			this.progress = 0;
			this.currid = 0;
			this.t = false;
                        this.fileExists = false;
			
			this.window = document.getElementById( "progress" );
			this.window.innerHTML = '';
			
			$( "#progress" ).dialog({
				title: 'Update in progress...',
				height: 480,
				width: 600,
				modal: true,
				autoOpen: false,
				beforeClose: function( event, ui ) {
					if( walker.progress == walker.servers ) {
						setTimeout( 'walker.init();', 0 );
						walker.button( false );
						return true;
					}
					
					if( confirm( "Are you sure you want to interrupt the update?" ) ) {
						setTimeout( 'walker.init();', 0 );
						clearTimeout( walker.t );
						walker.button( false );
						return true;
					} else
						return false;
				}
			});
			
		},
		
		button: function( bool ) {
			
			document.getElementById( "completebutton" ).disabled = bool;
			document.getElementById( "singlebutton" ).disabled = bool;
			document.getElementById( "serverid" ).disabled = bool;
			
		},
		
		start: function( server ) {
			
			this.button ( true );
			
			if( server )
				this.request( "init&id=" + server );
			else {
				this.request( "init" );
				var bar = document.createElement( "div" );
				bar.id = "progressbar";
				this.window.appendChild( bar );
				$( "#progressbar" ).progressbar( { value: 0 } );
			}
			
			$( '#progress' ).dialog( 'open' );
			
			this.snames = document.createElement( "div" );
			this.snames.id = "snames";
			this.window.appendChild( this.snames );
			
		},
		
		update: function( ) {
			
			if( !( this.progress == this.servers && this.done == true ) && this.fileExists )
//                            setTimeout( 'walker.status();', 0 );
				document.getElementById( "log" + this.currid ).src = this.file;
                        else if (!this.fileExists)
                            this.status();
			
			if( this.done && this.progress < this.servers ) {
				this.request( "next" );
				this.currid = walker.progress;
				this.done = false;
			}
			if( this.progress < this.servers )
				this.t = setTimeout( 'walker.update();', 1000 );
			else {
				var a = document.createElement( "iframe" );
				a.src = "?clean";
				a.style.visibility = "hidden";
				document.body.appendChild( a );
			}

		},
		
		request: function( parameter ) {
                        if(! parameter )
                                parameter = '';
			xmlhttp1 = getXMLHTTP();
			
			xmlhttp1.onreadystatechange = walker.handle;
			xmlhttp1.open( 'GET', '?walk&' + parameter, true );
			xmlhttp1.send( null );
			
			return false;
			
		},
                
                status: function( ) {
			xmlhttp2 = getXMLHTTP();
			
			xmlhttp2.onreadystatechange = walker.statusHandle;
			xmlhttp2.open( 'GET', this.file, true );
			xmlhttp2.send( null );
			
			return false;
			
		},
                statusHandle: function( ) {
			if ( xmlhttp2.readyState == 4 ) {
			
				if ( xmlhttp2.status == 200 ) {
					walker.fileExists = true;
				} else {	
				}
				
			}

			return false;
		},
		
		handle: function( ) {
			
			if ( xmlhttp1.readyState == 4 ) {
			
				if ( xmlhttp1.status == 200 ) {
					
					var response = eval( '(' + xmlhttp1.responseText + ')' );
					
					if( walker.servers == false ) {
						walker.servers = response.servers;
						walker.file = response.file;
					} else {
						walker.progress += 1;
						$( "#progressbar" ).progressbar( { value: ( walker.progress / walker.servers ) * 100 } );
					}
					
					if( walker.progress < walker.servers ) {
						
						walker.server = response.next;
						$( '#progress' ).dialog( { title: "Updating server: " + walker.server } );
						
						var sname = document.createElement( "a" );
						sname.id = "sname" + walker.progress;
						sname.innerHTML = walker.server;
						sname.href = "javascript:toggle( " + walker.progress + " )";
						walker.snames.appendChild( sname );
						walker.snames.appendChild( document.createElement( "br" ) );
						walker.snames.scrollTop = walker.snames.scrollHeight;

						var log = document.createElement( "iframe" );
						log.id = "log" + walker.progress;
						log.className = "logframe";
						walker.window.appendChild( log );
						toggle( walker.progress );
						if( walker.done ) {
							walker.request( "next" );
							walker.done = false;
							walker.update();
						} else
							walker.done = true;
						
					} else {
						
						$( '#progress' ).dialog( { title: "Update completed." } );
						
					}
				} else {
				
					alert( "Request failed. HTTP status code: " + xmlhttp1.status );
					
				}
				
			}

			return false;
		}
		
	}
	
	window.onload = function () {
		walker.init();
	}
	
</script>
<style>
	.ui-progressbar-value { background-image: url(images/pbar-ani.gif); }
	</style>


<div class="progress" id="progress"></div>

<input type="button" id="completebutton" onclick="walker.start();" value="Run Complete Walk"/> or
<select id="serverid">
<?php

	$result = mysql_query( "SELECT * from servers ORDER BY servername" ) or die( mysql_error() ); // retrieve all servers
	while( $row = mysql_fetch_array( $result ) ) // for each result
		echo "	<option value=\"" . $row['serverid'] . "\">" . $row['servername'] . "</option>\n";

?>
</select>
<input type="button" id="singlebutton" onclick="walker.start( document.getElementById('serverid').value );" value="Walk Server" />
<?php
	
	mysql_close();
	
	bottom($start);

?>
