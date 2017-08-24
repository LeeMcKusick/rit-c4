<?php

class Filter {
	
	// Removes all whitespace from a string, including whitespace that isn't trailing or leading
	public function whitespace($str){
		return preg_replace('/\s\s+/',' ', $str);
	}

	// Removes characters not valid in an e-mail address
	public function email($email){
		return strtolower(preg_replace('/[^a-z0-9+_.@-]/i','',$email));
	}

	// Removes tags, whitespace
	public function text($str){
		// Ensure it's a string
		$str = strval($str);
		// We strip all html tags
		$str = strip_tags($str);
		// Remove any whitespace using
		// the define method above
		$str = $this->whitespace($str);
		return $str;
	}

	
}


?>