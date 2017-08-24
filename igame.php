<?php
session_start();
require '../../../dbInfo.inc';

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
<meta name="viewport" content="height=device-height; initial-scale=1.0; minimum-scale=1.0; maximum-scale=1.0; user-scalable=no" />
<title>.:C4:.</title>
<script type="text/javascript" src="js/Objects/iCell.js"></script>
<script type="text/javascript" src="js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/jquery.scrollTo-1.4.1-min.js"></script>
<script type="text/javascript" src="js/iGameSystem.js"></script>
<script type="text/javascript" src="js/igame.js"></script>
<script type="text/javascript">
	var name = "<?php echo $_SESSION['name']; ?>";
	var gameID = <?php echo $_REQUEST['gameID']; ?>;
</script>

<style type="text/css">
	
	html, body, ul, li, button, h1, h3{
		margin: 0;
		padding: 0;	
	}
	body {
		background: #3a3a3a url('../images/lobbyBG.png') repeat-y;	
		width: 100%;
		height: 100%;
		color: white;
	}
	h1 {
		margin: 5px;
	}
	h1 a {
		color: white;
		text-decoration: none;
	}
	div {
		-webkit-transition: all 1s ease-out;
	}
	#chatArea {
		padding: 20px;
		border: 2px solid #111;
		background: #444 url('images/chatBG2.png');
		color: #eee;
		width: 436px;
		height: 170px;
		overflow:auto;
		font-size: smaller;
		font-family:Helvetica, Arial, sans-serif;
		text-shadow: #222 1px 1px 2px;
	}
	.chatInfo {
		color: #ccc;
	}
	#portrait {
		position: absolute;
		background: #333 url('images/portraitBG.png') repeat-y;
		min-height: 420px;
		width: 320px;

	}
	#landscape {
		background: #333;
		position: absolute;
		height: 320px;
	}
	.shown {
		top: 0;
		left: 0;
	}
	.hidden {
		top: 0;
		left: -500px;
	}
	#game {
		position: absolute;
		top: 135px;
		left: 0;
	}
	#playerList {
		list-style-type: none;
		position: absolute;
		top: 5px;
		left: 85px;
		border: 1px black solid;
		background: #eee;
		color: black;
	}

	.cell {
		fill: #444;
	}
	
	h3 {
		font-family: Helvetica, Arial, sans-serif;
		font-weight: normal;
		font-size: 17px;
		margin-bottom: 10px;
	}

	ul {
		margin-bottom: 25px;
	}

	#wrapper {
		width: 800px;
		height: 100%;
		position: absolute;
		top: 0;
		left: 0px;
		padding-left: 50px;
		background:  url('images/lobbyBG.png');
	}
	
	#userInfo {
		position: absolute;
		top: 150px;
		left: 500px;
	}

	button {
		padding: 5px;
		margin-left: 5px;
		background: #fff;
		font-family: 'Marker Felt';
	}

	#message {
		position: absolute;
		top: 100px;
		left: 250px;
		text-align: center;
		background: white;
		color: #333;
		border: 2px solid black;
		padding: 5px 15px;
		display: none;
	}

	.errorMessage {
		color: red;
		font-size: 26px;
		position: absolute;
		top: 75px;
		left: 10px;
	}
</style>
</head>

<body onload="gameInit();" onbeforeunload="confirmExit(event);">
<div id="portrait" class="shown">
	<h1><a href="./lobby.php">.:C4:.</a></h1>
     
	<div id="nyt" class="errorMessage">NOT YOUR TURN!</div>
	<div id="colFull" class="errorMessage">THAT COLUMN IS FULL!</div>
	<div id="winner" class="errorMessage"></div>
	
	<div id="game">
		<svg xmlns="http://www.w3.org/2000/svg" version="1.1"  width="600px" height="400px">
		</svg>
	</div>
	
	<div id="gameInfo">
    	<ul id="playerList">
			<li id="playerYou">You: <?php echo $_SESSION['name']; ?></li>
			<li id="playerOpp">Opponent: </li>
			<li id="playerWhoseTurn">Whose Turn: </li>
		</ul>
	
	</div>
</div>

<div id="landscape" class="hidden">
	<form action="" onsubmit="iGameSystem.chatSend(); return false;">
		<div id="chatArea"></div>
		<br />
		<input type="text" name="chatEntry" id="chatEntryBox" size="45" />
		<button id="chatSend" onclick="iGameSystem.chatSend();">Send</button>
	</form>
</div>


</body>
</html>