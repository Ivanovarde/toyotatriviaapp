
// GLOBAL VARIABLES

var window = window;
var l = window.location;

var console = window.console;
var $, jQuery = $;
var document = window.document;

var loadingData = false;
var currentAjaxProcess = '';

var device = '';
var lastOrientation = '';
var ivScrollTop = 0;
var scrollDirectionLastPos = 0;
var scrollDirection = 0;
var windowWidth = '';

var debug = false;
var queryDebug = '';


// App settings global vars
var currentDate = new Date();
var remote_server_url = "";
var remote_data_url = "";

var themesSelected = [];

var questionsTimeMode = true; 				// Wehter or not questions have time to be answered
var questionsIndex = 0; 						// Index of the current question during the trivia
var questionsChangeTime = 3; 					// Time to wait before change to the next question(in seconds)
var questionsMaxLength = 5; 					// Number of questions for the trivia
var questionsSelectedQuestions = [];		// Array to stored the selected random questions
var questionsAnswerMaxTime = 15; 			// Max time to answer a question (in seconds)
var questionsAnswerTimer = null;				// js timer object
var questionsSuccessLength = 0;				// Number of successfull answered questions
var questionsShowIncorrects = false;		// Show or not failed answers

var dashboardSuccessLength = null;
var dashboardQuestionsMaxlength = null;
var dashBoardTitleLow = '¡Hay que mejorar!';
var dashboardSubtitleLow = '¡Suerte para la próxima!';
var dashBoardTitleMedium = '¡Buen trabajo!';
var dashboardSubtitleMedium = '¡Superaste el desafío!';
var dashBoardTitleHigh = '¡Excelente trabajo!';
var dashboardSubtitleHigh = '¡Tenés un gran conocimiento sobre Toyota!';
var dashBoardTitleMax= '¡Felicitaciones!';
var dashboardSubtitleMax = '¡Sos un experto en Toyota!';


// App Selectors
var ivBody = null;

var preloader = null;

var themesHolder = null;
var themesBoxes = null;

var questionsPanel = null;
var questionsClock = null;
var questionsAnswerTimeHolder = null;
var questionListContainer = null;
var questionsContainers = null;
var questionsIndicatorsHolder = null;
var questionsIndicators = null;
var questionsStatusPanel = null;

var dashboard = $('#dashboard');
var dashboardHeader = null;
var dashboardFooter = null;
var dashboardCupHolder = null;
var dashboardTitle = null;
var dashboardSubtitle = null;



// Window Scroll
$(window).scroll(function(e){

	window.detectScrollDirection();
	ivScrollTop = $(window).scrollTop();

	//showHideBackToTop();

});

// Native window resize
$(window).on('resize', function () {

	updateSite();

});

//Using afterresize plugin
$(window).afterResize( function() {

	if(debug){console.log('Main > After resize fired');}

	//if(debug){window.getScreenInfo();}

	updateSite();

}, false, 100 );

// Window load
$(window).bind('load', function(){

	if(debug){console.log('Main > load: Start');}

	$('footer').removeClass('on');

	app.initialize();

	if(debug){console.log('Main > load: End');}

});

// jQuery ready
jQuery(document).ready(function() {

	if(debug){console.log('Main > documentReady: Start');}

	$.support.cors = true;
	//$.mobile.allowCrossDomainPages = true;

	//setIconButtonSize();

	$.ajaxSetup({timeout: 30000});
	$(document).ajaxStart(function(){ $("#ajax-status").show(); });
	$(document).ajaxStop(function(){  $("#ajax-status").hide(); });
	$(document).ajaxError(function(e, jqxhr, settings, exception) {
		loadingData = false;
		console.log('Ajax Error');
		console.log(e);
		console.log(jqxhr);
		console.log(settings);
		console.log(exception);
		$("#ajax-status").hide();
	});

	setVars();
	init();

	if(debug){console.log('Main > documentReady: End');}

});


