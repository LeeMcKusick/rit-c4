var iGameSystem = {
	
	CHAT_FETCH_INTERVAL: 2000, 			//Time in milliseconds between fetch requests for chat
	//CHECK_USER_INTERVAL: 20000,			//Time in milliseconds between fetch requests for online users
	//CHECK_CHALLENGES_INTERVAL: 10000, 	//Time in milliseconds between fetch requests for challenges
	AJAX_URL: "ingame_ajax.php",		//URL for AJAX calls
	
	getPlayerInfo: function() {
		$.ajax( {
			url: this.AJAX_URL,
			data: "call=getplayerinfo&gameID=" + gameID,
			success: function( jsonText ) {
				if ( jsonText != 'null') {
					var obj = eval( jsonText );
					if (name == obj[0].redPlayer) {
						color = "Red";
						oppColor = "Black";
						opponent = obj[0].blackPlayer;
					} else {
						color = "Black";
						oppColor = "Red";
						opponent = obj[0].redPlayer;
					}
					whoseTurn = obj[0].whoseTurn; 
				
					$('#playerYou').html(color + ' Player: '+ name).css('color', color);
					$('#playerOpp').html(oppColor + ' Player: '+opponent).css("color", oppColor);
					$('#playerWhoseTurn').html('It is '+whoseTurn+'\'s turn...');
				}
			}
		});
	},
	
	getBoard: function() {
		$.ajax({
			url: this.AJAX_URL,
			data: "call=getboard&gameID=" + gameID,
			success: function( jsonText ) {
				if (jsonText != 'null') {
					var obj = eval( jsonText );
				
					for (var i = 0; i < obj.length; i++) {
						var cell = getCellInMem("cell_"+ obj[i].col + obj[i].row);
						if (cell.droppable == true) {
							var g = document.createElementNS(svgns, 'g');
							var cir = document.createElementNS(svgns, 'circle');
							cir.setAttributeNS(null, 'cx', cell.getCenterX() );
							cir.setAttributeNS(null, 'cy', cell.getCenterY() );
							cir.setAttributeNS(null, 'r', '20px');
							cir.setAttributeNS(null, 'stroke-width', '1px');
							
							if (obj[i].occupant == name) {
								cir.setAttributeNS(null, 'fill', color);
								cir.setAttributeNS(null, 'stroke', oppColor);
							} else {
								cir.setAttributeNS(null, 'fill', oppColor);
								cir.setAttributeNS(null, 'stroke', color);
							}
							g.appendChild(cir);
							document.getElementsByTagName('svg')[0].appendChild(g);
							try {
								var cellAbove = getNextCellInColumn(cell.id);
								cellAbove.droppable = true;
							} catch(e) {}
							cell.droppable = false;
						}
					}
				}
			}
		});
	},
	
	/**********
	* Checks the game to see whose turn it is, and if there is a winner or not
	*
	* Has a boolean value to determine whether to check again.
	* Value is set to false if it is not the player's turn, or if there is a winner.
	**********/
	checkStatus: function() {
		$.ajax( { 
			url: this.AJAX_URL,
			data: "call=checkstatus&gameID=" + gameID,
			success: function( jsonText ) {
				if (jsonText != 'null') { 
					var repeatLoop = true;
					var obj = eval( jsonText );
					
					if (obj[0].winner != null) {
						repeatLoop = false;
						$('#winner').hide().html(obj[0].winner + " wins!").fadeIn();
						$('#playerWhoseTurn').html('The game is over. Please close <br/>this window and return to the lobby.');
					}
				
					if (whoseTurn != obj[0].whoseTurn) {
						whoseTurn = obj[0].whoseTurn; 
						$('#playerWhoseTurn').html('It is ' + whoseTurn + '\'s turn...');
						iGameSystem.getBoard();
					}
				
					if (whoseTurn == name) {
						repeatLoop = false;
					}
				
					if(repeatLoop) {
						setTimeout("iGameSystem.checkStatus()", 2500);
					}
				}
			}
		});
	},
	
	playPiece: function (id) {
		if (whoseTurn == name) {
			//var cell = getCellInMem(id);
			//alert("ID: " + cell.id + " Droppable: " + cell.droppable);
			
			var firstDroppable = getFirstDroppableInColumn(id);
			if (firstDroppable) {
				$.ajax({
					url: this.AJAX_URL,
					data: "call=insertpiece&gameID=" + gameID + "&column=" + firstDroppable.col,
					success: function ( jsonText ) {
						if (jsonText == "Sent") iGameSystem.checkStatus();
						iGameSystem.getBoard();
					}
				});
			} else {
				alert ("Column is full");
			}
		} else {
			nyt();
		}
	},

	insertPiece: function(column) {
		if (whoseTurn == name) {
			//var column = 
			$.ajax({
				url: this.AJAX_URL,
				data: "call=insertpiece&gameID=" + gameID + "&column=" + column,
				success: function ( jsonText ) {
					if (jsonText == "Sent") iGameSystem.checkStatus();
					iGameSystem.getBoard();
				}
			});
		} else {
			nyt();
		}
	}, 
	
	chatSend: function () {
		var message = $('#chatEntryBox').val();
		if (message != '') {
			$.ajax( {
				url: this.AJAX_URL,
				data: 'call=chatsend&gameID=' + gameID + '&message='+message,
				success: function ( text ) {
					$('#chatEntryBox').val("");
				}
			});
		}
	},
	
	chatFetch: function () {
		$.ajax( {
			url: this.AJAX_URL,		//URL to send request to
			data: "call=getchat&gameID=" + gameID + "&lastFetch=" + lastChatFetch,	//Tell the PHP you want to get the latest chat.
			success: function( jsonText ) {
				
				//What did I get?
				//$('#debug').html(jsonText);
				
				//Did I get anything?
				if (jsonText != 'null') {
					
					//Create an object
					var obj = eval( jsonText );
					
					//Set up string to add to.
					var stuffForPage = '';
					
					for (i in obj) {
						//Build a date object from the microtime timestamp, get the time portion,
						//split it on the space, and grab the first piece of the array (The whole time)
						var time = new Date(obj[i].timestamp * 1000).toTimeString().split(" ")[0];
						
						spanEle = document.createElement('span');
						document.getElementById('chatArea').appendChild(spanEle);
						
						//Build the string
						stuffForPage = "<span class='chatInfo'>" + obj[i].name + " [" + time + "]: </span><span class='chatMessage'>" + obj[i].message + "</span><br />";
						
						//Display it
						$('#chatArea>span:last').hide().html(stuffForPage).fadeIn();						
						
						//Reset the lastChatFetch time (plus some, so it doesn't keep grabbing the last message)
						lastChatFetch = obj[i].timestamp + 0.1;
					}
					
					//Scroll to end of chatArea
					$('#chatArea').scrollTo(200000, 400, {axis:'y', easing: 'swing'});
				}
			}
		});
		
		//Check again in a bit
		setTimeout("iGameSystem.chatFetch()", this.CHAT_FETCH_INTERVAL);
		
				
	}
}