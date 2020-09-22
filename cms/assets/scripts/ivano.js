$(document).on('scroll', function () {

});

$(window).on('resize', function () {

});

$(window).on('load', function () {

});

$(document).ready(function () {



});

var lang = [];
lang.textWarning = 'Atención';
lang.textSuccess = 'Exito';
lang.textDanger = 'Error';
lang.textCancel = 'Cancelar';
lang.textAccept = 'Aceptar';
lang.textDeleteRecordConfirm = 'Está por eliminar {records-length} registro{s}.<br>¿Está seguro que desea continuar?';
lang.textSendRecordEmailBatchConfirm = 'Se enviará un email a cada registro seleccionado.<br>¿Desea continuar?';
lang.textNoRecordSelected = 'No hay ningún registro seleccionado';
lang.textNoElementType = 'No se recibió la clase del registro';
lang.textNoBatchAction = 'No se recibió la acción para ejecutar';
lang.textLogoutConfirm = 'Está a unto de salir del sistema<br>¿Desea continuar?';
lang.booleanOption = [];
lang.booleanOption[0] = 'No';
lang.booleanOption[1] = 'Si';

/* LISTENERS */
$('a[href="#"], a.disallowed').livequery('click', function (e) { e.preventDefault(); });
$('.toggle-records').livequery('click', function (e) { toggleRecordsCheckboxes({e: e, el: $(this)}); });
$('.submit-modal-edit-form').livequery('click', function (e) { modalEditFormSubmit({e: e, el: $(this)}); });
$('.delete-record, .send-email').livequery('click', function (e) { makeBatchAction({e: e}); });
$('.validate').livequery('submit', function(e){return ivSendForm({ el: $(this), e: e}); });
$('#search-results input[type="checkbox"]').livequery('change', function(e){ showHideSearchControls({ el: $(this), e: e}); });

$('button.search-list, button.export-list').livequery('click', function(e){ prepareListResults({e: e}); });

$(document).on('click', '.logout', function(e){ logout({e: e}); });
$(document).on('change focus click', '.has-error', function (e) {
	$(this).removeClass('has-error');
});
$(document).on('show.bs.modal', '#modal-edit', function (e) {getEditForm({e: e}); });


var URL = '/' + (siteFolder ? siteFolder + '/' : '') +'php/actions.php';
var ajaxLoader = '';

function showHideSearchControls(data){
	var e = data.e;
	var resultControls = $('#results-controls');

	if($('#search-results input[type="checkbox"]:checked').length){
		resultControls.removeClass('fade').addClass('show');
	}else{
		resultControls.removeClass('show').addClass('fade');
	}
}

function prepareListResults(data){
	var e = data.e;
	var el = $(e.currentTarget);
	var form = $('#form-search-filters');
	var str = form.serialize();

	if(el.hasClass('export-list')){

		el.addClass('loading').attr('disabled', 'disabled');

		var iframe = createIframe({id: 'export', url: URL + '?action=export&' + str, callback: function(){
			el.removeClass('loading').removeAttr('disabled', 'disabled');
		}});
		iframe.appendTo($('body'));

		//el.removeClass('loading').removeAttr('disabled', 'disabled');
		return;
	}

	if(el.hasClass('search-list')){
		form.data('action', 'search');
	}

	e.preventDefault();

	ivSendForm({ el: form, btn: el, e: e});
}

function updateListEmailStatus(response){
	var d = new Date();
	var todaydate = d.ivDateFormat('dd-mm-yyyy');

	for(var i = 0; i < response.ids.length; i++){
		$('#record-' + response.ids[i]).find('.email-sent-status > div').attr('title', 'SI').html('SI');
		$('#record-' + response.ids[i]).find('.email-sent-date > div').attr('title', todaydate).html(todaydate);
	}
}

function updateListDeleteRecords(response){
	for(var i = 0; i < response.ids.length; i++){
		$('#record-' + response.ids[i]).remove();
	}
}

