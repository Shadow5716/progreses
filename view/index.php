
<?php
session_start();
error_reporting(0);
include('../includes/dbconnection.php');
include_once ('../sesion.php');
if ($_SESSION['autentificado'] == false){
    header('location:../logout.php');
        
	}
	?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Busqueda de Usuario</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Bootstrap Y JQuery -->
<link href="css/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="shortcut icon" type="image/x-icon" href="../imagenes/iconito.ico" />
<script src="css/jquery/dist/jquery.min.js"></script>
<script src="css/pdf_object/pdfobject.js"></script>
<style>
.pdfobject-container { height: 60rem; border: 1rem solid rgba(0,0,0,.1); }
.modal-dialog{background-color: #fff; padding: 20px 15px;}
#cancel{margin: 5px; display: block;}
.cargando{position: absolute;width: 30px;right: -40px;top: -2px;}
.hide{display: none;}
</style>
</head>

<body>



            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="active">
                        <a href="../dashboard.php"> <i class="menu-icon fa fa-dashboard"></i>Menu </a>
                    </li>

<li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-tasks"></i>Equipos</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-file-o"></i><a href="../add-computer.php">Añadir Equipo</a></li>
                            <li><i class="menu-icon fa fa-file-o"></i><a href="../manage-computer.php">Consultar Equipos Actuales</a></li>
                            <li><i class="menu-icon fa fa-file-o"></i><a href="../manage-brokencomputer.php">Consultar Equipos Dañados</a></li>
                            <li><i class="menu-icon fa fa-file-o"></i><a href="../manage-allcomputer.php">Consultar Todos los Equipos</a></li>
                        </ul>
                    </li>




 <li class="menu-item-has-children dropdown">
                        <a href="add-users.php" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-user"></i>Usuarios</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa-user"></i><a href="../add-users.php">Agregar Usuario</a></li>
                            <li><i class="fa fa-user"></i><a href="../manage-newusers.php">Consultar Usuarios Nuevos</a>
                            </li>
                            <li><i class="fa fa-user"></i><a href="../manage-olduser.php">Consultar Usuarios Antiguos</a>
                            </li>
                            <li><i class="fa fa-user"></i><a href="../manage-pendinguser.php">Consultar Usuarios con tiempo pendiente</a>
                            </li>
                            <li><i class="fa fa-user"></i><a href="../manage-banneduser.php">Consultar Usuarios Prohibidos</a>
                            </li>
                            <li><i class="fa fa-user"></i><a href="../view-allusers.php">Consultar la lista de Todos los usuarios</a>
                            </li>
                          
                        </ul>
                    </li>

                    <li class="menu-item-has-children dropdown">
                        <a href="add-users.php" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-user"></i>Alumnos</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa-user"></i><a href="../add-alumnos.php">Agregar Alumno</a></li>
                            <li><i class="fa fa-user"></i><a href="../update-alumno.php.php">Actualizar Datos</a>
                            </li>
                            <li><i class="fa fa-user"></i><a href="">Consultar Alumnos</a>
                            </li>
                        
                          
                        </ul>
                    </li>


<li class="active">
                        <a href="../search.php"> <i class="menu-icon fa fa-search"></i>Busqueda de Usuarios </a>
                    </li>

                    <li class="active">
                        <a href="../searchcomp.php"> <i class="menu-icon fa fa-search"></i>Busqueda de Computadoras </a>
                    </li>



                    
  <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-tasks"></i>Reporte</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-file-o"></i><a href="../bwdates-report-ds.php">Reporte entre Fechas</a></li>
                           
                        </ul>
                    </li>

                    <li class="active">
                        <a href="../view-admins.php"> <i class="menu-icon fa fa-file-o"></i>Lista de Administradores Actuales </a>
                    </li>


<div class="container">
	<div class="row">
		<div class="slide_uno col-md-12 col-sm-12 col-xs-12">
			<h2 class="text-center">Exportar Datos a PDF por Rango de Fecha</h2>
			<div class="clearfix"></div>
			<table class="table table-striped table-bordered">
				<div class="row">
					<div class="col-sm-12">
						<div id="datatable_length">
							<!-- RANGO DE FECHAS A BUSCAR Y EXPORTAR -->
							<label style="font-weight: normal;">Desde: <input class="form-control" type="date" id="bd-desde"/></label>
							<label style="font-weight: normal;">Hasta: <input class="form-control" type="date" id="bd-hasta"/></label>
							<button id="rango_fecha" class="btn-sm btn-primary">Buscar</button>
							<!-- BOTON PARA EXPORTAR EL RANGO DE FECHAS -->
							<a onClick="javascript:reportePDF();" class="btn-sm btn-danger" style="padding: 8px 15px; cursor: pointer; position: relative;">Exportar PDF<span><img src="cargando.gif" class="cargando hide"></span></a>
						</div>
					</div>
					
				</div>
				<div class="row">
					<thead>
					<tr>
						<th width="10">N°</th>
						<th width="30">Fecha de Entrada/Salida</th>
						<th width="30">Hora</th>
						<th width="30">Nombre</th>
						<th width="30">Equipo</th>
						<th width="30">Salio?</th>
						<th width="30">Estatus del Alumno</th>
					</tr>
					</thead>
					<!-- CONTENEDOR DONDE SE IMPRIMEN LA CONSULTA POR FECHAS -->
					<tbody id="actualizar">
						<?php include('includes/imprimir_bitacora.php'); ?>
					</tbody>
				</div>
			</table>
		</div>
	</div>
</div>

<div class="modal fade" id="ver-pdf" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="x_panel">
		<div class="x_title">
			<h2 class="text-center">Reporte Generado</h2>
			<div class="clearfix"></div>
		</div>

		 <div id="view_pdf"></div>
			<a id="cancel" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancelar</a>
		</div>
	</div>
</div>
	
<script type="text/javascript">
(function(){	
	$('#rango_fecha').on('click',function(){
		var desde = $('#bd-desde').val();
		var hasta = $('#bd-hasta').val();
		var url = 'dao/busca_por_fecha.php';
		$.ajax({
		type:'POST',
		url:url,
		data:'desde='+desde+'&hasta='+hasta,
		success: function(datos){
			$('#actualizar').html(datos);
		}
	});
	return false;
	});
})();
	
function reportePDF(){
	var desde = $('#bd-desde').val();
	var hasta = $('#bd-hasta').val();
	var url = 'dao/exportar_pdf.php';
	$('.cargando').removeClass('hide');
	$.ajax({
		type:'POST',
		url:url,
		data:'desde='+desde+'&hasta='+hasta,
		success: function(datos){
			$('.cargando').addClass('hide');
			$('#ver-pdf').modal({
				show:true,
				backdrop:'static'
			});	
			PDFObject.embed("temp/reporte.pdf", "#view_pdf");
		}
	});
	return false;
}


</script>
	
<!-- Bootstrap -->
<script src="css/bootstrap/dist/js/bootstrap.min.js"></script>

</body>
</html>