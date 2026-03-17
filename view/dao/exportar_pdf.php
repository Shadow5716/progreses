<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once '../dao/adminDAO.php';

$impr = new adminDAO();

if(strlen($_POST['desde'])>0 and strlen($_POST['hasta'])>0){
	$desde = $_POST['desde'];
	$hasta = $_POST['hasta'];

	$verDesde = date('Y/m/d', strtotime($desde));
	$verHasta = date('Y/m/d', strtotime($hasta));
}else{
	$desde = '1111-01-01';
	$hasta = '9999-12-30';

	$verDesde = '__/__/____';
	$verHasta = '__/__/____';
}
require_once('../tcpdf/tcpdf.php');


	$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Luis Blanco');
	$pdf->SetTitle($_POST['reporte_name']);

	$pdf->setPrintHeader(false); 
	$pdf->setPrintFooter(TRUE);
	$pdf->SetMargins(20, 10, 20, 20); 
	$pdf->SetAutoPageBreak(true, 20); 
	$pdf->SetFont('Helvetica', '', 10);
	$pdf->addPage();



$datos = $impr->buscarAllBitacoraFecha($desde,$hasta);

$content = '';

	$content .= '
		<div class="row">
		
        	<div class="col-md-12">
				
				<h1 style="text-align:center;">Reporte de los Alumnos</h1>
            	<h3 style="text-align:center;">Desde '.$verDesde.' hasta: '.$verHasta.'</h3>

      <table border="1" cellpadding="5">
        <thead>
          <tr bgcolor="#E5E5E5">
            <th width="6%">Nº</th>
            <th width="20%">Fecha Entrada/Salida</th>
            <th width="20%">Hora Entrada/Salida</th>
			<th width="20%">Nombre</th>
			<th width="20%">Equipo</th>
			<th width="10%">Salio?</th>
			<th width="10%">Estatus</th>
          </tr>
        </thead>
	';

	for($x=0; $x<count($datos); $x++){
	$x; $l = $x+1;
	$fecha = fechaNormal($datos[$x]['fecha']);
	$hora_entrada = $datos[$x]['hr_entrada'];
	$Nombre = $datos[$x]['nombre'];
	$equipo = $datos[$x]['equipo'];
	$salio = $datos[$x]['salio'];
	$estatus = $datos[$x]['estatus'];
		
	$content .= '
		<tr nobr="true" bgcolor="#f5f5f5">
            <td width="6%">'.$l.'</td>
            <td width="20%">'.$fecha.'</td>
            <td width="20%">'.$hora_entrada.'</td>
            <td width="20%">'.$Nombre.'</td>
            <td width="20%">'.$equipo.'</td>
            <td width="10%">'.$salio.'</td>
            <td width="10%">'.$estatus.'</td>
        </tr>
	';
	}

	$content .= '</table>';

	
//CONSULTA

$pdf->writeHTML($content, true, 0, true, 0);

$pdf->lastPage();

$pdf->output('../temp/reporte.pdf', 'F');
?>