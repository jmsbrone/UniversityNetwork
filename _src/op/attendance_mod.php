<?php 
	
	switch ($data['type']){
			case 'add':
				$Students_ID = checkInt($data['studentID']);
				$Classes_ID = checkInt($data['classID']);
				$status = checkString($data['status']);
				if ($status == 101) {
				$query = "-- Добавление пропуска
							INSERT INTO `studentattendancerecords` (`Students_ID`, `Classes_ID`,`Status`,`Modified`)
				VALUES ($Students_ID, $Classes_ID, ('прогул'),CURRENT_TIMESTAMP);";
				}
				if ($status == 102) {
				$query = "-- Добавление пропуска
							INSERT INTO `studentattendancerecords` (`Students_ID`, `Classes_ID`,`Status`,`Modified`)
				VALUES ($Students_ID, $Classes_ID, ('прогул, пропуск по уважительной'),CURRENT_TIMESTAMP);";
				}
				
					if($mysql->query($query)) 
							
							{$output = array('ID' => $mysql->insert_id, 'students_id' => $Students_ID,'classes_id'=>$Classes_ID,'status'=>$status);} 
							
					else 	{
							throw403();
							};
				break;	
			case 'list':
				$studentID = checkInt($data['studentID']);
				$query = "SELECT `Classes_ID`, `Status`
						FROM `StudentAttendanceRecords`
						WHERE `Students_ID` = $studentID;";
				if ($result = $mysql->query($query)) {	
					$output = array();
					 while ($row = $result->fetch_row())  
					 {
						$output[] = array(
						'classes_id' => $row[0], 
						'status' =>$row[1] 
						);
						
					};
					 /* очищаем результирующий набор */
					$result->close();
					
					} else {throw403();} 	
				break;							
				
			case 'delete':
				$noteID = checkInt($data['noteID']);
				$query = "DELETE FROM `StudentAttendanceRecords` WHERE `ID` = $noteID;
				INSERT INTO `dellog` (`Text`, `ID`) VALUES ('attendance', $noteID);";
					RunMultiQuery($query);
					$output = array('ID' =>$noteID);
				break;
				
				
			case 'grouplist':
				//$groupID = $data['Groups_ID'];
				$query = "-- Получение списка пропусков
							SELECT `ID`, `Students_ID`, `Classes_ID`, `Status`
							FROM `StudentAttendanceRecords`
							WHERE `Students_ID` IN (
							SELECT `Accounts_ID` FROM `Students` WHERE `Groups_ID` = $groupID);";
				if ($result = $mysql->query($query)) {	
					$output = array();
					 while ($row = $result->fetch_row())  
					 {
						$output[] = array(
						'id' => $row[0], 
						'students_ID' =>$row[1],
						'classes_ID' => $row[2],
						'status' => $row[3]
						);
						
					};
					 /* очищаем результирующий набор */
					$result->close();
					
					} else {throw403();} 	
				break;
				
		}
		?>
