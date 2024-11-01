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
require_once((dirname(dirname(dirname(dirname(__FILE__))))).'/lib/IDS/Logger.php');
require_once((dirname(dirname(dirname(dirname(__FILE__))))).'/lib/IDS/Config/Credentials.php');

use IDS\Logger;
use IDS\Credentials;


class Controller_default extends CI_Controller 
{
	function index()
	{
		http_response_code(404);
		require_once((dirname(dirname(dirname(dirname(__FILE__))))).'/lib/IDS/err.php');
		die();
	}
}
?>
