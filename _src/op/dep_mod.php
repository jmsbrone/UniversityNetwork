<?php 	
	switch ($data['type']){
			case 'add':
				$name = check_str($data['name']);
				$query = "INSERT INTO `Departments` (`Name`)
				VALUES ('$name');";		
					if($mysql->query($query)) {
						$output = array('id' => $mysql->insert_id, 'name' => $name);
					} 
							
						else 	{
							throw403();
						};				
				break;
				
			case 'list':
				$query = "-- Получение списка аудиторий
						SELECT `ID`, `name` FROM `Departments`;";
				if ($result = $mysql->query($query)) {	
					$output = array();
					while ($row = $result->fetch_row())  {
						$output[] = array(
						'id' => $row[0],
						'name' => $row[1] 
						);						
					};		
					 /* очищаем результирующий набор */
					$result->close();
					
				} 	else {
						throw403();
					} 	



			
				break;
			case 'modify':
				$new_name=check_str($data['name']);
				$ID = checkInt($data['depID']);
				$query = "-- Изменение данных аудитории
						UPDATE `Departments` 
						SET `name` = '$new_name' , `Modified` = CURRENT_TIMESTAMP 
						WHERE `id` = $ID;";							
						if(!($mysql->query($query))) {								
							throw403();
						} 
				
							else {
								$output = array('id' => $ID, 'name' => $new_name);
						}						
				break;
				
				
			case 'delete':
				$ID = checkInt($data['depID']);
				$query = "DELETE FROM `departments` WHERE `departments`.`ID` = $ID;
				INSERT INTO `dellog` (`Text`, `ID`) VALUES ('dep', $ID);";
					runMultiQuery($query); 					
					$output = array('id' =>$ID);						
				break;
			 
	}
		?>
