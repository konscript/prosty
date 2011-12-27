<?php
App::uses('AppHelper', 'View/Helper');

class UtilHelper extends AppHelper {
    function json_decode_if_json($str) {
    
    	// read as json
			$json = json_decode($str);		
			if ($json != null && json_last_error() === JSON_ERROR_NONE){
				$str = $json;
			}
			
			return "<pre>" . print_r($str, true) . "</pre>";
    }
}
?>
