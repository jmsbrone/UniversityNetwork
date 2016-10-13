<?php 
	
	switch ($data['type']){
			case 'add':
				$name = check_str($data['name']);		
					if(!($mysql->query("INSERT INTO `rooms` (`name`,`Modified`) VALUES ('$name',CURRENT_TIMESTAMP);"))) {
						throw403();
					} 
							else {
								$output = array('id' => $mysql -> insert_id, 'name' => $name);
							};
				break;
				
			case 'list':
				$query = "-- Получение списка аудиторий
						SELECT `ID`, `name` FROM `Rooms`;";
				if ($result = $mysql->query($query)) {	
					$output = array();
					 while ($row = $result->fetch_row()) {
						$output[] = array(
						'id' => $row[0],
						'name' => $row[1] 
						);
						
					 };
					 /* очищаем результирующий набор */
					$result->close();
					
				} 
				
				else {
					throw403();
				  } 	



			
				break;
			case 'modify':
				$new_name=check_str($data['name']);
				$ID = checkInt($data['roomID']);
				$query = "-- Изменение данных аудитории
						UPDATE `Rooms` 
						SET `name` = '$new_name',`Modified` = CURRENT_TIMESTAMP 
						WHERE `ID` = $ID;";			
						if(!($mysql->query($query))) {
								throw403();
						} 
					
								else {
									$output = array('ID' => $ID, 'name' => $new_name);
								};
						
				break;
			case 'delete':
				$ID = checkInt($data['roomID']);
				$query = "DELETE FROM `rooms` WHERE `rooms`.`ID` = $ID;
				INSERT INTO `dellog` (`Text`, `ID`) VALUES ('room', $ID);";
					RunMultiQuery($query);					
				break;
			 
		}
?>
