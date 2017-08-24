var iSystem = {
	
	CHAT_FETCH_INTERVAL: 2100, 			//Time in milliseconds between fetch requests for chat
	CHECK_USER_INTERVAL: 6000,			//Time in milliseconds between fetch requests for online users
	CHECK_CHALLENGES_INTERVAL: 6000, 	//Time in milliseconds between fetch requests for challenges
	AJAX_URL: "ajax_c4.php",

	register: function() {
		if ( $('#regUser').val() != "" && $('#regPass').val() != "" && $('#regRealName').val() != "") {
			if ( $('#regPass').val() != $('#regPassCon').val() ) {
				$('#registerResponse').hide().html("Please make sure your passwords match.").fadeIn();
			} else {
				$.post( this.AJAX_URL,//Url
				   //Data
				   { 	call: "register",
						username: $('#regUser').val(),
						pwd: $('#regPass').val(),
						name: $('#regRealName').val()
				   },
				  //Success
				  function(response) {
					   $('#response').hide().html(response).fadeIn();
					   
						if (response == "User added") {
							$('#userField').val( $('#regUser').val() );
							$('#passField').val( $('#regPass').val() );
							iSystem.login();
						}
				   }
			);	
			}
		} else {
			$('#registerResponse').hide().html("Please fill out the entire form.").fadeIn();
		}
	},
	
	login: function() {
		var usr = $('#userField').val();
		var pas = $('#passField').val();
		
		if (usr != "" && $('#passField').val() != "") {
			//$('#debug').hide().html('Logging in...').fadeIn();
			$.post( this.AJAX_URL,//Url
				//Data
				{ 	call: "login",
					username: usr,
					pwd: pas
				},
				//Success
				function(response) {
					if (response == "Valid User") {
						window.location = './ilobby.php';
					} else {
						$('#loginResponse').hide().html(response).fadeIn();
					}
				}
			);
		}
	},
	
	logout: function() {
		$.post( 
			//POST URL
			this.AJAX_URL,
			//Data to send
			{ 
				call: "logout",
		   	},
			//Successful callback function
		  	function(response) {
				if (response == "Logged Out") {
					window.location = './index.php';
			   	} else {
					//$('#response').hide().html(response).fadeIn();
			   	}
		   	}
		);
	},
	
	getOnlineUsers: function() {
		$.ajax( {
			url: this.AJAX_URL,
			data: "call=getonlineusers",
			success: function( jsonText ) {
				//$('#debug2').html(jsonText);
				if (jsonText != 'null') {
					var obj = eval(jsonText);	
					var stuffForPage = '';
					var userList = document.getElementById('onlineUsers');
					userList.innerHTML = "";
					for (i in obj) {
						var li = document.createElement('li');
						li.setAttribute('class', 'user');
						li.setAttribute('onclick', 'confirmChallenge("' + obj[i].name + '")'  );
						li.appendChild(document.createTextNode(obj[i].name + ": " + obj[i].wins + "-" + obj[i].losses + "-" + obj[i].ties));		
						userList.appendChild(li);
						
						
						//stuffForPage += "<a href='#' onclick='confirmChallenge(event); return false;'>" + obj[i].name + "</a> " + obj[i].wins + "/" + obj[i].losses + "/" + obj[i].ties + "<br />";
					}
					//$('#onlineUsers').html(stuffForPage);
				}
			
			}
			
		});		
		
		
		//Do it again soon
		setTimeout("iSystem.getOnlineUsers()", this.CHECK_USER_INTERVAL);
	},
	
	chatSend: function () {
		var message = $('#chatEntryBox').val();
		//alert();
		if (message != '') {
			$.ajax( {
				url: this.AJAX_URL,
				data: 'call=chatsend&message='+message,
				success: function ( text ) {
					//$('#debug').html(text);	
					$('#chatEntryBox').val("");
				}
			
			});
		}
	},
	
	chatFetch: function () {
		$.ajax( {
			url: this.AJAX_URL,		//URL to send request to
			data: "call=getchat",	//Tell the PHP you want to get the latest chat.
			success: function( jsonText ) {
				
				//What did I get?
				//$('#debug').html(jsonText);
				
				//Did I get anything?
				if (jsonText != 'null') {
					
					//Create an object
					var obj = eval(jsonText);
					
					//Set up string to add to.
					var stuffForPage = '';
					
					for (i in obj) {
						//Build a date object from the microtime timestamp, get the time portion,
						//split it on the space, and grab the first piece of the array (The whole time)
						var time = new Date(obj[i].timestamp * 1000).toTimeString().split(" ")[0];
						
						//Debugging
						//$('#debug2').html("Last message: " + time);
						
						spanEle = document.createElement('span');
						document.getElementById('chatArea').appendChild(spanEle);
						
						//Build the string
						stuffForPage = "<span class='chatInfo'>" + obj[i].name + " [" + time + "]: </span><span class='chatMessage'>" + obj[i].message + "</span><br />";
						$('#chatArea>span:last').hide().html(stuffForPage).fadeIn();
					}
					
					//Append it to the end
					//$('#chatArea').html( $('#chatArea').html() + stuffForPage);
					
					//Scroll to end
					$('#chatArea').scrollTo(200000, 400, {axis:'y', easing: 'swing'});
				}
			}
		});
		
		//Check again in a bit
		setTimeout("iSystem.chatFetch()", this.CHAT_FETCH_INTERVAL);
		
				
	},
	
	challenge: function(challengee) {
		if(challengee == name) {
			alert("You can't challenge yourself!")
		} else {
			$.ajax( {
				url: this.AJAX_URL,
				data: "call=challenge&challengee=" + challengee, 
				success: function (response) {
					if (response == "You can't challenge yourself!") {
						alert(response);
					}
				}
			});
		}
	},
	
	getChallenges: function (loop) {
		$.ajax( {
			url: this.AJAX_URL,
			data: "call=getchallenges",
			success: function (jsonText) {
				$('#challenges').html('');
				$('#openGames').html('');
				if (jsonText != 'null') {
					//Create an object
					var obj = eval(jsonText);
					
					for (i in obj) {

						//If the challenge is for this user
						if (obj[i].challengee == name) {

							//Still pending
							if (obj[i].status == 0) {
								//Do you accept or reject the challenge?
								var li = document.createElement('li');
								li.setAttribute('class', 'challenge');
								var accept = document.createElement('a');
								accept.setAttribute('href', '');
								accept.setAttribute('class', 'accept');
								accept.setAttribute('onclick', 'iSystem.acceptChallenge(\"' + obj[i].challenger + '\"); return false;')
								accept.appendChild(document.createTextNode('Accept'));
								var reject = document.createElement('a');
								reject.setAttribute('href', '');
								reject.setAttribute('class', 'reject');
								reject.setAttribute('onclick', 'iSystem.rejectChallenge(\"' + obj[i].challenger + '\"); return false;')
								reject.appendChild(document.createTextNode('Reject'));
								li.appendChild(document.createTextNode(obj[i].challenger));
								li.appendChild(document.createTextNode(": "));
								li.appendChild(accept);
								li.appendChild(document.createTextNode(" "));
								li.appendChild(reject);

								document.getElementById('challenges').appendChild(li);
							} 

							//If a gameID has been assigned
							else if (obj[i].status > 0) {
								var li = document.createElement('li');
								li.setAttribute('class', 'challenge');
								var a = document.createElement('a');
								a.setAttribute('href', './igame.php?gameID=' + obj[i].status);
								a.setAttribute('target', '_blank');
								li.appendChild(document.createTextNode(obj[i].challenger));
								a.appendChild(li);
								document.getElementById('openGames').appendChild(a);
							}
						}


						//if the challenge was made BY this user
						//If it's still pending don't do anything
						else if (obj[i].challenger == name) {
							//Was it rejected?
							if (obj[i].status == -1) {
								//$('#debug').html(obj[i].challengee + " rejected your challenge.");
								System.deleteChallenge(obj[i].challengee);
							} else if (obj[i].status == 0) {
								var li = document.createElement('li');
								li.setAttribute('class', 'challenge');
								li.appendChild(document.createTextNode(obj[i].challengee + " - Pending..."));
								document.getElementById('challenges').appendChild(li);
							}
							//Was it accepted?
							else if (obj[i].status > 0) {
								var li = document.createElement('li');
								li.setAttribute('class', 'challenge');
								var a = document.createElement('a');
								a.setAttribute('href', './igame.php?gameID=' + obj[i].status );
								a.setAttribute('target', '_blank');
								li.appendChild(document.createTextNode(obj[i].challengee));
								a.appendChild(li);
								document.getElementById('openGames').appendChild(a);
								//System.deleteChallenge(obj[i].challengee);
							} 
						}
					}
				}
			}
		});
		if(loop) setTimeout("iSystem.getChallenges(" + loop + ")", this.CHECK_CHALLENGES_INTERVAL);

	},
	acceptChallenge: function (challenger) {
		$.ajax( {
			url: this.AJAX_URL,
			data: "call=acceptchallenge&challenger=" + challenger,
			success: function( jsonText ) {
				var obj = eval( jsonText );
				//$('#debug3').html(obj[0].message);
				window.open("./game.php?gameID=" + obj[0].gameID);
				iSystem.getChallenges(false);
			}
		});
	},
	
	rejectChallenge: function (challenger) {
		$.ajax( {
			url: this.AJAX_URL,
			data: "call=rejectchallenge&challenger=" + challenger,
			success: function( response ) {
				//$('#debug3').html(response);
				iSystem.getChallenges(false);
				
			}
		});
	},
	deleteChallenge: function (challengee) {
		$.ajax( {
			url: this.AJAX_URL,
			data: "call=deletechallenge&challengee=" + challengee,
			success: function( response ) {
								
			}
		});
	}
	

}