var xhtmlns = "http://www.w3.org/1999/xhtml";
var svgns = "http://www.w3.org/2000/svg";
var BOARDX = 0;				//starting pos of board
var BOARDY = 0;				//look above
var boardArr = new Array();		//2d array [row][col]
var BOARDWIDTH = 7;				//how many squares across
var BOARDHEIGHT = 6;			//how many squares down

function gameInit(){
	$('.errorMessage').hide();
	
	//create a parent to stick board in...
	var gEle=document.createElementNS(svgns,'g');
	gEle.setAttributeNS(null,'transform','translate('+BOARDX+','+BOARDY+')');
	gEle.setAttributeNS(null,'id','game_'+gameID);
	gEle.setAttributeNS(null,'stroke','black');
	gEle.setAttributeNS(null,'stroke-width','1px');
	//stick g on board
	document.getElementsByTagName('svg')[0].insertBefore(gEle,document.getElementsByTagName('svg')[0].childNodes[5]);
	//create the board...
	//var x = new Cell(document.getElementById('someIDsetByTheServer'),'cell_00',75,0,0);
	for(i=0;i<BOARDWIDTH;i++){	//i represents columns
		boardArr[i]=new Array();
		for(j=0;j<BOARDHEIGHT;j++){ //j represents rows
			boardArr[i][j]=new Cell(document.getElementById('game_'+gameID),'cell_'+i+j,50,i,j); 
		}
	}
	
	GameSystem.chatFetch();
	GameSystem.getPlayerInfo();	
	GameSystem.checkStatus();
	GameSystem.getBoard();
	
}
/*
function gameInit() {
	
	$('#onlineUsers').ajaxStart( function() { 
		$('#loadingGif').css('display', "");
	});
	$('#onlineUsers').ajaxStop( function() { 
		$('#loadingGif').css('display', "none");
		//setTimeout("$('#loadingGif').css('display', 'none');", 500);
	});
}
*/


//Returns the cell in memory when passed a cell ID
function getCellInMem(id) {
	return boardArr[parseInt(id.substr((id.search(/\_/)+1),1))][parseInt(id.substr((id.search(/\_/)+2),1))];
}

function getNextCellInColumn(id) {
	return boardArr[parseInt(id.substr((id.search(/\_/)+1),1))][ parseInt( id.substr( ( id.search(/\_/) +2 ) , 1) ) + 1 ];
}

function getFirstDroppableInColumn(id) {
	var columnNum = parseInt( id.substr( ( id.search(/\_/) + 1 ) , 1) )
	var rowNum = parseInt( id.substr( ( id.search(/\_/) + 2 ) , 1) )
	for (var i = 0; i < BOARDHEIGHT; i++) {
		var cell = boardArr[columnNum][i];
		if (cell.droppable == true) {
			return cell;
		}
	}
	return false;
}

//Logs the user out 
function confirmExit(event) {
	//System.logout(); 
	
	//Forfeit game
	
	//Kill game?
}

function nyt() {
	//Fade in the "Not your turn" error
	$('#nyt').fadeIn();
	//Fade it out after 3 seconds
	setTimeout("$('#nyt').fadeOut();", 3000);
}
