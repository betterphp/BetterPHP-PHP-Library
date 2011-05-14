<?php

/*
 *
 *		This class provides an interface to work with imap and smtp servers.
 * 
 *		Requirements:
 *			- string.func.inc.php
 *			- error.func.inc.php
 *			- http.class.inc.php
 *
 */

// this class reprosents a single email, it is passed to the smtp::send() function
// the set_* methods can be used to manipulate the email, for example to send to 
// multiple addresses.
class email {
	
	public $to;
	public $from;
	public $subject;
	public $msg;
	public $files;
	
	public $date;
	
	public function __construct($to, $from, $subject, $msg, $files = null){
		$this->to		= $to;
		$this->from		= $from;
		$this->subject	= $subject;
		$this->msg		= $msg;
		$this->files	= ($files === null) ? array() : $files;
		
		$this->date = date('D, d M Y H:i:s O (T)');
	}
	
	public function set_to($to){ $this->to = $to; }
	public function set_from($from){ $this->from = $from; }
	public function set_subject($subject){ $this->subject = $subject; }
	public function set_msg($msg){ $this->msg = $msg; }
	public function set_files($files){ $this->files = $files; }
	
}

// some methods for working with the smtp server, but not directly
// related to sending mail.
class smtp_worker {
	
	protected $connection;
	protected $server_name;
	protected $features;
	
	// gets the responce from the server.
	protected function get_responce(){
		$lines = array();
		
		while (($line = fgets($this->connection)) !== false){
			$lines[] = $line;
			
			if (substr($line, 3, 1) === ' '){
				break;
			}
		}
		
		return implode("\n", $lines);
	}
	
	// gets the last status code from the server.
	protected function get_last_code(){
		return substr($this->get_responce(), 0, 3);
	}
	
}

// the core smtp class.
class smtp extends smtp_worker {
	
	public $server;
	public $port;
	public $user;
	public $pass;
	
	private $smtp_error_codes = array(
		'211' => 'System status',
		'214' => 'Help message',
		'220' => 'Service ready',
		'221' => 'Service closing transmission channel',
		'250' => 'Requested mail action okay, completed',
		'251' => 'User not local; will forward to',
		'354' => 'Start mail input; end with "."',
		'421' => 'Service not available, closing transmission channel',
		'450' => 'Requested mail action not taken: mailbox unavailable (E.g., mailbox busy)',
		'451' => 'Requested action aborted: local error in processing',
		'452' => 'Requested action not taken: insufficient system storage',
		'500' => 'Syntax error, command unrecognized (This may include errors such as command line too long)',
		'501' => 'Syntax error in parameters or arguments',
		'502' => 'Command not implemented',
		'503' => 'Bad sequence of commands',
		'504' => 'Command parameter not implemented',
		'550' => 'Requested action not taken: mailbox unavailable (E.g., mailbox not found, no access)',
		'551' => 'User not local',
		'552' => 'Requested mail action aborted: exceeded storage allocation',
		'553' => 'Requested action not taken: mailbox name not allowed (E.g., mailbox syntax incorrect)',
		'554' => 'Transaction failed'
	);
	
	public function __construct($server, $port = 25){
		$this->server	= $server;
		$this->port		= $port;
		
		// connect to the server.
		$this->connection = fsockopen($server, $port);
		stream_set_blocking($this->connection, 1);
		
		// process the server responce.
		$responce = $this->get_responce();
		$code = substr($responce, 0, 3);
		$desc = (isset($this->smtp_error_codes[$code])) ? $this->smtp_error_codes[$code] : 'unknown error';
		
		$responce = explode(' ', $responce);
		$this->server_name = $responce[1];
		
		// make sure the server is okay with us connecting.
		if ($code !== '220'){
			custom_error("smtp::__construct() smtp server (${server}) replied ${code} - ${desc}");
			return false;
		}
		
		// send the EHLO command.
		fwrite($this->connection, "EHLO ${server}\r\n");
		
		// process the responce.
		$features = str_replace("\r", '', $this->get_responce());
		$features = explode("\n", $features);
		unset($features[0], $features[count($features)]);
		$this->features = array();
		
		// add each of the servers fetaures to the features property.
		foreach ($features as $feature){
			$this->features[] = strtoupper(substr($feature, 4));
		}
		
		return true;
	}
	
	// sends a login request to the server.
	public function login($user, $pass){
		$this->user	= $user;
		$this->pass	= $pass;
		
		// create the plain text auth string.
		$string = base64_encode("\000${user}\000${pass}");
		
		// send the auth info.
		fwrite($this->connection, "AUTH PLAIN ${string}\r\n");
		
		// make sure the auth was sucessfull.
		$responce = $this->get_responce();
		
		if (substr($responce, 0, 1) === '5'){
			custom_error('smtp::login() authentication failed');
			return false;
		}
		
		return true;
	}
	
	// closes the connection to the server.
	public function close(){
		fwrite($this->connection, "QUIT\r\n");
		fclose($this->connection);
		
		return true;
	}
	
	// sends an email using the server.
	public function send($email){
		$to			= $email->to;
		$from		= $email->from;
		$subject	= $email->subject;
		$msg		= $email->msg;
		$files		= $email->files;
		
		$date		= $email->date;
		
		// validate the provided email.
		if (is_string($to) === false){
			custom_error('smtp::send() parameter->to should be a string, ' . gettype($to) . ' given');
			return false;
		}
		
		if (is_string($from) === false){
			custom_error('smtp::send() parameter->from should be a string, ' . gettype($from) . ' given');
			return false;
		}
		
		if (is_string($subject) === false){
			custom_error('smtp::send() parameter->subject should be a string, ' . gettype($subject) . ' given');
			return false;
		}
		
		if (is_string($msg) === false){
			custom_error('smtp::send() parameter->msg should be a string, ' . gettype($msg) . ' given');
			return false;
		}
		
		if (is_array($files) === false){
			custom_error('smtp::send() parameter->files should be an array, ' . gettype($files) . ' given');
			return false;
		}
		
		// send the MAIL command
		fwrite($this->connection, "MAIL FROM:<${from}>\r\n");
		
		// make sure the server said OK.
		if (($code = $this->get_last_code()) !== '250'){
			$desc = (isset($this->smtp_error_codes[$code])) ? $this->smtp_error_codes[$code] : 'unknown error';
			custom_error("smtp::send() smtp server (${server}) replied ${code} - ${desc}");
			return false;
		}
		
		// tell the server who the mail is for.
		fwrite($this->connection, "RCPT TO:<${to}>\r\n");
		
		// make sure it said Ok again.
		if (($code = $this->get_last_code()) !== '250'){
			$desc = (isset($this->smtp_error_codes[$code])) ? $this->smtp_error_codes[$code] : 'unknown error';
			custom_error("smtp::send() smtp server (${server}) replied ${code} - ${desc}");
			return false;
		}
		
		// construct the raw message data.
		$data[] = "MIME-Version: 1.0";
		$data[] = "From: ${from}";
		$data[] = "To: ${to}";
		$data[] = "Subject: ${subject}";
		$data[] = "Content-Type: text/plain; charset=UTF-8";
		$data[] = "Date: ${date}";
		$data[] = "X-Mailer: PHP " . PHP_VERSION;
		$data[] = "X-Sender: ${from}";
		$data[] = '';
		$data[] = $msg;
		
		// send the mail data.
		fwrite($this->connection, "DATA\r\n");
		fwrite($this->connection, implode("\r\n", $data));
		fwrite($this->connection, "\r\n.\r\n");
		
		return true;
	}
	
}

?>
