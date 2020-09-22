/*
Ivano vardé
05/2018
*/

//VARIABLES
var ajaxSubmitResponse = new Array();
var ajaxRequestResponse = new Array();
var ajaxLoader = '';
var ajaxSubmitBtn = '';


/*****************************************************************/
/**
 * @param object obj
 * @author Ivano
 * @desc Devuelve la cantidad de items dentro de un objeto
 * @use Object.size(response.followers)
 */
Object.size = function(obj) {
	var size = 0, key;
	for (key in obj) {
		if (obj.hasOwnProperty(key)) size++;
	}
	return size;
};
/*****************************************************************/


/* FUNCTIONS */

$( document ).ajaxStart(function(event) {
	//console.log(event);
	ajaxLoader = $('.ajax-loader').addClass('open');

	if(ajaxSubmitBtn.length){
		ajaxSubmitBtn.attr('disabled', 'disabled').addClass('loading');
	}
});

$( document ).ajaxComplete(function( event, xhr, settings ) {
	//console.log(event);
	//console.log(xhr);
	//console.log(settings);

	var responseMode = 'responseJSON';

	ajaxLoader.removeClass('open');

	if(ajaxSubmitBtn.length){
		ajaxSubmitBtn.removeAttr('disabled').removeClass('loading');
	}

	switch(settings.dataType.toLocaleLowerCase()){
		case 'html':
			responseMode = 'responseText';
		break;
	}

	ajaxNotification(xhr[responseMode]);

});

function ajaxNotification(response){

	var ajaxStatus = $('.ajax-status');
	var ajaxStatusInner = ajaxStatus.find(' > .ajax-status-inner');
	var background = '';

	if(response !== undefined){
		if(response.msg !== undefined){
			background = 'bg-' + response.class;

			ajaxStatusInner.html(response.msg);
			ajaxStatus.addClass('open ' + background);

			window.setTimeout(function () {
				ajaxStatus.removeClass('open');

				window.setTimeout(function () {
					ajaxStatusInner.html('');
					ajaxStatus.removeClass(background);
				}, 1000);

			}, 6000);
		}
	}

	ajaxLoader.removeClass('open');
}

function updateTableListRow(response){
	if(response.status){
		if(response.html !== undefined && response.record_id !== undefined){
			$('#' + response.record_id).html($(response.html).find('td'));
		}
	}
}

function getRandomInt(min, max) {
	var mn = parseInt(min);
	var mx = parseInt(max);
	 return Math.floor(Math.random() * (mx - mn + 1)) + mn;
}

function showHideBackToTop(){
	var el = $('.back-to-top');
	var showPoint = 150;

	el[$(window).scrollTop() > parseInt(showPoint) ? 'addClass' : 'removeClass']('open');
}

function fadingElements(data){
	var type = data.type || 'show';

	var group1 = $('.group1');
	var group2 = $('.group2');
	var group3 = $('.group3');
	var group4 = $('.group4');

	var complete = function(el){

		el.addClass('revealed');

	};

	var show = function(){
		hide();

		window.setTimeout(function(){group1.fadeIn(1000, function(){complete($(this));} );}, 500);
		window.setTimeout(function(){group2.fadeIn(1500, function(){complete($(this));} );}, 1000);
		window.setTimeout(function(){group3.fadeIn(1500, function(){complete($(this));} );}, 1900);
		window.setTimeout(function(){group4.fadeIn(1500, function(){complete($(this));} );}, 2600);

	};
	var hide = function(){
		group1.hide();
		group2.hide();
		group3.hide();
		group4.hide();

		$('[class*="group"]').removeClass('revealed');
	};
	if(type == 'show'){
		show();
	}
	if(type == 'hide'){
		hide();
	}
}

