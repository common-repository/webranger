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

class Whitelist extends CI_Model 
{
    private $allowed_ip_list = array();

	function __construct()
	{
        parent::__construct();
	   
        $s_config_file = parse_ini_file(__DIR__."/../../../lib/IDS/Config/Sensor.ini.php");
        $this->allowed_ip_list = explode(',',$s_config_file['allowed_ip_list']);
	}

    function check_ip($source_ip)
    {
        $result = false;

        $valid_ip = filter_var($source_ip,FILTER_VALIDATE_IP);
        if(!isset($valid_ip))    
            return $result;

        if(in_array($source_ip,$this->allowed_ip_list))
            $result = true;

        return $result;
    }
}

?>