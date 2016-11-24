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
	case "listlesson":
		$query = "-- SELECT * FROM `albums` 
					WHERE `id` IN (SELECT `albums_id` FROM `subjectAlbums` WHERE `subjects_id` = $subjectID) 
					OR `id` IN (SELECT `albums_id` FROM `albumClass`
					WHERE `classes_id` 
					IN (SELECT `classes`.`id` FROM `classes` 
					INNER JOIN `classRules` ON `classes`.`rules_id` = `classRules`.`id` 
					WHERE `classRules`.`subjects_id` = $subjectID));";
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
	case "list":
		$albID = $data['albumID'];
		
		$query = "-- Получение списка всех документов альбома
						SELECT `Uploads_ID` FROM `AlbumFiles` WHERE `Albums_ID` LIKE $albID;";
		if ($result = $mysql->query($query)) {	
			$outputing = array();
			while ($row = $result->fetch_row())  {
				$outputing[] = array(
				'uplID' => $row[0], 
				);
			};					
		} 	
		else {
				throw403();
		}
		$documents = array();
		$images = array();
		$archives = array();
		for ($i = 0; $i < count($outputing); $i = $i + 1) {
			query = "-- SELECT `FileType`,`FileSize`,`FileName`,`FileExtension` FROM `Uploads` WHERE `ID` LIKE $outputing[i]['uplID'];";
			
			if ($result1 = $mysql->query($query)) {	
				$result = array();
				while ($row = $result1->fetch_row())  {
					$result[] = array(
					'FileType' => $row[0], 
					'FileSize' => $row[1], 
					'FileName' => $row[2], 
					'FileExtension' => $row[3]
					);
				};
				switch ($result[i]['FileType']){
					case "archives":	
						$archives[] = array(
						'uplID' => $outputing[i]['uplID'],
						'FileSize' => $result[i]['FileType'],
						'FileName' =>$result[i]['FileName'],
						'FileExtension' => $result[i]['FileExtension']
						)
					break;
					case "images":
						$images[] = array(
						'uplID' => $outputing[i]['uplID'],
						'FileSize' => $result[i]['FileType'],
						'FileName' =>$result[i]['FileName'],
						'FileExtension' => $result[i]['FileExtension']
						);
					break;
					case "documents":
						$documents[] = array(
						'uplID' => $outputing[i]['uplID'],
						'FileSize' => $result[i]['FileType'],
						'FileName' =>$result[i]['FileName'],
						'FileExtension' => $result[i]['FileExtension']
						);
					break;
				}
			}
		else 
		{
				throw403();
		} 
		$output = array ('archives' => $archives, 'documents' => $documents, 'images' => $images) 
   	}
	break;
		

}






?>
