<?php
// 1. AÑADIMOS LA CONEXIÓN Y SESIÓN DE TU NUEVO PROYECTO
require_once '../php_action/core.php'; 

// 2. REQUERIMOS LA LIBRERÍA FPDF
require "./fpdf/fpdf.php";

// 3. SEGURIDAD (Cualquier usuario logueado)
if( !isset($_SESSION['userId']) ) {
    die("Acceso Denegado. Debes iniciar sesión para imprimir.");
}

// 4. ADAPTACIÓN DE CONSULTA DE DATOS (JOIN con users)
$id = (int)$_GET['id']; 
$sql_fetch = "SELECT ticket.*, users.username 
              FROM ticket 
              LEFT JOIN users ON ticket.user_id_asignado = users.user_id 
              WHERE ticket.id = $id";
$result_fetch = $connect->query($sql_fetch);

if ($result_fetch && $result_fetch->num_rows > 0) {
    $reg = $result_fetch->fetch_assoc();
} else {
    die("Error: No se encontró el ticket.");
}

// Cerramos la conexión
$connect->close();

// --- LÓGICA FPDF ---

class PDF extends FPDF
{
    // Puedes añadir cabecera y pie de página automáticos si quieres
    // function Header() { ... }
    // function Footer() { ... }
}

$pdf=new PDF('P','mm','Letter');
$pdf->SetMargins(15,20); // Márgenes Izquierdo/Derecho(15), Superior(20)
$pdf->SetAutoPageBreak(true, 25); 
$pdf->AliasNbPages();
$pdf->AddPage();

// ############################################
// ##     ¡OBTENER MÁRGENES CON GetX/PageWidth! ##
// ############################################
$leftMargin = $pdf->GetX(); // Obtener el margen izquierdo actual
$pageWidth = $pdf->GetPageWidth();
// Asumimos márgenes simétricos definidos en SetMargins(15,...)
// El margen derecho es igual al izquierdo (15mm en este caso)
$rightMarginValue = $leftMargin; 
$rightMarginPos = $pageWidth - $rightMarginValue; 
$contentWidth = $rightMarginPos - $leftMargin; // Ancho útil para MultiCell
// ############################################


$pdf->SetTextColor(0,0,128);
$pdf->SetFillColor(230,230,255); 
$pdf->SetDrawColor(150,150,150); 
$pdf->SetFont("Arial","B",11); 

if(file_exists('../img/logo.png')){
    $pdf->Image('../img/logo.png', $leftMargin, 10, 30); 
} else {
    $pdf->SetX($leftMargin); 
    $pdf->Cell(0, 6, utf8_decode('[Logo Empresa]'), 0, 1, 'L'); 
}
$pdf->SetXY($leftMargin + 35, 12); 
$pdf->Cell(0, 6, utf8_decode('Sistema de Gestión Interno'), 0, 1, 'L');
$pdf->SetX($leftMargin + 35); 
$pdf->SetFont("Arial","B",10);
$pdf->Cell(0, 6, utf8_decode('Reporte de Tarea / Incidencia'), 0, 1, 'L');
$pdf->Ln(15); 

// --- Título del Ticket ---
$pdf->SetFont("Arial","B",10);
$pdf->SetFillColor(200, 200, 240); 
$pdf->Cell(0, 8, utf8_decode('Detalles del Ticket #'.$reg['serie']), 1, 1, 'C', true); 
$pdf->Ln(5);

// --- Celdas de Datos Simples ---
$pdf->SetFont("Arial","",10); 
$labelWidth = 40; 
$pdf->SetFillColor(230,230,255); 

$pdf->SetFont("Arial","B",10); 
$pdf->Cell($labelWidth, 8, utf8_decode('Fecha:'), 1, 0, 'L', true);
$pdf->SetFont("Arial","",10); 
$pdf->Cell(0, 8, utf8_decode($reg['fecha']), 1, 1, 'L');

$pdf->SetFont("Arial","B",10);
$pdf->Cell($labelWidth, 8, utf8_decode('Serie:'), 1, 0, 'L', true);
$pdf->SetFont("Arial","",10);
$pdf->Cell(0, 8, utf8_decode($reg['serie']), 1, 1, 'L');

$pdf->SetFont("Arial","B",10);
$pdf->Cell($labelWidth, 8, utf8_decode('Estado:'), 1, 0, 'L', true);
$pdf->SetFont("Arial","",10);
$pdf->Cell(0, 8, utf8_decode($reg['estado_ticket']), 1, 1, 'L');

$pdf->SetFont("Arial","B",10);
$pdf->Cell($labelWidth, 8, utf8_decode('Asignado a:'), 1, 0, 'L', true); 
$pdf->SetFont("Arial","",10);
$asignado_a = $reg['username'] ? $reg['username'] : 'Sin asignar'; 
$pdf->Cell(0, 8, utf8_decode($asignado_a), 1, 1, 'L');

$pdf->SetFont("Arial","B",10);
$pdf->Cell($labelWidth, 8, utf8_decode('Departamento:'), 1, 0, 'L', true);
$pdf->SetFont("Arial","",10);
$pdf->Cell(0, 8, utf8_decode($reg['departamento']), 1, 1, 'L');

$pdf->SetFont("Arial","B",10);
$pdf->Cell($labelWidth, 8, utf8_decode('Asunto:'), 1, 0, 'L', true);
$pdf->SetFont("Arial","",10);
$pdf->Cell(0, 8, utf8_decode($reg['asunto']), 1, 1, 'L');

// --- Manejo Manual de MultiCell para "Problema" ---
$yAntesProblema = $pdf->GetY();
$xDespuesLabel = $leftMargin + $labelWidth; 
$alturaEstimadaLabel = 6; 

$pdf->SetFont("Arial","B",10);
$pdf->SetY($yAntesProblema);
$pdf->SetX($leftMargin); 
$pdf->Cell($labelWidth, $alturaEstimadaLabel, utf8_decode('Problema:'), 'LTB', 0, 'L', true); 
$pdf->SetXY($xDespuesLabel, $yAntesProblema); 
$pdf->SetFont("Arial","",10);
$pdf->MultiCell($contentWidth, $alturaEstimadaLabel, utf8_decode($reg['mensaje']), 'TRB', 'L'); 
$yDespuesProblema = $pdf->GetY(); 

// --- Manejo Manual de MultiCell para "Solucion" ---
$yAntesSolucion = $yDespuesProblema; 
$xDespuesLabel = $leftMargin + $labelWidth; 

$pdf->SetFont("Arial","B",10);
$pdf->SetY($yAntesSolucion);
$pdf->SetX($leftMargin); 
$pdf->Cell($labelWidth, $alturaEstimadaLabel, utf8_decode('Solucion:'), 'LTB', 0, 'L', true); 
$pdf->SetXY($xDespuesLabel, $yAntesSolucion);
$pdf->SetFont("Arial","",10);
$solucion = $reg['solucion'] ? $reg['solucion'] : '(Aún sin solución registrada)';
$pdf->MultiCell($contentWidth, $alturaEstimadaLabel, utf8_decode($solucion), 'TRB', 'L'); 
$yDespuesSolucion = $pdf->GetY(); 


// --- Pie de Página ---
$pdf->SetY($yDespuesSolucion + 10); 

$pdf->SetFont("Arial","I",8); 
$pdf->Cell(0, 5, "Reporte Generado Automáticamente - " . date("d/m/Y H:i"), 0, 0, 'C');

$pdf->output('Ticket_'.$reg['serie'].'.pdf', 'I');