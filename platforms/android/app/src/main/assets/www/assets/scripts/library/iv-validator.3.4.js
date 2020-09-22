
function validator(data){

	var debug = data.debug || false;
	var btn = data.el;
	var f = (data.form == null) ? btn.parents("form") : data.form;
	var callback = (data.callback !== undefined) ? data.callback : '';
	var showErrorFunction = (data.showErrorFunction !== undefined) ? data.showErrorFunction : '';
	var showErrorMode = (data.showErrorMode !== undefined) ? data.showErrorMode : 'normal';
	var errorTag = (data.errorTag !== undefined) ? data.errorTag : 'em';
	var errorClass = (data.errorClass !== undefined) ? data.errorClass : 'has-error';
	var useBootstrapError = (data.useBootstrapError !== undefined) ? data.useBootstrapError : 'false';
	 var useBootstrapDialog = (data.useBootstrapDialog !== undefined) ? data.useBootstrapDialog : 'false';

	var defaults = $("[placeholder]", f);
	var fields = $(":input", f).not(':submit, :button, :reset, input:hidden');

	if(debug){
		console.log(data);
		console.log(fields);
	}

	var er_email= /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/;
	var er_numeric = /^\d+$/;
	var nl = '<br>';
	var send = true;
	var errors = new Array();
	var messages = '';
	var validationRegEx = new RegExp("validate\:(\{.*\})", "i");
	//var aValidations = new Array('required','email', 'match', 'minlength', 'maxlength');

	var getFieldRules = function(f){

		//var data = ($(this).attr('data') !== undefined) ? eval('(' + $(this).attr('data') + ')') : '';

		var dataPosition = (f.attr('class') !== undefined) ? f.attr('class').search(validationRegEx) : -1;
		var validationRules = (f.attr('class') !== undefined && dataPosition != -1) ? f.attr('class').match(validationRegEx) : '';
		var data = (validationRules[1]) ? eval('(' + validationRules[1] + ')') : '';
		return data;
	};

	fields.each(function(i, el){
		var $thisField = $(el);
		var tagName = this.tagName.toLowerCase();
		var type = (tagName == 'select') ? 'select' : ((tagName == 'textarea') ? 'textarea' : $thisField.attr('type'));
		var pHolder = ($thisField.attr('placeholder') !== undefined) ?$thisField.attr('placeholder') : '';

		//var isRequired = (data.required !== undefined && data.required) ? true : false;
		//var isEmail = (data.email !== undefined && data.email) ? true : false;

		var data = getFieldRules($thisField);
		var isRequired = ($thisField.hasClass('required') || (data.required !== undefined && data.required == true)) ? true : false;
		var isEmail = ($thisField.hasClass('email') || (data.email !== undefined && data.email == true)) ? true : false;
		var isNumeric = ($thisField.hasClass('numeric') || (data.numeric !== undefined && data.numeric == true)) ? true : false;
		var minlength = (data.minlength !== undefined) ? data.minlength : false;
		var maxlength = (data.maxlength !== undefined) ? data.maxlength : false;
		var match = (data.match !== undefined && data.match !== false) ? data.match : '';
		var fieldName = (data.fieldname !== undefined && data.fieldname !== false) ? data.fieldname : $thisField.attr('id');
		var error = new Array();
		var m = '';
		var addField = false;

		if(debug){ console.log(data);}
		//data = false;

		if(data){
			//console.log(tagName);

			$thisField.attr('fieldName', fieldName);

			switch(type){
				case 'select':
					if(isRequired && $thisField.val() == ''){
						m = msgRequired.replace('{fieldName}', fieldName) + nl;
						addField = true;
					}
					if(minlength && minlength > 0){
						if($thisField.find('option:selected').length < minlength){
							m += msgMinlength.replace('{fieldName}', fieldName)
											.replace('{minlength}', minlength)
											.replace('{objects}', comboOption) + nl;
							addField = true;
						}
					}
					if(maxlength && maxlength > 0){
						if($thisField.find('option:selected').length > maxlength){
							m += msgMaxlength.replace('{fieldName}', fieldName)
											.replace('{maxlength}', maxlength)
											.replace('{objects}', comboOptions) + nl;
							addField = true;
						}
					}
				break;
				case 'text':
				case 'password':
				case 'textarea':
					if( (isRequired && ( (pHolder == $thisField.val()) || (isRequired && $thisField.val() == '') )) || (isRequired && (type == 'textarea') && $thisField.val() == '' ) ){
						m = msgRequired.replace('{fieldName}', fieldName) + nl;
						addField = true;
					}
					if(isEmail && !er_email.test($thisField.val())){
						m += msgEmail.replace('{fieldName}', fieldName) + nl;
						addField = true;
					}
						  if(isNumeric && !er_numeric.test($thisField.val()) && $thisField.val() != ''){
						m += msgNumeric.replace('{fieldName}', fieldName) + nl;
						addField = true;
					}
					if(minlength && minlength > 0){
						if($thisField.val().length < minlength){
							m += msgMinlength.replace('{fieldName}', fieldName)
												.replace('{minlength}', minlength)
												.replace('{objects}', characters) + nl;
							addField = true;
						}
					}
					if(match && ( $thisField.val() != $('#' + match).val() ) ){
						//var d = eval('(' + $('#' + match).attr('data') + ')');

						var d = getFieldRules($('#' + match));

						var tmpFieldName = (d.fieldname != undefined) ? d.fieldname : $('#' + match).attr('id');
						m += msgMatch.replace('{source}', tmpFieldName).replace('{match}', fieldName) + nl;
						addField = true;
					}
				break;
				case 'radio':
					var radio = $('[name="' + $thisField.attr('name') + '"]:checked');
					if(isRequired && radio.length < 1){
						m = msgRequired.replace('{fieldName}', fieldName) + nl;
						addField = true;
					}
				break;
				case 'checkbox':
					if(isRequired && !$thisField.is(':checked')){
						m = msgRequired.replace('{fieldName}', fieldName) + nl;
						addField = true;
					}
				break;
			}
			//console.log(addField + ' ' + $thisField.attr('id'));
			if(addField){
				error.push({field: $thisField, msg: m});
				errors.push(error);
			}
			//console.log(errors);
		}
	});

	if(debug){
		console.log('There are ' + errors.length + ' errors');
		console.log(errors);
	}

	if(errors.length > 0){
		send = false;

		switch(showErrorMode){
			case 'custom':

				if(!showErrorFunction){
					alert(errorFunctionNotDefined);
					return;
				}

				$(errorTag + '.' + errorClass).remove();

				$(errors).each(function(i, e){
					var $thisError = $(e);
					var element = $thisError.get(0).field;
					var pattern = new RegExp(element.attr('fieldName'), 'g');
					var tag = $('<' + errorTag + '>')
							.addClass(errorClass)
							.html($thisError.get(0).msg.replace(pattern, thisField).replace(/<strong>/g,'').replace(/<\/strong>/g,''));

					if(element.is(':radio')){
						$("input[name='" + element.attr('name') + "']").bind('focus click keydown', function(){
							tag.remove();
						});
					}else{
						element.bind('focus click keydown change', function(){
							element.parents('.' + errorClass).removeClass(errorClass);
							tag.remove();
						});
					}

					showErrorFunction(tag, element);
				});

			break;

			case 'normal':
			case 'dialog':

				$(errors).each(function(i, e){
					var $thisError = $(e);
					var element = $thisError.get(0).field;
					var elementContainer = $thisError.get(0).field.parents('.form-group');
					var isRadio = element.is(':radio') ? true : false;
					var elementHighlighted = (isRadio || useBootstrapError) ? elementContainer : element;
					var elementProcessed = isRadio ? $("input[name='" + element.attr('name') + "']") : element;

					if(debug){
						console.log(elementHighlighted);
						console.log(elementProcessed);
						console.log(isRadio);
						console.log('useBootstrapError: ' + useBootstrapError);
					}

					elementHighlighted.addClass(errorClass);

					elementProcessed.bind('focus click keydown change', function(e){
						elementHighlighted.removeClass(errorClass);
					});

					messages += $thisError.get(0).msg;
				});

				if(showErrorMode == 'dialog'){
					if(!useBootstrapDialog){
						dialog({'title': dialogErrorTitle, 'message': messages,'icon':'error', 'width':350, 'modal':true});
					}else{
						bootstrpDialog({'title': dialogErrorTitle, 'message': messages, 'fullwidth': false, 'size': 'regular'});
					}
				}

			break;
		}
	}else{

		if(callback !== ''){
			callback();
		}

		/*if(defaults.length > 0){
			defaults.each(function(){
				if($(this).attr('placeholder') == $(this).val()){
					$(this).val('');
				}
			});
		}*/
	}

	if(debug){
		console.log('validator send: ' + send);
		return false;
	}else{
		return send;
	}
}
