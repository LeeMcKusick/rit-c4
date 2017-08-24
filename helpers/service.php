<?php
////////////////////////////////////
/*	login/getToken
	
	takes: username and password to check if a legal login
	returns: encoded value based upon the time in microseconds+userId to make sure it is unique
*/
//Returns a security token that ends up getting stored in a session varable 
function getToken($userName,$password){
	include "../../../../dbInfo.inc";
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or 
					  die('There was a problem connecting to the database.');
	// check for valid user id	& pass
	if ($stmt = $mysqli->prepare("SELECT userId from checkers_users where userName = ? and password = ?")) {
		$stmt->bind_param("ss", $userName, createHash($password, 'test'));
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($uid);
		$num = $stmt->num_rows;
		$stmt->close();
	}
	
	//if I am valid, set security token
	if ($num == 1) {
		$token = makeToken($uid);
		if ($stmt = $mysqli->prepare("UPDATE checkers_users SET securityToken = ? WHERE userName = ?")) {
			$stmt->bind_param("ss", $token, $userName);
			$stmt->execute();
			//$stmt->store_result();
			//$num = $stmt->num_rows;
			$stmt->close();
		} 
		
		//Send back good news
		return json_encode(array('token'=>$token, 'userName'=>$userName));
	}
	return json_encode(array('token'=>null, 'userName'=>null));

}

////////////////////////////////////
/*	makeToken
	
	takes: a string (we are using unique user id)
	returns: encoded value based upon the time in microseconds+the string to make sure it is unique
*/
function makeToken($user){
	//uniqid - Gets a prefixed unique identifier based on the current time in microseconds.
	return base64_encode(uniqid($user.'_', true));
}

function insertToken($user) {
	
}

////////////////////////////////////
/*	destroyToken
	
	takes: a security token
		-if that token exists in the db, clears it
	returns: none
*/
function destroyToken($secToken){
	include "../../../../dbInfo.inc";
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or 
					  die('There was a problem connecting to the database.');
	if($stmt = $mysqli->prepare("Update checkers_users set securityToken='' where securityToken=?")){
			$stmt->bind_param("s",$secToken);
			$stmt->execute();
			$stmt->close();	
	 }
}

////////////////////////////////////
/*	returnAssArray
	
	takes: prepared statement
		-parameters already bound
	returns: json encoded multi-dimensional associative array
*/
function returnAssArray ($stmt){
	$stmt->execute();
	$stmt->store_result();
 	$meta = $stmt->result_metadata();
    $bindVarsArray = array();
    
	//using the stmt, get it's metadata (so we can get the name of the name=val pair for the associate array)!
	while ($column = $meta->fetch_field()) {
    	$bindVarsArray[] = &$results[$column->name];
    }

	//bind it!
	call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

	//now, go through each row returned,
	while($stmt->fetch()) {
    	$clone = array();
        foreach ($results as $k => $v) {
        	$clone[$k] = $v;
        }
        $data[] = $clone;
    }
    return json_encode($data);
}

////////////////////////////////////
/*	createHash
	
	takes: text you want to encode, salt (if no salt specified, will create a random one), mode
		-with no salt specified, you can't replicate results
	returns: encoded string of $inText with a salt
	
	'borrowed' from Paul - http://www.php.net/manual/en/function.sha1.php
*/
function createHash($inText, $saltHash=NULL, $mode='sha1'){ 
	// hash the text // 
	$textHash = hash($mode, $inText); 
	// set where salt will appear in hash // 
	$saltStart = strlen($inText); 
	// if no salt given create random one // 
	if($saltHash == NULL) { 
		$saltHash = hash($mode, uniqid(rand(), true)); 
	} 
	// add salt into text hash at pass length position and hash it // 
	if($saltStart > 0 && $saltStart < strlen($saltHash)) { 
		$textHashStart = substr($textHash,0,$saltStart); 
		$textHashEnd = substr($textHash,$saltStart,strlen($saltHash)); 
		$outHash = hash($mode, $textHashEnd.$saltHash.$textHashStart); 
	} elseif($saltStart > (strlen($saltHash)-1)) { 
		$outHash = hash($mode, $textHash.$saltHash); 
	} else { 
		$outHash = hash($mode, $saltHash.$textHash); 
	} 
	// put salt at front of hash // 
	$output = $saltHash.$outHash; 
	return $output; 
} 

//echo createHash('dan', "7a9r171483223b32r2rb3210nxb0");
echo getToken($_GET['name'], $_GET['pass']);
?>