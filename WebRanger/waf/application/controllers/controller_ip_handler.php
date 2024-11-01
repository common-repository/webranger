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

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once((dirname(dirname(dirname(dirname(__FILE__))))).'/lib/IDS/Logger.php');
require_once((dirname(dirname(dirname(dirname(__FILE__))))).'/lib/IDS/Config/Credentials.php');

use IDS\Logger;
use IDS\Credentials;

class Controller_ip_handler extends CI_Controller {

	function addRule()
	{
		$this->load->library('form_validation');
		$this->load->model('Whitelist');

		if($this->Whitelist->check_ip($_SERVER['REMOTE_ADDR']) === false) #Whitelisting Function
			$this->waf_exit();

		if($this->checkSensor($this->input->post('identifier',TRUE)) === false) #Check Sensor Name
			$this->waf_exit();

		$input_ip = filter_var($this->input->post('ip',TRUE),FILTER_VALIDATE_IP); #Sanitize IP
        if($input_ip === false)    
            $this->waf_exit();

        $min = $this->input->post('min',TRUE);
        if(strlen($min) > 3 && !is_numeric($min)) #Sanitize Minutes
			$this->waf_exit();

		$hour = $this->input->post('hour',TRUE);
        if(strlen($hour) > 3 && !is_numeric($hour)) #Sanitize Hours
			$this->waf_exit();

		$day = $this->input->post('day',TRUE);
        if(strlen($day) > 3 && !is_numeric($day)) #Sanitize Days
			$this->waf_exit();

		#Actual Process	
		$this->load->model('Ip');
		$this->load->model('Dbcon');
		$result = "false";
		
		$con = $this->Dbcon->getConnection();
		if($min == 0 && $hour == 0 && $day == 0)
		{
			if($this->Ip->addIp($input_ip,$min,$hour,$day,$con,true))
			{
				$waf_logger = new Logger();
				$payload = "IP Address: %s; Duration: Permanent Block";
				$payload = sprintf($payload,$input_ip,$min,$hour,$day);
				$waf_logger->modEvent(85,$payload);
				$result = "true";
			}
		}
		elseif($this->Ip->addIp($input_ip,$min,$hour,$day,$con))
		{
			$waf_logger = new Logger();
			$payload = "IP Address: %s; Duration: %d min %d hour %d day";
			$payload = sprintf($payload,$input_ip,$min,$hour,$day);
			$waf_logger->modEvent(81,$payload);
			$result = "true";
		}

		$arr['add_ip_result'] = $result;
		$con->close();
		echo json_encode($arr);
	}

	function deleteRule()
	{
		$this->load->model('Whitelist');

		if($this->Whitelist->check_ip($_SERVER['REMOTE_ADDR']) === false) #Whitelisting Function
			$this->waf_exit();

		if($this->checkSensor($this->input->post('identifier')) === false) #Check Sensor Name
			$this->waf_exit();

		$raw_ip_list = $this->input->post('ip_list',TRUE);
		if($raw_ip_list === false)
			$this->waf_exit();

		$input_ip_list = array();
		foreach($raw_ip_list as $single_ip)
		{
			if(!(filter_var($single_ip,FILTER_VALIDATE_IP))) #Sanitize IP List 
            	$this->waf_exit();
            else #Valid IP
            	array_push($input_ip_list,ip2long($single_ip));
		}

		#Actual Process	
		$this->load->model('Ip');
		$this->load->model('Dbcon');
		$result = "false";

		$con = $this->Dbcon->getConnection();
		if($this->Ip->delete($input_ip_list,$con))
		{
			$waf_logger = new Logger();
			$payload = "IP Address List: %s;";
			$payload = sprintf($payload,implode(", ",$raw_ip_list));
			$waf_logger->modEvent(82,$payload);
			$result = "true";
		}

		$arr['del_ip_result'] = $result;
		$con->close();
		echo json_encode($arr);
	}

	function modifyRule()
	{
		$this->load->model('Whitelist');

		if($this->Whitelist->check_ip($_SERVER['REMOTE_ADDR']) === false) #Whitelisting Function
			$this->waf_exit();

		if($this->checkSensor($this->input->post('identifier')) === false) #Check Sensor Name
			$this->waf_exit();

		$raw_ip_list = $this->input->post('ip_list',TRUE);
		if($raw_ip_list === false)
			$this->waf_exit();

		$input_ip_list = array();
		foreach($raw_ip_list as $single_ip)
		{
			if(!(filter_var($single_ip,FILTER_VALIDATE_IP))) #Sanitize IP List 
            	$this->waf_exit();
            else #Valid IP
            	array_push($input_ip_list,ip2long($single_ip));
		}

		$mode = $this->input->post('flag',TRUE);
		if($mode != 2 && $mode != 4) #Mode must either 2 or 4
			$this->waf_exit();

		$this->load->model('Ip');
		$this->load->model('Dbcon');
		$result = "false";

		$con = $this->Dbcon->getConnection();
		if($this->Ip->changeStatus($input_ip_list,$mode,$con))
		{
			$waf_logger = new Logger();
			$payload = "IP Address List: %s;";
			$payload = sprintf($payload,implode(", ",$raw_ip_list));

			if($mode == 2)
			$waf_logger->modEvent(85,$payload);
			elseif($mode == 4)
			$waf_logger->modEvent(84,$payload);
			$result = "true";
		}

		$arr['mod_ip_result'] = $result;
		$con->close();
		echo json_encode($arr);

	}

