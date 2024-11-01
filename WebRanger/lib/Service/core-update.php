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

require_once (dirname(__FILE__).'/../IDS/Update/WebRanger-Transport.php');
require_once (dirname(__FILE__).'/../IDS/Update/WebRanger-Error.php');
require_once (dirname(__FILE__).'/../IDS/Update/WebRanger-Prerequisite.php');
require_once (dirname(__FILE__).'/../IDS/Update/WebRanger-Filesystem.php');
require_once (dirname(__FILE__).'/../IDS/Update/WebRanger-Security.php');
require_once (dirname(__FILE__).'/../IDS/Update/WebRanger-Packager.php');

use IDS\WebRanger_Transport;
use IDS\WebRanger_Error;
use IDS\WebRanger_Filesystem;
use IDS\WebRanger_Prerequisite;
use IDS\WebRanger_Security;
use IDS\WebRanger_Packager;

#Sanitation
$safe_post = SanitizeInput($_POST);

#Setting up Configuration Variables
$webranger_home = dirname(dirname(dirname(__FILE__))); #Directory for Old WebRanger
$root_home = dirname($webranger_home);
$webranger_configuration_file = parse_ini_file($webranger_home."/lib/IDS/Config/Sensor.ini.php");
$local_version_control = parse_ini_file($webranger_home."/lib/IDS/Config/Version.ini.php");
$local_version_control = $local_version_control['version_control'];

#Load Models
$wr_checker = new WebRanger_Prerequisite();
$wr_fs = new WebRanger_Filesystem();
$wr_sec = new WebRanger_Security();
$wr_pk = new WebRanger_Packager();

if($webranger_configuration_file === false)
{
	$wr_error = new WebRanger_Error("Cannot Locate or Open Sensor Configuration File");
	$wr_error->printError();
	die();
}

#Security Mechanism
if($wr_sec->check_ip($_SERVER['REMOTE_ADDR'],explode(',',$webranger_configuration_file['allowed_ip_list'])) === false)
die();

if($webranger_configuration_file['sensor_username'] !== $safe_post['identifier'])
die();

#Get Service Version Number and Hash of Zip and Hashes
$remote_version_control = $safe_post['webranger_version_control'];
$zip_hash = $safe_post['zip_hash'];

#Check If Update is Needed
if($wr_checker->traversePrerequisites($webranger_home,$local_version_control,$webranger_configuration_file,$remote_version_control) === true)
{
	echo json_encode("No Updates Required");
	die();
}
else
{ 
	#Create Random Directory in Root Home
	$random_wr_str = substr(str_shuffle(MD5(microtime())), 0, 3);
	$random_wr_dir = "wr-tmp-".$random_wr_str;
	if(!mkdir($root_home."/".$random_wr_dir)) #Create Random Directory One Level Above
	{
		$wr_error = new WebRanger_Error("Cannot Create Temporary Directory");
		$wr_error->printError();
		die();
	}

	$random_wr_file = "wr-file-".$random_wr_str.".zip";
	$random_wr_file = $root_home."/".$random_wr_dir."/".$random_wr_file;

	#Download Client's WebRanger
	$wr_downloader = new WebRanger_Transport();
	$download_result = $wr_downloader->request($_POST['wr_url'],$random_wr_file,$local_version_control);
	
	if($download_result !== true)
	{
		$wr_fs->DeleteDirectory($root_home."/".$random_wr_dir);
		if(get_class($download_result) == "IDS\\WebRanger_Error")
		echo $download_result->printError(); 
		else
		echo "Unable to Determine Error for Downloading WebRanger";
		die();
	}
	
	if(!($zip_hash === md5_file($random_wr_file)))
	{
		$wr_fs->DeleteDirectory($root_home."/".$random_wr_dir);
		$wr_error = new WebRanger_Error("Zip File Does Not Match Hash");
		$wr_error->printError();
		die();
	}

	$unpack_result = $wr_pk->unpack_package($random_wr_file,$root_home."/".$random_wr_dir);

	if($unpack_result !== true)
	{
		$wr_fs->DeleteDirectory($root_home."/".$random_wr_dir);
		if(get_class($unpack_result) == "IDS\\WebRanger_Error")
		echo $unpack_result->printError();
		else
		echo "Unable to Determine Error for Extracting WebRanger";	
		die();
	}

	#Copy Configuration Files and Databases
	copy($webranger_home."/lib/IDS/Config/Config.ini.php",$root_home."/".$random_wr_dir."/lib/IDS/Config/Config.ini.php"); 
	copy($webranger_home."/lib/IDS/Config/Sensor.ini.php",$root_home."/".$random_wr_dir."/lib/IDS/Config/Sensor.ini.php"); 
	copy($webranger_home."/lib/IDS/Database/backlog.db",$root_home."/".$random_wr_dir."/lib/IDS/Database/backlog.db");
	copy($webranger_home."/lib/IDS/Database/sentry.db",$root_home."/".$random_wr_dir."/lib/IDS/Database/sentry.db");

	$file_ownership_mismatch = array();
	$file_permission_mismatch = array();

	if (function_exists('posix_geteuid'))
	{
		$php_uid = posix_geteuid();

		#Scan File Ownership
		$file_ownership_mismatch = $wr_fs->ScanFileOwnership($root_home."/".$random_wr_dir,$php_uid,1);
		if(sizeof($file_ownership_mismatch) !== 0) #Check if Ownership is Correct
		{
			$wr_fs->DeleteDirectory($root_home."/".$random_wr_dir);
			$wr_error = new WebRanger_Error("File Owneship Mismatch");
			$wr_error->printError();
			die();
		}
	}

	#Proceed with Scanning the Right File Permissions
	$file_permission_mismatch = $wr_fs->ApplyFilePermissions($root_home."/".$random_wr_dir, 1);
	if(sizeof($file_permission_mismatch) !== 0) #Check if Permission is Correct
	{
		$wr_fs->DeleteDirectory($root_home."/".$random_wr_dir);
		$wr_error = new WebRanger_Error("File Permission Mismatch");
		$wr_error->printError();
		die();
	}

	unlink($random_wr_file);
	$delete_denied = $wr_fs->DeleteDirectory($webranger_home);
	rename($root_home."/".$random_wr_dir,$root_home."/WebRanger");
	
	echo "Successful Update";
}

function SanitizeInput()
{
	$safePost = array();
	
	$safePost['zip_hash'] = (isset($_POST['zip_hash']) ? strlen($_POST['zip_hash']) <= 32 ? ctype_alnum($_POST['zip_hash']) ? $_POST['zip_hash'] : NULL : NULL : NULL);
	if(!isset($safePost['zip_hash']))
	die();

	$safePost['webranger_version_control'] = (isset($_POST['webranger_version_control']) ? strlen($_POST['webranger_version_control']) <= 10 ? preg_match('/^[0-9.]+$/',$_POST['webranger_version_control']) ? $_POST['webranger_version_control'] : NULL : NULL : NULL);
	if(!isset($safePost['webranger_version_control']))
	die();

	$safePost['identifier'] = (isset($_POST['identifier']) ? strlen($_POST['identifier']) <= 50 ? preg_match('/^[A-Za-z0-9_.-]+$/',$_POST['identifier'])? $_POST['identifier'] : NULL : NULL : NULL);
	if(!isset($safePost['identifier']))
	die();

	$safePost['wr_url'] = filter_var($_POST['wr_url'],FILTER_VALIDATE_URL); 
	if(!isset($safePost['wr_url']))
	die();  

	return $safePost;
}


?>