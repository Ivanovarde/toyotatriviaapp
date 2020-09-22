var newLeadNotificationTimer;
var newLeadNotificationTime = 3; // (in seconds)
var msgNoStoredRecords = 'En este momento no hay contactos almacenados';
var aCurrentRecord = {};


// SET SERVER URL  ============================================================
$(document).on('click', '#change-server-url', function (e) {

	e.preventDefault();

	var current_server_url = window.localStorage.getItem("remote_server_url");
	var settings_server_url = $("#settings-server-url").val();
	var msg = 'URL de servidor actualizada';
	var statusClass = 'bg-success';

	//console.log(settings_server_url + ' == ' + current_server_url);

	if (settings_server_url !== '' && settings_server_url !== undefined) {

		if (settings_server_url == current_server_url)
		{

			msg = 'Se ingresó la URL actual';
			statusClass = 'bg-warning';

		}else if(!urlCheck(settings_server_url))
		{

			msg = 'Ingresar una URL válida';
			statusClass = 'bg-danger';

		} else
		{

			// Guardo los datos
			window.localStorage.setItem("remote_server_url", settings_server_url);
			remote_server_url = settings_server_url;

		}

	} else
	{
		msg = 'Error. Intentar nuevamente.';
		statusClass = 'bg-danger';
	}

	showCpanelStatus({ msg: msg, class: statusClass });

});


// MUESTRO LA TABLA DE CONTACTOS =============================================
$(document).on('click', '.leads-show', function (e) {

	e.preventDefault();

	leadsShowPanel();

});


// ABRE EL PANEL DE CONTROL CON DOBLE CLICK ===================================
$(document).on('click', '#preloader-inner', function (e) {
	e.preventDefault();
	$('#cpanel').modal('show');
});


// DETERMINO SI HAY O NO HAY DIALOGOS ABIERTOS ================================
$(document).on('show.bs.modal', function () {
	if ($('.leads-holder').hasClass('on')) {
		$('.leads-holder').removeClass('on');
	}
});
$(document).on('hidden.bs.modal', function () {

	if ($('.leads-holder').hasClass('on')) {
		$('.leads-holder').removeClass('on');
	}

	if ($('.modal:visible').length > 0) {
		// restore the modal-open class to the body element, so that scrolling works
		// properly after de-stacking a modal.
		setTimeout(function () {
			$(document.body).addClass('modal-open');
		}, 0);
	}
});


// SET DATA URL  ==============================================================
/*
$(document).on('click', '#change-data-url', function (e) {

	e.preventDefault();

	var current_data_url = window.localStorage.getItem("remote_data_url");
	var settings_data_url = $("#settings-data-url").val();
	var msg = 'URL de datos actualizada';
	var statusClass = 'bg-success';

	//console.log(settings_data_url + ' == ' + current_data_url);

	if (settings_data_url !== '' && settings_data_url !== undefined) {

		if (settings_data_url == current_data_url) {
			return;
		}

		// Guardo los datos
		window.localStorage.setItem("remote_data_url", settings_data_url);
		remote_data_url = settings_data_url;

	} else {
		msg = 'Error. Intentar nuevamente.';
		statusClass = 'bg-danger';
	}

	showCpanelStatus({
		msg: msg,
		statusClass: statusClass
	});

});
*/


// ACTUALIZAR CONTENIDO =======================================================
/*
$(document).on('click', '#btn-update-content', function (e) {

	e.preventDefault();

	// Si no estoy conectado a Internet, cancelo
	if (app.isConnected) {

		showConfirm({
			body: 'Esta acción descargará y reemplazará todo el contenido de la aplicación. El proceso podría demorar unos minutos. ¿Desea continuar?',
			action: 'show',
			fn: function () {

				if (window.localStorage.getItem("appJsonData")) {

					// Hago backup del contenido actual;
					appCurrentMainData = window.localStorage.getItem("appJsonData");

					window.localStorage.removeItem("appJsonData");
				}

				getMainData({
					btn: $('#btn-update-content'),
					cb: function (response) {
						var dataBody = 'Los contenidos se acutalizaron correctamente';
						var dataClass = 'success';
						var dataIcon = 'check';

						if (response == null || response == '' || response === undefined) {
							dataBody = 'No se pudo acceder a los contenidos. Intentar más tarde';
							dataClass = 'danger';
							dataIcon = 'exclamation';
						}

						showAlert({
							body: dataBody,
							action: 'show',
							class: dataClass,
							icon: dataIcon,
						});

						$('footer').removeClass('on');
					}
				});
			}
		});

	} else {
		showAlert({
			body: 'Esta acción requiere conexión a internet.',
			class: 'danger',
			icon: 'exclamation',
			action: 'show'
		});

		return false;
	}

});
*/


