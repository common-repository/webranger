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


/**
 * @category	Security
 * @author	Dominic Lucenario dlucenario@pandoralabs.net
 * @version	MK1
 */

namespace IDS;

class Logger
{

    private $wids_server = '';
    private $auth_credentials = '';
    private $ip = 'local/unknown';
    private $log = array();

    public function __construct() #Construct Default Log Format
    {
        $this->ip = $_SERVER['REMOTE_ADDR'] .
            (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?
                ' (' . $_SERVER['HTTP_X_FORWARDED_FOR'] . ')' : '');
                
        $this->auth_credentials = new Credentials();
        $this->log['client_username'] = $this->auth_credentials->username;
        $this->log['client_hash'] = md5($this->auth_credentials->password);

        $version_control_array = parse_ini_file(dirname(__FILE__)."/Config/Version.ini.php");
        $this->log['version_control'] = $version_control_array['version_control'];

        $this->log['webranger_type'] = 1;
        $this->log['timestamp'] =  gmdate('Y-m-d H:i:s');
        $this->log['log_plugin_id'] = 7001;

        $this->log['log_sip'] = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $this->log['log_proxy_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $this->log['log_dip'] = $_SERVER['SERVER_ADDR'];
        $this->log['log_hostname'] = $_SERVER['SERVER_NAME'];

        $this->wids_server = $this->auth_credentials->wids_server;
    }

    public function execute(Report $data) 
    {                
        $this->log['log_data'] = $this->prepareData($data);
	
        foreach ($data as $event) 
        {            
        	$attackParameters = $event->getName() . '=' . ((!isset($this->urlencode) ||$this->urlencode) ? urlencode($event->getValue()) : $event->getValue());
            $filters = $event->getFilters(); 

			foreach ($filters as $key) 
			{
				$format  = "Signature: %s </br>";
				$format .= "Signature ID: %d </br>";
				$format .= "IP: %s </br>";        
				$format .= "Impact: %d </br>";
				$format .= "Affected tags: %s </br>";
				$format .= "Affected parameters: %s </br>";
				$format .= "Request URI: %s \</br>";
				$format .= "Origin: %s </br>";
			
				$dataString = sprintf(
					$format,
					$key->getDescription(),
					$key->getId(),
				   	$this->ip,
				   	$key->getImpact(),
				   	join(' ', $key->getTags()),
				   	trim($attackParameters),
				   	htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8'),
				   	$_SERVER['SERVER_ADDR']
			   	); 
				$this->log['log_data'] .= $dataString;
			}		
        }
	
		$this->log['log_name'] = "TI: ". $data->getImpact() ." + TAGS: ". join(' ', $data->getTags());
		$this->log['log_vendor_sig_id'] = $this->translateImpact($data->getImpact());

		if(strlen($this->log['log_data']) > 16384)
		$this->log['log_data'] = substr($this->log['log_data'],0,16384);

		$db = new \SQLite3(dirname(__FILE__).'/Database/backlog.db');
		$fp = @fsockopen($this->auth_credentials->wids_server, 443, $errno, $errstr, 1);

		if($fp)
		{
			$this->curl_post_async($fp,$this->auth_credentials->wids_server,$this->log);
			while($this->getBacklog($db))
			{
				$backlog = $this->getBacklog($db);
				$singleLog = new Logger();

				$singleLog->log['log_sip'] = $backlog['log_sip'];
				$singleLog->log['log_dip'] = $backlog['log_dip'];
				$singleLog->log['log_data'] = $backlog['log_data'];
				$singleLog->log['log_name'] = $backlog['log_name'];
				$singleLog->log['log_vendor_sig_id'] = $backlog['log_vendor_sig_id'];
				$singleLog->log['log_hostname'] = $backlog['log_hostname'];
				$singleLog->log['timestamp'] = $backlog['timestamp'];
				
				$fp = fsockopen($this->auth_credentials->wids_server, 443, $errno, $errstr, 1);
				$this->curl_post_async($fp,$this->auth_credentials->wids_server,$singleLog->log);
				$this->deleteBacklog($db,$backlog['rowid']);
			}	
		}
		else
			$this->insertToBacklog($db,$this->log);

        $db->close();                      	                                      
    	return true;
    } 
   
	private function prepareData($data) 
    	{
		$format = '"%s",%s,%d,"%s","%s","%s","%s"';

		$attackedParameters = '';
		foreach ($data as $event) {
		    $attackedParameters .= $event->getName() . '=' .
		        rawurlencode($event->getValue()) . ' ';
		}

		$dataString = sprintf($format,
		    urlencode($this->ip),
		    gmdate('Y-m-d H:i:s'),
		    $data->getImpact(),
		    join(' ', $data->getTags()),
		    urlencode(trim($attackedParameters)),
		    urlencode($_SERVER['REQUEST_URI']),
		    $_SERVER['SERVER_ADDR']
		);

		return $dataString;
   	 }


	private function translateImpact($TI)
	{
		if ($TI >= 1 && $TI<=15) 
			return 76;
		elseif($TI >= 16 && $TI<=29) 
			return 77;
		elseif($TI >= 30 && $TI<=49) 
			return 78;
		elseif($TI >= 50) 
			return 79;
	}
	
	public function isBlocked()
	{
		$ip = ip2long($this->log['log_sip']);
		$path = (dirname(__FILE__).'/Database/sentry.db');
		$db = new \SQLite3($path);

		$query = "SELECT status FROM ip_block where ip_address = ?";
		$prpstmt = $db->prepare($query);
        $prpstmt->bindValue(1, $ip, SQLITE3_INTEGER);
        $result = $prpstmt->execute();
		$row = $result->fetchArray();
		
		if($row['status'] == 1 || $row['status'] == 2)
		{
			$this->modEvent(80);
			header('HTTP/1.1 404 Not Found');
			$_GET['e'] = 404;
			require_once (dirname(__FILE__).'/err.php');
			$db->close();
			die();
		}

		#Check if Request Has Proxy
		if(isset($this->log['log_proxy_ip']))
		{
			$query = "SELECT status FROM ip_block where ip_address = ?";
			$prpstmt = $db->prepare($query);
	        $prpstmt->bindValue(1, $this->log['log_proxy_ip'], SQLITE3_INTEGER);
	        $result = $prpstmt->execute();
			$row = $result->fetchArray();
			
			if($row['status'] == 1 || $row['status'] == 2)
			{
				$this->modEvent(80);
				header('HTTP/1.1 404 Not Found');
				$_GET['e'] = 404;
				require_once (dirname(__FILE__).'/err.php');
				$db->close();
				die();
			}
		}

		$db->close();
		return true;
	}

	public function modEvent($event_type,$event_details = "")
	{		
		$db = new \SQLite3(dirname(__FILE__).'/Database/backlog.db');

		switch($event_type)
		{
			case 80:
				$this->log['log_vendor_sig_id'] = $event_type;
				$this->log['log_name'] = "Successful Blocked IP Address";
				$this->log['log_data'] = "Signature: Internal WIDS Event; ".$event_details;
				break;

			case 81:
				$this->log['log_vendor_sig_id'] = $event_type;
				$this->log['log_name'] = "WAF-Activity: Add Rule";
				$this->log['log_data'] = "Signature: Internal WAF Event; ".$event_details;
				break;

			case 82:
				$this->log['log_vendor_sig_id'] = $event_type;
				$this->log['log_name'] = "WAF-Activity: Delete Rule";
				$this->log['log_data'] = "Signature: Internal WAF Event; ".$event_details;
				break;

			case 83:
				$this->log['log_vendor_sig_id'] = $event_type;
				$this->log['log_name'] = "WAF-Activity: Upgrade Rule";
				$this->log['log_data'] = "Signature: Internal WAF Event; ".$event_details;
				break;

			case 84:
				$this->log['log_vendor_sig_id'] = $event_type;
				$this->log['log_name'] = "WAF-Activity: Deactivate Rule";
				$this->log['log_data'] = "Signature: Internal WAF Event; ".$event_details;
				break;

			case 85:
				$this->log['log_vendor_sig_id'] = $event_type;
				$this->log['log_name'] = "WAF-Activity: Permanent Block Rule";
				$this->log['log_data'] = "Signature: Internal WAF Event; ".$event_details;
				break;

			default:
				break;
		}

		$fp = @fsockopen($this->auth_credentials->wids_server, 443, $errno, $errstr, 1);
		if($fp)
			$this->curl_post_async($fp,$this->auth_credentials->wids_server,$this->log);
		else
			$this->insertToBacklog($db,$this->log);

		$db->close();
	}

	private function insertToBacklog($db,$data)
	{
		$sql = "INSERT INTO backlog (log_sip,log_dip,log_data,log_name,log_vendor_sig_id,log_hostname,timestamp) values(?,?,?,?,?,?,?)";

		$prpstatement = $db->prepare($sql);
		$prpstatement->bindValue(1, $data['log_sip'], SQLITE3_TEXT);
		$prpstatement->bindValue(2, $data['log_dip'], SQLITE3_TEXT);
		$prpstatement->bindValue(3, $data['log_data'], SQLITE3_TEXT);
		$prpstatement->bindValue(4, $data['log_name'], SQLITE3_TEXT);
		$prpstatement->bindValue(5, $data['log_vendor_sig_id'], SQLITE3_INTEGER);
		$prpstatement->bindValue(6, $data['log_hostname'], SQLITE3_TEXT);
		$prpstatement->bindValue(7, $data['timestamp'], SQLITE3_TEXT);

		$result = $prpstatement->execute();  
	}
	
	private function getBacklog($db)
	{
		$res = false;
		$sql = "SELECT * FROM backlog limit 1";
		$res = $db->query($sql);
		$res = $res->fetchArray();
		return $res;
	}

	private function deleteBacklog($db,$rowid)
	{
		$query = "DELETE FROM backlog where rowid = ?";
                $prpstatement = $db->prepare($query);
                $prpstatement->bindValue(1, $rowid, SQLITE3_INTEGER);
                $result = $prpstatement->execute();
	}
	
	private function curl_post_async($fp, $url,$params)
	{
	    foreach ($params as $key => &$val) {
	      if (is_array($val)) $val = implode(',', $val);
		$post_params[] = $key.'='.urlencode($val);
	    }
	    $post_string = implode('&', $post_params);

	    $parts=parse_url($url);
	    $out = "POST index.php HTTP/1.1\r\n";
	    $out.= "Host: ".$parts['host']."\r\n";
	    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $out.= "Content-Length: ".strlen($post_string)."\r\n";
	    $out.= "Connection: Close\r\n\r\n";
	    if (isset($post_string)) $out.= $post_string;

	    fwrite($fp, $out);
	    fclose($fp);
	}	
}

?>
