<?php 
/**
 * Plugin Name: WebRanger
 * Plugin URI: http://pandoralabs.net/webranger
 * Description: WebRanger protects your web application in real-time by identifying attacks and blocking threats. Detection of attacks and responding to it in a timely manner is the key to ensuring your web application’s safety. WebRanger is capable of protecting your web application against the OWASP Top 10 vulnerabilities.
 * Version: 1.0.3
 * Author: Dominic Lucenario
 * Author URI: http://webranger.pandoralabs.net
 * License: GPLv3
 */

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

class WebRanger
{
	private $wr_options;
	private $wr_view;
	private $wr_model;

	public function execute_scanner()
	{
		$options = get_option('wr_op_array');
		if($options !== false)
		{
			if($options['wr_status'] == 1)
			{
				if(file_exists(dirname(__FILE__).'/WebRanger/sentry.php'))
				{
					@include_once(dirname(__FILE__).'/WebRanger/sentry.php');
				}
			}
		}
	}

	public function check_requirements()
	{
		if(version_compare(PHP_VERSION,'5.0.0') < 0 || !extension_loaded("sqlite3"))
			wp_die("WebRanger Requirements Check Fail<br>1. WebRanger Requires at least PHP Version 5.0.0<br>
					2. Sqlite3 Module must be loaded");
	}

	public function clear_wr_options()
	{
		delete_option('wr_op_array');
	}

	public function include_files()
	{
		if(file_exists(dirname(__FILE__).'/WebRanger_Model.php'))
			include_once(dirname(__FILE__).'/WebRanger_Model.php');
		
		if(file_exists(dirname(__FILE__).'/WebRanger_View.php'))
			include_once(dirname(__FILE__).'/WebRanger_View.php');
	}

	#Also loads java scripting functions needed by WebRanger
	public function register_hooks()
	{
		wp_register_script('wr-script', plugins_url('/misc/wr_js.js',__FILE__));
		wp_register_script('wr-script-activated', plugins_url('/misc/wr_js_activated.js',__FILE__));
		wp_register_style('wr-style', plugins_url('/misc/wr_style.css',__FILE__));
		add_action('admin_menu', array($this,'generate_submenu'));

		if($this->wr_options['wr_status'] === 0)
		add_action('admin_post_wr_register', array($this->wr_model,'wr_function_register'));
		elseif($this->wr_options['wr_status'] === 1)
		add_action('admin_post_wr_mod', array($this->wr_model,'wr_function_mod'));
	}

	#Set Default Options
	public function set_options()
	{	
		#Check if sensor credetials file contains something
		$this->wr_options['wr_status'] = 0;
		if(file_exists(dirname(__FILE__)."/WebRanger/lib/IDS/Config/Sensor.ini.php"))
		{
			$tmp_sensor = parse_ini_file(dirname(__FILE__)."/WebRanger/lib/IDS/Config/Sensor.ini.php");
			if($tmp_sensor['sensor_username'] !== "" || $tmp_sensor['sensor_password'] !== "")
			$this->wr_options['wr_status'] = 1;
		}

		add_option( 'wr_op_array', $this->wr_options);

		#Executing Capsulation
		#Assigning Appropriate Classes to Respective Objects
		$this->wr_view = new WebRanger_View();
		$this->wr_model = new WebRanger_Model();
	}

	public function generate_submenu()
	{
		$view_mode = 'generate_wr_page';
		if($this->wr_options['wr_status'] === 1)
			$view_mode = 'generate_wr_activated_page';

		$page_hook_suffix = add_submenu_page(
			'options-general.php',
			'WebRanger',
			'WebRanger',
			'administrator',
			'wr_contents',
			array($this->wr_view,$view_mode)
			);

		 add_action('admin_print_scripts-'.$page_hook_suffix,array($this,'load_admin_scripts'));
	}

	public function load_admin_scripts()
	{
		wp_enqueue_script("jquery");

		if($this->wr_options['wr_status'] === 0)
		wp_enqueue_script('wr-script');
		elseif($this->wr_options['wr_status'] === 1)
		wp_enqueue_script('wr-script-activated');

			
		wp_enqueue_style( 'wr-style' );
	}
}



#Hook Settings Page
$wr_obj = new WebRanger();
add_action('init', array($wr_obj,'execute_scanner'));

if(is_admin())
{
	register_activation_hook(__FILE__,array($wr_obj,'check_requirements'));
	register_deactivation_hook(__FILE__,array($wr_obj,'clear_wr_options'));
	$wr_obj->include_files();
	$wr_obj->set_options();
	$wr_obj->register_hooks(); #Begin Execution
}

?>
