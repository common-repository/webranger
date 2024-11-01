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
/**
 *
 * @category  Security
 * @author    Dominic Lucenario dlucenario@pandoralabs.net
 * @version   MK1
 */
 
class Credentials {
     public $username = ""; 
     public $password = ""; 
     public $wids_server = "ssl://core.pandoralabs.net";

	function __construct()
	{
		$s_config_file = parse_ini_file(dirname(__FILE__)."/Sensor.ini.php");
		$this->username = $s_config_file['sensor_username'];
		$this->password = $s_config_file['sensor_password'];
	}
}	
