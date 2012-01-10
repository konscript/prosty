<?php

class UtilHelper extends AppHelper {

	function resolveFilesView($classname, $header, $files = array()){	
		$filesStr = "";
	
		foreach($files as $id=>$filename){
			$filesStr .= '<p class="file"><input type="checkbox" data-filename="'.$filename.'" id="'. $filename .'" /> <label for="'. $filename .'"> '. $filename .' </label> </p>';
		}

		return 
			'<div class="'.$classname.'">
				<p class="header">'.$header.':</p>			
				<div class="files">'.$filesStr.'</div>
			</div>';
	}

}

?>

