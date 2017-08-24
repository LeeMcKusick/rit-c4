<?php
session_start();

if (!isset($_SESSION['token'])) header("location: index.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Lobby .:C4:.</title>
<script type="text/javascript" src="js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/jquery.scrollTo-1.4.1-min.js"></script>
<script type="text/javascript" src="js/c4.js"></script>
<script type="text/javascript" src="js/system.js"></script>
<script type="text/javascript">
name = "<?php echo $_SESSION['name']; ?>";
</script>
<style type="text/css">
	
	body, ul, li, button, h3 {
		padding: 0;
		margin: 0;
	}

	body {
		background: #3a3a3a;	
		color: white;
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
	#chatArea {
		padding: 20px;
		border: 2px solid #111;
		background: #444 url('images/chatBG3.png') repeat-y;
		color: #eee;
		width: 375px;
		height: 300px;
		overflow:auto;
		font-size: smaller;
		font-family: Helvetica, Arial, sans-serif;
		text-shadow: #222 1px 1px 2px;
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
	}
	#chat {
		position: absolute;
		top: 125px;
		left: 50px;
	}
	#userInfo {
		position: absolute;
		top: 125px;
		left: 500px;
	}
	.chatInfo {
		color: #ccc;
	}

	#onlineUsers, #challenges, #openGames {
		list-style-type: none;
	}
	li.user, li.challenge {
		background: #ddd url('images/liBG.png');
		width: 280px;
		margin-left: 10px;
		line-height: 20px;
		border: 1px solid black;
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
		font-family: 'Helvetica', 'Arial', sans-serif;
		box-shadow: 1px 1px 3px #000;
		padding: 0px 10px;
		color: #e7e7e7;
		text-shadow: #000 1px 1px 2px;
		text-decoration: none;
	}
	li.user {
		font-size: smaller;
		line-height: 25px;
	}
	li.challenge {
		font-size: smaller;
	}
	ul a {
		text-decoration: none;
	}

	button {
		padding: 5px;
		margin-left: 5px;
		background: #fff;
		font-family: 'Marker Felt';
	}
	#welcomeMessage {
		font-family: 'Helvetica', 'Arial', sans-serif;
		font-size: 13px;
	}
	#logoutButton {
		color: #b00;
		font-size: smaller;
	}
	#message {
		position: absolute;
		top: 80px;
		left: 275px;
		text-align: center;
		background: black;
		color: white;
		border: 2px solid white;
		padding: 5px 15px;
		display: none;
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
	}
	#message a {
		color: green;
	}
	.accept {
		color: #5d5;
		text-decoration: underline;
		text-shadow: #000 1px 1px 2px;
	}
	.reject {
		color: #d55;
		text-decoration: underline;
	}
	
</style>
</head>

<body onload="init();" onbeforeunload="confirmExit(event);">
<div id="wrapper">
    <h1>.:C4:.</h1>
    <div id="welcomeMessage">
        <?php echo "Welcome, " . $_SESSION['name'] . "!"; ?>
		<a href="./index.php" id="logoutButton" onclick="System.logout(); return false;">Log Out</a>
    </div>
	<div id="message"></div>

	<div id="userInfo">
    	<h3>Online Users (W-L-T)</h3>
		<ul id="onlineUsers">
	
    	</ul>
		
		<h3>Challenges</h3>
		<ul id="challenges">
	
		</ul>
		<h3>Open Games</h3>
		<ul id="openGames">
		</ul>
		
	</div>
	<div id="chat">
		<form action="" onsubmit="return false;">
	    	<div id="chatArea">
    		
	    	</div>
	    	<br />
	    	<input type="text" name="chatEntry" id="chatEntryBox" size="45" />
	    	<button id="chatSend" onclick="System.chatSend();">Send</button>
		</form>
	
	</div>
	
    <div id="debug"></div>
    <div id="debug2"></div>
    <div id="debug3"></div>

</div>

</body>
</html>