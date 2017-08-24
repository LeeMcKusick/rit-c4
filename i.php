<?php
session_start();
if (isset($_SESSION['token'])) header('Location: ilobby.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="height=device-height; initial-scale=1.0; minimum-scale=1.0; maximum-scale=1.0; user-scalable=no" />

<title>.:C4:. Log In</title>

<style type="text/css">
	body {
		background: #242a25 url('images/background.png') no-repeat;
		color: white;
	}
	h2 {
		margin: 0;
		padding: 0;
		font-family: 'Marker Felt';
		font-weight: lighter;
		color: white;
		text-shadow: #aaa 1px 1px 3px;
	}
	.loginBox {
		margin: 0;
		padding: 15px;
		position: absolute;
		top: 0px;
		left: 0px;
	}
	label {
		font-family: 'Marker Felt';
		display: block;	
		color: #ddd;
		padding-top:10px;
	}

</style>
</head>

<body>
<div id="loginDiv">
    <div id="login" class="loginBox">
    	<?php if (isset($_SESSION['name'])) echo "<span id='userLoggedIn'>" . $_SESSION['name'] . " is logged in. <a href='' onclick='iSystem.logout(); return 				false;'>Logout?</a> or <a href='ilobby.php'>Go to Lobby</a></span>"; ?>
        <h2>Log in to C4</h2>    
        <form id="loginForm" action="index.php" method="post" onsubmit="iSystem.login(); return false;">
            <label for="username">Username: </label>
            <input type="text" id="userField" size="15" name="username" />
            <label for="password">Password: </label>
            <input type="password" id="passField" size="15" name="password" />
            <br /><br />
            <input type="submit" value="Log in" />    
        </form>
        <div id="loginResponse" class="errorMessage"></div>

	</div>
</div>
<script type="text/javascript" src="js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/iSystem.js"></script>
</body>
</html>
