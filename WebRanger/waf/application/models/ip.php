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

require_once(getcwd()."/../updater.php");

class Ip extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function addIP($IP, $minutes, $hour, $day, $connection, $pb_flag = false)
    {
        $IPs = ip2long($IP); //Convert IP Address to Long

        if($pb_flag === true) #Permanently Blocked.
        {
            $sql = "REPLACE INTO ip_block (ip_address,status,dateActivation) values(?,2,NULL)";
            $prpstatement = $connection->prepare($sql);
            $prpstatement->bindValue(1, $IPs, SQLITE3_INTEGER);
        }
        else
        {
            $blockedTime = (60*$minutes) + (3600*$hour) + (86400*$day);
            $this->load->helper('date');
            $time = time() + $blockedTime;
            $time =  gmdate("Y-m-d H:i:s",$time); //Format Unix Time to Specific String
            $sql = "REPLACE INTO ip_block (ip_address,status,dateActivation) values(?,?,?)";

            $prpstatement = $connection->prepare($sql);
            $prpstatement->bindValue(1, $IPs, SQLITE3_INTEGER);
            $prpstatement->bindValue(2, 1, SQLITE3_INTEGER);
            $prpstatement->bindValue(3, $time, SQLITE3_TEXT);
        }
        
        $result = $prpstatement->execute();
        if($result !== FALSE)
        return true;
        else
        return false;
	}

    function getTable($search = NULL,$connection,$offset)
    {
        UpdateClass::run();

        if($search == NULL)
        {  
            $sql = "SELECT * FROM ip_block order by status,ip_address limit 10 offset ?";
            $prpstatement = $connection->prepare($sql);
            $prpstatement->bindValue(1,$offset,SQLITE3_INTEGER);
            $result = $prpstatement->execute();
            return $result;
        }
        else
        {   
            $ip = ip2long($search);
            $sql = "SELECT * FROM ip_block where ip_address = ? order by ip_address";
            $prpstmt = $connection->prepare($sql);
            $prpstmt->bindValue(1, $ip, SQLITE3_INTEGER);
            $result = $prpstmt->execute();
            return $result;
        }
    }

    function changeStatus($checkedList, $value,$connection)
    {
        if($checkedList != NULL)
        {
            foreach($checkedList as $ip)
            {
                $query = "UPDATE ip_block set status = ?, dateActivation = NULL where ip_address = ?";
                $prpstatement = $connection->prepare($query);
                $prpstatement->bindValue(1, $value, SQLITE3_INTEGER);
                $prpstatement->bindValue(2, $ip, SQLITE3_INTEGER);
                $result = $prpstatement->execute();    
            }
            return true;
        }
    }

    function delete($deleteList,$connection)
    {
        $result = false;
        if($deleteList != NULL)
        {
            foreach($deleteList as $ip)
            {
                $query = "DELETE from ip_block where ip_address = ?";
                $prpstatement = $connection->prepare($query);
                $prpstatement->bindValue(1, $ip, SQLITE3_INTEGER);
                $result = $prpstatement->execute();
            }
            $result = true;
        }
        return $result;
    }

    function updateBlockTime($checkedList ,$minutes, $hour, $day, $connection)
    {
        $blockedTime = (60*$minutes) + (3600*$hour) + (86400*$day);
        $this->load->helper('date');
        foreach($checkedList as $entry)
        {
            $query = "SELECT dateActivation from ip_block where ip_address = ?";
            $prpstatement = $connection->prepare($query);
            $prpstatement->bindValue(1,$entry,SQLITE3_INTEGER);
            $dateOfEntry = $prpstatement->execute();

            $res = $dateOfEntry->fetchArray();
            $res = $res['dateActivation'];

            $updatequery = "UPDATE ip_block set dateActivation = ?, status = 1 where ip_address = ?";
            $updatestmt = $connection->prepare($updatequery);

            if($res != NULL)
            {
                $res = strtotime($res."UTC");
                $newtime = $res + $blockedTime; 
            }  
            else
            {
                $newtime = time() + $blockedTime;
            }
            
            $newtime = gmdate("Y-m-d H:i:s",$newtime);
            $updatestmt->bindValue(1,$newtime,SQLITE3_TEXT);
            $updatestmt->bindValue(2,$entry,SQLITE3_INTEGER);
            $updatestmt->execute();
            $res = NULL;
        }
        return true;
    }

    function getMax($connection)
    {
        $query = "SELECT count(ip_address) as max from ip_block";
        $res = $connection->query($query);
        $res = $res->fetchArray();
        return $res['max'];
    }
}

?>