// LISTENERS
//$('[class*="group"]').livequery(function () { fadingElements( {type: 'show'} ); });
//$(document).on('click', '.move-to', function (e) { moveTo({'e': e}); });
$(document).on('submit', 'form.validate', function(e){ preSubmitForm(  {'e': e, el: $(this)}); return false; });
$(document).on('click', 'a[href="#"]', function(e){ e.preventDefault(); });
$(document).on('click', '.change-section', function(e){ e.preventDefault(); changeSection($(this).data('target'));});
$(document).on('click', '.theme-box', function(e){ selectTheme({e: e, el: $(this)});});
$(document).on('click', '.end-game', function(e){ endGame({e: e, el: $(this)});});
$(document).on('click', '.question-option', function(e){ validateQuestion({e: e, el: $(this)});});


/*FUNCTIONS*/
function setVars() {

	if(debug){console.log('Main > setVars: Start');}

	ivBody = $('body');
	ivScrollTop = $(window).scrollTop();
	preloader = $('.preloader');

	remote_server_url = window.localStorage.getItem("remote_server_url");

	themesHolder = $('.themes-holder');
	themesBoxes = $('.theme-box');

	questionsStatusPanel = $('.answer-status');
	questionsIndicatorsHolder = $('.questions-indicators-holder');
	questionsIndicators = questionsIndicatorsHolder.find('> span');
	questionsPanel = $('.questions-panel');
	questionListContainer = $('.question-list');
	questionsClock = $('.questions-clock');
	questionsAnswerTimeHolder = $('.questions-time');
	dashboardSuccessLength = $('.dashboard-success-length');
	dashboardQuestionsMaxlength = $('.dashboard-questions-maxlength');
	dashboardHeader = dashboard.find('.header-inner');
	dashboardFooter = dashboard.find('.footer-inner');
	dashboardCupHolder = dashboard.find('.dashboard-cup-holder');
	dashboardTitle = $('.dashboard-title');
	dashboardSubtitle = $('.dashboard-subtitle');

	if(debug){console.log('Main > setVars: End');}

}

function init() {

	if(debug){console.log('Main > init: Start');}

	if(debug){window.getScreenInfo();}

	//getMainData();

	leadsUpdateStoredCounter();

	startTrivia();

	if(debug){console.log('Main > init: End');}

}

function updateSite(){

	// Update device and orientation variables
	device = window.getDeviceType();
	windowWidth = window.getWindowWidth();
	showHideBackToTop();
	queryDebug = parseInt($('#debug').data('querydebug'));

}

function startTrivia(){

	if(debug){console.log('Main > startTrivia: Start');}

	removePreloader();

	window.setTimeout(function(){

		changeSection('#welcome');

	}, 2000);

	if(debug){console.log('Main > startTrivia: End');}

}

function removePreloader(){

	if(debug){console.log('Main > removePreloader: Start');}

	preloader.removeClass('preloader-off');

	window.setTimeout(function(){

		preloader.addClass('preloader-off');

		$('html').removeClass('locked');

		if(debug){console.log('Main > removePreloader: End');}

	}, 2000);
}

function endGame(data){

	data.e.preventDefault();

	resetGame();

}

function resetGame(){

	$("form.validate").get(0).reset();

	themesSelected.length = 0;
	themesBoxes.removeClass('active');

	questionsIndicators.removeClass('active fail success timeout correct');
	//questionsContainers.removeClass('active');
	//questionsContainers.eq(0).addClass('active');
	//questionsContainers.find('.question-option').removeClass('active fail success timeout correct');
	questionsSuccessLength = 0;
	questionsIndex = 0;
	questionsSelectedQuestions.length = 0;
	questionListContainer.html('');

	questionsStatusPanel.removeClass('active fail success timeout');

	dashboardHeader.removeClass('show');
	dashboardFooter.removeClass('show');
	dashboardCupHolder.removeClass('show');
	dashboardTitle.html('');
	dashboardSubtitle.html('');

	changeSection('#welcome');

}

