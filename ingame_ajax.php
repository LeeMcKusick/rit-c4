<?php
session_start();
require './dbInfo.inc';
include "helpers/commHelpers.php";
include "helpers/inputfilter.php";

class Ingame_Ajax {

	private $mysqli; 		//Mysqli Object
	//private $username;		//User's login username
	private $name;			//User's real name
	private $datatype = '';	//
	private $filter;
	private $gameID;
	
	function Ingame_Ajax($call, $gameID) {
		
		//Create a new filter object
		$this->filter = new InputFilter();
		$call = $this->filter->process($call);
		$game = $this->filter->process($gameID);
		
		//Open a new mysqli connection
		$this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or 
					  die('There was a problem connecting to the database.');
		
		
		
		//If the user is logged in, set up name and gameID variables
		if (isset($_SESSION['token'])) {
			$this->name = $this->get_Realname_From_Token($_SESSION['token']);
			$this->gameID = intval($game);
		}
		
		if (!$this->is_User_In_This_Game()) {
			return false;
		}
		
		//Set up data string
		$dataToSend = "";
			//What do I do?
			switch($call) {
				case 'getplayerinfo':
					if (isset($_SESSION['token'])) {
						$dataToSend = $this->getPlayerInfo();
					}
					break;
				case 'checkstatus':
					if (isset($_SESSION['token'])) {
						$dataToSend = $this->checkStatus();
					}
					break;
				case 'insertpiece':
					if(!$this->thisUsersTurn()) break;
					if (isset($_SESSION['token'])) {
						$dataToSend = $this->insertPiece( intval($this->filter->process($_REQUEST['column']) ));
					}
					break;
				case 'getboard':
					if (isset($_SESSION['token'])) {
						$dataToSend = $this->getBoard();
					}
					break;
				case 'checkwin':
					if (isset($_SESSION['token'])) {
						$dataToSend = $this->checkWin();
					}
					break;
				case 'chatsend':
					if (isset($_SESSION['token'])) {
						if ($_REQUEST['message'] != '' && strlen($_REQUEST['message']) < 255) $dataToSend = $this->chatSend($this->filter->process($_REQUEST['message'])); 
					}
					break;
				case 'getchat':
					if (isset($_SESSION['token'])) $dataToSend = $this->getChat(floatval($this->filter->process($_REQUEST['lastFetch'])));
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
	
	//Opponent
	//Board status
	//
	function getPlayerInfo() {
		$query = "SELECT blackPlayer, redPlayer, whoseTurn 
					FROM ".SQL_PREFIX."games 
					WHERE gameID = ?";
					
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('i', $this->gameID);
			$data = returnAssArray($stmt);
			$stmt->close();

			return $data;
		}
	}
	
	function checkStatus() {
		$query = "SELECT whoseTurn, winner 
					FROM ".SQL_PREFIX."games 
					WHERE gameID = ?";
					
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('i', $this->gameID);
			$data = returnAssArray($stmt);
			$stmt->close();

			return $data;
		}	
	}
	
	function insertPiece($col) {
 		//Find the first empty row in the column.
		//This players turn?
		if(!$this->thisUsersTurn()) return "not your turn";
		//Select all rows in the given column, starting with row 0;
		$query = "SELECT row FROM ".SQL_PREFIX."boardPieces WHERE col = ? AND gameID = ? ORDER BY row ASC";

		if( $stmt = $this->mysqli->prepare($query) ) {
			
			$stmt->bind_param('ii', $col, $this->gameID);
			$stmt->execute();
			$stmt->bind_result($returnedRow);
			
			//return "test";

			$row = -1; //start at -1. If it doesn't find any rows for that column, when it adds 1 later, it will be at 0;
			
			while ($stmt->fetch()) {
				if ($returnedRow == 5) { 
					$stmt->close();
					return "full";
				}
				
				$row++;
			}
			
			$row++; //$row before this is the row with the piece in it. Add one to make it the next slot.
			$stmt->close();
			//return $row . ", " . $col;
			return $this->insert($row, $col);
			//$stmt->close();	
		} else {
			return "Fail";
		}
	}
	
