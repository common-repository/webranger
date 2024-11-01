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

class WebRanger_Packager
{
	public function unpack_package($file, $to)
	{
		$res = false;

		if(class_exists('ZipArchive'))
		{
			$res = $this->unpack_package_ZA($file,$to);
		}
		else
		{
			$res = $this->unpack_package_PCL($file, $to);
		}
		return $res;
	}


	public function unpack_package_ZA($file, $to)
	{
		$res = false;
		$zip = new \ZipArchive;

		if ($zip->open($file) !== TRUE) 
			return new WebRanger_Error('Fail in Opening Zip File'); #End Process	

		if($zip->extractTo($to) !== TRUE)
		{
			$zip->close();
			return new WebRanger_Error('Fail in Extracting Zip File'); #End Process	
		}
	    $zip->close();

		$res = true;
		return $res;
	}

	public function unpack_package_PCL($file, $to)
	{
		$res = false;

		require_once(__DIR__."/../External/pclzip/pclzip.lib.php");
		$archive = new \PclZip($file);

		if($archive->extract(PCLZIP_OPT_PATH, $to) == 0)
		{
			$res = new WebRanger_Error('PCLZIP Error: '.$archive->errorInfo(true));
		}
		else
		{
			$res = true;			
		}

		return $res;
	}
}//END BRACKET

?>