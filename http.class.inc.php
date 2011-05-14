<?php

/*
 *
 *		This class provides some static methods for http connections.
 * 
 *		Requirements:
 *			- file.class.inc.php
 *
 */

class http {
	
	// send http post data to the url.
	public static function post($url, $data, $return = true){
		$url = parse_url($url);
		$boundary = md5(microtime(true));
		
		$post = '';
		foreach ($data as $name => $value){
			if (file_exists($value)){
				$post .= "--{$boundary}\r\n";
				$post .= "Content-Disposition: form-data; name=\"{$name}\"; filename=\"" . basename($value) . "\"\r\n";
				$post .= 'Content-Type: ' . file::get_mime($value) . ";\r\n\r\n";
				$post .= file_get_contents($value) . "\r\n";	
			}else{
				$post .= "--{$boundary}\r\n";
				$post .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
				$post .= $value . "\r\n";
			}
		}
		
		$post .= "--{$boundary}--\r\n";
		
		if (isset($url['query'])){
			$head  = "POST {$url['path']}?{$url['query']} HTTP/1.1\r\n";
		}else{
			$head  = "POST {$url['path']} HTTP/1.1\r\n";
		}
		
		$head .= "Host: {$url['host']}\r\n";
		$head .= "Content-Type: multipart/form-data; boundary=\"{$boundary}\"\r\n";
		$head .= 'Content-Length: ' . strlen($post) . "\r\n";
		$head .= "Connection: close\r\n\r\n";
		
		$sock = fsockopen($url['host'], ((isset($url['port'])) ? $url['port'] : 80));
		fwrite($sock, $head . $post);
		
		if ($return === true){
			return stream_get_contents($sock);
		}
	}
	
	// sends http get data to the url.
	public static function get($url, $data = null, $return = true){
		$url = parse_url($url);
	
		if ($data !== null){
			$url['path'] .= '?' . http_build_query($data);
		}
	
		$head  = "GET {$url['path']} HTTP/1.1\r\n";
		$head .= "Host: {$url['host']}\r\n";
		$head .= "Connection: Close\r\n\r\n";
	
		$sock = fsockopen($url['host'], ((isset($url['port'])) ? $url['port'] : 80));
		fwrite($sock, $head);	
		
		if ($return === true){
			return stream_get_contents($sock);
		}
	}
	
	// forces the download of a file.
	public static function force_download($path){
		if (file_exists($path) === false){ return false; }
		
		header('Content-Type: application/octetstream'); // <-- this is for stupid IE6
		header('Content-Type: application/octet-stream');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="' . basename($path) . '"');
		header('Content-Length: ' . filesize($path));
		
		// php4 method used here because large files would use a lot or RAM with readfile.
		$file = fopen($path, 'rb');
		
		while (feof($file) === false){
			echo fread($file, 4096);
		}
		
		fclose($file);
	}
	
	// forces the download of raw data.
	public static function force_download_raw($name, $size, $data){
		header('Content-Type: application/octetstream'); // <-- this is for stupid IE6
		header('Content-Type: application/octet-stream');
		header('Content-Description: File Transfer');
		header("Content-Disposition: attachment; filename=\"{$name}\"");
		header("Content-Length: {$size}");
		
		echo $data;
	}
	
}

?>
