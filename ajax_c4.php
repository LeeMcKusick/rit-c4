<?php
session_start();
require "./dbInfo.inc";
include "helpers/commHelpers.php";
include "helpers/inputfilter.php";

class Ajax_C4 {

	private $mysqli; 		//Mysqli Object
	private $username;		//User's login username
	private $name;			//User's real name
	private $datatype = '';	//
	private $filter;
	
	function Ajax_C4($call) {
		//Create a new filter object
		$this->filter = new InputFilter();
		$call = $this->filter->process($call);
		
		//Open a new mysqli connection
		$this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or 
					  die('There was a problem connecting to the database.');
		
		//If the user is logged in, set up a username variable
		if (isset($_SESSION['token'])) $this->username = $this->get_Username_From_Token($_SESSION['token']);
		if (isset($_SESSION['token'])) $this->name = $this->get_Realname_From_Token($_SESSION['token']);
		
		//Set up data string
		$dataToSend = "";
		
		//What do I do?
		switch($call) {
			case 'login': 
				if( $_POST && !empty($_POST['username']) && !empty($_POST['pwd']) ) 
					$dataToSend = $this->login($this->filter->process($_POST['username']), $this->filter->process($_POST['pwd']));
				break;
			case 'register': 
				if( $_POST && !empty($_POST['username']) && !empty($_POST['pwd']) ) 
					$dataToSend = $this->register($this->filter->process($_POST['username']), $this->filter->process($_POST['pwd']), $this->filter->process($_POST['name']));
				break;
			case 'getonlineusers':
				if (isset($_SESSION['token'])) $dataToSend = $this->getOnlineUsers();
				break;
			case 'logout':
				if (isset($_SESSION['token'])) $dataToSend = $this->logout();
				break;
			case 'chatsend':
				if (isset($_SESSION['token'])) {
					if ($_REQUEST['message'] != '' && strlen($_REQUEST['message']) < 255) $dataToSend = $this->chatSend($this->filter->process($_REQUEST['message'])); 
				}
				break;
			case 'getchat':
				if (isset($_SESSION['token'])) $dataToSend = $this->getChat();
				break;
			case 'challenge': 
				if (isset($_SESSION['token'])) $dataToSend = $this->challenge($this->filter->process($_REQUEST['challengee']));
				break;
			case 'getchallenges':
				if (isset($_SESSION['token'])) $dataToSend = $this->getChallenges();
				break;
			case 'acceptchallenge':
				if (isset($_SESSION['token'])) $dataToSend = $this->acceptChallenge($this->filter->process($_REQUEST['challenger']));
				break;
			case 'rejectchallenge':
				if (isset($_SESSION['token'])) $dataToSend = $this->rejectChallenge($this->filter->process($_REQUEST['challenger']));
				break;
			case 'deletechallenge':
				if (isset($_SESSION['token'])) $dataToSend = $this->deleteChallenge($this->filter->process($_REQUEST['challengee']));
				break;
		}
		
		//Close the mysqli connection
		$this->mysqli->close();
		
		//Create header info
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Content-Type:text/plain");
		
		//Send data
		echo $dataToSend;
	}
	
	
	
	
	
	/****************************
	
			FUNCTIONS
	
	****************************/

