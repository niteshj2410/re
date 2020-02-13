<?php	
	$imgArr=array('png'=>'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=','gif'=>'R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
	$imgVal= array_rand($imgArr);
	$imgVal='gif';
	header('Content-Type: image/'.$imgVal.'');
	echo base64_decode($imgArr[$imgVal]);
?>