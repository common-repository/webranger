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

class WebRanger_Error
{
	public $wr_error_description;
	public $wr_error_no;

	public function __construct($error)
	{
		$this->wr_error_description = $error;
	}

	public function printError()
	{
		$error = "WebRanger Error: ". $this->wr_error_description;
		echo json_encode($error."\r\n");
	}
}
?>