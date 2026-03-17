<!--Layout1.php formulario-->
<?php 



  header('Content-Type: text/html; charset=UTF-8'); 
  error_reporting(E_ERROR | E_PARSE); // Desactiva la notificaci�n y warnings de error en php.
?>

<!DOCTYPE html> 
<html lang="es"> 
<head>  
<link rel="shortcut icon" type="image/x-icon" href="imagenes/iconito.ico" />
    <meta charset="UTF-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<meta name="description" content="Registro">  
	<meta name="keywords" content="Ir al inicio">  

	<title> 
		<?php $title = "Inicio de Sesion";
			echo $title;
	 	?> 
	</title> 
	<head>
	 
	
</head>

	<link rel="icon" href="imagenes/favicon.ico" type="image/x-icon">
	<script src="js/jquery.js"></script>
	<script src="js/bootstrap.js"></script>
	 
 	<link href="assets/css/style.css" rel="stylesheet">

</head> 

<body> 
	<div class="container"> 
		<?php 
			include_once ('menuEstiloForm1.php');
		?>
	</div> 
</body>
</html> 