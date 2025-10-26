<?php
// 1. AÑADIMOS LA CONEXIÓN Y SESIÓN DE TU NUEVO PROYECTO
// Usamos ../ para "subir" un nivel desde la carpeta 'lib'
require_once '../php_action/core.php'; 

// 2. REQUERIMOS LA LIBRERÍA FPDF (esto no cambia)
require "./fpdf/fpdf.php";

// 3. ADAPTACIÓN DE SEGURIDAD:
// El archivo original no tenía seguridad de sesión, 
// pero ahora asumimos que al menos debe haber una sesión activa.
if( !isset($_SESSION['userId']) ) {
    die("Acceso Denegado. Debe iniciar sesión.");
}

// 4. ADAPTACIÓN DE CONSULTA DE DATOS
// Usamos $connect y buscamos por 'serie'

// Obtenemos la SERIE (en lugar del ID) y la limpiamos
$serie_id = $connect->real_escape_string($_GET['id']); 

$sql_fetch = "SELECT * FROM ticket WHERE serie = '$serie_id'";
$result_fetch = $connect->query($sql_fetch);

if ($result_fetch && $result_fetch->num_rows > 0) {
    $reg = $result_fetch->fetch_assoc();
} else {
    die("Error: No se encontró el ticket con esa serie.");
}

// Cerramos la conexión
$connect->close();

// --- NADA DE AQUÍ HACIA ABAJO NECESITA CAMBIOS ---

class PDF extends FPDF
{
}

$pdf=new PDF('P','mm','Letter');
$pdf->SetMargins(15,20);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetTextColor(0,0,128);
$pdf->SetFillColor(0,255,255);
$pdf->SetDrawColor(0,0,0);
$pdf->SetFont("Arial","b",9);

if(file_exists('../img/logo.png')){
    $pdf->Image('../img/logo.png',40,10,-300);
}

$pdf->Cell (0,5,iconv("UTF-8", "ISO-8859-1",'Sistema de Gestión (Nombre de tu Empresa)'),0,1,'C');
$pdf->Cell (0,5,iconv("UTF-8", "ISO-8859-1",'Reporte de problema mediante Ticket'),0,1,'C');

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->Cell (0,5,iconv("UTF-8", "ISO-8859-1",'Información de Ticket #'.$reg['serie']),0,1,'C');

$pdf->Cell (35,10,'Fecha',1,0,'C',true);
$pdf->Cell (0,10,iconv("UTF-8", "ISO-8859-1",$reg['fecha']),1,1,'L');
$pdf->Cell (35,10,'Serie',1,0,'C',true);
$pdf->Cell (0,10,iconv("UTF-8", "ISO-8859-1",$reg['serie']),1,1,'L');
$pdf->Cell (35,10,'Estado',1,0,'C',true);
$pdf->Cell (0,10,iconv("UTF-8", "ISO-8859-1",$reg['estado_ticket']),1,1,'L');
$pdf->Cell (35,10,'Nombre',1,0,'C',true);
$pdf->Cell (0,10,iconv("UTF-8", "ISO-8859-1",$reg['nombre_usuario']),1,1,'L');
$pdf->Cell (35,10,'Email',1,0,'C',true);
$pdf->Cell (0,10,iconv("UTF-8", "ISO-8859-1",$reg['email_cliente']),1,1,'L');
$pdf->Cell (35,10,'Departamento',1,0,'C',true);
$pdf->Cell (0,10,iconv("UTF-8", "ISO-8859-1",$reg['departamento']),1,1,'L');
$pdf->Cell (35,10,'Asunto',1,0,'C',true);
$pdf->Cell (0,10,iconv("UTF-8", "ISO-8859-1",$reg['asunto']),1,1,'L');
$pdf->Cell (35,15,'Problema',1,0,'C',true);
$pdf->Cell (0,15,iconv("UTF-8", "ISO-8859-1",$reg['mensaje']),1,1,'L');
$pdf->Cell (35,15,'Solucion',1,0,'C',true);
$pdf->Cell (0,15,iconv("UTF-8", "ISO-8859-1",$reg['solucion']),1,1,'L');

$pdf->Ln();

$pdf->cell(0,5,"Reporte Generado - " . date("Y"),0,0,'C');

$pdf->output();