// SINCRONIZAR CONTACTOS ======================================================
$('#btn-sync-records').one('click', function (e) {

	console.log('leads > ');

	//console.log('click 1');
	e.preventDefault();
	recordsStartSync({e: e, el: $(this)});

});


// MUESTRO LA URL DEL SERVER Y DE LOS DATOS ===================================
$('#cpanel').on('show.bs.modal', function () {

	var server_url = window.localStorage.getItem("remote_server_url");
	var data_url = window.localStorage.getItem("remote_data_url");

	if(window.localStorage.getItem("remote_server_url") !== null){
		$('#settings-server-url').val(server_url);
	}

	if(window.localStorage.getItem("remote_data_url") !== null){
		$('#settings-data-url').val(data_url);
	}

});



// LEADS FUNCTIIONS ===========================================================
function leadsKeepInMemory() {

	var isValid = false;
	var f = $('form#form-lead');
	var fieldname = '';

	isValid = validator({
		'form': f,
		'useBootstrapError': true, //useBootstrapError,
		'useBootstrapDialog': false, //useBootstrapDialog,
		'debug': false
	});

	if (!isValid)
	{
		showAlert({
			body: 'Por favor, completar todos los campos',
			class: 'danger',
			icon: 'exclamation',
			action: 'show'
		});

		return false;
	}

	// Agrego los campos del formularo al registro
	f.find(':input').not(':button').each(function (i, o) {

		var fieldname = $(o).attr('name');
		var value = $(o).val();

		aCurrentRecord[fieldname] = value;

	});

	console.log('Leads > aCurrentRecord: leadsKeepInMemory detail (next line)');
	//console.log(aCurrentRecord);
	//console.log('Edad: ' + aCurrentRecord.age);

	// Antes de enviar guardo el registro actual
	//leadsStoreLocally(aCurrentRecord);

	// Si estoy conectado a Internet hago el envío
	/*
	if (app.isConnected)
	{
		recordsSendToRemoteServer();
	}
	*/

	return true;

}

function leadsShowPanel() {

	var leadsHolder = $('.leads-holder');
	var table = $('#leads-list').html('');
	var thead = $('<thead>').appendTo(table);
	var tbody = $('<tbody>');
	var data, headers = '';

	if (leadsHolder.hasClass('on')) {
		leadsHolder.removeClass('on');
		return;
	}

	var leads = JSON.parse(window.localStorage.getItem("stored_leads"));
	var wanted_fields = ['date', 'firstname', 'lastname', 'email', 'age', 'dni', 'city', 'score', 'topics'];

	if (leads === null || leads.length < 1) {
		showAlert({
			body: msgNoStoredRecords,
			action: 'show',
			class: 'info',
			icon: 'info',
		});

		return;
	}

	//console.log('leads (from localstore)');
	//console.log(leads);

	data += '<tr>';

	$(wanted_fields).each(function (i, o) {

		headers += '<th>' + o.replace('_', ' ').capitalize() + '</th>';

	});

	data += '</tr>';

	for (var i = 0; i < leads.length; i++) {

		var row = leads[i];

		//console.log('row (leads[' + i + '])');
		//console.log(row);

		data += '<tr>';

		//for (var n in row) {

		$(wanted_fields).each(function(i, o){

			//console.log('cells (row[' + n + '])');
			//console.log(row[n]);

			var fieldname = o;
			var value = row[o];

			data += '<td>' + value + '</td>';

		})



		//}

		data += '</tr>';
	}

	thead.html(headers).appendTo(table);
	tbody.html(data).appendTo(table);

	var panelBody = leadsHolder.find('.card-body');
	var panelHeading = leadsHolder.find('.card-header');
	var tableResponsive = leadsHolder.find('.table-responsive');

	var panelHeadingHeight = panelHeading.outerHeight();
	var panelBodyHeight = panelBody.outerHeight()
	var tableResponsiveHeight = parseInt(panelBodyHeight) + parseInt(panelHeadingHeight);

	//console.log(panelBodyHeight);
	//console.log(panelHeadingHeight);
	//console.log(tableResponsiveHeight);


	//tableResponsive.css('height', '100%');
	//tableResponsive.attr('style', 'height: calc(100% - ' + tableResponsiveHeight + 'px)');

	leadsHolder.addClass('on');

}

