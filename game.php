<?php
session_start();
require './dbInfo.inc';

//Make sure a user is logged in.
if (!isset($_SESSION['token'])) { header("location: index.php"); }

//If there's no gameID, shove the user back to the lobby.
if (!isset($_REQUEST['gameID'])) { header("location: lobby.php"); }

//If there is a gameID being sent, make sure the user is part of that game
else {
	$mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or 
				  die('There was a problem connecting to the database.');
	
	//Check if this user is part of this game
	//Query returns all games this user is a part of
	$query = "SELECT gameID FROM ".SQL_PREFIX."games WHERE redPlayer = ? OR blackPlayer = ?";
	if($stmt = $mysqli->prepare($query)) {
		$stmt->bind_param('ss', $_SESSION['name'], $_SESSION['name']);
		$stmt->execute();
		$stmt->bind_result($id);
		$redirect = true;
		
		//Loop through all games, and if this one matches any of them,
		//Tell the server not to redirect back to the lobby
		while ($stmt->fetch()) {
			if ($id == $_REQUEST['gameID']) {
				$stmt->close();
				$redirect = false;
				break;					
			}
		}
		
		if ($redirect) {
			$stmt->close();
			header("location: lobby.php");
		} else {
		}
	}
}

//make sure the mine-type is SVG (xml), NOT html...
header('Content-type: application/xhtml+xml');
//HAVE TO echo this out - if not php short open tags will try to parse
echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>.:C4:.</title>
<script type="text/javascript" src="js/Objects/Cell.js"></script>
<script type="text/javascript" src="js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/jquery.scrollTo-1.4.1-min.js"></script>
<script type="text/javascript" src="js/gameSystem.js"></script>
<script type="text/javascript" src="js/game_c4.js"></script>
<script type="text/javascript">
	var name = "<?php echo $_SESSION['name']; ?>";
	var gameID = <?php echo $_REQUEST['gameID']; ?>;
	var lastChatFetch = 0;
	var opponent = "";
	var whoseTurn = "";
	var oppColor = "";
</script>
<link type="text/css" href="./css/styles.css" rel="stylesheet" />
</head>

<body onload="gameInit();" onbeforeunload="confirmExit(event);">
    <h1><a href="./lobby.php">.:C4:.</a></h1>
     
<div id="nyt" class="errorMessage">NOT YOUR TURN!</div>
<div id="colFull" class="errorMessage">THAT COLUMN IS FULL!</div>
<div id="winner" class="errorMessage"></div>
<div id="game">
<svg xmlns="http://www.w3.org/2000/svg" 
	version="1.1"  width="600px" height="400px">
</svg>
</div>
<div id="gameInfo">
    <ul id="playerList">
		<li id="playerYou">You: <?php echo $_SESSION['name']; ?></li>
		<li id="playerOpp">Opponent: </li>
		<li id="playerWhoseTurn">Whose Turn: </li>
	</ul>
	
</div>
	
	<div id="chat">
		<form action="" onsubmit="return false;">
			<div id="chatArea"></div>
			<br />
			<input type="text" name="chatEntry" id="chatEntryBox" size="45" />
			<button id="chatSend" onclick="GameSystem.chatSend();">Send</button>
		</form>
	</div>
	
	<div id="debug"></div>
</body>
</html>