<?php

class ResetPasswordComponent extends Component {

	// Generates a random password.
	// Returns array: 0 => hashed, 1 => cleartext
	function generateRandomPassword ($length = 10)
	{
	
	  // start with a blank password
	  $password = "";
	
	  // define possible characters - any character in this string can be
	  // picked for use in the password, so if you want to put vowels back in
	  // or add special characters such as exclamation marks, this is where
	  // you should do it
	  $possible = "012346789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	  // we refer to the length of $possible a few times, so let's grab it now
	  $maxlength = strlen($possible);
	
	  // check for length overflow and truncate if necessary
	  if ($length > $maxlength) {
	    $length = $maxlength;
	  }
	
	  // set up a counter for how many characters are in the password so far
	  $i = 0; 
	
	  // add random characters to $password until $length is reached
	  while ($i < $length) { 
	
	    // pick a random character from the possible ones
	    $char = substr($possible, mt_rand(0, $maxlength-1), 1);
	
	    // have we already used this character in $password?
	    if (!strstr($password, $char)) { 
	      // no, so it's OK to add it onto the end of whatever we've already got...
	      $password .= $char;
	      // ... and increase the counter by one
	      $i++;
	    }
	  }
		
		// hash the password (has to be saved in db)
		$hash = Security::hash($password, null, true);
		
	  // Return the password hashed, and in cleartext
	  return array($hash, $password);
	}
}
?>
