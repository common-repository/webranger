<?php
/*
    This file is part of WebRanger.

    WebRanger is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebRanger is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebRanger.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace IDS;
require_once (__DIR__.'/WebRanger-Error.php');
use IDS\WebRanger_Error;

class WebRanger_Transport
{
	public function request($url,$filename,$version_control)
	{
		$res = false;
		$default_setting = array(
				"timeout" => 300,
				"user-agent" => "WebRanger/".$version_control."; http://".$_SERVER['SERVER_NAME'],
				"http-version" => "HTTP/1.1"
			);

		#Determine the Mode of Transportation (curl or stream)
		if(function_exists('curl_init') || function_exists('curl_exec'))
		{
			$res = $this->request_curl($url,$filename,$default_setting);
			return $res;
		}
		else
		{
			$res = $this->request_stream($url,$filename,$version_control);
			return $res;
		}
	}

	public function request_curl($url,$filename,$params)
	{
		$local_stream = fopen($filename, 'w+');
		if($local_stream === false)
		{
			return new WebRanger_Error('Local Stream Fail to Connect'); #End Process
		}	

		$connection = curl_init();	

		curl_setopt($connection, CURLOPT_CONNECTTIMEOUT, $params['timeout']);
		curl_setopt($connection, CURLOPT_TIMEOUT,$params['timeout']);
		curl_setopt($connection, CURLOPT_USERAGENT, $params['user-agent']);
		curl_setopt($connection, CURLOPT_FILE, $local_stream);
		curl_setopt($connection, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($connection, CURLOPT_URL, $url);
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($connection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

		$res = curl_exec($connection);
		if($res === FALSE)
		{
			$error = "CURL Operation Error: ".curl_error($connection)."(".curl_errno($connection).")";
			curl_close($connection);
			return new WebRanger_Error($error);
		}

		return true;
	}

	public function request_stream($url,$filename,$version_control)
	{
		#Dyanmic Variables
		$host_request = parse_url($url);
		$transport = "ssl";
		$target_host = $host_request['host']; //Repository
		$port_number = 443;

		#SSL Options
		$ssl = array(
			'verify_peer' => false,
			'verify_peer_name' => false
			);

		#Static Connection Variables
		$connection_error = null;
		$connection_error_str = null;
		$connection_timeout = 300;
		$connection_flag = STREAM_CLIENT_CONNECT;
		$connection_context = stream_context_create(array('ssl'=>$ssl));

		#Header List of Request Stream
		$http_head_method = "GET";
		$http_head_path = $host_request['path']; //Path of WebRanger
		$http_head_version = "HTTP/1.1";
		$http_head_useragent = "WebRanger/".$version_control."; http://".$_SERVER['SERVER_NAME'];

		#Establish Remote Stream
		$remote_stream = stream_socket_client($transport."://".$target_host.":".$port_number,$connection_error,$connection_error_str,$connection_timeout,$connection_flag,$connection_context);

		if($remote_stream === false)
		{
			return new WebRanger_Error('Remote Stream Fail to Connect: '.$connection_error . ': ' . $connection_error_str ); #End Process
		}

		stream_set_timeout($remote_stream, $connection_timeout);

		$http_head = $http_head_method." ".$http_head_path." ".$http_head_version."\r\n";
		$http_head .= "Host: ".$target_host."\r\n";
		$http_head .= $http_head_useragent."\r\n";
		$http_head .= "\r\n\r\n";

		fwrite($remote_stream, $http_head);
		$local_stream = fopen($filename,"w+");

		if($local_stream === false)
		{
			return new WebRanger_Error('Local Stream Fail to Connect'); #End Process
		}

		$bodyStarted = false; #Flag for Body Recognition
		$strResponse = '';
		$block = '';

		while (!feof($remote_stream)) 
		{
			$block = fread($remote_stream,4096);
			if (!$bodyStarted)
			{
				$strResponse .= $block;
				if (strpos($strResponse, "\r\n\r\n" )) #Signifies the Start of Header
				{
					$process = $this->processResponse($strResponse);
					$bodyStarted = true;
					$block = $process['body'];
					unset($strResponse);
					$process['body'] = '';
				}
			}

			$rs_byte = strlen($block); 				  # Number of Bytes from Remote Stream
			$ls_byte = fwrite($local_stream, $block); # Number of Bytes from Local Stream

			#Check if Equals
			if($rs_byte != $ls_byte) #Failure to Write on Files
			{
				fclose($remote_stream);
				fclose($local_stream);
				return new WebRanger_Error('Fail to Write in Local Stream'); #End Process
			}
		}
		return true;
	}

	public function processResponse($strResponse) 
	{
		$res = explode("\r\n\r\n", $strResponse, 2);
		return array('headers' => $res[0], 'body' => isset($res[1]) ? $res[1] : '');
	}

}
?>