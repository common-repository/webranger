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

class WebRanger_Security
{
	public function check_ip($source_ip,$allowed_ip_list)
    {
        $result = false;

        $valid_ip = filter_var($source_ip,FILTER_VALIDATE_IP);
        if(!isset($valid_ip))    
            return $result;

        if(in_array($source_ip,$allowed_ip_list))
            $result = true;

        return $result;
    }
}
?>