<?php 
	switch ($data['type']){
			case 'add':
				$programID = checkInt($data['programID']);
				$json = checkString($data['json']);
				$groupID = checkInt($data['groupID']);
				$query = "INSERT INTO `assignments` (`Program_ID`, `Data`,`Modified`)
							VALUES ($programID, '$json',CURRENT_TIMESTAMP);
    
							SET @AssignID = @@IDENTITY;

							INSERT INTO `assignmentstatus` (`Students_ID`, `Assignments_ID`)
							SELECT `Accounts_ID`, @AssignID
							FROM `Students`
							WHERE `Groups_ID` = $groupID;
							INSERT INTO `assignmentstatus` (`Modified`)
							VALUES (CURRENT_TIMESTAMP);";
					
					runmultiquery($query);
					
				break;
				
			case 'list':
				$programID = checkInt($data['programID']);
				$query = "-- Список работ по предмету
						SELECT `ID`, `Data`
						FROM `assignments`
						WHERE `Program_ID` = $programID;";
				if ($result = $mysql->query($query)) {	
					$output = array();
					 while ($row = $result->fetch_row())  
					 {
						$output[] = array(
						'id' => $row[0],
						'text' => $row[1], 
						);
						
					};
					/* очищаем результирующий набор */
					$result->close();
					
				} else {throw403();} 	
				break;
				
			case 'toggle':
				$userID = checkInt($data['userID']);
				$asgID = checkInt($data['asgID']);
				$query = "--Изменить статус работы
							UPDATE `assignmentstatus`
							SET `Completed` = NOT `Completed`, `Modified` = CURRENT_TIMESTAMP
							WHERE `Students_ID` = $userID AND `Assignments_ID` = $asgID;";
						if(!($mysql->query($query))) 
							{throw403();} 
					
						else {
							$output = array('students_ID' => $userID, 'assignments_ID' => $asgID);
						};
				break;
				
				
			case 'modify':
				$json=checkString($data['json']);
				$asgID = checkInt($data['asgID']);
				$query = "-- Редактирование
						UPDATE `assignments`
						SET `Data` = '$json',`Modified` = CURRENT_TIMESTAMP
						WHERE `ID` = $asgID";
						if(!($mysql->query($query))) 
							{throw403();} 
					
								else {
								$output = array('ID' => $asgID, 'json' => $json);
								};	
				break;
			case 'delete':
				$asgID = checkInt($data['asgID']);
				$query = "DELETE FROM `Assignments` WHERE `ID` = $asgID;
				INSERT INTO `dellog` (`Text`, `ID`) VALUES ('assignment', $asgID);";
					runmultiquery($query);
					
					$output = array('ID' =>$asgID);
							
					
				break;
				
			 
		}
		?>
