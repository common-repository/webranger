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
    
require_once (dirname(__FILE__).'/lib/IDS/Logger.php');
require_once (dirname(__FILE__).'/lib/IDS/Config/Credentials.php');
require_once (dirname(__FILE__).'/lib/IDS/Init.php');
require_once (dirname(__FILE__).'/lib/IDS/Monitor.php');
require_once (dirname(__FILE__).'/lib/IDS/Report.php');
require_once (dirname(__FILE__).'/lib/IDS/Converter.php');
require_once (dirname(__FILE__).'/lib/IDS/Event.php');
require_once (dirname(__FILE__).'/lib/IDS/Filter.php');
require_once (dirname(__FILE__).'/lib/IDS/Filter/Storage.php');
require_once (dirname(__FILE__).'/updater.php');

use IDS\Logger;
use IDS\Init;
use IDS\Monitor;
use IDS\Log\CompositeLogger;
use IDS\Log\FileLogger;
try 
{

    if(isset($_SERVER['HTTP_USER_AGENT']))
        if(strcmp($_SERVER['HTTP_USER_AGENT'],"webranger-agent") == 0)
        {
            $tmp_array['sentry_location'] = str_replace($_SERVER['DOCUMENT_ROOT'],"",dirname(__FILE__));
            echo json_encode($tmp_array);
            die();
        }
 
    $newLog = new Logger();
    UpdateClass::run();
    $newLog->isBlocked();

    #Nginx Specific
    if(function_exists('getallheaders'))
    {
        $headers = getallheaders();
    }
    else
    {
        $headers = ''; 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
    }

    $request = array(
        'HEADER' => $headers,
        'GET' => $_GET,
        'POST' => $_POST,
        'COOKIE' => $_COOKIE
    );
    
    $init = Init::init(dirname(__FILE__) . '/lib/IDS/Config/Config.ini.php');
    $init->config['General']['base_path'] = dirname(__FILE__) . '/lib/IDS/';
    $init->config['General']['use_base_path'] = true;
    $init->config['Caching']['caching'] = 'none';

    $ids = new Monitor($init);
    $result = $ids->run($request); 
    
    if (!$result->isEmpty()) { 
       $newLog->execute($result);
    }

} catch (\Exception $e) {
}

?>