function leadsStoreLocally(lead) {

	console.log('Leads > leadsStoreLocally: Start');
	//console.log('Current Record');
	//console.log(lead);

	window.clearTimeout(newLeadNotificationTimer);

	var counter = $('#pending-records-total');
	var aStoredLeads = [];

	//Si tengo datos guardados localmente, los consulto directamente desde ahi
	var stored_leads = JSON.parse(window.localStorage.getItem("stored_leads"));

	// Si hay datos localmente
	if (stored_leads !== null) {
		// Obtengo los datos de los registros previos para no perderlos
		aStoredLeads = stored_leads;
	}

	//console.log('aStoredLeads');
	//console.log(aStoredLeads);

	// Agrego el contacto actual
	aStoredLeads.push(lead);

	//console.log('aStoredLeads with new record');
	//console.log(aStoredLeads);
	//console.log('stringify');
	//console.log(JSON.stringify(aStoredLeads));

	// Guardo los datos
	window.localStorage.setItem("stored_leads", JSON.stringify(aStoredLeads));
	console.log('Leads > leadsStoreLocally: Store data localy');

	// Actualizo Contador
	leadsUpdateStoredCounter();

	counter.addClass('new');
	newLeadNotificationTimer = window.setTimeout(function () {
		counter.removeClass('new');
	}, parseInt(newLeadNotificationTime * 1000));

	console.log('Leads > leadsStoreLocally: End');

}

function leadsUpdateStoredCounter() {

	var counter = 0;

	//Si tengo los datos guardados localmente, los consulto directamente desde ahi
	var stored_leads = JSON.parse(window.localStorage.getItem("stored_leads"));

	// Si hay datos localmente
	if (stored_leads != null) {
		counter = stored_leads.length;
	}

	// ACtualizo el Bubble Count de Contactos Pendientes
	$('#pending-records-total').html(counter);

}


