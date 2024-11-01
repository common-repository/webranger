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

class WebRanger_Filesystem
{
	public function ScanFileOwnership($cur_dir, $php_uid, $mode = 0)
	{
		$ownership_mismatch_list = array();

		if($mode == 1)
			if(fileowner($cur_dir) != $php_uid)
				array_push($ownership_mismatch_list,$cur_dir);

		$dir = new \DirectoryIterator($cur_dir);
		foreach ($dir as $fileinfo) 
		{
		    if (!$fileinfo->isDot()) //Determine if File != '.' or '..'
		    {
		    	//if($fileinfo->getOwner() != $GLOBALS['php_uid'] && $fileinfo->getGroup() !=  $GLOBALS['php_gid']) old
		    	if($fileinfo->getOwner() != $php_uid) //File Ownership Mismatch //might be updated for group reference
		        {
		        	array_push($ownership_mismatch_list,$cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename());
		        }
		        //chown($cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename(),33); //attemp to change ownership

		        if($fileinfo->isDir()) //item is Dir Type
		        {
		        	$this->ScanFileOwnership($cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename(), $php_uid);
		        }
		    }
		}
		return $ownership_mismatch_list;
	}

	public function ApplyFilePermissions($cur_dir,$mode = 0)
	{
		$permission_mismatch_list = array();

		#First Instance of the Recursive Function
		if($mode == 1) 
			if($this->getchmod($cur_dir) != 755)
				chmod($cur_dir,0755);

		#Main Recursive Function
		$dir = new \DirectoryIterator($cur_dir);
		foreach ($dir as $fileinfo) 
		{
		    if (!$fileinfo->isDot()) //Determine if File != '.' or '..'
		    {
		    	if($fileinfo->isFile()) //Item is File Type
		        {
		        	if($this->getchmod($cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename()) != 644)
		        		if(!(chmod($cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename(),0644)))
		        			array_push($permission_mismatch_list,$cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename()."<br>");
		        }

		        if($fileinfo->isDir()) //Item is Dir Type
		        {
		        	if($this->getchmod($cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename()) != 755)
		        		if(!(chmod($cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename(),0755)))
		        			array_push($permission_mismatch_list,$cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename()."<br>");
		        	$this->ApplyFilePermissions($cur_dir.DIRECTORY_SEPARATOR.$fileinfo->getFilename());
		        }
		    }
		}
		return $permission_mismatch_list;
	}

	public function DeleteDirectory($cur_dir)
	{
	    if(!file_exists($cur_dir)) {
	       return new WebRanger_Error("Path Does Not Exist");
	    }

	    $directoryIterator = new \DirectoryIterator($cur_dir);

	    foreach($directoryIterator as $fileInfo) 
	    {
	        $filePath = $fileInfo->getPathname();
	        if(!$fileInfo->isDot()) 
	        {
	            if($fileInfo->isFile()) 
	            {
	                unlink($filePath);
	            } 
	            elseif($fileInfo->isDir()) 
	            {
	                if($this->is_dir_empty($filePath)) 
	                {
	                    rmdir($filePath);
	                } 
	                else 
	                {
	                    $this->DeleteDirectory($filePath);
	                }
	            }
	        }
	    }
	    rmdir($cur_dir);
	    return true;
	}

	public function is_dir_empty($dir) 
	{
	  if (!is_readable($dir)) 
	  	return NULL; 
	  return (count(scandir($dir)) == 2);
	}

	public function getchmod($file) 
	{
		return substr( decoct( @fileperms( $file ) ), -3 );
	}

}//END BRACKET

?>