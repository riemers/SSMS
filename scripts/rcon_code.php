<?php

    define('PACKET_SIZE', '1400');
    define('SERVERQUERY_INFO', "\xFF\xFF\xFF\xFFTSource Engine Query");
    define ('REPLY_INFO', "\x49");
    define('SERVERQUERY_GETCHALLENGE', "\xFF\xFF\xFF\xFF\x57");
    define ('REPLY_GETCHALLENGE', "\x41");
    define('SERVERDATA_AUTH', 3) ;
    define ('SERVERDATA_EXECCOMMAND', 2) ;
    
    class srcds_rcon
    {
		
		private $socket;
		
        private function getByte(&$string)
        {
            $data = substr($string, 0, 1);
            $string = substr($string, 1);
            $data = unpack('Cvalue', $data);
            return $data['value'];
        }
    
        private function getShortUnsigned(&$string)
        {
            $data = substr($string, 0, 2);
            $string = substr($string, 2);
            $data = unpack('nvalue', $data);
            return $data['value'];
        }
    
        private function getShortSigned(&$string)
        {
            $data = substr($string, 0, 2);
            $string = substr($string, 2);
            $data = unpack('svalue', $data);
            return $data['value'];
        }
    
        private function getLong(&$string)
        {	
            $data = substr($string, 0, 4);
            $string = substr($string, 4);
            $data = unpack('Vvalue', $data);
            return $data['value'];
        }
    
        private function getFloat(&$string)
        {
            $data = substr($string, 0, 4);
            $string = substr($string, 4);
            $array = unpack("fvalue", $data);
            return $array['value'];
        }
    
        private function getString(&$string)
        {
            $data = "";
            $byte = substr($string, 0, 1);
            $string = substr($string, 1);
            while (ord($byte) != "0")
            {
                    $data .= $byte;
                    $byte = substr($string, 0, 1);
                    $string = substr($string, 1);
            }
            return $data;
        }
    
        public function connect( $serverid ) {
			
			$socket = &$this->socket;
			
			$result = mysql_query("SELECT * from servers where serverid = '$serverid'") or die(mysql_error());
			$row = mysql_fetch_array( $result );
			$ip = $row['ip'];
			$port = $row['port'];
			$password = $row['rconpass'];
		
			if( !$ip )
				return "Error: Empty server IP found in database.\n";
				
			if( !$port )
				return "Error: Empty server port found in database.\n";
			
			if( !$password )
				return "Error: Empty rcon password found in database.\n";
		
            $requestId = 1;
            $s2 = '';
            $socket = @fsockopen ('tcp://'.$ip, $port, $errno, $errstr, 1);
			
            if (! $socket )
                return "Error: Unable to connect to server; " . $errstr . "\n";
			
            $data = pack("VV", $requestId, SERVERDATA_AUTH).$password.chr(0).$s2.chr(0);
            $data = pack("V",strlen($data)).$data;        
            fwrite ($socket, $data, strlen($data));	
            
            $junk = fread ($socket, PACKET_SIZE);
            $string = fread ($socket, PACKET_SIZE);
			
			if(! $string )
				return "Error: Server did not respond to authentication.\n";
			
            $size = $this->getLong($string);
            $id = $this->getLong($string) ;
            
            if ($id == -1)
				return "Error: Authentication failed.\n";
            
			return false;
        }
		
		public function disconnect( ) {
			
			$socket = &$this->socket;
			fclose( $socket );
			$socket = false;
		
		}
		
        public function command( $command ) {
			
			$socket = &$this->socket;
			
			if(! $socket ) {
				echo "Error: No connection to server active.\n";
				return false;
			}
			
			$requestId = 2;
			$s2 = '';
			
            $data = pack ("VV", $requestId, SERVERDATA_EXECCOMMAND).$command.chr(0).$s2.chr(0) ;
            $data = pack ("V", strlen ($data)).$data ;
            fwrite ($socket, $data, strlen($data)) ;
            $requestId++ ;
            $i = 0 ;
            $text = '' ;
            
            while ($string = fread($socket, 4))
            {
				$info[$i]['size'] = $this->getLong($string) ;
				$string = fread($socket, $info[$i]['size']) ;
				$info[$i]['id'] = $this->getLong ($string) ;
				$info[$i]['type'] = $this->getLong ($string) ;
				$info[$i]['s1'] = $this->getString ($string) ;
				$info[$i]['s2'] = $this->getString ($string) ;
				$text .= $info[$i]['s1'];
				$i++ ;

				if( empty( $text ) ) {
					echo "Error: No data received from server.\n";
					return false;
				}
				
				return $text;
				
            } 
			
        }  
		
    }

?>
