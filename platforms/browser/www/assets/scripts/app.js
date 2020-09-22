
// Declaro las variables globales
var isConnected = false;
var connectionStatusInterval = 10; // (in seconds)

/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
var app = {

	debug: false,
	isConnected: isConnected,
	connectionTimer: null,
	imgUrl: 'https://ssl.gstatic.com/gb/images/v1_76783e20.png?',

	// Bind Event Listeners
	// Bind any events that are required on startup. Common events are:
	// 'load', 'deviceready', 'offline', and 'online'.
	bindEvents: function () {

		console.log('App > bindEvents: Start');

		document.addEventListener('deviceready', this.onDeviceReady, false);

		// Detecto si estoy online u offline
		document.addEventListener("offline", app.checkConnection, false);
		document.addEventListener("online", app.checkConnection, false);
	},

	// Update DOM on a Received Event
	receivedEvent: function(id) {

		console.log('App > receivedEvent: Received Event: ' + id);
/*
		var parentElement = document.getElementById(id);
		var listeningElement = parentElement.querySelector('.listening');
		var receivedElement = parentElement.querySelector('.received');

		listeningElement.setAttribute('style', 'display:none;');
		receivedElement.setAttribute('style', 'display:block;');
*/
	},

	// deviceready Event Handler
	// Bind any cordova events here. Common events are:
	// 'pause', 'resume', etc.
	onDeviceReady: function() {

		console.log('App > onDeviceReady: Start');

		this.receivedEvent('deviceready');
		app.initialize();

		console.log('App > onDeviceReady: End');

	},

	// Application Constructor
	initialize: function() {

		console.log('App > initialize: Start');
		//document.addEventListener('deviceready', this.onDeviceReady.bind(this), false);

		// Detecto si estoy online u offline
		//document.addEventListener("offline", estoyConectado , false);
		//document.addEventListener("online", estoyConectado , false);

		app.checkConnection();

		console.log('App > initialize: End');

	},

	checkConnection: function(){

		//Hay una api interesate tambien en:
		//https://googlechrome.github.io/samples/network-information/

		console.log('App > checkConnection: start');

		console.log(navigator);

		if(app.connectionTimer !== null)
		{
			window.clearTimeout(app.connectionTimer);
		}

		if(app.debug)
		{

			app.isConnected = true;

		}else
		{

			// Navigator connection is a apache cordova plugin
			if(navigator.connection !== undefined)
			{
				//console.log(networkState = navigator.connection.type);
				//console.log(networkState = navigator.connection.effectiveType);

				try{
					var networkState = navigator.connection.type;
					var states = {};

					states[Connection.UNKNOWN]  = 'Unknown connection';
					states[Connection.ETHERNET] = 'Ethernet connection';
					states[Connection.WIFI]     = 'WiFi connection';
					states[Connection.CELL_2G]  = 'Cell 2G connection';
					states[Connection.CELL_3G]  = 'Cell 3G connection';
					states[Connection.CELL_4G]  = 'Cell 4G connection';
					states[Connection.NONE]     = 'No network connection';

					if(networkState == Connection.NONE){
						app.isConnected = false;
					}else{
						app.isConnected = true;
						console.log('App > checkConnection: detected from Cordova plugin');
					}

					console.log('App > checkConnection: ' + states[networkState]);

				}
				catch(err) {

					app.isConnected = false;
					//console.log('App > checkConnection: ' + err);
					console.log('App > checkConnection: ' + err.message + '. - isConnected: ' + app.isConnected);

				}

			// Else block with native but not so reliable method navigator.online (check with caniuse.com)
			} else if (typeof navigator === "object" && typeof navigator.onLine === "boolean")
			{

				app.isConnected = navigator.onLine;
				console.log('App > checkConnection: detected from Navigator Online');

			} else
			{

				//hack for older bizzare browsers
				var i = new Image();

				i.onerror = function () {
					console.log('checkConnection: Offline');

					app.showConnectionStatus();
					app.isConnected = false;

					//return app.isConnected;
				}

				i.onload = function () {

					app.showConnectionStatus();
					app.isConnected = true;

					console.log('App > checkConnection: detected from Google Image ping');

					//return app.isConnected;
				};

				i.src = app.imgUrl + new Date().getTime();
			}

		}

		app.showConnectionStatus();

		app.connectionTimer = window.setTimeout(app.checkConnection, parseInt(connectionStatusInterval * 1000) ); //10 seg

		console.log('App > checkConnection: isConnected = ' + app.isConnected);
		console.log('App > checkConnection: End');

		return app.isConnected;

	},

	showConnectionStatus: function(){

		console.log('App > showConnectionStatus: Start');

		var netstatus = $('.net-status');

		if(app.isConnected)
		{
			netstatus.addClass('online');
		}else
		{
			netstatus.removeClass('online');
		}

		console.log('App > showConnectionStatus: End');

	}

};

