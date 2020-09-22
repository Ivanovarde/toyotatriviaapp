<!DOCTYPE html>
<html lang="es-AR" prefix="og: http://ogp.me/ns#">

<head>
	<meta charset="UTF-8">
	<title>ExpoAgro - CRM</title>
	<link rel="stylesheet" href="styles/styles.css" type="text/css">
	<script src="js/cms.js"></script>
</head>

<body>
<?php
/*---------------------------------*/
/* Lectura del TOP                 */
/*---------------------------------*/
	include("expo-top.php");
/*---------------------------------*/
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
		<td valign="top">
			<form id="form-login" action="expo-login.php" method="post" name="Formulario" onSubmit="aceptar(); return false">

				<table border="0" cellspacing="3" cellpadding="0" align="center">
					<tr>
						<td valign="middle" align="right">
							Usuario:
						</td>
						<td class="texto" valign="middle">
							<input type="text" name="user_login" size="30" maxlength="20" class="texto">
						</td>
					</tr>
					<tr>
						<td valign="middle" align="right">
							Password:
						</td>
						<td valign="middle" class="texto" >
							<input type="password" name="password_login" size="30" maxlength="10" class="texto">
						</td>
					</tr>
					<tr>
						<td valign="middle" align="right">

						</td>
						<td valign="middle" class="texto" align="left">
							<input type="submit" name="enviar datos" class="fdo_action_call" value="Ingresar" >
						</td>
					</tr>
				</table>

			</form>
		</td>
	</tr>
</table>

<?php
/*---------------------------------*/
/* Lectura del PIE                 */
/*---------------------------------*/
	include("expo-pie.php");
/*---------------------------------*/
?>
</body>

</html>
