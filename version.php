<?php
	
	require_once 'lib/steam-condenser.php';
	
        $master = new MasterServer(MasterServer::SOURCE_MASTER_SERVER);
        $challenge = $master->getChallenge();
	
	        $data = array(
	            'challenge' => $challenge,
	            'gamedir' => "tf",
	            'product' => "tf",
	            'version' => "1.1.3.2"
	        );
	        $reply = $master->sendHeartBeat($data);
			print_r($reply);
	        if(empty($reply)) {
	                next;
			echo "no reply";
	        } elseif($reply[sizeof($reply) - 1] instanceof M2S_REQUESTRESTART_Packet) {
	                $response = $reply[0]->getMessage(); // see reply of version..
			print_r($response);
	                if ($response == "Your server is out of date, please upgrade") {
				echo "we got a update";
			}
		}
?>