function changeSection(selector){

	if(debug){console.log('Main > changeSection: Start');}

	var newSection = $('section > div' + selector);
	var newSectionID = newSection.attr('id');
	var currentSection = $('section > div.active');
	var currentSectionID = currentSection.attr('id');

	if(!newSection.length)
	{
		if(debug){console.log('There is no section to change to (' + selector + ')');}
		return;
	}

	if(debug){console.log('currentSectionID: ' + currentSectionID + ' - newSectionID: ' + newSectionID);}

	switch(newSectionID)
	{

		case 'welcome':

		break;

		case 'user-data':

		break;

		case 'themes':

			//Validate form
			//Takes user data and keep it in memory
			if(!leadsKeepInMemory())
			{
				return false;
			}

		break;

		case 'questions':

			if(themesSelected.length != 2){

				themesHolder.addClass('shake');
				window.setTimeout(function(){
					themesHolder.removeClass('shake');
				}, 500);

				if(debug){console.log('Main > changeSection: Select 2 themes');|
				return;
			}

			selectQuestions();
			formatQuestions();
			showQuestion();

		break;

		case 'dashboard':

			getTriviaScore();

			//Add score to the lead data in memory
			aCurrentRecord.date = currentDate.sqlFormat();
			aCurrentRecord.score = questionsSuccessLength;
			aCurrentRecord.topics = themesSelected.join(', ');
			leadsStoreLocally(aCurrentRecord);

			if(debug){console.log('Main > aCurrentRecord detail (next line)');}
			//if(debug){console.log(aCurrentRecord);}

		break;

	}

	currentSection.removeClass('active');
	newSection.addClass('active');

	if(debug){console.log('Main > changeSection: End');}

}

function selectTheme(data){

	if(debug){console.log('Main > selectTheme: Start');}

	var e = data.e;
	var el = data.el;
	var themesContainer = $('#themes');

	e.preventDefault();

	el.toggleClass('active');

	//if(debug){console.log(activeThemes.length);}

	themesSelected.length = 0;

	if(themesContainer.find('.theme-box.active').length > 2)
	{
		el.removeClass('active');
		return;
	}
	else{

		themesContainer.find('.theme-box.active').each(function(i, o){
			themesSelected.push($(o).data('theme'));
		});

	}

	if(debug){
		console.log('themesSelected: ' + themesSelected);
		console.log('themesSelectedLength: ' + themesSelected.length);

		console.log('Main > selectTheme: End');
	}

}

function getRandomIntInclusive(min, max) {

	min = Math.ceil(min);
	max = Math.floor(max);
	//The maximum is inclusive and the minimum is inclusive
	return Math.floor(Math.random() * (max - min + 1) + min);

}

function selectQuestions(){

	if(debug){console.log('Main > selectQuestions: Start');}

	var selectedQuestions = [];
	var arrThemeOne = [];
	var arrThemeTwo = [];
	var arrQuestionsOne = [];
	var arrQuestionsTwo = [];
	var idx;
	var currentTheme;
	var currentQuestions;
	var currentQuestion;

	arrThemeOne = JSON.parse(JSON.stringify(window.mainContent.temas[themesSelected[0]]));
	arrThemeTwo = JSON.parse(JSON.stringify(window.mainContent.temas[themesSelected[1]]));
	arrQuestionsOne = arrThemeOne.preguntas;
	arrQuestionsTwo = arrThemeTwo.preguntas;

	for(var t = 0; t < questionsMaxLength; t++){

		//Select theme: first 3 theme 1, last 2 theme 2
		if(t < Math.ceil(questionsMaxLength / 2) ){

			currentTheme = arrThemeOne;
			currentQuestions = arrQuestionsOne;

		}else{

			currentTheme = arrThemeTwo;
			currentQuestions = arrQuestionsTwo;

		}

		idx = getRandomIntInclusive(1, currentQuestions.length) - 1;
		currentQuestion = currentQuestions[idx];
		currentQuestion.themename = currentTheme.nombre;
		currentQuestion.themecolor = currentTheme.color;
		selectedQuestions.push(currentQuestion);

		//remove the item from the array to avoid duplicated
		currentQuestions.splice(currentQuestions[idx], 1);

	}

	if(debug){
		//console.log(currentQuestion);
		//console.log(selectedQuestions);
		//console.log(window.mainContent.temas.producto.preguntas);
	}

	questionsSelectedQuestions = selectedQuestions;

	if(debug){console.log('Main > selectQuestions: End');}

}

function formatQuestions(){

	if(debug){console.log('Main > formatQuestions: Start');}

	var questionMarkup = getQuestionMarkup('question');
	var optionMarkup = getQuestionMarkup('option');
	var outputQuestion = '';
	var outputOptions = '';
	var correctIndex = ''
	var optionValue = '';
	var selectedQuestions = questionsSelectedQuestions;

	$(selectedQuestions).each(function(i, cc){

		var currentQuestion = cc;
		var currentQuestionsOptions = currentQuestion.respuestas;

		correctIndex = currentQuestion.correcta;
		optionValue = '';
		outputOptions = '';

		$(currentQuestionsOptions).each(function(j, co){

			optionValue = correctIndex == j ? 'success' : 'fail';

			outputOptions += optionMarkup;
			outputOptions = outputOptions.replace('{value}', optionValue);
			outputOptions = outputOptions.replace('{optiontext}', co);

		});

		outputQuestion = questionMarkup;
		outputQuestion = outputQuestion.replace('{color}', currentQuestion.themecolor);
		outputQuestion = outputQuestion.replace('{themename}', currentQuestion.themename);
		outputQuestion = outputQuestion.replace('{questiontext}', currentQuestion.pregunta);
		outputQuestion = outputQuestion.replace('{options}', outputOptions);

		questionListContainer.append(outputQuestion);

	});


	questionsIndicators.eq(0).addClass('active');

	questionsContainers = questionsPanel.find('.questions-container');

	if(debug){console.log('Main > formatQuestions: End');}

}

function showQuestion(){

	if(debug){console.log('Main > showQuestion: Start');}

	var currentQuestionContainer = questionsContainers.eq(questionsIndex);
	var currentQuestionsText = currentQuestionContainer.find('.question-text');
	var currentQuestionsOptions = currentQuestionContainer.find('.question-option');

	questionsPanel.addClass('loading');
	questionsStatusPanel.removeClass('fail success timeout');

	if(debug){console.log('questionsIndex + 1 (' + parseInt(questionsIndex + 1) + ') > questionsMaxLength (' +  questionsMaxLength + ')');}

	if(parseInt(questionsIndex + 1) > questionsMaxLength)
	{

		dashboardSuccessLength.html(questionsSuccessLength);
		dashboardQuestionsMaxlength.html(questionsMaxLength);

		changeSection('#dashboard');

		if(debug){console.log('showQuestion: Go to dashboard');}
		return;

	}

	questionsContainers.removeClass('active');
	questionsIndicators.eq(questionsIndex).addClass('active');
	currentQuestionContainer.addClass('active');

	window.setTimeout(function(){

		window.setTimeout(function(){

			currentQuestionsText.addClass('show');

		}, 250);

		currentQuestionsOptions.each(function(i, qo){

			var questionOption = $(qo);

			window.setTimeout(function(){

				questionOption.addClass('show');

			}, i * 250);

		});

		questionsPanel.removeClass('loading');

		startQuestionTimer();

	}, 1000);

	if(debug){console.log('Main > showQuestion: End');}

}

function getQuestionMarkup(mode){

	var question = '<div class=" questions-container">' +

			'<div class="label-container">' +
				'<div class="label bg-{color}">{themename}</div>' +
			'</div>' +

			'<div class="question-text">{questiontext}</div>' +

			'{options}' +

		'</div>';

	var option = '<div class="question-option " data-value="{value}">' +
				'<span>{optiontext}</span>' +
			'</div>';


	if(mode == 'question'){
		return question;
	}

	if(mode == 'option'){
		return option;
	}

	return;
}

function startQuestionTimer(){

	if(debug){console.log('Main > startQuestionTimer: Start');}

	var qTime = questionsAnswerMaxTime;

	questionsAnswerTimeHolder.text(qTime);

	// If we are playing in timing mode, start the clock
	if(questionsTimeMode){

		if(questionsAnswerTimer !== null)
		{
			stopQuestionTimer();
		}

		questionsClock.addClass('spin');

		questionsAnswerTimer = window.setInterval(function(){

			qTime--;

			// Question timeout
			if(qTime < 1)
			{

				if(debug){console.log('questionsAnswerTimer: ' + questionsAnswerTimer);}

				validateQuestion();

			}

			questionsAnswerTimeHolder.text(qTime);

		}, 1000);

	}else
	{
		if(debug){console.log('Main > startQuestionTimer: Timer mode: ' + questionsTimeMode);}
	}

	if(debug){console.log('Main > startQuestionTimer: End');}

}

function stopQuestionTimer(){

	questionsClock.removeClass('spin');
	window.clearTimeout(questionsAnswerTimer);

}

function validateQuestion(data){

	var data = data === undefined ? {} : data;
	var e = data.e || jQuery.Event( "click" );
	var el = data.el || $();
	var elValue = el.data('value') || 'timeout';
	//var questionContainer = $('.questions-container.active');
	var currentQuestionContainer = questionsContainers.eq(questionsIndex);
	var currentQuestions = currentQuestionContainer.find('.question-option').not(el);

	e.preventDefault();

	//if(debug){console.log(currentQuestions);}

	// If we are playing in timing mode, stop the clock
	if(questionsTimeMode)
	{

		stopQuestionTimer();

	}

	if(elValue == 'success')
	{

		if(debug){console.log('correcto!!');}
		el.addClass(elValue);
		questionsSuccessLength++;

	}
	else
	{

		if(debug){console.log('incorrecto!!');}
		el.addClass('selected');

	}

	currentQuestions.each(function(i, o)
	{

		var option = $(o);
		var dataValue = option.data('value');

		if(!questionsShowIncorrects && dataValue == 'fail')
		{
			dataValue = '';
		}

		if(dataValue == 'success')
		{
			dataValue = 'correct';
		}

		option.addClass(dataValue);

	});

	questionsStatusPanel.addClass(elValue);
	questionsIndicators.eq(questionsIndex).removeClass('active').addClass(elValue);

	if(debug){
		//console.log('questionsIndex: ' + questionsIndex);
		//console.log('questionsIndicators.eq(questionsIndex)');
		//console.log(questionsIndicators.eq(questionsIndex));
	}

	questionsIndex++;

	window.setTimeout( function()
	{

		showQuestion(questionsIndex);

	}, parseInt(questionsChangeTime * 1000) );

}

function getTriviaScore(){

	if(debug){console.log('Main > getTriviaScore: Start');}

	//var third = Math.ceil(questionsMaxLength / 100 * 33.33);
	//var half = Math.ceil(questionsMaxLength / 100 * 50);
	var successPercent = questionsSuccessLength / questionsMaxLength * 100;

	if(debug){console.log('Main > getTriviaScore: successPercent: ' + successPercent);}

	if(successPercent < 35)
	{
		dashboardTitle.html(dashBoardTitleLow);
		dashboardSubtitle.html(dashboardSubtitleLow);
	}
	else if(successPercent < 71 )
	{
		dashboardTitle.html(dashBoardTitleMedium);
		dashboardSubtitle.html(dashboardSubtitleMedium);
	}
	else if(successPercent < 99 )
	{
		dashboardTitle.html(dashBoardTitleHigh);
		dashboardSubtitle.html(dashboardSubtitleHigh);
	}
	else
	{
		dashboardTitle.html(dashBoardTitleMax);
		dashboardSubtitle.html(dashboardSubtitleMax);
	}

	window.setTimeout(function(){

		dashboardCupHolder.addClass('show');

		window.setTimeout(function(){

			dashboardHeader.addClass('show');

			window.setTimeout(function(){

				dashboardFooter.addClass('show');

			}, 500);

		}, 350);

	}, 350);

	if(debug){console.log('Main > getTriviaScore: End');}

}

function ajaxReadyCheck(request) {
	var r = true;

	if(debug){console.log('ajaxReadyCHeck -> loadingData value: ' + loadingData + ' (about to call ' + request + ')');}

	if (loadingData) {
		if(debug){console.log('Can\'t perform the action ' + request + '. The erver is busy loading data' + (currentAjaxProcess ? ' for: ' + currentAjaxProcess : '.'));}
		r = false;
	}
	currentAjaxProcess = request;
	return r;
}

function preSubmitForm(data){

	if(debug){console.log('Main > preSubmitForm: Start');}

	var e = data.e;
	var form = data.el;

	e.preventDefault();

	if(form.hasClass('ee-form'))
	{

		form.find('#msg-name').val($('#name').val());
		form.find('#msg-email').val($('#sender-email').val());

		if(form.attr('id') == 'contact_form')
		{

			form.data('dialog', false)
				.data('url', '/')
				.data('cb', 'contactFormCallback')
				.data('datatype', 'html')
				.data('action', 'submit-contact');

			//$('#contact_form').append($('.hiddenFields'));
		}

	}

	window.submitForm( {'e': e, 'el': form} );

	if(debug){console.log('Main > preSubmitForm: End');}

}

function showCpanelStatus(data) {

	var statusClass = data.class === undefined ? 'bg-success' : data.class;
	var msg = data.msg === undefined ? false : data.msg;
	var status = $('#modal-status').removeAttr('class').text('');

	if (!msg) {
		if(debug){console.log('showCpanelStatus -> No hay mensaje para mostrar en status');}
		return;
	}

	status.addClass(statusClass).text(msg);

	window.setTimeout(function () {
		status.removeClass(statusClass).text('');
	}, 4000);
}

function isSelector(data) {
	return $(document).find(data).length;
}

function urlCheck(url) {
	var expression = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/gi;
	var regex = new RegExp(expression);

	if (url.match(regex)) {
		return true;
	} else {
		return false;
	}
}

function showConfirm(data) {

	var options = {
		mode: 'confirm',
		action: data.action || 'close',
		title: data.title,
		body: data.body,
		btn1text: "Cancelar",
		btn2text: "Aceptar",
		fn: data.fn || function (e) {}
	};

	showAlert(options);

}

function showAlert(data) {

	//if(debug){console.log(data);}

	var el = data.el || $('#page-alert');
	var alertMode = data.mode || 'alert';
	var action = data.action || 'close';
	var alertTitle = data.title || 'Atención';
	var alertBody = data.body || '';
	var alertClass = data.class || 'info';
	var alertIcon = data.icon || 'info';
	var alertButton1Text = data.btn1text || 'Cerrar';
	var alertButton2Text = data.btn2text || '';
	var btn2fn = data.fn || function (e) {};
	var button1 = $('.alert-btn-1');
	var button2 = $('.alert-btn-2');

	if (!alertTitle) {
		console.log('showAlert: No Title');
		return;
	}

	if (!alertBody) {
		console.log('showAlert: No body');
		return;
	}

	//el.removeAttr('class');
	//el.addClass('alert alert-dismissible fade show');

	$('#modal-alert-title').html(alertTitle);
	$('#alert-body').html(alertBody);
	el.addClass(alertClass);

	switch (alertClass) {
		case 'danger':
		case 'fail':
		case 'warning':
			alertIcon = 'exclamation';
			break;
		case 'success':
			alertIcon = 'check';
			break;
		case 'info':
			alertIcon = 'info';
			break;

	}

	button1.html(alertButton1Text);
	button2.addClass('hidden').html(alertButton2Text);

	alertIcon = 'fa fa-' + alertIcon;
	$('#alert-icon').removeAttr('class').addClass(alertIcon + ' ' + 'bg-' + alertClass);

	button2.on('click', function (e) {

		if (alertMode == 'confirm') {
			el.modal('hide');
			window.setTimeout(function () {
				btn2fn(e);
			}, 1000);

			return;
		}

	});

	if (alertMode == 'confirm') {
		button2.removeClass('hidden').removeAttr('data-dismiss');
		el.modal('hide');
	}

	if (action == 'show') {
		el.modal('show');
	} else if (action == 'close') {
		el.modal('hide');
	}

	//el = '';
	//el.modal('toggle');
}

//showHideBackToTop
/*
function showHideBackToTop(){
	var el = $('.back-to-top');
	var height = 150;
	el[$(window).scrollTop() > parseInt(height) ? 'addClass' : 'removeClass']('open');
}
*/

//moveTo
/*
function moveTo(data) {
	var e = data.e;
	var el = $(e.target);
	var target = el.data('target') || 0;
	var duration = el.data('duration') || 1000;
	var goto = 0;

	 e.preventDefault();

	 if(isSelector(target)){
		 goto = $(target).offset().top;
	 }else if($.isNumeric(target)){
		 goto = target;
	 }else if(/^[1-9][0-9]?%$|^100%$/.test(target)){
		 goto = parseInt(($(window).height() / 100) * target.replace('%', ''));
	 }

	 $('html, body').animate({scrollTop: goto}, {
		duration: duration,
		easing: "swing",
		complete: function(){

		}
	});
	 return false;
}
*/

//fadingElements
/*
function fadingElements(data){
	var type = data.type || 'show';

	var group1 = $('.group-1');
	var group2 = $('.group-2');
	var group3 = $('.group-3');
	var group4 = $('.group-4');

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
*/

