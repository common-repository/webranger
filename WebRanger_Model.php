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
    
class WebRanger_Model
{
	public function wr_function_register()
	{
		if(!current_user_can('administrator'))
		{
			wp_die('You are not allowed to be on this page.');
		}

		check_admin_referer('wr_op_verify');

		$function_option = sanitize_text_field($_POST['option-form']);
		$webranger_server = "https://webranger.pandoralabs.net/api/Api/activate_wp_sensor"; #Remote Call for Activating WebRanger

		switch ($function_option)
		{
			case 0: #New Form
				$res = $this->wr_register_activate($webranger_server);
				break;
			case 1: 
				$res = $this->wr_activate($webranger_server);
				break;
			case 2:
				$res = true;
				break;
			default:
				wp_die("WebRanger Error: Unexpected Input from Form");
				break;
		}
		if($res === true)
		{
			if(file_exists(dirname(__FILE__)."/WebRanger/lib/IDS/Config/Sensor.ini.php"))
			{
				$sensor_information = array("allowed_ip_list"=>"52.8.253.208,52.24.150.60"); #IP Addresses of WebRanger Console (Security Control* Whitelisting Module)
				$sensor_information['sensor_username'] = isset($_POST['wr_sensor_name']) ? sanitize_text_field($_POST['wr_sensor_name']) :NULL;
				$sensor_information['sensor_password'] = isset($_POST['wr_sensor_key']) ? sanitize_text_field($_POST['wr_sensor_key']) :NULL;
				$sensor_section = array();
				$sensor_section['WebRanger'] = $sensor_information;
				if($this->write_ini_file($sensor_section,dirname(__FILE__)."/WebRanger/lib/IDS/Config/Sensor.ini.php",true) !== false)
				{
					$wr_option_array = array("wr_status"=>1);
					update_option('wr_op_array', $wr_option_array); #Update Option Variable in WP
					wp_redirect(admin_url('options-general.php?page=wr_contents')); #Redirect to WebRanger
				}
				else
				{
					wp_redirect(admin_url('options-general.php?page=wr_contents&error_code=1')); #Fail to Write
				}	
			}
		}
		else
		{
			wp_redirect(admin_url('options-general.php?page=wr_contents&error_code=1')); #Fail in activating WebRanger
		}
	}

	public function wr_register_activate($url)
	{
		$result = 0;

		#Sanitize Input
		$wr_post_array = array();

		$wr_post_array['wr_account_email'] = isset($_POST['wr_account_email']) ? sanitize_text_field($_POST['wr_account_email']) :NULL;
		$wr_post_array['wr_account_identification'] = isset($_POST['wr_account_identification']) ? sanitize_text_field($_POST['wr_account_identification']) :NULL;
		$wr_post_array['wr_account_fname'] = isset($_POST['wr_account_fname']) ? sanitize_text_field($_POST['wr_account_fname']) :NULL;
		$wr_post_array['wr_account_lname'] = isset($_POST['wr_account_lname']) ? sanitize_text_field($_POST['wr_account_lname']) :NULL;
		$wr_post_array['wr_sensor_name'] = isset($_POST['wr_sensor_name']) ? sanitize_text_field($_POST['wr_sensor_name']) :NULL;
		$wr_post_array['wr_sensor_key'] = isset($_POST['wr_sensor_key']) ? sanitize_text_field($_POST['wr_sensor_key']) :NULL;
		$wr_post_array['wr_sensor_domain'] = $_SERVER['SERVER_NAME'];
		$wr_post_array['wr_wp_api'] = 0; 

		$wr_post_option = array(
					'body' => $wr_post_array,
					'timeout' => '15',
					'sslverify' => false
			);

		try
		{
			$post_result = wp_remote_post($url,$wr_post_option);
			if(is_array($post_result))
			{
				if($post_result['response']['code'] != 200)
					return $result;
				if($post_result['body'] != 1)
					return $result;
			}
			elseif(get_class($post_result) == "WP_Error")
				return $result;

			$result = true;
		}
		catch(Exception $e) #Something went wrong
		{
			return $result;
		}

		return $result;
	}