	//Register user
	//Accepts a username and password
	function register($usr, $pwd, $name) {
		$query = "INSERT INTO ".SQL_PREFIX."users
				(username, password, name) 
				VALUES (?,?,?)";

		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('sss', $usr, $this->createHash($pwd, '0B1'), $name);
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {				
				$stmt->close();
				return "User added";
			} else { 
				$stmt->close();
				return "User already exists. Please choose another username."; 
			}	
		}
	}
	
	//Log user in
	//Accepts a username, and an MD5 encoded password
	//Checks database for matching record, and if it finds one, send back "Valid User"
	function login($usr, $pwd) {
		$query = "SELECT username, name
				FROM ".SQL_PREFIX."users
				WHERE username = ? AND password = ?
				LIMIT 1";

		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('ss', $usr, $this->createHash($pwd, '0B1'));
			$stmt->execute();
			$stmt->bind_result($un, $name);
			
			if ($stmt->fetch()) {
				$stmt->close();
				$_SESSION['token'] = $this->createToken($un);
				$_SESSION['name'] = $name;
				$this->change_User_Online($un, 0);
				return "Valid User";
			} else { 
				$stmt->close();
				return "Please enter a correct username and password"; 
			}	
		}
	}
	
	//Logs the current user out
	function logout() {
		if (isset($_SESSION['token'])) {
			$this->delete_This_Users_Challenges();
			$this->change_User_Online($this->username, -1);
			unset($_SESSION['token']);
			unset($_SESSION['name']);
			
			
			if (isset($_COOKIE[session_name()])) 
				setcookie(session_name(), '', time() - 1000);
			session_destroy();
			return "Logged Out";
		}
	}
	
	//Returns JSON encoded list of all users whose gameID == 0
	function getOnlineUsers() {
		$query = "SELECT name, wins, losses, ties
				FROM ".SQL_PREFIX."users 
				WHERE online=0";
					
		if($stmt = $this->mysqli->prepare($query)) {
			$data = returnAssArray($stmt);
			$stmt->close();
			return $data;
			
		}
	}
	
	//Inserts message into c4_chat table
	//Calculates timestamp useing microtime_milli function below
	function chatSend($m) {
		$query = "INSERT INTO ".SQL_PREFIX."chat 
				(name, message, timestamp) 
				VALUES (?,?,?)";
		if($stmt = $this->mysqli->prepare($query)) {
			$d = $this->microtime_milli();
			$stmt->bind_param('ssd', $_SESSION['name'], $m, $d);
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
				return "Sent";
			} else if ($this->mysqli->affected_rows != 0){
				$stmt->close();
				return "Error.";
			}
		}
		return "Error.";
	}
	
	
	//Retrieves chat messages created after the user last checked for new messages (stored in session)
	//If this is the first time the user checked, set the session variable
	//Returns JSON encoded data of records including username, message, and timestamp
	function getChat() {
		//Get time of last check.
		//If time is not set, assume this is the first time, and set a time stamp.
		if (!isset($_SESSION['lastPing'])) {
			$_SESSION['lastPing'] = $this->microtime_milli();
		}
		
		$pingTime = $_SESSION['lastPing'];
		$query = "SELECT name, message, timestamp 
					FROM ".SQL_PREFIX."chat 
					WHERE timestamp > ? 
					ORDER BY timestamp ASC";
					
		if($stmt = $this->mysqli->prepare($query)) {
			$_SESSION['lastPing'] = $this->microtime_milli();
			$stmt->bind_param('d', $pingTime);
			$data = returnAssArray($stmt);

			$stmt->close();

			return $data;
		}
	}
	
	//Inserts a challenge entry into the challenges table. Takes a challengee argument, and uses $this->username for challenger.
	//Returns "Challenge to __ confirmed." if successful, "You've already challenged this user" if unsuccessful
	function challenge($challengee) {
		if ($this->name == $challengee) {
			return "You can't challenge yourself!";
		} else {
			$query = "INSERT INTO ".SQL_PREFIX."challenges (challenger, challengee) VALUES (?,?)";
		
			if($stmt = $this->mysqli->prepare($query)) {
				$stmt->bind_param('ss', $_SESSION['name'], $challengee);
				$stmt->execute();
			
				if($this->mysqli->affected_rows == 1) {
					$stmt->close();
					return "Challenge to ".$challengee." confirmed.";
				} else {
					$stmt->close(); 
					return "You've already challenged this user.";	
				}
			}
		}
	}
	
	function getChallenges() {
		$query = "SELECT challenger, challengee, status 
					FROM ".SQL_PREFIX."challenges 
					WHERE challengee = ? OR challenger = ?";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('ss', $_SESSION['name'], $_SESSION['name']);
			$data = returnAssArray($stmt);

			$stmt->close();

			return $data;
		}
	}
	
	function acceptChallenge($challenger) {
		$query = "UPDATE ".SQL_PREFIX."challenges 
					SET status = ?
					WHERE challenger = ? AND challengee = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$gameID = $this->create_New_Game($challenger);
			$stmt->bind_param('iss', $gameID, $challenger, $_SESSION['name']);
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
				//$this->change_Users_GameID($this->username, $gameID);
				//$this->change_Users_GameID($challenger, $gameID);
				$clone = array();
				$clone['message'] = "Challenge from ".$challenger ." accepted.";
				$clone['gameID'] = $gameID;
				$data[] = $clone;
				return json_encode($data);
			} else {
				$stmt->close(); 
				return "Error";	
			}
		}
	}
	
	function rejectChallenge($challenger) {
		$query = "UPDATE ".SQL_PREFIX."challenges 
				SET status = -1 
				WHERE challenger = ? AND challengee = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('ss', $challenger, $_SESSION['name']);
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
				return "Challenge from ".$challenger." rejected.";
			} else {
				$stmt->close(); 
				return "Error";	
			}
		}
	}
	
	function deleteChallenge($challengee) {
		$query = "DELETE FROM ".SQL_PREFIX."challenges WHERE challenger = ? AND challengee = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('ss', $_SESSION['name'], $challengee );
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
				return "Success";
			} else {
				$stmt->close(); 
				return "Error";	
			}
		}
	}
	
	
	
	
	/****************************
	
			FUNCTIONS
	
	****************************/
	
	//Returns microtime in seconds to two decimal places
	function microtime_milli() {
		list($usec, $sec) = explode(" ", microtime());
    	return ( ( (float)$usec) + ((float)$sec));
	} 
	
	//Create a new game, and return the gameID
	function create_New_Game($other_guy) {
		$whoseTurn = (rand(0,1) == 1) ? $other_guy : $this->name;
		//return $whoseTurn;
		$query = "INSERT INTO ".SQL_PREFIX."games 
				(blackPlayer, redPlayer, whoseTurn, lastMove) 
				VALUES (?,?,?,?)";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('sssi', $other_guy ,$this->name, $whoseTurn, time());
			$stmt->execute();
		
			if($this->mysqli->affected_rows > 0) {
				$id = $this->mysqli->insert_id;
				$stmt->close();
				return $id;
			} else {
				$stmt->close(); 
				return "Error";	
			}
		}
	}
	
	
	//Function: change_User_Online
	//Accepts a username, and a new online value
	//Updates users online record in c4_users table
	function change_User_Online($username, $online) {
		$query = "UPDATE ".SQL_PREFIX."users 
				SET online = ?
				WHERE username = ?
				LIMIT 1";
				
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('is', $online, $username);
			$stmt->execute();
			
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
				return true;
			}
		}
	}
	
	//Returns a username from a passed security token
	function get_Username_From_Token($token) {
		$query = "SELECT username
				FROM ".SQL_PREFIX."users
				WHERE securityToken = ?
				LIMIT 1";

		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('s', $token);
			$stmt->execute();
			$stmt->bind_result($un);
			
			if ($stmt->fetch()) {
				$stmt->close();
				return $un;
			} else {
				$stmt->close();
				return "error";
			}	
		}
	}
	//Returns a username from a passed security token
	function get_Realname_From_Token($token) {
		$query = "SELECT name
				FROM ".SQL_PREFIX."users
				WHERE securityToken = ?
				LIMIT 1";

		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('s', $token);
			$stmt->execute();
			$stmt->bind_result($un);
			
			if ($stmt->fetch()) {
				$stmt->close();
				return $un;
			} else {
				$stmt->close();
				return "error";
			}	
		}
	}
	
	//Returns a username from a passed real name
	function get_Username_From_RealName($name) {
		$query = "SELECT username
				FROM ".SQL_PREFIX."users
				WHERE name = ?
				LIMIT 1";

		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('s', $name);
			$stmt->execute();
			$stmt->bind_result($un);
			
			if ($stmt->fetch()) {
				$stmt->close();
				return $un;
			} else {
				$stmt->close();
				return "error";
			}
		}
	}
	
	//Called on logout
	//Deletes from challenges table all challenges for this user, whether he is the challenger or challengee
	function delete_This_Users_Challenges() {
		$query = "DELETE FROM ".SQL_PREFIX."challenges WHERE (challenger = ? OR challengee = ?) AND status < 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('ss', $_SESSION['name'], $_SESSION['name'] );
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
			} else {
				$stmt->close(); 
			}
		}
	}
	
	
	function createToken($user) {
		$token = base64_encode(uniqid($user.'_', true));
		if ($stmt = $this->mysqli->prepare("UPDATE ".SQL_PREFIX."users SET securityToken = ? WHERE username = ? LIMIT 1")) {
			$stmt->bind_param("ss", $token, $user);
			$stmt->execute();
			//$stmt->store_result();
			//$num = $stmt->num_rows;
			$stmt->close();
			//echo "Bah";
			return $token;
		}
	}
	
	////////////////////////////////////
	/*	createHash
		
		takes: text you want to encode, salt (if no salt specified, will create a random one), mode
			-with no salt specified, you can't replicate results
		returns: encoded string of $inText with a salt
		
		'borrowed' from Paul - http://www.php.net/manual/en/function.sha1.php
	*/
	function createHash($inText, $saltHash=NULL, $mode='sha384'){ 
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
	
}

//Make a new object, using the call parameter sent from the user
new Ajax_C4($_REQUEST['call']);

?>
