/* ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''' */
/* '''''''''''''''       Habilita el pase          '''''''''''''''''*/
/* ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''' */
function aceptar() {
	if (blanco(document.Formulario.user_login.value)) {
		alert("Por favor ingrese un nombre de usuario.");
		document.Formulario.user_login.focus();
		return false;
	}

	if (blanco(document.Formulario.password_login.value)) {
		alert("Por favor ingrese una contraseña.");
		document.Formulario.password_login.focus();
		return false;
	}

	document.Formulario.submit();
	return true;
}


/* ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''*/
/* '''''''''''''''Verifica que los campos no esten en blanco'''''''''''''''''' */
/* ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''*/
function blanco(s) {
	for (var i = 0; i < s.length; i++) {
		var c = s.charAt(i);
		if ((escape(c) != '%0D') && (escape(c) != '%0A') && (c != " "))
			return false;
	}
	return true
}
/* ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''' */
//-->
