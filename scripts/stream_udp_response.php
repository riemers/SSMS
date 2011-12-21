<?php

class Stream_UDP_Response
{
  public function __construct($response, $start_index)
  {
    $this->_response      = $response;
    $this->_current_index = $start_index;
  }

  public function GetByte($ord = false)
  {
    $byte = $this->_response[$this->_current_index];
    if ($ord) {
       $byte = ord($byte);
    }
    $this->_current_index++;
    return $byte;
  }

  public function GetShort()
  {
    $short = null;
    for ($i = 0; $i < 2; $i++) {
      $short .= $this->GetByte();
    }
    $short = unpack('S', $short);
    return $short[1];
  }

  public function GetLong()
  {
    $long = null;
    for ($i = 0; $i < 4; $i++) {
      $long .= $this->GetByte();
    }
    $long = unpack('L', $long);
    return $long[1];
  }

  public function GetFloat()
  {
    $float = null;
    for ($i = 0; $i < 4; $i++) {
      $float .= $this->GetByte();
    }
    $float = unpack('f', $float);
    return $float[1];
  }

  public function GetChar()
  {
    return chr($this->GetByte(true));
  }

  public function GetString()
  {
    $start_index = $this->_current_index;
    for ($this->_current_index; ($this->_current_index < strlen($this->_response)) && ($this->_response[$this->_current_index] != chr(0)); $this->_current_index++);
    $string = substr($this->_response, $start_index, $this->_current_index - $start_index);
    $this->_current_index++;
    return $string;
  }
}

?>