	function upgradeRule()
	{
		$this->load->model('Whitelist');

		if($this->Whitelist->check_ip($_SERVER['REMOTE_ADDR']) === false) #Whitelisting Function
			$this->waf_exit();

		if($this->checkSensor($this->input->post('identifier')) === false) #Check Sensor Name
			$this->waf_exit();

		$raw_ip_list = $this->input->post('ip_list',TRUE);
		if($raw_ip_list === false)
			$this->waf_exit();

		$input_ip_list = array();
		foreach($raw_ip_list as $single_ip)
		{
			if(!(filter_var($single_ip,FILTER_VALIDATE_IP))) #Sanitize IP List 
            	$this->waf_exit();
            else #Valid IP
            	array_push($input_ip_list,ip2long($single_ip));
		}

		$min = $this->input->post('min',TRUE);
        if(strlen($min) > 3 && !is_numeric($min)) #Sanitize Minutes
			$this->waf_exit();

		$hour = $this->input->post('hour',TRUE);
        if(strlen($hour) > 3 && !is_numeric($hour)) #Sanitize Hours
			$this->waf_exit();

		$day = $this->input->post('day',TRUE);
        if(strlen($day) > 3 && !is_numeric($day)) #Sanitize Days
			$this->waf_exit();

		$this->load->model('Ip');
		$this->load->model('Dbcon');
		$result = "false";

		$con = $this->Dbcon->getConnection();
		if($this->Ip->updateBlockTime($input_ip_list,$min,$hour,$day,$con))
		{	
			$waf_logger = new Logger();
			$payload = "IP Address List: %s; Duration: %d min %d hour %d day";
			$payload = sprintf($payload,implode(", ",$raw_ip_list),$min,$hour,$day);
			$waf_logger->modEvent(83,$payload);
			$result = "true";
		}
		$arr['upg_ip_result'] = $result;
		$con->close();
		echo json_encode($arr);
	}

	function getRules()
	{
		$this->load->model('Whitelist');
		$pageNo = $this->input->get('page', TRUE);
		$offset = $this->input->get('offset',TRUE);
		$identifier = $this->input->get('identifier',TRUE);
		$ip_search = $this->input->get('ip',TRUE);
		$from_search = false;

		if($ip_search !== FALSE)
		{
			$from_search = true;
			$ip_search = filter_var($ip_search,FILTER_VALIDATE_IP); #Sanitize IP
        	if($ip_search === false)    
            	$this->waf_exit();
    	}	

		if($this->Whitelist->check_ip($_SERVER['REMOTE_ADDR']) === false) #Whitelisting Function
			$this->waf_exit();

		if($this->checkSensor($identifier) === false) #Check Sensor Name
			$this->waf_exit();
		
		#Sanitation Function
		$offset = (isset($offset) ? is_numeric($offset) ? $offset : NULL : NULL);
		if($offset === NULL)
			$this->waf_exit();

		if($from_search === false)
		{
			if(strlen($pageNo) > 10 && !is_numeric($pageNo) && $pageNo === false) #Sanitation Function
				$this->waf_exit();
		}

		$this->load->model('Dbcon');
		$this->load->model('Ip');
		$con = $this->Dbcon->getConnection();
		$result_list = array();
		$rule_list = array();

		if($from_search === false)
		{
			$db_link = $this->Ip->getTable(NULL,$con,(($pageNo-1)*10));
			$result_list['total'] = $this->Ip->getMax($con);
		}
		else
		$db_link = $this->Ip->getTable($ip_search,$con,0);

		while($row = $db_link->fetchArray())
		{
			$one_rule['ip_address'] = long2ip($row['ip_address']);
			$one_rule['status'] = $this->convertStatus($row['status']);
			if($row['status'] === 1)
			$one_rule['dateActivation'] = $this->localizeTime($offset,$row['dateActivation']);
			else
			$one_rule['dateActivation'] = NULL;
			array_push($rule_list,$one_rule);
		}

		$result_list['results'] = $rule_list;
		$con->close();
		echo json_encode($result_list);
	}

	function waf_exit()
	{
		http_response_code(404);
		require_once((dirname(dirname(dirname(dirname(__FILE__))))).'/lib/IDS/err.php');
		die();
	}

	private function checkSensor($remote_sensor_name)
	{
		$result = false;

		if($remote_sensor_name === false)
			return $result;

		$s_config_file = parse_ini_file(dirname(__FILE__)."/../../../lib/IDS/Config/Sensor.ini.php");
		$local_sensor_name = $s_config_file['sensor_username'];

		if($remote_sensor_name === $local_sensor_name)
			$result = true;

		return $result;
	}

	private function convertStatus($local_status)
	{
		switch($local_status)
		{
			case 1:
				return "Active";
				break;
			case 2:
				return "Permanently Blocked";
				break;
			case 3:
				return "Expired";
				break;
			case 4:
				return "Inactive";
				break;
			default:
				break;
		}
	}

	private function localizeTime($offset,$time)
	{
		$local_date = gmdate("Y-m-d H:i:s",@strtotime($time)+$offset);
		return $local_date;
	}

}
?>
