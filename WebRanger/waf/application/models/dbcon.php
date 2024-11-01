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

class Dbcon extends CI_Model 
	{
		function __construct()
	    {
	        parent::__construct();
	    }

	    function getConnection()
	    {
	    	$db = new SQLite3(getcwd().'/../lib/IDS/Database/sentry.db');
	    	return $db;
	    }
	}
?>