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

class UpdateClass
{
	public static function run()
	{
		try
		{
			$db = new SQLite3(dirname(__FILE__).'/lib/IDS/Database/sentry.db');
			
			$query = "select * from ip_block where status = 1";
			$result = $db->query($query);

			while($entry = $result->fetchArray())
			{
				$dateOfEntry = @strtotime($entry['dateActivation']);
				if(@strtotime(gmdate("Y-m-d H:i:s",time())) >= $dateOfEntry)
				{
					$query = "UPDATE ip_block SET status = ?, dateActivation = NULL where ip_address = ?";
					$prpstatement = $db->prepare($query);
					$prpstatement->bindValue(1, 3, SQLITE3_INTEGER);
					$prpstatement->bindValue(2, $entry['ip_address'], SQLITE3_INTEGER);
					$prpstatement->execute();	
				}
			}
		}
		catch(Exception $e)
		{

		}
	} 
}
?>
