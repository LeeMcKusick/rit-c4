<?php
session_start();

if (!isset($_SESSION['token'])) header("location: i.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="height=device-height; initial-scale=1.0; minimum-scale=1.0; maximum-scale=1.0; user-scalable=no" />
<title>Lobby .:C4:.</title>
<script type="text/javascript" src="js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/jquery.scrollTo-1.4.1-min.js"></script>
<script type="text/javascript" src="js/iSystem.js"></script>
<script type="text/javascript">
	name = "<?php echo $_SESSION['name']; ?>";
	function init() {
		window.onorientationchange = doOrient;
		
		iSystem.getOnlineUsers();
		iSystem.chatFetch();
		iSystem.getChallenges(true);
	
		doOrient();
	}
	
	function doOrient() {
		setTimeout(function() { window.scrollTo(0, 1); }, 500);
		
		switch(window.orientation) {
			case 0:
				document.getElementById('landscape').setAttribute('class', 'hidden');
				document.getElementById('portrait').setAttribute('class', 'shown');
				break;
			case 90:
				horiz();
				break;
			case -90: 
				horiz();
				break;

		}
	}

	function horiz() {
		document.getElementById('portrait').setAttribute('class', 'hidden');
		document.getElementById('landscape').setAttribute('class', 'shown');
	}
	
	function confirmChallenge(name) {
		if(confirm('Challenge ' + name + '?')) iSystem.challenge(name);
	}
</script>

<style type="text/css">
	
	body, ul, li, button {
		padding: 0;
		margin: 0;
	}

	body {
		background: url('images/portraitBG.png');
		color: white;	
	}
	div {
		-webkit-transition: all 1s ease-out;
	}
	h3 {
		font-family: "Marker Felt";
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
		background: #333 url('images/portraitBG.png');
		min-height: 360px;
		width: 320px;

	}
	#landscape {
		background: #333;
		position: absolute;
		min-height: 320px;
	}
	.shown {
		top: 0;
		left: 0;
	}
	.hidden {
		top: 0;
		left: -500px;
	}
	#onlineUsers, #challenges, #openGames {
		list-style-type: none;
	}
	li.user, li.challenge {
		background: #ddd url('images/liBG.png');
		width: 280px;
		margin-left: 10px;
		height: 45px;
		line-height: 45px;
		border: 1px solid black;
		-webkit-border-radius: 10px;
		font-size: 18px;
		font-family: 'Helvetica';
		box-shadow: 1px 1px 3px #000;
		padding: 0px 10px;
		color: #e7e7e7;
		text-shadow: #000 1px 1px 2px;
	}
	li.user {
	}
	li.challenge {
	}
	li.challenge a {
		color: #0000dd;
		font-size: 22px;
		font-family: "Marker Felt";
	}
	#logoutButton {
		position: absolute;
		top: 5px;
		left: 240px;
		color: red;
		border: 1px #b00 solid;
		padding: 5px;
		background: black;
		
	}
	button {
		padding: 5px;
		margin-left: 5px;
		background: #fff;
		font-family: 'Marker Felt';
	}
	
</style>
</head>

<!--
	<body onload="init();" onbeforeunload="confirmExit(event);">
	-->
<body onload="init();">

<div id="landscape" class="hidden">

	<form action="" onsubmit="iSystem.chatSend(); return false;">
    	<div id="chatArea"></div>
    	<br />
	
    	<input type="text" name="chatEntry" id="chatEntryBox" size="65" />
    	<button id="chatSend">Send</button>
	</form>
	
</div>

<div id="portrait" class="shown">
	<div id="debug"></div>
    <div id="debug2"></div>
    <div id="debug3"></div>
	<h3>Challenges</h3>
	<ul id="challenges">
		
	</ul>
	<h3>Online Users</h3>
	<ul id="onlineUsers">
		
	</ul>
	<h3>Open Games</h3>
	<ul id="openGames">
		</ul>

	<a id="logoutButton" href="#" onclick="iSystem.logout(); return false;">Log Out</a>


</div>
</body>
</html>