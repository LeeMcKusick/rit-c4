<?php
session_start();
if (strpos($_SERVER['HTTP_USER_AGENT'],"iPhone"))  { header('location: i.php'); }

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>.:C4:. Log In</title>
<script type="text/javascript" src="js/jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/system.js"></script>
<script type="text/javascript" src="js/c4.js"></script>
<style type="text/css">
	body {
		background: #111;
	}
	h2 {
		margin: 0;
		padding: 0;
		font-family: Helvetica, Arial, sans-serif;
		font-weight: lighter;
	}
	#loginDiv {
		position: absolute;
		top: 75px;
		left: 100px;
	}
	.loginBox {
		background: white;
		margin: 0;
		padding: 15px;
		position: absolute;
		top: 30px;
		left: 0px;
		width: 225px;
		height: 300px;
		border: 1px solid #333;
	}
	.tab {
		margin: 0;
		position: absolute;
		top: 0px;
		height: 30px;
		width: 80px;
		background: #52955B;
		border-top: 1px solid #333;
		border-left: 1px solid #333;
		color: white;
		text-align: center;
	}
	.tab:hover {
		background: #52C55B;	
	}
	.tab span {
		position: relative;
		top: 5px;
		font-family: Helvetica, Arial, sans-serif;

	}
	#loginButton {
		left: 0px;
		border-top-left-radius: 7px;
	}
	#registerButton {
		left: 80px;	
		border-right: 1px solid #333;
		border-top-right-radius: 7px;

	}
	label {
		display: block;	
		font-size: smaller;
		color: #555;
		padding-top:10px;
	}
	#ie-warning {
		font-size: smaller;
		background: #333;
		border: 1px yellow solid;
		display: none;
	}
	#login, #register { 
		border-top-right-radius: 7px; 
		border-bottom-right-radius: 5px;
		border-bottom-left-radius: 5px;
	}
	
	<!--[if IE]>
	#ie-warning {
		display: block;
	}
	<![endif]-->
	

</style>
</head>

<body onload="loginInit();">
<div id="loginDiv">
	
	<div id="loginButton" class="tab" onclick="$('#register').fadeOut(); $('#login').fadeIn();">
    	<span>Log in</span>
    </div>
    <div id="registerButton" class="tab" onclick="$('#login').fadeOut(); $('#register').fadeIn();">
    	<span>Register</span>
    </div>
    <div id="login" class="loginBox">
    	<?php if (isset($_SESSION['name'])) echo "<span id='userLoggedIn'>" . $_SESSION['name'] . " is logged in. <a href='' onclick='System.logout(); return 				false;'>Logout?</a> or <a href='lobby.php'>Go to Lobby</a></span>"; ?>
        <h2>Log in to C4</h2>    
        <form id="loginForm" action="index.php" method="post" onsubmit="System.login(); return false;">
            <label for="username">Username: </label>
            <input type="text" id="userField" size="15" name="username" />
            <label for="password">Password: </label>
            <input type="password" id="passField" size="15" name="password" />
            <br /><br />
            <input type="submit" value="Log in" />    
        </form>
        <div id="loginResponse" class="errorMessage"></div>

	</div>
    
    <div id="register" class="loginBox">
    	<?php if (isset($_SESSION['name'])) echo "<span id='userLoggedIn'>" . $_SESSION['name'] . " is logged in.<br/> <a href='' onclick='System.logout(); return 				false;'>Logout?</a> or <a href='lobby.php'>Go to Lobby</a></span>"; ?>
        <h2>New? Register Here</h2>
        <form id="registerForm" action="" method="post" onsubmit="System.register(); return false;">
            <label for="newusername">Pick a unique login name: </label>
            <input type="text" id="regUser" size="15" name="newusername" />
            <label for="name">What name do you want to go by?</label>
            <input type="text" id="regRealName" size="15" name="name" />
            <label for="newpassword">Set a password: </label>
            <input type="password" id="regPass" size="15" name="newpassword" />
            <label for="confirm_pwd">Type it again, just to be sure: </label>
            <input type="password" id="regPassCon" size="15" name="confirm_pwd" /> 
            <br /><br />
            <input type="submit" value="Register" />
        </form>
        <div id="registerResponse" class="errorMessage"></div>

    </div>
	<div id="debug">
	
	</div>
	
	<div id="ie-warning">
		<p>Internet Explorer users should download <a href="http://www.mozilla.com/firefox">Mozilla Firefox</a> or another compatibile browser.</p>
	</div>
</div>

</body>
</html>
