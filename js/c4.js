function init() {
	$('#message').hide();
	/*
	$('#onlineUsers').ajaxStart( function() { 
		$('#loadingGif').css('display', "");
	});
	$('#onlineUsers').ajaxStop( function() { 
		$('#loadingGif').css('display', "none");
		//setTimeout("$('#loadingGif').css('display', 'none');", 500);
	});
	*/
	System.getOnlineUsers();
	System.chatFetch();
	System.getChallenges(true);
	
	
}

function confirmChallenge(name) {
	
	displayMessage("Challenging: " + name + " <a href='' onclick='System.challenge(\"" + name + "\"); return false;'>Confirm</a>");
}

function confirmExit(event) {
	System.logout(); 
}	

function loginInit() {
	$('#register').hide();	
}
 
function displayMessage(message) {
	$('#message').hide().html(message).fadeIn();
	setTimeout("$('#message').fadeOut()", 4000);
}