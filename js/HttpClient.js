  // the following may be changed by the calling script as in this example:
  //         [snip]
  //         var client = new HttpClient('POST',true,false,'XML');
  //         [snip]
  
function HttpClient(method,Async,xmlhttp,result) {
	// default HTTP request type ('GET', 'POST')
	this.requestType=method;
	// make async calls? (true, false)
	this.isAsync=Async;
	// variable to encapsulate this XMLHttpRequest instance (true, false)
	this.xmlhttp=xmlhttp;
	// looking for XML back, or Text? ('XML' or 'Text')
	this.requestResult=result;
}

//////////////////////////////////////////////////////////////////////
//  modifications to original code from the book "Understanding Ajax"
//  to remove calling-script-only concerns...
//  calling script must now define one functions:
//            function callback
//	and two optional functions:
//            function onSend
//            function onLoad
//
//  see below for proper function forms
//  
//  NOTE: you may want to strip comments from this file
//  before deploying to a production server with handheld clients
//  to reduce the filesize
// 
//////////////////////////////////////////////////////////////////////

HttpClient.prototype = {
	
	// create this function in the calling script...
	// it is to be called upon successful return from an async request...
    // use this to create a custom handler for the response to your request
	// (to handle what happens on the client-side when the response arrives back)
	callback:false,
	// example declaration in calling script:
	//          client.callback = function(result) { 
	//            document.getElementById('target').innerHTML = result; 
  //          }

	// create this function in the calling script...
	// it is called when the XMLHttpRequest is sent
	// and you use it to create a custom I-am-loading effect
  onSend:false,
	// example declaration:
	//          client.onSend = function() {
	//            document.getElementById('HttpClientStatus').style.display = 'block';
	//          }

	// create this function in the calling script...
	// it is called **before** your callback function (see above)
	// when readyState 4 has been reached
  onLoad:false,
	// example declaration:
	//          client.onLoad = function() {
	//            document.getElementById('HttpClientStatus').style.display = 'none';
	//          }

	// this is called when an http error happens
	// you could also move this to the calling script
	// and havbe it declare at run-time how to handle an error
	onError:function(error) {
		alert(error.message);
	},

	// method to initialize an xmlhttpclient...
	// if it ever gets any easier to create a new XMLHttpRequest object
	// most of the code for this function will go away 
	init:function() {
		try {
		    // if Mozilla / Safari / IE 7
		    this.xmlhttp = new XMLHttpRequest();
		} catch (e) {
			// must be IE
			var XMLHTTP_IDS = new Array(
			'MSXML2.XMLHTTP.5.0',
			'MSXML2.XMLHTTP.4.0',
			'MSXML2.XMLHTTP.3.0',
			'MSXML2.XMLHTTP',
			'Microsoft.XMLHTTP' );
			var success = false;
			for (var i=0;i < XMLHTTP_IDS.length && !success; i++) {
				try {
					this.xmlhttp = new ActiveXObject(XMLHTTP_IDS[i]);
					success = true;
				} catch (e) {}
			}
			if (!success) {
				throw new Error('Unable to create XMLHttpRequest.');
			}
		}
	},

	// method to make a page request
	// @param string url  The page to make the request too
	// @param string payload  What you are sending if this is a POST request
	makeRequest: function(url,payload) {
		if (!this.xmlhttp) {
			this.init();
		}
		this.xmlhttp.open(this.requestType,url,this.isAsync);

		// set onreadystatechange here since it will be reset after a completed call in Mozilla
		var self = this;
		this.xmlhttp.onreadystatechange = function() { self._readyStateChangeCallback(); }

		if(this.requestType=="POST"){
			this.xmlhttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded;");
		}
		this.xmlhttp.send(payload);

		if (!this.isAsync) {
			return this.xmlhttp.responseText;
		}
	},
	

	// internal method used to handle ready state changes
	_readyStateChangeCallback:function() {
		switch(this.xmlhttp.readyState) {
			case 2:
				if(this.onSend != false){
					this.onSend();
				}
				break;
			case 4:
				if(this.onLoad != false){
					this.onLoad();
				}
				if (this.xmlhttp.status == 200) {
					if(this.requestResult == 'XML'){
						this.callback(this.xmlhttp.responseXML);
					}else{
						this.callback(this.xmlhttp.responseText);
					}
				}
				else {
					this.onError(new Error('HTTP Error Making Request: ['+this.xmlhttp.status+'] '+this.xmlhttp.statusText));
				}
			break;
		}
	}
}
