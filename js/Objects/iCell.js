//////////////////////////////////////////////////////
// Class: Cell										//
// Description:  This will create a cell object		// 
// (board square) that you can reference from the 	//
// game. 											//
// Arguments:										//
//		size - tell the object it's width & height	//
//		??
//		??
//		??
//		??
//////////////////////////////////////////////////////
	
	
// Cell constructor
function Cell(myParent,id,size,col,row) {
	this.parent = myParent;
	this.id = id;
	this.size = size;
	this.col = col;
	this.row = row;
	//initialize the other instance vars
	this.occupied = '';
	this.state = 'alive';
	this.x = this.col * this.size;
	this.y = Math.abs(BOARDHEIGHT - this.row -1) * this.size;
	//this.color = (((this.row+this.col)%2) == 0) ? 'black' : 'red'
	this.color = "white";
	//this.droppable = (((this.row+this.col)%2) == 0) ? true : false
	this.droppable = (this.row == 0) ? true : false;
	
	//create it...
	this.object = this.createIt();
	this.parent.appendChild(this.object);
	//$("#" + this.id).css('fill', '#DDD');
	this.myBBox = this.getMyBBox();
	
}

//////////////////////////////////////////////////////
// Cell : Methods									//
// Description:  All of the methods for the			// 
// Cell Class (remember WHY we want these to be		//
// seperate from the object constructor!)			//
//////////////////////////////////////////////////////
//create it...
Cell.prototype.createIt = function(){
	var rect = document.createElementNS(svgns,'rect');
	rect.setAttributeNS(null,'id',this.id);
	rect.setAttributeNS(null,'width',this.size+'px');
	rect.setAttributeNS(null,'height',this.size+'px');
	rect.setAttributeNS(null,'x',this.x+'px');
	rect.setAttributeNS(null,'y',this.y+'px');
	rect.setAttributeNS(null,'class','cell');
	rect.onclick = function(){
		if (name == whoseTurn) iGameSystem.playPiece(this.id);
		else nyt();
	};
	/*
	rect.onmouseover = function() {
		for (var i = 0; i < BOARDHEIGHT; i++) {
			
		}
	};
	rect.onmouseout = function() {
		
	};
	*/
	return rect;
}

//get my BBox
Cell.prototype.getMyBBox = function(){
	return this.object.getBBox();
}

//get my center x
Cell.prototype.getCenterX = function(){
	return (BOARDX + this.x + (this.size/2));
}

//get my center y
Cell.prototype.getCenterY = function(){
	return (BOARDY + this.y + (this.size/2));
}

//set me to occupied...
Cell.prototype.isOccupied = function(pieceId){
	this.occupied = pieceId;
	//for testing purposes only!
	this.changeFill('alert');
}

//set me to unoccupied...
Cell.prototype.notOccupied = function(){
	this.occupied = '';
	//for testing purposes only!
	this.changeFill(this.color);
}

//for testing purposes only!
//to 'see' if the current cell is being 'filled' correctly with the new piece!
Cell.prototype.changeFill=function(toWhat){
	document.getElementById(this.id).setAttributeNS(null,'class','cell_'+toWhat);
}