	function insert($row, $col) {
		if($this->thisGameOver()) return "Game Over";
		//Insert the piece
		$query = "INSERT INTO ".SQL_PREFIX."boardPieces (gameID, row, col, occupant) VALUES (?,?,?,?)";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('iiis', $this->gameID, $row, $col, $this->name);
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
				$this->changeTurn();
				$this->checkWin();
				return "Sent";
				
			} else if ($this->mysqli->affected_rows == 0){
				$stmt->close();
				return "Error. Did not send.";
			} else {
				return "Boom.";
			}
			return "You fail at life";
		}
		return "Oops. You broke it.";
	}
	
	function getBoard() {
		
		$query = "SELECT row, col, occupant FROM ".SQL_PREFIX."boardPieces WHERE gameID = ? ORDER BY col, row";
		if ($stmt = $this->mysqli->prepare($query)){
			$stmt->bind_param('i', $this->gameID);
			$data = returnAssArray($stmt);
			$stmt->close();

			return $data;
		}
	}
	
	function changeTurn() {
		
		$query = "SELECT blackPlayer, redPlayer FROM ".SQL_PREFIX."games WHERE gameID = ? AND whoseTurn = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('is', $this->gameID, $this->name);
			$stmt->execute();
			$stmt->bind_result($black, $red);
			
			if($stmt->fetch()) {
				$stmt->close();
				$whoseTurn = ($this->name == $black) ? $red : $black;
				$query = "UPDATE ".SQL_PREFIX."games SET whoseturn = ? WHERE gameID = ? LIMIT 1";
				if($stmt2 = $this->mysqli->prepare($query)) {
					$stmt2->bind_param('si', $whoseTurn, $this->gameID);
					$stmt2->execute();
					
					if($this->mysqli->affected_rows == 1) {
						$stmt2->close();
						return "Turn changed";
					} else {
						$stmt2->close();
						return "Error. Not your turn.";
					}
				}
			} else {
				return "Error.";
			}
		}
	}
	
	function checkWin() {
		$query = "SELECT row, col, occupant FROM ".SQL_PREFIX."boardPieces WHERE gameID = ? ORDER BY col, row";
		if ($stmt = $this->mysqli->prepare($query)){
			$stmt->bind_param('i', $this->gameID);
			$stmt->execute();
		
			$stmt->bind_result($row, $col, $occ);
			
			$boardArr = array();
			$data = "";
			while ($stmt->fetch()){
				$boardArr[$col][$row] = $occ;
				$data .= $col . " " . $row . ", " . $occ . "\n";
			}
			$stmt->close();
			
			$data .= $this->rcount($boardArr);
			
			//return $data;
			//Check horizontal
			for ($i = 0; $i < 6; $i++){
				for($j = 0; $j < 4; $j++){
					//return $boardArr[$j+3][$i];
					if (isset($boardArr[$j][$i]) && $boardArr[$j][$i] == $boardArr[$j+1][$i] && $boardArr[$j][$i] == $boardArr[$j+2][$i] && $boardArr[$j][$i] == $boardArr[$j+3][$i]) {
						$data = $this->setWinner( $boardArr[$j][$i] );
					}
				}
			}
			
			//Check vertical
			for ($i = 0; $i < 7; $i++){
				for($j = 0; $j < 3; $j++){
					if (isset($boardArr[$i][$j]) && $boardArr[$i][$j] == $boardArr[$i][$j+1] && $boardArr[$i][$j] == $boardArr[$i][$j+2] && $boardArr[$i][$j] == $boardArr[$i][$j+3]) {
						$data = $this->setWinner( $boardArr[$i][$j] );
					}
				}
			}
			
			//Check diagonal
			for ($i = 0; $i < 7; $i++) {
				for ($j = 0; $j < 3; $j++) {
					if (isset($boardArr[$i][$j]) && $boardArr[$i][$j] == $boardArr[$i+1][$j+1] && $boardArr[$i][$j] == $boardArr[$i+2][$j+2] && $boardArr[$i][$j] == $boardArr[$i+3][$j+3]) {
						$data = $this->setWinner( $boardArr[$i][$j] );
					} else if (isset($boardArr[$i][$j]) && $boardArr[$i][$j] == $boardArr[$i-1][$j+1] && $boardArr[$i][$j] == $boardArr[$i-2][$j+2] && $boardArr[$i][$j] == $boardArr[$i-3][$j+3]) {
						$data = $this->setWinner( $boardArr[$i][$j] );
					}
				}
			}
			
			return $data;
			
		}
	}
	
	
	function setWinner($winner) {
		$query = "UPDATE ".SQL_PREFIX."games SET winner = ? WHERE gameID = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('si', $winner, $this->gameID);
			$stmt->execute();
			$stmt->close();
		}
		
		$query = "DELETE FROM ".SQL_PREFIX."challenges WHERE status = ? LIMIT 1";
		if ($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('i', $this->gameID);
			$stmt->execute();
			$stmt->close();
		}
		
		//Update winner's win count
		$query = "SELECT wins FROM ".SQL_PREFIX."users WHERE name = ?";
		if ($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('s', $winner);
			$stmt->execute();
			$stmt->bind_result($wins);
			if($stmt->fetch()) {
				$wins++;
			}
			$stmt->close();
		}
		$query = "UPDATE ".SQL_PREFIX."users SET wins = ? WHERE name = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('is', $wins, $winner);
			$stmt->execute();
			$stmt->close();
		}
		
		//Update loser's lose count
		//Who lost?
		$query = "SELECT blackPlayer, redPlayer FROM ".SQL_PREFIX."games WHERE gameID = ?";
		if ($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('i', $this->gameID);
			$stmt->execute();
			$stmt->bind_result($black, $red);
			if($stmt->fetch()) {
				if($black == $winner) $loser = $red;
				else $loser = $black;
			}
			$stmt->close();
		}
		//How many times has he lost?
		$query = "SELECT losses FROM ".SQL_PREFIX."users WHERE name = ?";
		if ($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('s', $loser);
			$stmt->execute();
			$stmt->bind_result($losses);
			if($stmt->fetch()) {
				$losses++;
			}
			$stmt->close();
		}
		$query = "UPDATE ".SQL_PREFIX."users SET losses = ? WHERE name = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('is', $losses, $loser);
			$stmt->execute();
			$stmt->close();
		}
		
	}
	
	
	/**********
		CHAT
	**********/
	
	//Inserts message into c4_chat table
	//Calculates timestamp useing microtime_milli function below
	function chatSend($m) {
		$query = "INSERT INTO ".SQL_PREFIX."gamechat 
				(gameID, name, message, timestamp) 
				VALUES (?,?,?,?)";
		if($stmt = $this->mysqli->prepare($query)) {
			$d = $this->microtime_milli();
			$stmt->bind_param('issd', $this->gameID, $_SESSION['name'], $m, $d);
			$stmt->execute();
		
			if($this->mysqli->affected_rows == 1) {
				$stmt->close();
				return "Sent";
			} else if ($this->mysqli->affected_rows != 0){
				$stmt->close();
				return "Error. Did not send.";
			}
		}
		return "Oops. You broke it.";
	}
	
	//Retrieves chat messages for specified game
	//Returns JSON encoded data of records including name, message, and timestamp
	function getChat($fetch) {
		$query = "SELECT name, message, timestamp 
					FROM ".SQL_PREFIX."gamechat 
					WHERE gameID = ? AND timestamp > ?
					ORDER BY timestamp ASC";
					
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('id', $this->gameID, $fetch);
			$data = returnAssArray($stmt);
			$stmt->close();

			return $data;
		}
	}
	
	
	
	/****************************
	
		HELPER FUNCTIONS
	
	****************************/
	
	//Returns microtime in seconds to two decimal places
	function microtime_milli() {
		list($usec, $sec) = explode(" ", microtime());
    	return ( ( (float)$usec) + ((float)$sec));
	} 
	
	//Recursively counts multidimensional arrays
	//From PHP.net, Written by michael.debyl@gmail.com
	function rcount ($array) {
	  $count = 0;
	  if (is_array($array)) {
	    foreach($array as $id=>$sub) {
	    if (!is_array($sub)) { $count++; }
	     else { $count = ($count + $this->rcount($sub)); }
	    }
	    return $count;
	  }
	  return false;
	}
	
	//Returns name from a passed security token
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
	
	//Checks to see if this user is part of this game. Returns true if yes, false if no.
	function is_User_In_This_Game() {
		//Returns the id of all the games this user is a part of
		$query = "SELECT gameID FROM ".SQL_PREFIX."games WHERE gameID = ? AND (redPlayer = ? OR blackPlayer = ?) LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('iss', $this->gameID, $this->name, $this->name);
			$stmt->execute();
			//$stmt->bind_result($id);
			
			if($stmt->fetch()) {
				$stmt->close();
				return true;
			} else {
				$stmt->close();
				return false;
			}
		}
	}
	
	function thisGameOver() {
		$query = "SELECT winner FROM ".SQL_PREFIX."games WHERE gameID = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('i', $this->gameID);
			$stmt->execute();
			$stmt->bind_result($winner);
			
			if($stmt->fetch()) {
				if ($winner != null) return true;
				else return false;
			}
		}	
	}
	
	function thisUsersTurn() {
		$query = "SELECT gameID FROM ".SQL_PREFIX."games WHERE gameID = ? AND whoseTurn = ? LIMIT 1";
		if($stmt = $this->mysqli->prepare($query)) {
			$stmt->bind_param('is', $this->gameID, $this->name);
			$stmt->execute();
			
			if($stmt->fetch()) {
				$stmt->close();
				return true;
			} else {
				$stmt->close();
				return false;
			}
		}
	}
}

//Make a new object, using the call parameter sent from the user
new Ingame_Ajax($_REQUEST['call'], $_REQUEST['gameID']);

?>