function ivSendForm(data) {
	var el = data.el;
	var btn = data.btn !== undefined ? data.btn : el.find('[type="submit"]');
	var isAjax = (el.data('ajax') !== undefined ? el.data('ajax') : true);
	//var URL = '/' + (siteFolder ? siteFolder + '/' : '') +'php/actions.php';
	var isValid = false;
	var background = 'error';
	var callback = (el.data('cb') !== undefined ? el.data('cb') : data.cb ? data.cb : '');
	var d = '';
	var action = (el.data('action') !== undefined ? el.data('action') :
					(el.find('[type="hidden"][name="action"]') ? el.find('[type="hidden"][name="action"]').val() :
					(btn.data('action') !== undefined ? btn.data('action') : '') ));
	var ajaxType = (el.data('type') !== undefined ? el.data('type') : (data.type ? data.type : 'json'));
	var extraData = el.data('extra');


	isValid = validator({
		'el': btn,
		'form': el,
		'useBootstrapError': true,
		'useBootstrapDialog': false,
		'debug': false
	});

	//console.log('isAjax: ' + isAjax);

	if (!isValid) {
		console.log('ivSendForm: Fields Not Valid ');
		return false;
	}

	d = el.serializeArray();
	d.push({name: 'action', value: action});

	if(extraData){
		extraData = $.parseJSON(el.data('extra').replace(/\'/g,"\""));

		for(var element in extraData){
			d.push({name: element, value: extraData[element]});
		}
	}

	//console.log(d);
	//console.log(isValid);
	//return false;

	if (isAjax) {
		data.e.preventDefault();
		var ajaxStatus = $('.ajax-status');
		var ajaxStatusInner = ajaxStatus.find(' > .ajax-status-inner');
		var ajaxLoader = $('.ajax-loader');
		var tempHtml = '';

		//console.log('ivSendForm: Start');

		if(!ajaxSubmitBtn){
			ajaxSubmitBtn = btn;
			//btn.attr('disabled', 'disabled').addClass('loading');
		}

		$.ajax({
				url: URL,
				type: 'POST',
				data: d,
				dataType: ajaxType,
				timeout: 20000
		})
		.done(function (response, status, xhr) {
			//				console.log('prepareSendForm: done: ');
			//				console.log(response);
			//				console.log(status);
			//				console.log(xhr);

			if(!response.status && response.expired == true){
				redirect({url: response.url});
			}

			if (status == 'success') {
				//el.resetForm();
			}

		})
		.fail(function (jqXHR, status, errorThrown) {
			//console.log('ivSendForm: Fail: ');
			//console.log(jqXHR);
			//console.log(status);
			//console.log(errorThrown);
		})
		.always(function (response, status, xhr) {

			//btn.removeAttr('disabled').removeClass('loading');

			//console.log(response);
			//console.log(callback);

			if(callback){
				window[callback](response);
			}

			modalAjaxClose(response);

		});

		return false;
	}
}

function modalAjaxClose(response){
	if(response.status){
		if(response.modal !== undefined){
			$(response.modal).modal('hide');
		}
	}
}

function dateAddDays(date, q){
	//console.log('dateAddDays: start');
	//console.log('dateAddDays: sumo ' + q + ' dia/s a: ' + date);
	var newDate = new Date(date);
	var finalNewDate;
	newDate.setDate(newDate.getDate() + q);

	//finalNewDate = formatDate(newDate.getTime(), inputDateFormat);
	finalNewDate = newDate;
	//console.log('dateAddDays: finalNewDate: ' + finalNewDate);

	return finalNewDate;
}

function formatDate(time, format) {
	var t = new Date(time);
	var tf = function (i) { return (i < 10 ? '0' : '') + i };
	return format.replace(/yyyy|MM|dd|HH|mm|ss/g, function (a) {
		switch (a) {
			case 'yyyy':
				return tf(t.getFullYear());
				break;
			case 'MM':
				return tf(t.getMonth() + 1);
				break;
			case 'mm':
				return tf(t.getMinutes());
				break;
			case 'dd':
				return tf(t.getDate());
				break;
			case 'HH':
				return tf(t.getHours());
				break;
			case 'ss':
				return tf(t.getSeconds());
				break;
		}
	});
}

/* Scroll To */
function doScroll(event){
	var el = $(event.currentTarget);
	var fullUrl = el.attr('href') !== undefined ? el.attr('href') : '';
	var parts, targetEl, trgt, targetOffset, targetTop;
	event.preventDefault();

	targetTop = 0;

	if(fullUrl){
		parts = fullUrl.split("#");
		trgt = parts[1];
		targetEl = $("#" + trgt);
		targetOffset = targetEl.offset();
		targetTop = targetOffset.top;
	}

	$('html, body').animate({scrollTop: targetTop}, 800);
}

function goTo(data){
	//console.log(data);
	var el, tmpel, callback, offset, final;
	var speed = data.speed || 800;
	var ease = data.ease || "swing";

	if(data.target !== undefined){
		//console.log(1);
		el = data.target;
	}else if(data.e !== undefined){
		//console.log(2);
		data.e.preventDefault();
		tmpel = $(data.e.currentTarget);
		el = ($(tmpel.data('target')).length) ? $(tmpel.data('target'))  : $(tmpel.attr('href'));
	}
	//console.log(el);
	//console.log(typeof el);

	if ( typeof el === 'string' ) {
		//console.log(3);
		el = $(el);
	} else if ( typeof el === 'object' ) {
		//console.log(4);
		el = $(el);
	}else if ( el instanceof jQuery) {
		//console.log(5);
		el = el;
	} else {
		//console.log(6);
		console.log('goTo(): there is no target');
		return false;
	}


	if(!el.length){
		console.log('goTo(): jquery object is empty');
		return false;
	}

	offset = el.data('offset') != undefined ? el.data('offset') : (data.offset != undefined ? data.offset : '');
	callback = el.data('cb') != undefined ? el.data('cb') : (data.cb != undefined ? data.cb : '');
	final = (offset) ? parseInt(el.offset().top - offset) : el.offset().top;

	$("html, body").stop().animate(
		{ scrollTop: final },
		{
			duration: speed,
			easing: ease,
			complete: function(){
				if(callback){
					window[callback]();
				}
			}
		}
	);
}

function moveTo(data){
	var event = data.e;
	var useHash = (data.usehash) ? data.usehash : false;
	var contentArea = data.contentArea;
	var id = contentArea.attr('id');
	var goPosition = (id == 'homepage' ? 0 : $(contentArea).offset().top);
	var href = $(event.currentTarget).attr('href');

	if(!useHash){
		event.preventDefault();
	}

	if(isMobile.any()){
		goPosition = (id == 'homepage') ? 0 : parseInt(goPosition - 10);
	}

	$("html, body").stop().animate(
		{ scrollTop: goPosition },
		{
			duration: 1000,
			easing: "swing",
			complete: function(){}
		}
	);
}

function submitForm(data){

	var isValid = false;
	var f = data.el;
	var URL = f.data('url') || URL;
	var showDialog = f.data('dialog') || false;
	var callback = f.data('cb') || false;
	var datatype = f.data('datatype') || 'json';
	var isAjax = (f.data('ajax') != undefined ? f.data('ajax') : true);
	var btn = (f.data('submit') != undefined) ? $(f.data('submit')) : f.find('[type="submit"]');
	var d = f.serializeArray();
	var debug = f.data('debug') || false;
	debug = true;

	d.push({name: 'action', value: f.data('action')});

	isValid = validator({
		'el': btn,
		'form': f,
		'useBootstrapError': useBootstrapError,
		'useBootstrapDialog': useBootstrapDialog,
		'debug': false
	});
	//btn.unbind('click');

	//ilog(isValid);

	if(!isValid && !isAjax){

		data.e.preventDefault();
		loadingData = false;

		if(debug){console.log('isValid: ' + isValid + ' | isAjax: ' + isAjax);}

		return false;

	}else if(isValid && !isAjax){

		if(debug){console.log('isValid: ' + isValid + ' | isAjax: ' + isAjax);}

		return;
	}

	data.e.preventDefault();

	if(!isValid && !debug){
		loadingData = false;
		return false;
	}

	if(!ajaxReadyCheck('submitForm')){
		return false;
	}
	loadingData = true;

	makeAjaxRequest({
		'debug': false,
		'URL': URL,
		'data': d,
		'btn': btn,
		'dataType': datatype,
		'callback': function(){
			response = ajaxRequestResponse['response'];
			status = ajaxRequestResponse['status'];
			xhr = ajaxRequestResponse['xhr'];
			var iconClass = '';

			loadingData = false;

			if(response.status){
				f.resetForm();
				iconClass = 'icon-success';
			}else{
				iconClass = 'icon-warning';
			}

			if(showDialog){

				if(useBootstrapDialog){
					bootstrapDialog({'title': response.title, 'message': response.msg, 'btn1Text': response.button_1_text, 'fullwidth': false, 'size': 'regular'});
				}else{
					dialog({'title':response.title,'message':response.msg, width: '40%', 'modal':true});
				}
			}

			if(callback !== false){
				window[callback](response);
			}
		}
	});

	return false;
}

function ajaxReadyCheck(request){
	var r = true;
	if(loadingData){
		console.log('Cant perform the action ' + request + '. Server is busy loading data' + (currentAjaxProcess ? ' for: ' + currentAjaxProcess : '.'));
		r = false;
	}
	currentAjaxProcess = request;
	return r;
}

function getSegments(p){
	var s = window.location.pathname;
	var s = (country_code) ? s.replace('\/' + country_code + '\/', '\/') : s;
	//var s = s.substring(1, s.length - 1).split('/');
	var s = s.ivTrimChar('/').split('/');
	if(p != ''){
		return (s[parseInt(p - 1)] != undefined ? s[parseInt(p - 1)] : false);
	}
	return s;
}

function getDeviceType(){
	var type = 'desktop';
	var w = $(window).width();
	var widths = [
		{'width':1024, 'device': 'tablet'},
		{'width':992, 'device': 'tablet'},
		{'width':768, 'device': 'mobile'},
		{'width':480, 'device': 'mobile'},
		{'width':320, 'device': 'mobile'}
	];

	$(widths).each(function(i, e){
		if(w <= e.width){
			type = e.device;
		}
	});

	return type;
}

function isPortrait(){
	var w = $(window).width();
	var h = $(window).height();
	if(w > h){
		return false;
	}
	return true;
}

function getDeviceOrientation(){
	var w = $(window).width();
	var h = $(window).height();

	orientation = 'portrait';
	if(w > h){
		orientation = 'landscape';
	}
	return orientation;
}

function getWindowWidth(){
	return $(window).width();
}

function getWindowHeight(){
	return $(window).height();
}

function detectScrollDirection(){

	//Sets the current scroll position
	var st = $(window).scrollTop();

	//Determines up-or-down scrolling
	if (st > scrollDirectionLastPos){
		//console.log("DOWN");
		scrollDirection = 0;
		$('#scrolldir').html('down');
		scrollDirectionLastDir = 0;
	}else {
		//console.log("up");
		scrollDirection = 1;
		$('#scrolldir').html('up');
		scrollDirectionLastDir = 1;
	}

	//$('#scrolltop').html(st);
	//Updates scroll position
	scrollDirectionLastPos = st;
}

function getVieportMidPoint(){
	var $w = $(window);
	var top = $w.scrollTop();
	var bottom = top + $w.height();
	return (top + bottom) / 2;
}

function getScreenInfo(){
	if(!devmode) return;

	var data = ($('#data').length) ? $('#data') : $('<div id="data">');
	var style = {'display': 'block', 'position': 'fixed', 'top': '0', 'bottom': 'auto', 'left': '20%', 'right': 'auto', 'width': '100px', 'height': 'auto',	'z-index': '100000', 'color': '#fff', 'background': 'rgba(0,0,0,0.3)', 'font-size': '0.8em', 'padding': '5px'};
	var ww = $(window).width();
	var wh = $(window).height();

	data.css(style);
	$('body').append(data);
	data.html('ww: ' + ww +
				'<br>' + 'wh: ' + wh +
				'<br>' + 'dv: ' + getDeviceType() +
				'<br>' + 'or: ' + getDeviceOrientation() +
				  '<br>' + 'sd: ' + scrollDirection +
				'<br>' + 'V: 1') ;
}

function initializeMaps(){
	console.log('initializeMaps: start');
	var delay = 100;
	var canvas = $("#map");
	var zoomLevel = 16;
	var locations = [
		{title: 'The Stiles Hotel', address: '1120 Collins Ave., Miami Beach, Florida', lat: 0, lng: 0, info: '<div class="info-window"><strong>The Stiles Hotel</strong><br><i class="fa fa-map-marker"></i>1120 Collins Ave., Miami Beach, Florida<br><i class="fa fa-envelope"></i><a href="mailto:info@thestileshotel.com">info@thestileshotel.com</a><br><i class="fa fa-phone"></i><a href="tel:8774049690">+1 (877) 404-9690</a></div>'}
	]

	var mapStyle = [
		{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#58c1b8"},{"visibility":"on"}]}
	];
	var markerImage = {
		url: window.mr_variant == undefined ? 'assets/images/stiles-marker.png' : '../assets/images/stiles-marker.png'
		,size: new google.maps.Size(25, 40)
		,scaledSize: new google.maps.Size(25, 40)
	};
	var markerData = {};
	var markerList = {};
	var markerIdPreffix = 'iv-marker-';
	var locationsCounter = 0;
	var timer = '';
	var geocoder = new google.maps.Geocoder();
	//var bounds = new google.maps.LatLngBounds();
	var infowindow = new google.maps.InfoWindow();
	var latlng = new google.maps.LatLng(25.761681, -80.191788);
	var mapOptions = {
		zoom: zoomLevel
		,minZoom: 10
		,zoomControlOptions: {
			position: google.maps.ControlPosition.RIGHT_CENTER
		}
		,disableDoubleClickZoom : false
		,panControl: false
		,center: latlng
		,mapTypeControl : false
		,draggable: ($(document).width() > 966 ? true : false)
		,mapTypeId: google.maps.MapTypeId.ROADMAP
		,scrollwheel: false
		,disableDefaultUI: false
		,styles: mapStyle
	}

	var map = new google.maps.Map(canvas.get(0), mapOptions);

	//map.setZoom(15);

	//console.log(infowindow);

	google.maps.event.addDomListener(window, 'resize', function() {
		if(locations.length > 1){
			//map.setCenter(latlng);
			map.panTo(latlng);
		}else{
			map.panTo(markerList['order-0'].getPosition());
		}
	});


	function geocodeAddress(address) {
		//console.log('initializeMaps: geocodeAddress: ' + address);
		var goNextLocation = true;

		geocoder.geocode({address: address}, function (results, status){
			//console.log('initializeMaps: geocodeAddress:geocode: start');
			if (status == google.maps.GeocoderStatus.OK) {

				goNextLocation = false;
				//console.log(results);
				markerData.lat = results[0].geometry.location.lat();
				markerData.lng = results[0].geometry.location.lng();

				console.log('initializeMaps: geocodeAddress:geocode: finish ok');
				createMarker(markerData);
			}else if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
				console.log('initializeMaps: geocodeAddress:geocode: finish OVER_QUERY_LIMIT');
				locationsCounter--;
			} else {
				console.log('initializeMaps: Something happened with: ' + address + '. Error: ' + status);
			}

			if(goNextLocation){
				nextLocation();
			}
		});
	}

	function createMarker(data) {
		//console.log('initializeMaps: createMarker: ' + data.title);
		//console.log(data);
		var address = data.address.trim();
		var contentString = data.info + '<br>' + data.lat + ', ' + data.lng;
		var markerOptions = {
			id: data.id
			,order: data.order
			,position: new google.maps.LatLng(data.lat, data.lng)
			,map: map
			,icon: markerImage
			,title: data.title.trim()
			,optimised: false
			,animation:  google.maps.Animation.DROP
		}

		var marker = new google.maps.Marker(markerOptions);

		// add marker to list used later to get content and additional marker information
		markerList[marker.id] = marker;
		markerList['order-' + marker.order] = marker;

		google.maps.event.addListener(marker, 'click', function() {
			infowindow.setContent(contentString);
			infowindow.open(map, marker);
		});

		//bounds.extend(marker.position);

		locationsCounter++;

		nextLocation();
	}

	function nextLocation() {
		//console.log(locationsCounter + ' < ' + locations.length);

		if (locationsCounter < locations.length) {
			var location = locations[locationsCounter];

			//console.log(location);

			if((!location.lat || !location.lng) && !location.address){
				console.log('initializeMaps: You must provide lat and lng or a valid address ');
				console.log(location);
				return;
			}

			if(timer != ''){
				//window.clearTimeout(timer);
			}

			markerData = {
				id: (location.id || markerIdPreffix + locationsCounter)
				,order: locationsCounter
				,title: (location.title || 'Marker #' + locationsCounter)
				,address: (location.address || '')
				,lat: (location.lat || 0)
				,lng: (location.lng || 0)
				,info: (location.info || '')
			};

			if(!location.lat || !location.lng){
				window.setTimeout(function(){
					geocodeAddress(location.address);
				}, delay);
			}else{
				createMarker(markerData);
			}

			//locationsCounter++;

		} else {
			// SI hay mas de 1 icono, hace un enfoque automatico de todos
			if(locations.length > 1){
				//map.fitBounds(bounds);
				//console.log(bounds);
			}else{
				map.panTo(markerList['order-0'].getPosition());
			}
		}
	}

	var winHeight = $(window).height();
	var footerHeight = $("footer").height();
	$("#map").css("height", parseInt(winHeight - footerHeight));

	nextLocation();
}

function addGoogleMapsApiV3(data){
	// Load Google MAP API JS with callback to initialise when fully loaded
	var script = document.createElement('script');
	var apiKey = data.key; //'AIzaSyAvpnlHRidMIU374bKM5-sx8ruc01OvDjI';
	script.type = 'text/javascript';
	script.src = 'http://maps.google.com/maps/api/js?key=' + apiKey + '&callback=initializeMaps';
	script.className = 'gMapsAPI';
	document.body.appendChild(script);
}

function isTestSite(){
	var h = window.location.hostname.split('.');
	if(h[h.length - 1] == 'nmd'){
		return true;
	}
	return false;
}

function ilog(data, visible){
	if((debug && window.console) || visible){
		console.log(data);
	}
}

function ivCaptionize(data){
	var imgHolder = data.el.addClass('iv-img-holder');
	var el = imgHolder.find('img');
	var classes = imgHolder.attr('class');
	var alt = el.attr('alt');
	var imgCaption = '';
	var imgCaptionCSS = '';

	if(alt == ''){ return;	}

	var position = (data.position != undefined) ? data.position : 'relative';
	var fx = (data.fx != undefined) ? data.fx : 'slide';
	var placement = (data.placement != undefined) ? data.placement : 'bottom';
	var prefix = (data.prefix != undefined) ? data.prefix + ' ' : '';
	var sufix = (data.sufix != undefined) ? ' ' + data.sufix : '';

	imgCaption = $('<div class="iv-img-caption">').html(prefix + alt + sufix);

	imgCaptionCSS = {
		display: 'none', position: 'absolute', width: 'auto', left: 0, right: 0
	};
	imgCaptionCSS[placement] = 0;

	imgHolder.css({'position': position, 'text-align': (classes.indexOf('center') > -1 ? 'center' : '')});
	imgCaption.css(imgCaptionCSS);

	imgHolder.append(imgCaption);

	if(fx == 'slide' && ($.browser.msie && $.browser.version <= 7)){
		fx = 'fade';
	}

	switch(fx){
		case 'slide':
			imgHolder.mouseenter(
				function(){imgCaption.slideDown('fast');}
			);
			imgHolder.mouseleave(
				function(){imgCaption.slideUp('fast');}
			);
		break;
		case 'fade':
			imgHolder.mouseenter(
				function(){imgCaption.fadeIn('fast');}
			);
			imgHolder.mouseleave(
				function(){imgCaption.fadeOut('fast');}
			);
		break;
		case 'fixed':
			imgCaption.show();
		break;
	}
}

function performAcrobatDetection(){
	// The returned object
	var browser_info = {
		  name: null,
		  acrobat : null,
		  acrobat_ver : null
	};

	if(navigator && (navigator.userAgent.toLowerCase()).indexOf("chrome") > -1) browser_info.name = "chrome";
	else if(navigator && (navigator.userAgent.toLowerCase()).indexOf("msie") > -1) browser_info.name = "ie";
	else if(navigator && (navigator.userAgent.toLowerCase()).indexOf("firefox") > -1) browser_info.name = "firefox";
	else if(navigator && (navigator.userAgent.toLowerCase()).indexOf("msie") > -1) browser_info.name = "other";

	try{
		if(browser_info.name == "ie"){
			var control = null;
			// load the activeX control
			try{
				// AcroPDF.PDF is used by version 7 and later
				control = new ActiveXObject('AcroPDF.PDF');
			}
			catch (e){}

			if (!control){
				try{
					// PDF.PdfCtrl is used by version 6 and earlier
					control = new ActiveXObject('PDF.PdfCtrl');
				}
				catch (e) {}
			}

			if(!control){
				browser_info.acrobat == null;
				return browser_info;
			}
			version = control.GetVersions().split(',');
			version = version[0].split('=');
			browser_info.acrobat = "installed";
			browser_info.acrobat_ver = parseFloat(version[1]);

		}else if(browser_info.name == "chrome"){
			for(key in navigator.plugins){
				if(navigator.plugins[key].name == "Chrome PDF Viewer" || navigator.plugins[key].name == "Adobe Acrobat"){
					browser_info.acrobat = "installed";
					browser_info.acrobat_ver = parseInt(navigator.plugins[key].version) || "Chome PDF Viewer";
				}
			}
		}else if(navigator.plugins != null){ // NS3+, Opera3+, IE5+ Mac, Safari (support plugin array):  check for Acrobat plugin in plugin array
			var acrobat = navigator.plugins['Adobe Acrobat'];
			if(acrobat == null){
				browser_info.acrobat = null;
				return browser_info;
			}
			browser_info.acrobat = "installed";
			browser_info.acrobat_ver = parseInt(acrobat.version[0]);
		}
	}
	catch(e){
		browser_info.acrobat_ver = null;
	}
	return browser_info;
}

function makeAjaxSubmit(data){
	var btn = data.btn;
	var form = (data.form != null) ? data.form : btn.parents("form");
	var _target = (data.target != null) ? data.target : '';
	var _callback = (data.callback != null) ? data.callback : '';
	var _URL = (data.URL != null) ? data.URL : '';
	var _dataType = (data.dataType != null) ? data.dataType : 'json';
	var _type = (data.type != null) ? data.type : 'POST';
	var _data = (data.data != null) ? data.data : '';
	var _beforeSubmit = (data.validator != null) ? data.validator : '';
	var _resetForm = (data.resetForm != null) ? data.resetForm : false;
	var _clearForm = (data.clearForm != null) ? data.clearForm : false;
	var _timeout = (data.timeout != null) ? data.timeout : '';
	var debug = (data.debug != null) ? data.debug : false;

	$(form).submit(function(){

		enableDisable({'el':btn,'disable':true});

		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object and then invoke ajaxSubmit
		$(form).ajaxSubmit({
			target: _target,   						// target element(s) to be updated with server response
			//beforeSubmit: showRequest(formData, jqForm, options) //Tiene estos parametros por default
			beforeSubmit: _beforeSubmit,  			// pre-submit callback
			success: function(response, status, xhr, form){	// post-submit callback

				ajaxSubmitResponse['response'] = response;
				ajaxSubmitResponse['status'] = status;
				ajaxSubmitResponse['xhr'] = xhr;
				ajaxSubmitResponse['form'] = form;

				// Si debug es true
				if(debug){
					console.log(response);
					console.log(status);
					console.log(xhr);
					console.log(form);
				}

				// Si se hizo bien el ajaxsubmit pero hubo un error,
				// muestro el error en un dialog
				if(response.error != ''){
					//dialog({'title':'Error','message':response.error, 'modal':true});

					console.log('iv-common makeAjaxSubmit: ' + response.error);
					loadingData = false;

				}else{
					// Si hay una funcion callback
					if(_callback){
						_callback();
					}
				}

				enableDisable({'el':btn,'disable':false});
			},

			// other available options:
			url:       _URL,     					// override for form's 'action' attribute
			type:      _type,        				// 'get' or 'post', override for form's 'method' attribute
			dataType:  _dataType,        			// 'xml', 'script', or 'json' (expected server response type)
			data: _data,							// json con parametros adicionales a los campos del form (ej: {action:'listar'}
			clearForm: _clearForm,        			// clear all form fields after successful submit
			resetForm: _resetForm,        			// reset the form after successful submit

			// $.ajax options can be used here too, for example:
			timeout:   _timeout,
			error: function(){
				enableDisable({'el':btn,'disable':false});
				if(debug){
					console.log("Failed to submit\n");
				}
			}
		});
		// !!! Important !!!
		// always return false to prevent standard browser submit and page navigation
		btn.unbind('click');
		return false;
	});
}

function replaceServerPaths(data){
	var r ;
	var array = false;
	var json = false;

	var replacer = function(s){
		if(typeof s == 'string'){
			return s
			.replace(new RegExp( "\{siteBaseAssets\}", "g" ), siteBaseAssets)
			.replace(new RegExp( "\{siteBase\}", "g" ), siteBase)
			.replace(new RegExp( "\{siteName\}", "g" ), siteName);
		}else{
			return s;
		}
	};

	switch (typeof data){
		case 'object':
			r = {};
			json = true;
			for(var i in data){
				r[i] = replaceServerPaths(data[i]);
			}
		break;

		default:
			if(json){
				r.push(replacer(data));
			}else{
				return replacer(data);
			}
		break;

	}
	return r;

}

function makeAjaxRequest(data){
	//console.log(data);
	var btn = data.btn;
	var _callback = (data.callback != null) ? data.callback : '';
	var _URL = (data.URL != null) ? data.URL : '';
	var _dataType = (data.dataType != null) ? data.dataType : 'json';
	var _type = (data.type != null) ? data.type : 'POST';
	var _data = (data.data != null) ? data.data : '';
	var _validator = (data.validator !== undefined) ? data.validator : '';
	var _timeout = (data.timeout != null) ? data.timeout : '';
	var _contentType = (data.contentType != null) ? data.contentType : '';
	var _cache = (data.cache) ? data.cache : '';
	var debug = (data.debug != null) ? data.debug : false;
	var _replaceServerPaths = (data.replaceServerPaths != undefined) ? data.replaceServerPaths : false;
	var isValid = false;

	if(btn !== undefined){
		enableDisable({'el':btn,'disable':true});
	}

	if(_validator != ''){
		isValid = _validator({'el':btn});

		if(!isValid){

			enableDisable({'el':btn,'disable':false});

			btn.unbind('click');

			loadingData = false;

			return false;
		}
	}

	$.ajax({
		url: _URL,
		type: _type,
		data: _data,
		dataType: _dataType,
		timeout:  _timeout
		//beforeSend: showRequest(formData, jqForm, options), //Tiene estos parametros por default
		//beforeSend: function(){console.log('before send'); return false;}
	})
	.done(function(response, status, xhr) {

		// Reemplazo las variables del servidor en las respuestas ajax
		ajaxRequestResponse['response'] = (_replaceServerPaths) ? replaceServerPaths(response) : response;
		ajaxRequestResponse['status'] = status;
		ajaxRequestResponse['xhr'] = xhr;

		// Si debug es true
		if(debug){
			console.log('iv-common.js makeAjaxRequest debug');
			console.log(response);
			console.log(status);
			console.log(xhr);
		}

		if(_dataType == 'json' && response.error != ''){
			//dialog({'title':'Error','message':response.error, 'modal':true});

			console.log('iv-common.js makeAjaxRequest: ' + response.error);
			loadingData = false;

		}else{
			// Si hay una funcion callback
			if(_callback){
				_callback();
			}
		}

	})
	.fail(function(response, status, xhr) {

		if(debug){
			console.log("Failed to submit\n");
		}

		console.log('iv common: makeAjaxRequest error');

	})
	.always(function(response, status, xhr) {

		if(btn != null){
			enableDisable({'el':btn,'disable':false});
		}

		loadingData = false;

	});

	btn.unbind('click');
}

function inStr(haystack, needle) { //equivalent of the php strpos
	 //alert('looking for "'+needle+'" in "'+haystack+'"');

	 if (haystack.indexOf(needle) > -1) {
		  return true;
	 } else {
		  return false;
	 }
}

function isSet(var_to_check)
{
	 if (typeof var_to_check !== 'undefined') {
		  return true;
	 }
	 else
		  return false;
}

function limitCheckbox(checkboxname, limit)
{
	 numChecked = 0;
	 $('input[name=' + checkboxname + ']').each(function () {
		  if (this.checked)
		  {
				numChecked++;
				if (numChecked > 3)
				{
					 alert("You can select a maximum of " + limit + " checkboxes");
					 this.checked = false;
				}
		  }
	 });
}

function getPageName(){
	 return location.pathname.substring(1);
}

function getQueryString(key){
	 var re = new RegExp("[?&]" + key + "=([^&$]*)", "i");
	 var offset = location.search.search(re);
	 if (offset == -1)
		  return null;
	 return RegExp.$1;
}

function getQueryVariable(variable) {
	 var query = window.location.search.substring(1);
	 var vars = query.split("&");
	 for (var i = 0; i < vars.length; i++) {
		  var pair = vars[i].split("=");
		  if (pair[0] == variable) {
				return pair[1];
		  }
	 }
}

function setCookie(name, value, days) {
	 var date = new Date();
	 date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
	 var expires = "; expires=" + date.toGMTString();
	 document.cookie = name + "=" + value + expires;
}

function getCookie(name) {
	 var n = name + "=";
	 var cookie = document.cookie.split(';');
	 for (var i = 0; i < cookie.length; i++) {
		  var c = cookie[i];
		  while (c.charAt(0) == ' ') {
				c = c.substring(1, c.length);
		  }
		  if (c.indexOf(n) == 0) {
				return c.substring(n.length, c.length);
		  }
	 }
	 return null;
}

function getParam(p) {
	 var match = RegExp('[?&]' + p + '=([^&]*)').exec(window.location.search);
	 return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
}

function _gaLink(a) {
	 url = a.href;
	 _gaq.push(function () {
		  if (a.target == '_blank') {
				window.open(_gat._getTrackers()[0]._getLinkerUrl(url));
		  } else {
				_gaq.push(['_link', url]);
		  }
	 });
	 return false;
}

function dateAddDays(date, q){
	//console.log('dateAddDays: start');
	//console.log('dateAddDays: sumo ' + q + ' dia/s a: ' + date);

	var newDate = new Date(date.replace(/-/g, '/'));
	var finalNewDate;
	newDate.setDate(newDate.getDate() + q);

	//finalNewDate = formatDate(newDate.getTime(), inputDateFormat);
	finalNewDate = newDate;
	//console.log('dateAddDays: finalNewDate: ' + finalNewDate);

	return finalNewDate;
}

function formatDate(time, format) {
	var t = new Date(time);
	var tf = function (i) { return (i < 10 ? '0' : '') + i };
	return format.replace(/yyyy|MM|dd|HH|mm|ss/g, function (a) {
		switch (a) {
			case 'yyyy':
				return tf(t.getFullYear());
				break;
			case 'MM':
				return tf(t.getMonth() + 1);
				break;
			case 'mm':
				return tf(t.getMinutes());
				break;
			case 'dd':
				return tf(t.getDate());
				break;
			case 'HH':
				return tf(t.getHours());
				break;
			case 'ss':
				return tf(t.getSeconds());
				break;
		}
	});
}

function makeDatePicker(data){

	//console.log(data);

	var isRange = data.isRange || false;
	var options = {
		changeMonth: true,
		changeYear: true,
		showWeek: false,
		firstDay: 0, //Domingo
		showOn: 'both',
		defaultDate: "+1w",
		dateFormat: 'mm-dd-yy',
		buttonImage: '', //main_url + 'assets/adm_images/icon_date.png',
		numberOfMonths: (isRange ? (getDeviceType() == 'mobile' ? 1 : 2) : 1),
		buttonImageOnly: true,
		showButtonPanel: true,
		showOtherMonths: true,
		showStatus: true,
		showAnim: "blind",
		buttonText: '',
		dateBookingFormat: 'yyyy-MM-dd',
		onClose: function( selectedDate ) {
			var currentInput = this;
			//console.log(options.dateBookingFormat);
			var newDate = new Date(selectedDate.replace(/-/g, '/'));
			var tomorrowDate = new Date(dateAddDays(selectedDate, 1));
			if(isRange && currentInput.value != ''){
				if($(currentInput).hasClass('datefrom')){
					$( ".dateto" ).datepicker( "option", "minDate", tomorrowDate );
					$( ".dateto" ).val(formatDate(tomorrowDate, 'MM-dd-yyyy'));
					$('[name="departure_date"]').val(formatDate(tomorrowDate, options.dateBookingFormat));
				}
			}
			//console.log(formatDate(newDate, options.dateBookingFormat));
			if($(currentInput).hasClass('datefrom')){
				$('[name="arrival_date"]').val(formatDate(newDate, options.dateBookingFormat));
			}else if($(currentInput).hasClass('dateto')){
				$('[name="departure_date"]').val(formatDate(newDate, options.dateBookingFormat));
			}
		}
		,yearRange:"-80:+1"
		/*
		appendText: "(format dd/mm/yyyy)",
		inline: true,
		hideIfNoPrevNext:false,
		navigationAsDateFormat:false,
		gotoCurrent: false,
		showMonthAfterYear:false,
		altField:'pepe',
		altFormat:'hols',
		shortYearCutoff:"+10",
		showCurrentAtPos:2,
		stepMonths:1,
		stepBigMonths:12
		*/
	};

	$.each(data, function(key, val){
		if(options[key] != null && options[key] != undefined){
			options[key] = val;
		}
	});

	data.el.datepicker(options).attr("readonly", "readonly");
}

function ivAccordion(data){

	//console.log(data);

	var container = data.el;
	var device = container.data('device') || '';
	var effect = container.data('effect') || 'slide';
	var fadeSpeed = container.data('speed') || 750;
	var defaultTitleElement = '> ul li';
	var defaultTitleClass = 'iv-accordion-title';
	var defaultPaneClass = 'iv-accordion-pane';
	var callback = container.data('cb') || '';
	var debug = container.data('debug') || false;

	if((device) && (device != getDeviceType())){
		return;
	}

	var titles = (container.find(defaultTitleElement).length) ? container.find(defaultTitleElement) : container.find(($(container.data('iv-accordion-titles'))));
	var panes = (container.find($('.' + defaultPaneClass)).length) ? container.find('.' + defaultPaneClass) : $($(container.data('iv-accordion-panes')));
	var initial = (data.initial != undefined) ? data.initial : (container.data('iv-accordion-initial') != undefined ? container.data('iv-accordion-initial') : -1);

	if(!titles.length){
		console.log('iv-common: iv-accordion has no titles');
	}
	if(!panes.length){
		console.log('iv-common: iv-accordion has no panes');
	}

	if(debug){
		console.log(titles);
		console.log(panes);
		console.log(initial);
	}

	panes.hide();

	if(initial != -1){
		if(effect == 'slide'){
			panes.eq(initial).slideDown();
		}
		if(effect == 'fade'){
			panes.eq(initial).fadeIn(fadeSpeed);
		}
		titles.eq(initial).addClass('active');
	}

	titles.click(function(e){
		var currentTitle = $(e.currentTarget);
		var itemIndex = titles.index(currentTitle);
		var currentPane = panes.eq(itemIndex);

		if(currentTitle.hasClass('active')){
			if(effect == 'slide'){
				currentPane.slideToggle('fast' );
			}
			if(effect == 'fade'){
				//return;
				currentPane.fadeToggle(fadeSpeed);
			}
			titles.removeClass('active');
		}else{
			if(effect == 'slide'){
				panes.slideUp('fast');
				currentPane.slideDown('fast' );
			}
			if(effect == 'fade'){
				panes.fadeOut('fast');
				currentPane.stop().fadeIn(fadeSpeed);
			}
			titles.removeClass('active');
			currentTitle.addClass('active');
		}
		//updateScrollBar(currentPane);

		if(callback){
			window[callback]({'titles': titles, 'panes': panes, 'title': currentTitle, 'pane': currentPane, 'index': itemIndex});
		}


		e.preventDefault();
	});
}

//camel case string
function toCamelCase(str) {
	 return str.replace(/(?:^|\s)\w/g, function (match) {
		  return match.toUpperCase();
	 });
}

function screenshotPreview(elem){
	/* CONFIG */
		xOffset = 10;
		yOffset = 30;

		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result

	/* END CONFIG */
	elem.hover(function(e){
		this.t = this.title;
		this.title = "";
		var c = (this.t != "") ? "<br/>" + this.t : "";
		$("body").append("<div id='screenshotviewer'><img src='"+ this.rel +"' alt='url preview' />"+ c +"</div>");
		$("#screenshotviewer")
			.css({'position': 'absolute', "top":(e.pageY - xOffset) + "px", "left":(e.pageX + yOffset) + "px", 'z-index': 10000} )
			.fadeIn("fast");
	},
	function(){
		this.title = this.t;
		$("#screenshotviewer").remove();
	});
	elem.mousemove(function(e){
		$("#screenshotviewer")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px");
	});
}

//USO JSON {'funcion':'xx','tiempo' (en segundos):xx, 'parametros':'xx'}
function executeFuncion(opciones){
	var f = opciones.funcion;
	var d = eval(opciones.delay * 1000);
	var p = opciones.parametros;

	setTimeout(f+"("+p+")",d);
}

function utf8_encode ( argString ) {

	// Encodes an ISO-8859-1 string to UTF-8
	//
	// version: 1008.1718
	// discuss at: http://phpjs.org/functions/utf8_encode
	// +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   improved by: sowberry
	// +    tweaked by: Jack
	// +   bugfixed by: Onno Marsman
	// +   improved by: Yves Sucaet
	// +   bugfixed by: Onno Marsman
	// +   bugfixed by: Ulrich
	// *     example 1: utf8_encode('Kevin van Zonneveld');
	// *     returns 1: 'Kevin van Zonneveld'
	var string = (argString+''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");

	var utftext = "";
	var start, end;
	var stringl = 0;

	start = end = 0;
	stringl = string.length;
	for (var n = 0; n < stringl; n++) {
		var c1 = string.charCodeAt(n);
		var enc = null;

		if (c1 < 128) {
			end++;
		} else if (c1 > 127 && c1 < 2048) {
			enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
		} else {
			enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
		}
		if (enc !== null) {
			if (end > start) {
				utftext += string.substring(start, end);
			}
			utftext += enc;
			start = end = n+1;
		}
	}

	if (end > start) {
		utftext += string.substring(start, string.length);
	}

	return utftext;
}

function utf8_decode ( str_data ) {
	// Converts a UTF-8 encoded string to ISO-8859-1
	//
	// version: 1008.1718
	// discuss at: http://phpjs.org/functions/utf8_decode
	// +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
	// +      input by: Aman Gupta
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   improved by: Norman "zEh" Fuchs
	// +   bugfixed by: hitwork
	// +   bugfixed by: Onno Marsman
	// +      input by: Brett Zamir (http://brett-zamir.me)
	// +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// *     example 1: utf8_decode('Kevin van Zonneveld');
	// *     returns 1: 'Kevin van Zonneveld'
	var tmp_arr = [], i = 0, ac = 0, c1 = 0, c2 = 0, c3 = 0;

	str_data += '';

	while ( i < str_data.length ) {
		c1 = str_data.charCodeAt(i);
		if (c1 < 128) {
			tmp_arr[ac++] = String.fromCharCode(c1);
			i++;
		} else if ((c1 > 191) && (c1 < 224)) {
			c2 = str_data.charCodeAt(i+1);
			tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
			i += 2;
		} else {
			c2 = str_data.charCodeAt(i+1);
			c3 = str_data.charCodeAt(i+2);
			tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			i += 3;
		}
	}

	return tmp_arr.join('');
}

/**
 *
 * @param data json el:element,beforeProcess:fn,callBack:fn,speed:int|string|empty
 * @return
 */
function showHide(data){
	var div = data.el;
	var speed = (data.speed != null) ? data.speed : 250;
	var _beforeProcess = (data.beforeProcess != null) ? data.beforeProcess : false;
	var _callBack = (data.callBack != null) ? data.callBack : false;

	if(div == '' || div == null){
		//console.log('showHideDiv: no hay elemento para procesar');
		return;
	}

	if(_beforeProcess){
		_beforeProcess();
	}
	div.slideToggle(speed, function(){
		if(_callBack){
			_callBack();
		}
	});
}

function queryStringToJson(data){
	var d = data
		.replace(new RegExp( "\&", "g" ),"\',\'")
		.replace(new RegExp( "\=", "g" ),"\':\'");
	d = decodeURI(d);
	d = decodeURIComponent(d);
	d = '{\'' + d + '\'}';
	return eval('(' + d + ')');
}

function enableDisable(data){
	var el = data.el;
	var disable = (data.disable == undefined) ? true : data.disable;

	if(disable){
		el.attr('disabled','disabled');
	}else{
		el.removeAttr('disabled');
	}
}

function dialog(data){
	//$('#dialog').dialog("destroy");

	var d = $('#dialog');
	var message_holder = $('#iv-dialog-message');

	var _mode = (data.mode == null) ? 'alert' : data.mode;
	var _title = data.title;
	var _message = data.message;
	//ui-icon-alert (error), ui-icon-circle-check (ok), ui-icon-info (info), ui-icon-notice (info), ui-icon-help (conf)
	var _icon = (data.icon == null) ? 'info' : data.icon;

	var _modal = (data.modal == null) ? false : data.modal;
	var _resizable = (data.resizable == null) ? false : data.resizable;
	var _height = (data.height == null) ? 'auto' : data.height;
	var _width = (data.width == null) ? 300 : data.width;

	var _btn1Text = (data.btn1Text == null) ? 'Aceptar' : data.btn1Text;
	var _btn1Function = (data.btn1Function == null) ? false : data.btn1Function;
	var _btn2Text = (data.btn2Text == null) ? 'Cancelar' : data.btn2Text;
	var _btn2Function = (data.btn2Function == null) ? false : data.btn2Function;

	var _open = (data.open == null) ? '' : data.open;

	switch(_icon){
		case 'ok': _icon = 'ui-icon-circle-check'; break;
		case 'error': _icon = 'ui-icon-alert'; break;
		case 'info': _icon = 'ui-icon-info'; break;
		case 'help': _icon = 'ui-icon-help'; break;
	}

	var _iconHolder;

	// Seteo los botones
	if(_mode != 'message'){
		var buttons = {};
		buttons[_btn1Text] = function(){
			if(_btn1Function){
				data.btn1Function();
			}
			$(this).dialog('close');
		};
		if(_mode == 'confirm'){
			_icon = 'ui-icon-help';
			buttons[_btn2Text] = function(){
				if(_btn2Function){
					data.btn2Function();
				}
				$(this).dialog('close');
			};
		}
	}

	_iconHolder = $('.ui-icon').addClass(_icon);
	message_holder.html(_message);

	d.dialog({
		title: _title,
		resizable: _resizable,
		height:_height,
		width:_width,
		modal: _modal,
		position: 'center',
		buttons: buttons,
		open: _open
	});
}

function bootstrapDialog(data){
	var d = $('#modal-popup');
	var _title = data.title;
	var _message = data.message;
	var _fullwidth = (data.fullwidth == null) ? data.fullwidth : false;
	var _size = (data.size == null) ? '' : data.size;
	var _sizeClass = '';

	var _btn1Text = (data.btn1Text == null) ? 'CANCELAR' : data.btn1Text;
	var _btn1Function = (data.btn1Function == null) ? false : data.btn1Function;
	var _btn2Text = (data.btn2Text == null) ? 'ACEPTAR' : data.btn2Text;
	var _btn2Function = (data.btn2Function == null) ? false : data.btn2Function;

	var _open = (data.open == null) ? '' : data.open;

	if(_fullwidth === true){
		d.addClass('container');
	}

	if(_size != ''){
		switch(_size){
			case 'large': _sizeClass = 'modal-lg'; break;
			case 'small': _sizeClass = 'modal-sm'; break;
		}

		d.find('.modal-dialog').addClass(_sizeClass);
	}

	$('.modal-title').text(_title);
	$('.modal-body').html(_message);
	$('.modal-btn-1').text(_btn1Text).click(
		function(e){
			if(_btn1Function !== false){
				data.btn1Function();
			}else{
				d.modal('hide');
			}
		}
	);
	if(_btn2Function !== false){
		$('.modal-btn-2').text(_btn2Text).click(
			function(e){
				data.btn2Function();
			}
		);
	}else{
		$('.modal-btn-2').hide();
	}

	d.modal({
		keyboard: true,
		show: true
	});

	//$('#modal-panel').modal(options)
}

function friendlyURL(url){
	// Clean up the title
	var urlRetorno = url
		.toLowerCase() // change everything to lowercase
		.replace(/^\s+|\s+$/g, "") // trim leading and trailing spaces
		.replace(/[_|\s]+/g, "-") // change all spaces and underscores to a hyphen
		.replace(/[^a-z0-9-]+/g, "") // remove all non-alphanumeric characters except the hyphen
		.replace(/[-]+/g, "-") // replace multiple instances of the hyphen with a single instance
		.replace(/^-+|-+$/g, "") // trim leading and trailing hyphens
		;

	return urlRetorno;
}

function createIframe(data){
	var id = data.id || new Date().getTime();
	var iframeID = 'iframe-' + id;
	var url = data.url;
	var src;
	var iframe = $('#' + iframeID).length ?  $('#' + iframeID) : $('<iframe></iframe>').attr('id', iframeID);

	iframe.on('load', function(){
		console.log('iframe: #' + id + ' loaded');
		data.callback();
	});

	if(typeof url == 'boolean'){
		src = 'javascript:false';
	}else if(typeof url == 'string'){
		src = url;
	}

	iframe
		.attr({src: src})
		.css({position: 'absolute', top: '-1000', left: '-1000', height: 0, width: 0, 'display': 'none'});

	return iframe;
}

function delivery(d){
	var tmp = d.el.attr('id').split('_');
	var idB = tmp[0];
	var idE = tmp[1];
	var idL = tmp[2];
	var type = tmp[3];
	var URL = siteBase + '_ajx/entry_jx_delivery.php?f=' + F;

	//console.log('blog_id:' + idB + ' entry_id:' + idE + ' language_id:' + idL);

	$.ajaxFileUploadIv(
		{
			url:URL,
			secureuri:true,
			//type: 'POST',
			formId: false, // Sin formId, se crea un formulario en el momento. con formId, envia los campos de ese formulario
			dataType: 'json',
			additionalData:{'idB':idB,'idE':idE,'idL':idL,'t':type},
			success: function(response, status){
				if(typeof(response.error) != 'undefined'){
					if(response.error != ''){
						alert('error: ' + response.error);
					}else{
						if(response.status == true){
							pClass = "msgOk";
						}else{
							pClass = "msgError";
						}
					}
				}
				statusOff();
			},
			error: function(response, status, e){
				//console.log(data);
				alert(e + '\ndata: ' + response + '\ne: ' + e);
				statusOff();
			}
		}
	);
	statusOff();
}

function download(obj){
	var iFrame, par, objeto, idObjeto, seccion;
	objeto = obj.attr("id").split("_");
	seccion = objeto[0];
	idObjeto = objeto[1];

	par = "t="+seccion+"&id=" + idObjeto+'&l=' + l;
	iFrame = $("#iframe");
	iFrame.attr("src",siteBase + "_jx/_delivery.php?" + par);
}

function statusOn(){
	$("#pnlStatus").show();
}

function statusOff(){
	$("#pnlStatus").hide();
}

function clearSelect(sel){
	$("#" + sel)[0].options.length = 0;
}

function fillSelect(data){
	var origen = $('#' + data.origen);
	var destino = $('#' + data.destino);
	var valor = origen.val();
	var accion = data.accion;
	if(valor!=0){
		prendeStatus();
		$.ajax({
			type:'POST',
			data: {"accion":accion,"id":valor},
			url: siteBase+"_jx/_utiles.php?f="+F,
			dataType: 'json',
			success: function(respuesta){
				 limpiarSelect(destino.attr('id'));
				 for(i=0; i < respuesta.items.length; i++){
					opcion = new Option(respuesta.items[i].texto,respuesta.items[i].valor);
					destino[0].options[i]=opcion;
				 }
				 apagaStatus();
			}
		});
	}
}

function isArray(a) {
	if (a.constructor.toString().indexOf("Array") == -1){
		return false;
	}else{
		return true;
	}
}

function inArray(needle, haystack, argStrict) {
	 // Checks if the given value exists in the array
	 //
	 // version: 1003.2411
	 // discuss at: http://phpjs.org/functions/in_array    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	 // +   improved by: vlado houba
	 // +   input by: Billy
	 // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	 // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);    // *     returns 1: true
	 // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
	 // *     returns 2: false
	 // *     example 3: in_array(1, ['1', '2', '3']);
	 // *     returns 3: true    // *     example 3: in_array(1, ['1', '2', '3'], false);
	 // *     returns 3: true
	 // *     example 4: in_array(1, ['1', '2', '3'], true);
	 // *     returns 4: false
	 var key = '', strict = !!argStrict;
	 if (strict) {
		  for (key in haystack) {
				if (haystack[key] === needle) {
					 return true;
				}
		  }
	 } else {
		  for (key in haystack) {
				if (haystack[key] == needle) {
					 return true;
				}
		  }
	 }
	 return false;
}

function setUpUnload()
{
	 window.onbeforeunload = function () {
		  return ''; //insert custom message here.
	 };
}

function resetForm(formId)
{
	 //$('[type="text"]').val("");
	 $('#' + formId + ' textarea').val("");
	 $(':input', '#' + formId)
				.not(':button, :submit, :reset, :hidden, :checkbox , :radio')
				.val('')
				.removeAttr('checked')
				.removeAttr('selected');

	 clearErrors(formId);
}

/* This will countdown the number of characters remaining and put the result in a field called fieldID+'_chars'*/
function getCharsRemaining(fieldID, numChars)
{
	 var charcount = (parseInt(numChars) - $('#' + fieldID).val().length);
	 if (charcount <= 0) {
		  $('#' + fieldID).val($('#' + fieldID).val().substring(0, numChars));
	 }
	 $('#' + fieldID + '_chars').html(charcount);
}


//THIS IS AN AWESOME FUNCTION CREATED BY DAVID WHICH TAKES ALL INPUTS OF A PASSED FORM AND FORMATS THEM TO AN OBJECT SO IT CAN BE PASSED AS THE DATA OBJECT OF A JSON AJAX REQUEST.
function allInputsToObject(form_id, options, additional_non_input_values)
{
	 /*
	  OPTIONS:
	  nest_checkboxes_by_name : if set it will nest all the cehckboxes like data[name][id] = true/false
	  use_checkbox_value_if_true : if set will use the check boxes value instead of true. can be used with the nest option. value/false

	  nest_radiobuttons_by_name : if set it will nest all the radiobuttons like data[name][id] = true/false
	  */

	 // get all the inputs into an array.
	 var $inputs = $('#' + form_id + ' :input');

	 // not sure if you wanted this, but I thought I'd add it.
	 // get an associative array of just the values.
	 var values = {};

	 $inputs.each(function ()
	 {
		  if (this.type == "checkbox")
		  {
				if (options.nest_checkboxes_by_name == true && this.name != '')
				{
					 if (typeof values[this.name] == 'undefined') {
						  values[this.name] = {}
					 } //check if the array has been initialized, if not then initialize it

					 if (options.use_checkbox_value_if_true && $('#' + this.id).is(':checked')) //if option set AND the checkbox is checked use its value
					 {
						  values[this.name][this.id] = this.value;
					 }
					 else
					 {
						  values[this.name][this.id] = $('#' + this.id).is(':checked');
					 }
				}
				else
				{
					 if (options.use_checkbox_value_if_true && $('#' + this.id).is(':checked')) //if option set AND the checkbox is checked use its value
					 {
						  values[this.id] = this.value;
					 }
					 else
					 {
						  values[this.id] = $('#' + this.id).is(':checked');
					 }

				}
		  }
		  //KATIE ADDED RADIO BUTTONS BECAUSE DAVID MESSED UP AND FORGOT ABOUT THEM
		  else if (this.type == "radio")
		  {
				if (options.nest_radiobuttons_by_name == true && this.name != '')
				{
					 if (typeof values[this.name] == 'undefined') {
						  values[this.name] = {}
					 } //check if the array has been initialized, if not then initialize it
					 values[this.name][this.id] = $('#' + this.id).is(':checked').val();
				}
				else
				{
					 if ($('#' + this.id).is(':checked')) //if option set AND the checkbox is checked use its value
					 {
						  values[this.id] = this.value;
					 }

				}
		  }

		  else
		  {
				values[this.id] = $(this).val();
		  }
	 });

	 //add the additional non input values to the value object
	 $.each(additional_non_input_values, function (index) {
		  values[index] = String(this); //typecast because js will see it as an array of characters if not typecasted
	 });

	 return values;
}


// PLUGINS
$.fn.wait = function(time, type) {
	time = time || 1000;
	type = type || "fx";
	return this.queue(type, function() {
		var self = this;
		setTimeout(function() {
			$(self).dequeue();
		}, time);
	});
};

$.fn.queryString = function () {
  // This function is anonymous, is executed immediately and
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
	var pair = vars[i].split("=");
		// If first entry with this name
	if (typeof query_string[pair[0]] === "undefined") {
	  query_string[pair[0]] = decodeURIComponent(pair[1]);
		// If second entry with this name
	} else if (typeof query_string[pair[0]] === "string") {
	  var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
	  query_string[pair[0]] = arr;
		// If third or later entry with this name
	} else {
	  query_string[pair[0]].push(decodeURIComponent(pair[1]));
	}
  }
  return query_string;
};

//hasAttr Plugin
$.fn.hasAttr = function(name) {
	return this.attr(name) !== undefined && this.attr(name) !== false;
};


// PROTOTYPES

/**
 * Devuelve un Unix timespamp
 * @param mode: s for seconds / m for miliseconds. Default s
 * @return timestamp
 */
Date.prototype.ivTimeStamp = function(mode){
	var m = mode || 's';
	var divisor = 1000;
	var r;
	if (!this.now) {
		 this.now = function() { return new Date().getTime(); }
	}

	switch(m){
		case 'm':
			divisor = 1;
		break;
	}

	r = Math.floor(this.now() / divisor);
	//console.log(r);
	return r;
};

/**
 * @desc Da formato a fechas basadas en un objeto Date(). Ej: d = new Date().ivDateFormat('dd-mm-yyy');
 * @param string format formato de fecha basado en sintaxis de php
 * @return Fecha con formato deseado
 */
Date.prototype.ivDateFormat = function(format) {

	//console.log(this);

	//var t = new Date(this);
	var t = new Date(this);
	var tf = function (i) { return (i < 10 ? '0' : '') + i };

	return format.replace(/yyyy|mm|dd|hh|ii|ss/g, function (a) {
		switch (a) {
			case 'yyyy':
				return tf(t.getFullYear());
				break;
			case 'mm':
				return tf(t.getMonth() + 1);
				break;
			case 'ii':
				return tf(t.getMinutes());
				break;
			case 'dd':
				return tf(t.getDate());
				break;
			case 'hh':
				return tf(t.getHours());
				break;
			case 'ss':
				return tf(t.getSeconds());
				break;
		}
	});
};

Date.prototype.ivDateFront = function(sep) {
	//console.log('1: ' + this.toUTCString() );
	var newDate = this.toUTCString().split(' ')[1] + sep + this.toUTCString().split(' ')[2] + sep + this.getFullYear();
	return newDate;
};

Date.prototype.ivDateBooking = function(){
	return this.getDate() + '-' + (this.getMonth() + 1) + '-' + this.getFullYear();
};

Date.prototype.ivDateTomorrow = function(mode){
	var d = new Date(this.setDate(this.getDate() + 1));
	//console.log(d);
	var r = '';
	if(mode == 'front'){
		r = d.toUTCString().split(' ')[2] + ' ' + d.toUTCString().split(' ')[1] + ' ' + d.getFullYear();
	}else{
		r =  d.getDate() + '-' + (d.getMonth() + 1) + '-' + d.getFullYear();
	}
	console.log('tomorrow: ' + r);
	return r;
};

Date.prototype.ivDateSqlFormat = function(){
	var day = ("0" + this.getDate()).slice(-2);
	var month = ("0" + (this.getMonth() + 1)).slice(-2);
	var sqlDate = this.getFullYear() + "-" + (month) + "-" + (day);

	return sqlDate;
};

//use like str_replace from phpâ€¦. use like 'okay.this.is.a.string'.replaceAll('.', ' ');
String.prototype.ivReplaceAll = function (token, newToken, ignoreCase) {
	 var _token;
	 var str = this + "";
	 var i = -1;

	 if (typeof token === "string") {

		  if (ignoreCase) {

				_token = token.toLowerCase();

				while ((
						  i = str.toLowerCase().indexOf(
						  token, i >= 0 ? i + newToken.length : 0
						  )) !== -1
						  ) {
					 str = str.substring(0, i) +
								newToken +
								str.substring(i + token.length);
				}

		  } else {
				return this.split(token).join(newToken);
		  }

	 }
	 return str;
};

String.prototype.ivFloat = function() {
  return parseFloat(this.replace(',', ''));
}

String.prototype.ivReplaceAll = function(search, replacement) {
	var target = this;
	return target.replace(new RegExp(search, 'g'), replacement);
};

String.prototype.ivTrimChar = function (charToRemove) {

	var string = this;

	if(!charToRemove){return string;}

	while(string.charAt(0) == charToRemove) {
		string = string.substring(1);
	}

	while(string.charAt(string.length-1) == charToRemove) {
		string = string.substring(0, string.length - 1);
	}

	return string;
}

/*
//
// * Create a web friendly URL slug from a string.
// *
// * Requires XRegExp (http://xregexp.com) with unicode add-ons for UTF-8 support.
// *
// * Although supported, transliteration is discouraged because
// *     1) most web browsers support UTF-8 characters in URLs
// *     2) transliteration causes a loss of information
// *
// * @author Sean Murphy <sean@iamseanmurphy.com>
// * @copyright Copyright 2012 Sean Murphy. All rights reserved.
// * @license http://creativecommons.org/publicdomain/zero/1.0/
// *
// * @param string s
// * @param object opt
// * @return string
//
function url_slug(s, opt) {
	s = String(s);
	opt = Object(opt);

	var defaults = {
		'delimiter': '-',
		'limit': undefined,
		'lowercase': true,
		'replacements': {},
		'transliterate': (typeof(XRegExp) === 'undefined') ? true : false
	};

	// Merge options
	for (var k in defaults) {
		if (!opt.hasOwnProperty(k)) {
			opt[k] = defaults[k];
		}
	}

	var char_map = {
		// Latin
		'À': 'A', 'Á': 'A', 'Â': 'A', 'Ã': 'A', 'Ä': 'A', 'Å': 'A', 'Æ': 'AE', 'Ç': 'C',
		'È': 'E', 'É': 'E', 'Ê': 'E', 'Ë': 'E', 'Ì': 'I', 'Í': 'I', 'Î': 'I', 'Ï': 'I',
		'Ð': 'D', 'Ñ': 'N', 'Ò': 'O', 'Ó': 'O', 'Ô': 'O', 'Õ': 'O', 'Ö': 'O', 'Ő': 'O',
		'Ø': 'O', 'Ù': 'U', 'Ú': 'U', 'Û': 'U', 'Ü': 'U', 'Ű': 'U', 'Ý': 'Y', 'Þ': 'TH',
		'ß': 'ss',
		'à': 'a', 'á': 'a', 'â': 'a', 'ã': 'a', 'ä': 'a', 'å': 'a', 'æ': 'ae', 'ç': 'c',
		'è': 'e', 'é': 'e', 'ê': 'e', 'ë': 'e', 'ì': 'i', 'í': 'i', 'î': 'i', 'ï': 'i',
		'ð': 'd', 'ñ': 'n', 'ò': 'o', 'ó': 'o', 'ô': 'o', 'õ': 'o', 'ö': 'o', 'ő': 'o',
		'ø': 'o', 'ù': 'u', 'ú': 'u', 'û': 'u', 'ü': 'u', 'ű': 'u', 'ý': 'y', 'þ': 'th',
		'ÿ': 'y',

		// Latin symbols
		'©': '(c)',

		// Greek
		'Α': 'A', 'Β': 'B', 'Γ': 'G', 'Δ': 'D', 'Ε': 'E', 'Ζ': 'Z', 'Η': 'H', 'Θ': '8',
		'Ι': 'I', 'Κ': 'K', 'Λ': 'L', 'Μ': 'M', 'Ν': 'N', 'Ξ': '3', 'Ο': 'O', 'Π': 'P',
		'Ρ': 'R', 'Σ': 'S', 'Τ': 'T', 'Υ': 'Y', 'Φ': 'F', 'Χ': 'X', 'Ψ': 'PS', 'Ω': 'W',
		'Ά': 'A', 'Έ': 'E', 'Ί': 'I', 'Ό': 'O', 'Ύ': 'Y', 'Ή': 'H', 'Ώ': 'W', 'Ϊ': 'I',
		'Ϋ': 'Y',
		'α': 'a', 'β': 'b', 'γ': 'g', 'δ': 'd', 'ε': 'e', 'ζ': 'z', 'η': 'h', 'θ': '8',
		'ι': 'i', 'κ': 'k', 'λ': 'l', 'μ': 'm', 'ν': 'n', 'ξ': '3', 'ο': 'o', 'π': 'p',
		'ρ': 'r', 'σ': 's', 'τ': 't', 'υ': 'y', 'φ': 'f', 'χ': 'x', 'ψ': 'ps', 'ω': 'w',
		'ά': 'a', 'έ': 'e', 'ί': 'i', 'ό': 'o', 'ύ': 'y', 'ή': 'h', 'ώ': 'w', 'ς': 's',
		'ϊ': 'i', 'ΰ': 'y', 'ϋ': 'y', 'ΐ': 'i',

		// Turkish
		'Ş': 'S', 'İ': 'I', 'Ç': 'C', 'Ü': 'U', 'Ö': 'O', 'Ğ': 'G',
		'ş': 's', 'ı': 'i', 'ç': 'c', 'ü': 'u', 'ö': 'o', 'ğ': 'g',

		// Russian
		'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo', 'Ж': 'Zh',
		'З': 'Z', 'И': 'I', 'Й': 'J', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O',
		'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'C',
		'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sh', 'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu',
		'Я': 'Ya',
		'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh',
		'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
		'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c',
		'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu',
		'я': 'ya',

		// Ukrainian
		'Є': 'Ye', 'І': 'I', 'Ї': 'Yi', 'Ґ': 'G',
		'є': 'ye', 'і': 'i', 'ї': 'yi', 'ґ': 'g',

		// Czech
		'Č': 'C', 'Ď': 'D', 'Ě': 'E', 'Ň': 'N', 'Ř': 'R', 'Š': 'S', 'Ť': 'T', 'Ů': 'U',
		'Ž': 'Z',
		'č': 'c', 'ď': 'd', 'ě': 'e', 'ň': 'n', 'ř': 'r', 'š': 's', 'ť': 't', 'ů': 'u',
		'ž': 'z',

		// Polish
		'Ą': 'A', 'Ć': 'C', 'Ę': 'e', 'Ł': 'L', 'Ń': 'N', 'Ó': 'o', 'Ś': 'S', 'Ź': 'Z',
		'Ż': 'Z',
		'ą': 'a', 'ć': 'c', 'ę': 'e', 'ł': 'l', 'ń': 'n', 'ó': 'o', 'ś': 's', 'ź': 'z',
		'ż': 'z',

		// Latvian
		'Ā': 'A', 'Č': 'C', 'Ē': 'E', 'Ģ': 'G', 'Ī': 'i', 'Ķ': 'k', 'Ļ': 'L', 'Ņ': 'N',
		'Š': 'S', 'Ū': 'u', 'Ž': 'Z',
		'ā': 'a', 'č': 'c', 'ē': 'e', 'ģ': 'g', 'ī': 'i', 'ķ': 'k', 'ļ': 'l', 'ņ': 'n',
		'š': 's', 'ū': 'u', 'ž': 'z'
	};

	// Make custom replacements
	for (var k in opt.replacements) {
		s = s.replace(RegExp(k, 'g'), opt.replacements[k]);
	}

	// Transliterate characters to ASCII
	if (opt.transliterate) {
		for (var k in char_map) {
			s = s.replace(RegExp(k, 'g'), char_map[k]);
		}
	}

	// Replace non-alphanumeric characters with our delimiter
	var alnum = (typeof(XRegExp) === 'undefined') ? RegExp('[^a-z0-9]+', 'ig') : XRegExp('[^\\p{L}\\p{N}]+', 'ig');
	s = s.replace(alnum, opt.delimiter);

	// Remove duplicate delimiters
	s = s.replace(RegExp('[' + opt.delimiter + ']{2,}', 'g'), opt.delimiter);

	// Truncate slug to max. characters
	s = s.substring(0, opt.limit);

	// Remove delimiter from ends
	s = s.replace(RegExp('(^' + opt.delimiter + '|' + opt.delimiter + '$)', 'g'), '');

	return opt.lowercase ? s.toLowerCase() : s;
}
	*/

/**
 * Devuelve Formatea un numero utilizando cantidad de decimales, separador de mil, separador de decimales. Ejemplo de uso: (123456789.12345).ivFormatMoney(2, '.', ',')
 * @param c: Posiciones decimales
 * @param d: Separador de mil
 * @param t: Separador de decimales
 * @return String formateado
 */
Number.prototype.ivFormatMoney = function (c, d, t) {

	var n = this;
	var c = isNaN(c = Math.abs(c)) ? 2 : c;
	var d = d === undefined ? "," : d;
	var t = t === undefined ? "." : t;
	var s = n < 0 ? "-" : "";
	var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "";
	var j = (j = i.length) > 3 ? j % 3 : 0;

	//var multiplier = Math.pow(10, c);
 //	r = (Math.round(n * multiplier) / multiplier);

	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

/**
 * Convierte las letras extrañas, acentuadas o con tildes UNICODE en letras UTF8 comunes
 * @link http://www.utf8-chartable.de/unicode-utf8-table.pl
 * @see http://www.utf8-chartable.de/unicode-utf8-table.pl
 * @desc referencias de las columnas (unicode, caracter, UTF8-hex, HTML 4 Entity, HTML codificacion numerica, nombre)
 */
String.prototype.ivMakeFriendlyUrl = function (sep){

	var sep = sep;
	var nonSep = (sep == '-') ? '_' : '-';
	var regexSep = new RegExp('[' + sep + ']+','g');
	//var regexNonSep = new RegExp('[' + nonSep + '|\s]+','g');
	var s = this;
	var unsafe = new Array(
			"\u00C0",/*	�	c3 80	&Agrave;	� 	&#192;	� 	LATIN CAPITAL LETTER A WITH GRAVE*/
			"\u00C1",/*	�	c3 81	&Aacute;	� 	&#193;	� 	LATIN CAPITAL LETTER A WITH ACUTE*/
			"\u00C2",/*	�	c3 82	&Acirc;		� 	&#194;	� 	LATIN CAPITAL LETTER A WITH CIRCUMFLEX*/
			"\u00C3",/*	�	c3 83	&Atilde;	� 	&#195;	� 	LATIN CAPITAL LETTER A WITH TILDE*/
			"\u00C4",/*	�	c3 84	&Auml;		� 	&#196;	� 	LATIN CAPITAL LETTER A WITH DIAERESIS*/
			"\u00C5",/*	�	c3 85	&Aring;		� 	&#197;	� 	LATIN CAPITAL LETTER A WITH RING ABOVE*/
			"\u00C6",/*	�	c3 86	&AElig;		� 	&#198;	� 	LATIN CAPITAL LETTER AE*/
			"\u00C7",/*	�	c3 87	&Ccedil;	� 	&#199;	� 	LATIN CAPITAL LETTER C WITH CEDILLA*/
			"\u00C8",/*	�	c3 88	&Egrave;	� 	&#200;	� 	LATIN CAPITAL LETTER E WITH GRAVE*/
			"\u00C9",/*	�	c3 89	&Eacute;	� 	&#201;	� 	LATIN CAPITAL LETTER E WITH ACUTE*/
			"\u00CA",/*	�	c3 8a	&Ecirc;		� 	&#202;	� 	LATIN CAPITAL LETTER E WITH CIRCUMFLEX*/
			"\u00CB",/*	�	c3 8b	&Euml;		� 	&#203;	� 	LATIN CAPITAL LETTER E WITH DIAERESIS*/
			"\u00CC",/*	�	c3 8c	&Igrave;	� 	&#204;	� 	LATIN CAPITAL LETTER I WITH GRAVE*/
			"\u00CD",/*	�	c3 8d	&Iacute;	� 	&#205;	� 	LATIN CAPITAL LETTER I WITH ACUTE*/
			"\u00CE",/*	�	c3 8e	&Icirc;		� 	&#206;	� 	LATIN CAPITAL LETTER I WITH CIRCUMFLEX*/
			"\u00CF",/*	�	c3 8f	&Iuml;		� 	&#207;	� 	LATIN CAPITAL LETTER I WITH DIAERESIS*/
			"\u00D0",/*	�	c3 90	&ETH;		� 	&#208;	� 	LATIN CAPITAL LETTER ETH*/
			"\u00D1",/*	�	c3 91	&Ntilde;	� 	&#209;	� 	LATIN CAPITAL LETTER N WITH TILDE*/
			"\u00D2",/*	�	c3 92	&Ograve;	� 	&#210;	� 	LATIN CAPITAL LETTER O WITH GRAVE*/
			"\u00D3",/*	�	c3 93	&Oacute;	� 	&#211;	� 	LATIN CAPITAL LETTER O WITH ACUTE*/
			"\u00D4",/*	�	c3 94	&Ocirc;		� 	&#212;	� 	LATIN CAPITAL LETTER O WITH CIRCUMFLEX*/
			"\u00D5",/*	�	c3 95	&Otilde;	� 	&#213;	� 	LATIN CAPITAL LETTER O WITH TILDE*/
			"\u00D6",/*	�	c3 96	&Ouml;		� 	&#214;	� 	LATIN CAPITAL LETTER O WITH DIAERESIS*/
			"\u00D8",/*	�	c3 98	&Oslash;	� 	&#216;	� 	LATIN CAPITAL LETTER O WITH STROKE*/
			"\u00D9",/*	�	c3 99	&Ugrave;	� 	&#217;	� 	LATIN CAPITAL LETTER U WITH GRAVE*/
			"\u00DA",/*	�	c3 9a	&Uacute;	� 	&#218;	� 	LATIN CAPITAL LETTER U WITH ACUTE*/
			"\u00DB",/*	�	c3 9b	&Ucirc;		� 	&#219;	� 	LATIN CAPITAL LETTER U WITH CIRCUMFLEX*/
			"\u00DC",/*	�	c3 9c	&Uuml;		� 	&#220;	� 	LATIN CAPITAL LETTER U WITH DIAERESIS*/
			"\u00DD",/*	�	c3 9d	&Yacute;	� 	&#221;	� 	LATIN CAPITAL LETTER Y WITH ACUTE*/
			"\u00DE",/*	�	c3 9e	&THORN;		� 	&#222;	� 	LATIN CAPITAL LETTER THORN*/
			"\u00DF",/*	�	c3 9f	&szlig;		� 	&#223;	� 	LATIN SMALL LETTER SHARP S*/
			"\u00E0",/*	�	c3 a0	&agrave;	� 	&#224;	� 	LATIN SMALL LETTER A WITH GRAVE*/
			"\u00E1",/*	�	c3 a1	&aacute;	� 	&#225;	� 	LATIN SMALL LETTER A WITH ACUTE*/
			"\u00E2",/*	�	c3 a2	&acirc;		� 	&#226;	� 	LATIN SMALL LETTER A WITH CIRCUMFLEX*/
			"\u00E3",/*	�	c3 a3	&atilde;	� 	&#227;	� 	LATIN SMALL LETTER A WITH TILDE*/
			"\u00E4",/*	�	c3 a4	&auml;		� 	&#228;	� 	LATIN SMALL LETTER A WITH DIAERESIS*/
			"\u00E5",/*	�	c3 a5	&aring;		� 	&#229;	� 	LATIN SMALL LETTER A WITH RING ABOVE*/
			"\u00E6",/*	�	c3 a6	&aelig;		� 	&#230;	� 	LATIN SMALL LETTER AE*/
			"\u00E7",/*	�	c3 a7	&ccedil;	� 	&#231;	� 	LATIN SMALL LETTER C WITH CEDILLA*/
			"\u00E8",/*	�	c3 a8	&egrave;	� 	&#232;	� 	LATIN SMALL LETTER E WITH GRAVE*/
			"\u00E9",/*	�	c3 a9	&eacute;	� 	&#233;	� 	LATIN SMALL LETTER E WITH ACUTE*/
			"\u00EA",/*	�	c3 aa	&ecirc;		� 	&#234;	� 	LATIN SMALL LETTER E WITH CIRCUMFLEX*/
			"\u00EB",/*	�	c3 ab	&euml;		� 	&#235;	� 	LATIN SMALL LETTER E WITH DIAERESIS*/
			"\u00EC",/*	�	c3 ac	&igrave;	� 	&#236;	� 	LATIN SMALL LETTER I WITH GRAVE*/
			"\u00ED",/*	�	c3 ad	&iacute;	� 	&#237;	� 	LATIN SMALL LETTER I WITH ACUTE*/
			"\u00EE",/*	�	c3 ae	&icirc;		� 	&#238;	� 	LATIN SMALL LETTER I WITH CIRCUMFLEX*/
			"\u00EF",/*	�	c3 af	&iuml;		� 	&#239;	� 	LATIN SMALL LETTER I WITH DIAERESIS*/
			"\u00F0",/*	�	c3 b0	&eth;		� 	&#240;	� 	LATIN SMALL LETTER ETH*/
			"\u00F1",/*	�	c3 b1	&ntilde;	� 	&#241;	� 	LATIN SMALL LETTER N WITH TILDE*/
			"\u00F2",/*	�	c3 b2	&ograve;	� 	&#242;	� 	LATIN SMALL LETTER O WITH GRAVE*/
			"\u00F3",/*	�	c3 b3	&oacute;	� 	&#243;	� 	LATIN SMALL LETTER O WITH ACUTE*/
			"\u00F4",/*	�	c3 b4	&ocirc;		� 	&#244;	� 	LATIN SMALL LETTER O WITH CIRCUMFLEX*/
			"\u00F5",/*	�	c3 b5	&otilde;	� 	&#245;	� 	LATIN SMALL LETTER O WITH TILDE*/
			"\u00F6",/*	�	c3 b6	&ouml;		� 	&#246;	� 	LATIN SMALL LETTER O WITH DIAERESIS*/
			"\u00F8",/*	�	c3 b8	&oslash;	� 	&#248;	� 	LATIN SMALL LETTER O WITH STROKE*/
			"\u00F9",/*	�	c3 b9	&ugrave;	� 	&#249;	� 	LATIN SMALL LETTER U WITH GRAVE*/
			"\u00FA",/*	�	c3 ba	&uacute;	� 	&#250;	� 	LATIN SMALL LETTER U WITH ACUTE*/
			"\u00FB",/*	�	c3 bb	&ucirc;		� 	&#251;	� 	LATIN SMALL LETTER U WITH CIRCUMFLEX*/
			"\u00FC",/*	�	c3 bc	&uuml;		� 	&#252;	� 	LATIN SMALL LETTER U WITH DIAERESIS*/
			"\u00FD",/*	�	c3 bd	&yacute;	� 	&#253;	� 	LATIN SMALL LETTER Y WITH ACUTE*/
			"\u00FE",/*	�	c3 be	&thorn;		� 	&#254;	� 	LATIN SMALL LETTER THORN*/
			"\u00FF"/*	�	c3 bf	&yuml;		� 	&#255;	� 	LATIN SMALL LETTER Y WITH DIAERESIS*/
			);

	var safe = new Array(
			"A",/*	�	c3 80	&Agrave;	� 	&#192;	� 	LATIN CAPITAL LETTER A WITH GRAVE*/
			"A",/*	�	c3 81	&Aacute;	� 	&#193;	� 	LATIN CAPITAL LETTER A WITH ACUTE*/
			"A",/*	�	c3 82	&Acirc;		� 	&#194;	� 	LATIN CAPITAL LETTER A WITH CIRCUMFLEX*/
			"A",/*	�	c3 83	&Atilde;	� 	&#195;	� 	LATIN CAPITAL LETTER A WITH TILDE*/
			"A",/*	�	c3 84	&Auml;		� 	&#196;	� 	LATIN CAPITAL LETTER A WITH DIAERESIS*/
			"A",/*	�	c3 85	&Aring;		� 	&#197;	� 	LATIN CAPITAL LETTER A WITH RING ABOVE*/
			"A",/*	�	c3 86	&AElig;		� 	&#198;	� 	LATIN CAPITAL LETTER AE*/
			"C",/*	�	c3 87	&Ccedil;	� 	&#199;	� 	LATIN CAPITAL LETTER C WITH CEDILLA*/
			"E",/*	�	c3 88	&Egrave;	� 	&#200;	� 	LATIN CAPITAL LETTER E WITH GRAVE*/
			"E",/*	�	c3 89	&Eacute;	� 	&#201;	� 	LATIN CAPITAL LETTER E WITH ACUTE*/
			"E",/*	�	c3 8a	&Ecirc;		� 	&#202;	� 	LATIN CAPITAL LETTER E WITH CIRCUMFLEX*/
			"E",/*	�	c3 8b	&Euml;		� 	&#203;	� 	LATIN CAPITAL LETTER E WITH DIAERESIS*/
			"I",/*	�	c3 8c	&Igrave;	� 	&#204;	� 	LATIN CAPITAL LETTER I WITH GRAVE*/
			"I",/*	�	c3 8d	&Iacute;	� 	&#205;	� 	LATIN CAPITAL LETTER I WITH ACUTE*/
			"I",/*	�	c3 8e	&Icirc;		� 	&#206;	� 	LATIN CAPITAL LETTER I WITH CIRCUMFLEX*/
			"I",/*	�	c3 8f	&Iuml;		� 	&#207;	� 	LATIN CAPITAL LETTER I WITH DIAERESIS*/
			"Dj",/*	�	c3 90	&ETH;		� 	&#208;	� 	LATIN CAPITAL LETTER ETH*/
			"N",/*	�	c3 91	&Ntilde;	� 	&#209;	� 	LATIN CAPITAL LETTER N WITH TILDE*/
			"O",/*	�	c3 92	&Ograve;	� 	&#210;	� 	LATIN CAPITAL LETTER O WITH GRAVE*/
			"O",/*	�	c3 93	&Oacute;	� 	&#211;	� 	LATIN CAPITAL LETTER O WITH ACUTE*/
			"O",/*	�	c3 94	&Ocirc;		� 	&#212;	� 	LATIN CAPITAL LETTER O WITH CIRCUMFLEX*/
			"O",/*	�	c3 95	&Otilde;	� 	&#213;	� 	LATIN CAPITAL LETTER O WITH TILDE*/
			"O",/*	�	c3 96	&Ouml;		� 	&#214;	� 	LATIN CAPITAL LETTER O WITH DIAERESIS*/
			"O",/*	�	c3 98	&Oslash;	� 	&#216;	� 	LATIN CAPITAL LETTER O WITH STROKE*/
			"U",/*	�	c3 99	&Ugrave;	� 	&#217;	� 	LATIN CAPITAL LETTER U WITH GRAVE*/
			"U",/*	�	c3 9a	&Uacute;	� 	&#218;	� 	LATIN CAPITAL LETTER U WITH ACUTE*/
			"U",/*	�	c3 9b	&Ucirc;		� 	&#219;	� 	LATIN CAPITAL LETTER U WITH CIRCUMFLEX*/
			"U",/*	�	c3 9c	&Uuml;		� 	&#220;	� 	LATIN CAPITAL LETTER U WITH DIAERESIS*/
			"Y",/*	�	c3 9d	&Yacute;	� 	&#221;	� 	LATIN CAPITAL LETTER Y WITH ACUTE*/
			"B",/*	�	c3 9e	&THORN;		� 	&#222;	� 	LATIN CAPITAL LETTER THORN*/
			"S",/*	�	c3 9f	&szlig;		� 	&#223;	� 	LATIN SMALL LETTER SHARP S*/
			"a",/*	�	c3 a0	&agrave;	� 	&#224;	� 	LATIN SMALL LETTER A WITH GRAVE*/
			"a",/*	�	c3 a1	&aacute;	� 	&#225;	� 	LATIN SMALL LETTER A WITH ACUTE*/
			"a",/*	�	c3 a2	&acirc;		� 	&#226;	� 	LATIN SMALL LETTER A WITH CIRCUMFLEX*/
			"a",/*	�	c3 a3	&atilde;	� 	&#227;	� 	LATIN SMALL LETTER A WITH TILDE*/
			"a",/*	�	c3 a4	&auml;		� 	&#228;	� 	LATIN SMALL LETTER A WITH DIAERESIS*/
			"a",/*	�	c3 a5	&aring;		� 	&#229;	� 	LATIN SMALL LETTER A WITH RING ABOVE*/
			"a",/*	�	c3 a6	&aelig;		� 	&#230;	� 	LATIN SMALL LETTER AE*/
			"c",/*	�	c3 a7	&ccedil;	� 	&#231;	� 	LATIN SMALL LETTER C WITH CEDILLA*/
			"e",/*	�	c3 a8	&egrave;	� 	&#232;	� 	LATIN SMALL LETTER E WITH GRAVE*/
			"e",/*	�	c3 a9	&eacute;	� 	&#233;	� 	LATIN SMALL LETTER E WITH ACUTE*/
			"e",/*	�	c3 aa	&ecirc;		� 	&#234;	� 	LATIN SMALL LETTER E WITH CIRCUMFLEX*/
			"e",/*	�	c3 ab	&euml;		� 	&#235;	� 	LATIN SMALL LETTER E WITH DIAERESIS*/
			"i",/*	�	c3 ac	&igrave;	� 	&#236;	� 	LATIN SMALL LETTER I WITH GRAVE*/
			"i",/*	�	c3 ad	&iacute;	� 	&#237;	� 	LATIN SMALL LETTER I WITH ACUTE*/
			"i",/*	�	c3 ae	&icirc;		� 	&#238;	� 	LATIN SMALL LETTER I WITH CIRCUMFLEX*/
			"i",/*	�	c3 af	&iuml;		� 	&#239;	� 	LATIN SMALL LETTER I WITH DIAERESIS*/
			"o",/*	�	c3 b0	&eth;		� 	&#240;	� 	LATIN SMALL LETTER ETH*/
			"n",/*	�	c3 b1	&ntilde;	� 	&#241;	� 	LATIN SMALL LETTER N WITH TILDE*/
			"o",/*	�	c3 b2	&ograve;	� 	&#242;	� 	LATIN SMALL LETTER O WITH GRAVE*/
			"o",/*	�	c3 b3	&oacute;	� 	&#243;	� 	LATIN SMALL LETTER O WITH ACUTE*/
			"o",/*	�	c3 b4	&ocirc;		� 	&#244;	� 	LATIN SMALL LETTER O WITH CIRCUMFLEX*/
			"o",/*	�	c3 b5	&otilde;	� 	&#245;	� 	LATIN SMALL LETTER O WITH TILDE*/
			"o",/*	�	c3 b6	&ouml;		� 	&#246;	� 	LATIN SMALL LETTER O WITH DIAERESIS*/
			"o",/*	�	c3 b8	&oslash;	� 	&#248;	� 	LATIN SMALL LETTER O WITH STROKE*/
			"u",/*	�	c3 b9	&ugrave;	� 	&#249;	� 	LATIN SMALL LETTER U WITH GRAVE*/
			"u",/*	�	c3 ba	&uacute;	� 	&#250;	� 	LATIN SMALL LETTER U WITH ACUTE*/
			"u",/*	�	c3 bb	&ucirc;		� 	&#251;	� 	LATIN SMALL LETTER U WITH CIRCUMFLEX*/
			"u",/*	�	c3 bc	&uuml;		� 	&#252;	� 	LATIN SMALL LETTER U WITH DIAERESIS*/
			"y",/*	�	c3 bd	&yacute;	� 	&#253;	� 	LATIN SMALL LETTER Y WITH ACUTE*/
			"b",/*	�	c3 be	&thorn;		� 	&#254;	� 	LATIN SMALL LETTER THORN*/
			"y"/*	�	c3 bf	&yuml;		� 	&#255;	� 	LATIN SMALL LETTER Y WITH DIAERESIS*/
			);

	var signs = new Array(
			"\u0021",/*	!	21 						&#33;	! 	EXCLAMATION MARK*/
			"\u0022",/*	"	22		&quot;		" 	&#34;	" 	QUOTATION MARK*/
			"\u0023",/*	#	23						&#35;	# 	NUMBER SIGN*/
			"\u0024",/*	$	24						&#36;	$ 	DOLLAR SIGN*/
			"\u0025",/*	%	25						&#37;	% 	PERCENT SIGN*/
			"\u0026",/*	&	26		&amp;		& 	&#38;	& 	AMPERSAND*/
			"\u0027",/*	'	27						&#39;	' 	APOSTROPHE*/
			"\u0028",/*	(	28						&#40;	( 	LEFT PARENTHESIS*/
			"\u0029",/*	)	29						&#41;	) 	RIGHT PARENTHESIS*/
			"\u002A",/*	*	2a						&#42;	* 	ASTERISK*/
			"\u002B",/*	+	2b						&#43;	+ 	PLUS SIGN*/
			"\u002C",/*	,	2c						&#44;	, 	COMMA*/
			//"\u002D",/*	-	2d						&#45;	- 	HYPHEN-MINUS*/
			"\u002E",/*	.	2e						&#46;	. 	FULL STOP*/
			"\u002F",/*	/	2f						&#47;	/ 	SOLIDUS*/
			"\u003A",/*	:	3a 						&#58;	: 	COLON*/
			"\u003B",/*	;	3b						&#59;	; 	SEMICOLON*/
			"\u003C",/*	<	3c		&lt;		< 	&#60;	< 	LESS-THAN SIGN*/
			"\u003D",/*	=	3d						&#61;	= 	EQUALS SIGN*/
			"\u003E",/*	>	3e		&gt;		> 	&#62;	> 	GREATER-THAN SIGN*/
			"\u003F",/*	?	3f						&#63;	? 	QUESTION MARK*/
			"\u0040",/*	@	40						&#64;	@ 	COMMERCIAL AT*/
			"\u005B",/*	[	5b 						&#91;	[ 	LEFT SQUARE BRACKET */
			"\u005D",/*	]	5d						&#93;	] 	RIGHT SQUARE BRACKET*/
			"\u005E",/*	^	5e						&#94;	^ 	CIRCUMFLEX ACCENT*/
			//"\u005F",/*	_	5f						&#95;	_ 	LOW LINE*/
			"\u0060",/*	`	60						&#96;	` 	GRAVE ACCENT*/
			"\u007B",/*	{	7b 						&#123;	{ 	LEFT CURLY BRACKET*/
			"\u007C",/*	|	7c						&#124;	| 	VERTICAL LINE*/
			"\u007D",/*	}	7d						&#125;	} 	RIGHT CURLY BRACKET*/
			"\u007E",/*	~	7e						&#126;	~ 	TILDE*/
			"\u00A0",/*	 	c2 a0	&nbsp;		  	&#160;	  	NO-BREAK SPACE*/
			"\u00A1",/*	�	c2 a1	&iexcl;		� 	&#161;	� 	INVERTED EXCLAMATION MARK*/
			"\u00A2",/*	�	c2 a2	&cent;		� 	&#162;	� 	CENT SIGN*/
			"\u00A3",/*	�	c2 a3	&pound;		� 	&#163;	� 	POUND SIGN*/
			"\u00A4",/*	�	c2 a4	&curren;	� 	&#164;	� 	CURRENCY SIGN*/
			"\u00A5",/*	�	c2 a5	&yen;		� 	&#165;	� 	YEN SIGN*/
			"\u00A6",/*	�	c2 a6	&brvbar;	� 	&#166;	� 	BROKEN BAR*/
			"\u00A7",/*	�	c2 a7	&sect;		� 	&#167;	� 	SECTION SIGN*/
			"\u00A8",/*	�	c2 a8	&uml;		� 	&#168;	� 	DIAERESIS*/
			"\u00A9",/*	�	c2 a9	&copy;		� 	&#169;	� 	COPYRIGHT SIGN*/
			"\u00AA",/*	�	c2 aa	&ordf;		� 	&#170;	� 	FEMININE ORDINAL INDICATOR*/
			"\u00AB",/*	�	c2 ab	&laquo;		� 	&#171;	� 	LEFT-POINTING DOUBLE ANGLE QUOTATION MARK*/
			"\u00AC",/*	�	c2 ac	&not;		� 	&#172;	� 	NOT SIGN*/
			"\u00AD",/*	�	c2 ad	&shy;		� 	&#173;	� 	SOFT HYPHEN*/
			"\u00AE",/*	�	c2 ae	&reg;		� 	&#174;	� 	REGISTERED SIGN*/
			"\u00AF",/*	�	c2 af	&macr;		� 	&#175;	� 	MACRON*/
			"\u00B0",/*	�	c2 b0	&deg;		� 	&#176;	� 	DEGREE SIGN*/
			"\u00B1",/*	�	c2 b1	&plusmn;	� 	&#177;	� 	PLUS-MINUS SIGN*/
			"\u00B2",/*	�	c2 b2	&sup2;		� 	&#178;	� 	SUPERSCRIPT TWO*/
			"\u00B3",/*	�	c2 b3	&sup3;		� 	&#179;	� 	SUPERSCRIPT THREE*/
			"\u00B4",/*	�	c2 b4	&acute;		� 	&#180;	� 	ACUTE ACCENT*/
			"\u00B5",/*	�	c2 b5	&micro;		� 	&#181;	� 	MICRO SIGN*/
			"\u00B6",/*	�	c2 b6	&para;		� 	&#182;	� 	PILCROW SIGN*/
			"\u00B7",/*	�	c2 b7	&middot;	� 	&#183;	� 	MIDDLE DOT*/
			"\u00B8",/*	�	c2 b8	&cedil;		� 	&#184;	� 	CEDILLA*/
			"\u00B9",/*	�	c2 b9	&sup1;		� 	&#185;	� 	SUPERSCRIPT ONE*/
			"\u00BA",/*	�	c2 ba	&ordm;		� 	&#186;	� 	MASCULINE ORDINAL INDICATOR*/
			"\u00BB",/*	�	c2 bb	&raquo;		� 	&#187;	� 	RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK*/
			"\u00BC",/*	�	c2 bc	&frac14;	� 	&#188;	� 	VULGAR FRACTION ONE QUARTER*/
			"\u00BD",/*	�	c2 bd	&frac12;	� 	&#189;	� 	VULGAR FRACTION ONE HALF*/
			"\u00BE",/*	�	c2 be	&frac34;	� 	&#190;	� 	VULGAR FRACTION THREE QUARTERS*/
			"\u00BF",/*	�	c2 bf	&iquest;	� 	&#191;	� 	INVERTED QUESTION MARK*/
			"\u00D7",/*	�	c3 97	&times;		� 	&#215;	� 	MULTIPLICATION SIGN*/
			"\u00F7"/*	�	c3 b7	&divide;	� 	&#247;	� 	DIVISION SIGN*/
			//"\u005C" /*	\	5c						&#92;	\ 	REVERSE SOLIDUS*/
			);


	for (var i=0; i < unsafe.length; i++) {
		s = s.replace(new RegExp(unsafe[i], 'g'), safe[i])
		.replace(new RegExp('[\¡\™\£\¢\§\ˆ\¶\•\ª\º\–\≠\œ\˙\´\®\þ\¥\¨\ʼ\ø\,\“\‘\«\¯\ß]','g'), '')
		.replace(new RegExp('[\©\ˍ\˝\˚\…\æ\ˀ\.\¸\˛\≤\≥\÷\Œ̇́\‰\Œ\Þ̄\Ð\Ð̵̵\¿\ʔ\„]','g'), '')
		.replace(new RegExp('[!\"\#\$\%\&\'()\*\+,\.\/\:\;<=>\?@\[\\\]\^\`\{\|\}\~\]','g'), '')
		.replace(new RegExp('[' + signs.join('\\') + ']','g'), '')
		.replace(/\\/g, '')
		.toLowerCase() 				// change everything to lowercase*;
		.replace(regexSep, sep) 		// replace multiple instances of the hyphen with a single instance
		.replace(new RegExp('[' + nonSep + ']', 'g'), sep) 	// change all spaces and underscores to a hyphen
		.replace(/[\s]/g, sep) 	// change all spaces and underscores to a hyphen
		.replace(/^\s+|\s+$/g, "");	// trim leading and trailing spaces
	}
	return s;

};

String.prototype.ivCapitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
}

String.prototype.ivCamelize = function() {
	return this.toLowerCase().toUpperCaseEachWord();
}

String.prototype.ivToUpperCaseFirstChar = function() {
	return this.substr( 0, 1 ).toUpperCase() + this.substr( 1 );
}

String.prototype.ivToLowerCaseFirstChar = function() {
	return this.substr( 0, 1 ).toLowerCase() + this.substr( 1 );
}

String.prototype.ivToUpperCaseEachWord = function( delim ) {
	delim = delim ? delim : ' ';
	return this.split( delim ).map( function(v) { return v.toUpperCaseFirstChar() } ).join( delim );
}

String.prototype.ivToLowerCaseEachWord = function( delim ) {
	delim = delim ? delim : ' ';
	return this.split( delim ).map( function(v) { return v.toLowerCaseFirstChar() } ).join( delim );
}

String.prototype.float = function () {
	return parseFloat(this.replace(',', ''));
};

//use like str_replace from phpâ€¦. use like 'okay.this.is.a.string'.replaceAll('.', ' ');
String.prototype.ivReplaceAll = function (token, newToken, ignoreCase) {
	 var _token;
	 var str = this + "";
	 var i = -1;

	 if (typeof token === "string") {

		  if (ignoreCase) {

				_token = token.toLowerCase();

				while ((
						  i = str.toLowerCase().indexOf(
						  token, i >= 0 ? i + newToken.length : 0
						  )) !== -1
						  ) {
					 str = str.substring(0, i) +
								newToken +
								str.substring(i + token.length);
				}

		  } else {
				return this.split(token).join(newToken);
		  }

	 }
	 return str;
};
