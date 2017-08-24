	//////////////////////////////////////////////////////
	// Class: Piece										//
	// Description: Using the javascript prototype, you //
	// can make faux classes. This allows objects to be //
	// made which act like classes and can be referenced//
	// by the game.										//
	//////////////////////////////////////////////////////
	
	
	// Piece constructor
	// creates and initializes each Piece object
	function Piece(board,player,cellRow,cellCol,type,num){
		this.board = board;			// piece needs to know the svg board object so that it can be attached to it.
		this.player = player;		// piece needs to know what player it belongs to.
		this.type = type;			// piece needs to know what type of piece it is. (put in so it could be something besides a checker!)
		this.current_cell = boardArr[cellRow][cellCol];	// piece needs to know what its current cell/location is.
		this.number = num;			// piece needs to know what number piece it is.
		this.isCaptured = false;	// a boolean to know whether the piece has been captured yet or not.
		
		//id looks like 'piece_0|3' - for player 0, the third piece
		this.id = "piece_" + this.player + "|" + this.number;	// the piece also needs to know what it's id is.
		this.current_cell.isOccupied(this.id);			//set THIS board cell to occupied
		this.x=this.current_cell.getCenterX();						// the piece needs to know what its x location value is.
		this.y=this.current_cell.getCenterY();						// the piece needs to know what its y location value is as well.

		this.object = eval("new " + type + "(this)");	// based on the piece type, you need to create the more specific piece object (Checker, Pawn, Rook, etc.)
		this.piece = this.object.piece;					// a shortcut to the actual svg piece object
		this.setAtt("id",this.id);						// make sure the SVG object has the correct id value (make sure it can be dragged)
		if(this.player == playerId){
			this.piece.addEventListener('mousedown',function(){ setMove(this.id);},false);	// add a mousedown event listener to your piece so that it can be dragged.
		}else{
			this.piece.addEventListener('mousedown',nypwarning,false);	//tell the user that isn't his piece!
		}
		//this.piece.addEventListener('mousedown',function(){ document.getElementById('output2').firstChild.nodeValue=this.id;},false); 	//for testing purposes only...
		document.getElementsByTagName('svg')[0].appendChild(this.piece);

		// return this piece object
		return this;
	}
	
	// function that allows a quick setting of an attribute of the specific piece object
	Piece.prototype.setAtt = function(att,val) {
		this.piece.setAttributeNS(null,att,val);
	}
	
	//change cell (used to move the piece to a new cell and clear the old)
	Piece.prototype.changeCell = function(newCell,row,col){
		this.current_cell.notOccupied();
		document.getElementById('output').firstChild.nodeValue='dropped cell: '+newCell;
		this.current_cell = boardArr[row][col];
		this.current_cell.isOccupied(this.id);
	}
	
	//when called, will remove the piece from the document and then re-append it (put it on top!)
	Piece.prototype.putOnTop = function(){
		document.getElementsByTagName('svg')[0].removeChild(this.piece);
		document.getElementsByTagName('svg')[0].appendChild(this.piece);
	}
	
	// Checker constructor
	function Checker(parent) {
		this.parent = parent;		//I can now inherit from Piece class												// each Checker should know its parent piece object
		this.isKing = false;														// each Checker should know if its a 'King' or not (not a king on init)
		this.piece = document.createElementNS("http://www.w3.org/2000/svg","g");	// each Checker should have an SVG group to store its svg checker in
		if(this.parent.player == playerId){
			this.piece.setAttributeNS(null,"style","cursor: pointer;");						// change the cursor
		}
		this.piece.setAttributeNS(null,"transform","translate("+this.parent.x+","+this.parent.y+")");
		
		
		// create the svg 'checker' piece.
		var circ = document.createElementNS("http://www.w3.org/2000/svg","circle");
		circ.setAttributeNS(null,"r",'25');
		circ.setAttributeNS(null,"class",'player' + this.parent.player);					// change the color according to player
		this.piece.appendChild(circ);												// add the svg 'checker' to svg group
		//create more circles to prove I'm moving the group (and to make it purty)
		var circ = document.createElementNS("http://www.w3.org/2000/svg","circle");
		circ.setAttributeNS(null,"r",'18');
		circ.setAttributeNS(null,"fill",'white');
		circ.setAttributeNS(null,"opacity",'0.3');
		this.piece.appendChild(circ);
		var circ = document.createElementNS("http://www.w3.org/2000/svg","circle");
		circ.setAttributeNS(null,"r",'10');
		circ.setAttributeNS(null,"fill",'white');
		circ.setAttributeNS(null,"opacity",'0.3');
		this.piece.appendChild(circ);
		
		
		// return this object to be stored in a variable
		return this;
	}
	