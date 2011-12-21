<?php

require_once('stream_udp.php');

class Source_Query
{
  const A2S_INFO         = "\xFF\xFF\xFF\xFF\x54\x53\x6F\x75\x72\x63\x65\x20\x45\x6E\x67\x69\x6E\x65\x20\x51\x75\x65\x72\x79\x00";
  const A2S_GETCHALLENGE = "\xFF\xFF\xFF\xFF\x57";
  const A2S_PLAYER       = "\xFF\xFF\xFF\xFF\x55";
  const A2S_RULES        = "\xFF\xFF\xFF\xFF\x56";

  private $_socket = null;

  private function _GetDedicated($response)
  {
    $type = $response->GetChar();

    if ($type == 'l') {
      return 'Listen';
    } elseif ($type == 'd') {
      return 'Dedicated';
    } else {
      return 'SourceTV';
    }
  }

  private function _GetOS($response)
  {
    if ($response->GetChar() == 'l') {
      return 'Linux';
    } else {
      return 'Windows';
    }
  }

  public function __construct($ip, $port)
  {
    $this->_socket = new Stream_UDP($ip, $port);
  }

  public function GetInfo()
  {
    $info     = array();
    $response = $this->_socket->Send(Source_Query::A2S_INFO, 5);
	
	if( $response === false )
		return false;
	
    $info['net_ver']      = $response->GetByte(true);
    $info['name']         = $response->GetString();
    $info['map']          = $response->GetString();
    $info['dir']          = $response->GetString();
    $info['desc']         = $response->GetString();
    $info['app_id']       = $response->GetShort();
    $info['num_players']  = $response->GetByte(true);
    $info['max_players']  = $response->GetByte(true);
    $info['num_bots']     = $response->GetByte(true);
    $info['dedicated']    = $this->_GetDedicated($response);
    $info['os']           = $this->_GetOS($response);
    $info['private']      = $response->GetByte(true);
    $info['secure']       = $response->GetByte(true);
    $info['game_ver']     = $response->GetString();

    return $info;
  }

  public function GetPlayers()
  {
    $players     = array();
    $challenge   = substr($this->_socket->Send(Source_Query::A2S_GETCHALLENGE, 0, true), 5);
    $response    = $this->_socket->Send(Source_Query::A2S_PLAYER.$challenge, 5);

    $num_players = $response->GetByte(true);

    for ($player_index = 0; $player_index < $num_players; $player_index++) {
      $response->GetByte();

      $player          = array();
      $player['name']  = $response->GetString();
      $player['kills'] = $response->GetLong();
      $player['time']  = date("H:i:s", mktime(0, 0, $response->GetFloat()));

      $players[$player_index] = $player;
    }

    return $players;
  }
}

?>
