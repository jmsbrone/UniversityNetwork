<?php

switch ($data['type']){
    case "listsub":
		$subjID=$data['subjectID'];
		$query = "-- Получение списка альбомов для предмета
						SELECT * FROM `albums` 
					WHERE `id` IN (SELECT `Albums_ID` FROM `SubjectAlbums` WHERE `Subjects_ID` LIKE $subjID;)";
		if ($result = $mysql->query($query)) {	
			$output = array();
			while ($row = $result->fetch_row())  {
				$output[] = array(
				'albumID' => $row[0], 
				'name' => $row[1]
				);						
			};		
					
		} 	
		else {
				throw403();
		} 
		break;  

}






?>