	public function wr_activate($url)
	{
		$result = false;

		#Sanitize Input
		$wr_post_array = array();

		$wr_post_array['wr_account_email'] = isset($_POST['wr_account_email']) ? sanitize_text_field($_POST['wr_account_email']) :NULL;
		$wr_post_array['wr_account_identification'] = isset($_POST['wr_account_identification']) ? sanitize_text_field($_POST['wr_account_identification']) :NULL;
		$wr_post_array['wr_sensor_name'] = isset($_POST['wr_sensor_name']) ? sanitize_text_field($_POST['wr_sensor_name']) :NULL;
		$wr_post_array['wr_sensor_key'] = isset($_POST['wr_sensor_key']) ? sanitize_text_field($_POST['wr_sensor_key']) :NULL;
		$wr_post_array['wr_sensor_domain'] = $_SERVER['SERVER_NAME'];
		$wr_post_array['wr_wp_api'] = 1; 

		$wr_post_option = array(
			'body' => $wr_post_array,
			'timeout' => '15',
			'sslverify' => false
		);
		try
		{
			$post_result = wp_remote_post($url,$wr_post_option);
			if(is_array($post_result))
			{
				if($post_result['response']['code'] != 200)
					return $result;
				if($post_result['body'] != 1)
					return $result;
			}
			elseif(get_class($post_result) == "WP_Error")
				return $result;

			$result = true;
		}
		catch(Exception $e) #Something went wrong
		{
			return $result;
		}
		return $result;
	}

	public function wr_function_mod()
	{
		if(!current_user_can('administrator'))
		{
			wp_die('You are not allowed to be on this page.');
		}

		check_admin_referer('wr_op_verify_mod');

		$function_option = sanitize_text_field($_POST['submit']);
		$sensor_information = array("allowed_ip_list"=>"52.8.253.208,52.24.150.60"); #IP Addresses of WebRanger Console (Security Control* Whitelisting Module)

		if(strcmp($function_option,"Update Keys") === 0)
		{
			$sensor_information['sensor_username'] = isset($_POST['wr_sensor_name']) ? sanitize_text_field($_POST['wr_sensor_name']) :NULL;
			$sensor_information['sensor_password'] = isset($_POST['wr_sensor_key']) ? sanitize_text_field($_POST['wr_sensor_key']) :NULL;
		}
		elseif(strcmp($function_option,"Reset WebRanger") === 0)
		{
			$sensor_information['sensor_username'] = "";
			$sensor_information['sensor_password'] = "";
		}
		$sensor_section = array();
		$sensor_section['WebRanger'] = $sensor_information;

		if($this->write_ini_file($sensor_section,dirname(__FILE__)."/WebRanger/lib/IDS/Config/Sensor.ini.php",true) !== false)
		{
			wp_redirect(admin_url('options-general.php?page=wr_contents')); #Redirect to WebRanger
		}
		else
			wp_redirect(admin_url('options-general.php?page=wr_contents&error_code=1')); #Redirect to WebRanger
	}

	private function write_ini_file($assoc_arr, $path, $has_sections=FALSE) 
	{ 
	    $content = ""; 
	    if ($has_sections) 
	    { 
	        foreach ($assoc_arr as $key=>$elem) 
	        { 
	            $content .= "[".$key."]\n"; 
	            foreach ($elem as $key2=>$elem2) 
	            { 
	                if(is_array($elem2)) 
	                { 
	                    for($i=0;$i<count($elem2);$i++) 
	                    { 
	                        $content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
	                    } 
	                } 
	                else if($elem2=="") $content .= $key2." = \n"; 
	                else $content .= $key2." = \"".$elem2."\"\n"; 
	            } 
	    	} 
    	} 
    	else 
    	{ 
	        foreach ($assoc_arr as $key=>$elem) 
	        { 
	            if(is_array($elem)) 
	            { 
	                for($i=0;$i<count($elem);$i++) 
	                { 
	                    $content .= $key."[] = \"".$elem[$i]."\"\n"; 
	                } 
	            } 
	            else if($elem=="") $content .= $key." = \n"; 
	            else $content .= $key." = \"".$elem."\"\n"; 
	        } 
    	} 

	    if (!$handle = fopen($path, 'w')) 
	    { 
	        return false; 
	    }

	    $success = fwrite($handle, $content);
	    fclose($handle); 
	    if($success !== false)
	    	return true;
	    else
	    	return false; 
	}
}

?>