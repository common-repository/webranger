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

class WebRanger_Prerequisite
{
	public function traversePrerequisites($webranger_home,$local_version_number,$configuration_file,$remote_version_control)
	{
		$res = false;

		if(!$this->checkVersion($local_version_number,$remote_version_control))
			return false;

		$res = true; 
		return $res;
	}

	private function checkVersion($local_version_number,$remote_version_number)
	{
		$res = false;

		if($local_version_number == $remote_version_number)
		{
			$res = true;
		}
		return $res;
	}
}
?>