// RECORDS EXPORT / SYNC ======================================================
function recordsStartSync(data) {

	console.log('Leads > recordsStartSync: Start');

	var e = data.e;
	var el = data.el;
	var counter = 0;

	e.preventDefault();
	e.stopPropagation();
	e.stopImmediatePropagation();

	if(!remote_server_url || remote_server_url === null || remote_server_url === undefined)
	{
		// Muestro confirmación para el envío de los contactos al servidor
		showAlert({
			body: 'Antes de exportar los registros debe configurar la url del servidor remoto en el campo "URL del servidor"',
			class: 'warning',
			action: 'show'
		});

		el.off().one('click', function (e) {

			e.preventDefault();
			recordsStartSync({"e": e,"el": $(this)});

		});

		return false;

	}

	//Si tengo datos guardados localmente, los consulto directamente desde ahi
	var stored_leads = JSON.parse(window.localStorage.getItem("stored_leads"));

	// Si estoy conectado a Internet y hay registros, envio
	if (app.isConnected && stored_leads !== null && stored_leads.length > 0)
	{

		counter = stored_leads.length;

		// Muestro confirmación para el envío de los contactos al servidor
		showConfirm({
			body: 'Exportar ' + counter + ' ' + (counter > 1 ? 'registros' : 'registro') + ' al servidor.<br>Esta acción podría demorar unos minutos.<br>¿Desea continuar?',
			action: 'show',
			fn: function () {
				recordsSendToRemoteServer(el);
			}
		});

		// Si cancelo el envío, vuelvo a "bindear" el click del boton, sino, quedaría sin evento de click
		el.off().one('click', function (e)
		{
			//console.log('click 4');
			e.preventDefault();
			recordsStartSync({"e": e,"el": $(this)});
		});

	} else
	{

		el.off().one('click', function (e)
		{
			//console.log('click 3');
			e.preventDefault();
			recordsStartSync({"e": e,"el": $(this)});
		});

		if (!app.isConnected)
		{

			showAlert({
				body: 'Esta acción requiere conexión a internet.',
				class: 'danger',
				icon: 'exclamation',
				action: 'show'
			});

		}

		if (stored_leads === null || stored_leads.length < 1)
		{

			showAlert({
				body: msgNoStoredRecords,
				class: 'info',
				icon: 'exclamation',
				action: 'show'
			});

		}

	}

	console.log('Leads > recordsStartSync: End');

}

function recordsSendToRemoteServer(btn) {

	console.log('Leads > recordsSendToRemoteServer: Start');

	//Levanto todos los registros guardados y los mando via post al server
	var stored_leads = JSON.parse(window.localStorage.getItem("stored_leads"));

	console.log('Leads > recordsStartSync: stred_leads detail (next line)');
	//console.log(stored_leads);

	if (!ajaxReadyCheck('recordsSendToRemoteServer'))
	{
		return false;
	}

	loadingData = true;

	btn.addClass('loading').attr('disabled', 'disabled');

	console.log('Leads > recordsStartSync: stred_leads -> URL: ' + remote_server_url);

	$.ajax({
		url: remote_server_url + '/system/php/actions.php?action=store&time=' + currentDate.ivTimeStamp(),
		async: false,
		type: 'POST',
		data: {
			'stored_leads': stored_leads
		},
		dataType: 'json',
		charset: 'UTF-8',
		timeout: 10000
	})
	.done(function (response, status, xhr) {

		loadingData = false;

		var responseClass = 'danger';

		// Si NO HUBO respuesta del servidor
		if (response === null || response === undefined) {
			console.log(status);
		}

		// si HAY respuesta del servidor
		if (response !== null && response !== '') {

			// Si NO HUBO error
			if (response.status) {

				responseClass = 'success';

				// Vacio los registros locales
				window.localStorage.removeItem("stored_leads");

			} // Si HUBO error y vienen registros devueltos
			else if (!response.status && response.failed_records.length) {

				// Vacio los registros locales
				window.localStorage.removeItem("stored_leads");

				// Guardo los registros devueltos
				window.localStorage.setItem("stored_leads", JSON.stringify(response.failed_records));
			}

			// Actualizo el contador
			leadsUpdateStoredCounter();

			window.setTimeout(function () {
				showAlert({
					body: response.msg,
					class: responseClass,
					//icon: 'check',
					action: 'show'
				});
				console.log(response.msg);
			}, 1000);

		}

	})
	.fail(function (jqXHR, status, errorThrown) {
		console.log('recordsSendToRemoteServer: Fail: ');
		console.log(jqXHR);
		console.log(status);
		console.log(errorThrown);

		loadingData = false;

		//console.log('Fail recordsSendToRemoteServer(): lead detail: (next line)');
		//console.log(lead);

	})
	.always(function (response, status, xhr) {
		console.log('recordsSendToRemoteServer always');

		//$('#btn-sync-records').removeClass('loading').removeAttr('disabled');
		btn.removeClass('loading').removeAttr('disabled');

		btn.off().one('click', function (e) {
			//console.log('click 2');
			recordsStartSync({ "e": e, "el": $(this) });
		});

	});

	console.log('Leads > recordsSendToRemoteServer: End');

}
