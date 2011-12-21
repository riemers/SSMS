<?php

require_once('stream_udp_response.php');

class Stream_UDP
{
  const MAX_PACKET_SIZE = 1400;
  private $_socket = null;
  private function _Connect()
  {
    if (!$this->_socket = fsockopen("udp://".$this->_ip, $this->_port, $error_number, $error_string, 1)) {
      die($error_string);
    }
  }

  private function _Disconnect()
  {
    fclose($this->_socket);
  }

  public function __construct($ip, $port)
  {
    $this->_ip   = $ip;
    $this->_port = $port;
  }

  public function Send($buffer, $start_index = 0, $raw = false)
  {
    $this->_Connect();
    fwrite($this->_socket, $buffer);
    $response = fread($this->_socket, Stream_UDP::MAX_PACKET_SIZE);
    $this->_Disconnect();
	
	if( strpos( $response, "Banned by server" ) !== false || empty($response) )
		return false;
	
	if ( $raw )
		return $response;
	else
		return new Stream_UDP_Response( $response, $start_index );
  }
}

?>
