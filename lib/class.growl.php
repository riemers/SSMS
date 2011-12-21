<?php
/**
 * Growl Notification Class
 * 
 * This class allows a PHP script to send notifications to a system running
 * the Mac OS X application Growl. This class was built from the Growl class
 * originally written by Tyler Hall, who really deserves the credit for this.
 * 
 * The original class can be found here: http://clickontyler.com/php-growl/
 * 
 * @author Nick Williams
 * @version 1.0.0 03/26/2008
 */
class Growl {
	const GROWL_PRIORITY_LOW = -2;
	const GROWL_PRIORITY_MODERATE = -1;
	const GROWL_PRIORITY_NORMAL = 0;
	const GROWL_PRIORITY_HIGH = 1;
	const GROWL_PRIORITY_EMERGENCY = 2;
	
	protected $appName = "PHP Growl";
	protected $address;
	protected $password;
	protected $port = 9887;
	protected $notifications = array();

	/**
	 * Initializes the growl class with the specified app name (if specified).
	 *
	 * @param string $app_name a name to use when sending notifications to Growl
	 */
	public function __construct($app_name = null) {
		if(isset($app_name))
			$this->appName = utf8_encode($app_name);
	}
	
	/**
	 * Sets the address and password for the system running Growl. Omit the
	 * password parameter if none is set.
	 *
	 * @param string $address the IP address of the system running Growl
	 * @param string $password the password required for access
	 */
	public function setAddress($address, $password = "") {
		$this->address = $address;
		$this->password = $password;
	}
	
	/**
	 * Adds a notification type/category for notifications sent.
	 *
	 * @param string $name the name of the notification
	 * @param boolean $enabled whether or not the notification type is enabled
	 */
	public function addNotification($name, $enabled = true) {
		if($name != "")
			$this->notifications[] = array("name" => utf8_encode($name), "enabled" => $enabled);
	}
	
	/**
	 * Registers with the system running Growl.
	 *
	 * @param string $address a new address to use, otherwise the current class variable is used
	 * @param string $password a new password to use, otherwise the current class variable is used
	 */
	public function register($address = null, $password = "") {
		if(isset($address))	{
			$this->address = $address;
			$this->password = $password;
		}
		
		$data = "";
		$defaults = "";
		$num_defaults = 0;
		
		for($i = 0; $i < count($this->notifications); $i++)	{
			$data .= pack("n", strlen($this->notifications[$i]["name"])) . $this->notifications[$i]["name"];
			
			if($this->notifications[$i]["enabled"])	{
				$defaults .= pack("c", $i);
				$num_defaults++;
			}
		}

		// pack(Protocol version, type, app name, number of notifications to register)
		$data  = pack("c2nc2", 1, 0, strlen($this->appName), count($this->notifications), $num_defaults) . $this->appName . $data . $defaults;
		$data .= pack("H32", md5($data . $this->password));

		$this->send($data);
	}
	
	/**
	 * Sends a notification to the Growl application.
	 *
	 * @param string $name the name of the notification type (must be one of the types set with the addNotification() funciton).
	 * @param string $title the title of the notification
	 * @param string $message the message content for the notification
	 * @param integer $priority the priority level for the notification
	 * @param boolean $sticky determines whether or not the notification should be "sticky"
	 */
	public function notify($name, $title, $message, $priority = 0, $sticky = false) {
		$name     = utf8_encode($name);
		$title    = utf8_encode($title);
		$message  = utf8_encode($message);
		$priority = intval($priority);
		
		$flags = ($priority & 7) * 2;
		if($priority < 0) $flags |= 8;
		if($sticky) $flags |= 1;

		// pack(protocol version, type, priority/sticky flags, notification name length, title length, message length. app name length)
		$data = pack("c2n5", 1, 1, $flags, strlen($name), strlen($title), strlen($message), strlen($this->appName));
		$data .= $name . $title . $message . $this->appName;
		$data .= pack("H32", md5($data . $this->password));

		$this->send($data);
	}
	
	/**
	 * Sends the supplied notification data.
	 *
	 * @param object $data the data object holding the notification details
	 */
	protected function send($data) {
		if(function_exists("socket_create") && function_exists("socket_sendto")) {
			$sck = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			socket_sendto($sck, $data, strlen($data), 0x100, $this->address, $this->port);
		}
		elseif(function_exists("fsockopen")) {
			$fp = fsockopen("udp://" . $this->address, $this->port);
			fwrite($fp, $data);
			fclose($fp);
		}
	}
}
?>