function makeBatchAction(data){
	var e = data.e;
	var el = $(e.currentTarget);
	var elementType = el.data('element-type') || false;
	var batchAction = el.data('batch-action') || false;
	var records = $('#search-results').find('tbody .checkbox-record:checked');
	var controls = $('#results-controls');
	var recordsLength = records.length;
	var ids = [];
	var extraData = el.data('extra');
	var text = '';
	var callback = false;

	e.preventDefault();

	if(elementType === false){
		console.log('makeBatchAction: ' + lang.textNoElementType);
		return;
	}

	if(batchAction === false){
		console.log('makeBatchAction: ' + lang.textNoBatchAction);
		return;
	}

	if(recordsLength < 1){
		console.log('makeBatchAction: ' + lang.textNoRecordSelected);
		return;
	}

	records.each(function(i, o){
		ids.push( $(o).val() );
	});

	var d = [];
	d.push({name: 'action', value: batchAction});
	d.push({name: 'aId', value: ids});
	d.push({name: 'element_type', value: elementType});
	d.push({name: 'email_template', value: elementType});

	if(extraData){
		extraData = $.parseJSON(el.data('extra').replace(/\'/g,"\""));

		for(var element in extraData){
			d.push({name: element, value: extraData[element]});
		}
	}

	switch(batchAction){
		case 'send_email':
			text = lang.textSendRecordEmailBatchConfirm;
			callback = 'updateListEmailStatus';
		break;
		case 'delete':
			text = lang.textDeleteRecordConfirm;
			callback = 'updateListDeleteRecords';
		break;
	}

	showConfirm({
		body: text,
		fn: function(e){

			e.preventDefault();

			ajaxSubmitBtn = el;

			console.log(d);

			$.ajax({
				url: URL,
				type: 'POST',
				data: d,
				dataType: 'json',
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

				if(response.ids !== undefined){

					if(callback !== false){
						window[callback](response);
					}

					$('#page-alert').modal('hide');
				}

			})
			.fail(function (jqXHR, status, errorThrown) {
				console.log('MakeBatchAction: Fail: ');
				console.log(jqXHR);
				console.log(status);
				console.log(errorThrown);

			})
			.always(function (response, status, xhr) {
				el.unbind().off().one('click', function (e) { makeBatchAction({e: e}); });
				records.prop('checked', false);
				controls.addClass('fade').removeClass('show');
			});
		}
	});

	return false;

}

function showConfirm(data){
	var options = {
		mode: 'confirm',
		title: data.title,
		body: data.body,
		btn1text: lang.textCancel,
		btn2text: lang.textAccept,
		fn: data.fn || function(e){}
	};

	showAlert(options);
}

function showAlert(data){
	var el = data.el || $('#page-alert');
	var alertMode = data.mode || 'alert';
	var alertTitle = data.title || 'Atención';
	var alertBody = data.body || '';
	var alertClass = data.class || 'warning';
	var alertIcon = data.icon || 'info';
	var alertButton1Text = data.btn1text || lang.textAccept;
	var alertButton2Text = data.btn2text || '';
	var btn2fn = data.fn || function(e){};
	var button1 = $('.alert-btn-1');
	var button2 = $('.alert-btn-2');

	if(!alertTitle){
		console.log('showAlert: No Title');
		return;
	}
	if(!alertBody){
		console.log('showAlert: No body');
		return;
	}

	//el.removeAttr('class');
	//el.addClass('alert alert-dismissible fade show');

	$('#modal-alert-title').html(alertTitle);
	$('#alert-body').html(alertBody);
	el.addClass(alertClass);

	switch(alertClass){
		case 'danger':
			 alertIcon = 'exclamation';
		break;
		case 'success':
			 alertIcon = 'check';
		break;
	}

	button1.html(alertButton1Text);
	button2.html(alertButton2Text);

	alertIcon = 'fa fa-' + alertIcon;
	$('#alert-icon').removeAttr('class').addClass(alertIcon + ' ' + 'bg-' + alertClass);

	button2.off().one('click', function(e){
		btn2fn(e);
	});

	if(alertMode == 'confirm'){
		button2.removeClass('invisible').removeAttr('data-dismiss');
	}

	el.modal('toggle');
}

function modalEditFormSubmit(data){
	var e = data.e;
	var el = data.el;
	var f = el.parents('#modal-edit').find('form');
	e.preventDefault();

	ajaxSubmitBtn = el;

	f.submit();
}

function getEditForm(data){
	var el = $(data.e.relatedTarget);
	var id = el.data('id');
	var elementType = el.data('element-type');
	var container = $('#modal-edit').find('.modal-body');
	var title = $('#modal-edit').find('.modal-title');
	var d = [];

	d.push({name: 'action', value: 'edit_form'});
	d.push({name: 'element_type', value: elementType});
	d.push({name: 'id', value: id});

	ajaxSubmitBtn = '';

	$.ajax({
		url: URL,
		type: 'POST',
		data: d,
		dataType: 'json',
		timeout: 20000
	})
	.done(function (response, status, xhr) {
		//				console.log('prepareSendForm: done: ');
		//				console.log(response);
		//				console.log(status);
		//				console.log(xhr);

	})
	.fail(function (jqXHR, status, errorThrown) {
		console.log('getEditForm: Fail: ');
		console.log(jqXHR);
		console.log(status);
		console.log(errorThrown);
	})
	.always(function (response, status, xhr) {
		title.html(response.title);
		container.html(response.html);

		if(!response.status && response.expired == true){
			redirect({url: response.url});
		}
	});

}

function redirect(data){
	var url = data.url || '';
	//console.log(url);

	if(url){
		//console.log(url); return;
		window.location = url;
	}
}

function toggleRecordsCheckboxes(data){
	var el = data.el;
	var elements = el.parents('table').find('input:checkbox').not($(this));
	elements.prop('checked', el.prop('checked'));
}

function showSearchResults(data){
	var searchResultsContainer = $('#search-results-container');
	var resultsTitle = $('#results-title');
	var recordsFound = resultsTitle.find('.records-found');
	var formResults = $('#form-results');

	recordsFound.html(data.records_found);
	resultsTitle.removeClass('fade').addClass('show');
	searchResultsContainer.html(data.html).removeClass('fade').addClass('show');
}

function logout(){
	console.log('logout');
	var msg = lang.textLogoutConfirm;

	var options = {
		mode: 'confirm',
		title: lang.textWarning,
		body: msg,
		btn1text: lang.textCancel,
		btn2text: lang.textAccept,
		fn: function(e){

			$.ajax({
				url: URL,
				type: 'POST',
				data: {action: 'logout'},
				dataType: 'json',
				timeout: 10000
			})
			.done(function (response, status, xhr) {
				console.log('logout: done');
				//				console.log(response);
				//				console.log(status);
				//				console.log(xhr);

				if (response.status) {
					redirect({url: response.url});
				}

			})
			.fail(function (jqXHR, status, errorThrown) {
				console.log('logout: Fail: ');
			})
			.always(function (response, status, xhr) {
				console.log(response);
			});

		}
	};

	showConfirm(options);

}

