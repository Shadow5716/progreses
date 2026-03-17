<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once 'adminDAO.php';

$impr = new adminDAO();


//EJECUTAMOS LA CONSULTA DE BUSQUEDA
if($_POST['desde']==false || $_POST['hasta']==false){
	include('../includes/imprimir_bitacora.php');
}else{
	$desde = $_POST['desde'];
	$hasta = $_POST['hasta'];

	//EJECUTAMOS LA CONSULTA DE BUSQUEDA
	


	$datos = $impr->buscarAllBitacoraFecha($desde,$hasta);
	

?>
<?php 
	if(count($datos)>0){ 
	for($x=0; $x<count($datos); $x++){
?>
<tr>
	<td><?php  $x; $l = $x+1; echo $l; ?></xtd>
	<td><?php echo fechaNormal($datos[$x]['InTime']); ?></td>
	<td><?php echo $datos[$x]['Status']; ?></td>
	<td><?php echo $datos[$x]['UserName']; ?></td>
</tr>

<?php
	}
	}else{
?>
	<tr class="odd"><td valign="top" colspan="8" class="dataTables_empty">No hay datos disponibles en la tabla</td></tr>
<?php
	}
}			
?>
