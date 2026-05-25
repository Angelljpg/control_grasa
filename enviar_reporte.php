<?php
session_start();
date_default_timezone_set('America/Mexico_City');

require 'includes/conexion.php';
// Mandamos llamar tu nueva clase MailSender
require 'libs/EmailSender.php';

if(!isset($_SESSION['id_usuario'])) {
    die("Acceso denegado");
}

if(isset($_GET['fecha_filtro']) && !empty($_GET['fecha_filtro'])) {
    $fecha_reporte = $_GET['fecha_filtro'];
} else {
    $fecha_reporte = date('Y-m-d'); 
}

$sql = "SELECT * FROM bitacora_recuperacion 
        WHERE fecha_captura = ? 
        AND corte_programado IN ('4:00 AM', '11:00 AM', '12:00 PM', '4:00 PM') 
        ORDER BY FIELD(corte_programado, '4:00 AM', '11:00 AM', '12:00 PM', '4:00 PM')";
$stmt = $conexion->prepare($sql);
$stmt->execute([$fecha_reporte]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$meses = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
list($anio, $mes, $dia) = explode('-', $fecha_reporte);
$fecha_formateada = $dia . '/' . $meses[$mes] . '/' . $anio;

$recorridos = []; $parlot = []; $felpas = []; $grasa = []; $caldo = []; $drenado = []; $observaciones_juntas = [];

foreach($registros as $row) {
    $recorridos[] = "<strong>" . $row['corte_programado'] . "</strong>";
    $parlot[] = $row['porcentaje_grasa'] . ' %';

    $todas_las_felpas = ['F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8', 'F9'];
    $felpas_fo = !empty($row['felpas_fuera_servicio']) ? array_map('trim', explode(',', $row['felpas_fuera_servicio'])) : [];
    $felpas_funcionando = array_diff($todas_las_felpas, $felpas_fo);
    
    if(empty($felpas_fo)) {
        $felpas[] = "<span style='color: #10b981; font-weight: bold;'>TODAS OPERANDO</span>";
    } else {
        $felpas[] = "<span style='color: #10b981; font-weight: bold;'>OP: " . implode(', ', $felpas_funcionando) . "</span><br><span style='color: #dc3545; font-weight: bold;'>F.O.: " . implode(', ', $felpas_fo) . "</span>";
    }

    $grasa[] = strtoupper($row['grasa_tanque_confirmacion']);
    $caldo[] = strtoupper($row['caldo_cocedor']);
    $drenado[] = strtoupper($row['drenado_tanque']);
    
    if(!empty($row['observaciones'])) {
        $hora_obs = date("H:i", strtotime($row['hora_captura']));
        $observaciones_juntas[] = $hora_obs . ' ' . strtoupper($row['observaciones']);
    }
}

$colspan_total = (count($registros) > 0 ? count($registros) : 1) + 1;

$cuerpo_correo = '
<div style="font-family: Arial, sans-serif; background-color: #f2f5f8; padding: 20px;">
    <table style="width: 100%; max-width: 900px; margin: 0 auto; background: white; border-collapse: collapse; border: 2px solid #0f172a; text-transform: uppercase;">
        <tr><th colspan="'.$colspan_total.'" style="background-color: #007bdc; color: white; padding: 15px; font-size: 1.5rem;">REPORTE DE GRASA</th></tr>
        <tr><th colspan="'.$colspan_total.'" style="background-color: #e8f4fd; color: #007bdc; padding: 10px; text-align: left; border-bottom: 2px solid #007bdc;">FECHA : '.$fecha_formateada.'</th></tr>
        
        <tr><td style="border: 1px solid #cbd5e1; padding: 12px; background-color: #f8fafc; font-weight: bold; text-align: right;">RECORRIDOS</td>';
            foreach($recorridos as $r) { $cuerpo_correo .= '<td style="border: 1px solid #cbd5e1; padding: 12px; text-align: center;">'.$r.'</td>'; }
        $cuerpo_correo .= '</tr><tr><td style="border: 1px solid #cbd5e1; padding: 12px; background-color: #f8fafc; font-weight: bold; text-align: right;">% PARLOT</td>';
            foreach($parlot as $p) { $cuerpo_correo .= '<td style="border: 1px solid #cbd5e1; padding: 12px; text-align: center;">'.$p.'</td>'; }
        $cuerpo_correo .= '</tr><tr><td style="border: 1px solid #cbd5e1; padding: 12px; background-color: #f8fafc; font-weight: bold; text-align: right;">FELPAS</td>';
            foreach($felpas as $f) { $cuerpo_correo .= '<td style="border: 1px solid #cbd5e1; padding: 12px; text-align: center; font-size: 0.8rem;">'.$f.'</td>'; }
        $cuerpo_correo .= '</tr><tr><td style="border: 1px solid #cbd5e1; padding: 12px; background-color: #f8fafc; font-weight: bold; text-align: right;">GRASA TANQUE</td>';
            foreach($grasa as $g) { $cuerpo_correo .= '<td style="border: 1px solid #cbd5e1; padding: 12px; text-align: center;">'.$g.'</td>'; }
        $cuerpo_correo .= '</tr><tr><td style="border: 1px solid #cbd5e1; padding: 12px; background-color: #f8fafc; font-weight: bold; text-align: right;">CALDO CC</td>';
            foreach($caldo as $c) { $cuerpo_correo .= '<td style="border: 1px solid #cbd5e1; padding: 12px; text-align: center;">'.$c.'</td>'; }
        $cuerpo_correo .= '</tr><tr><td style="border: 1px solid #cbd5e1; padding: 12px; background-color: #f8fafc; font-weight: bold; text-align: right;">DRENADO 20MIL</td>';
            foreach($drenado as $d) { $cuerpo_correo .= '<td style="border: 1px solid #cbd5e1; padding: 12px; text-align: center;">'.$d.'</td>'; }
        $cuerpo_correo .= '</tr>
        
        <tr><th colspan="'.$colspan_total.'" style="background-color: #e8f4fd; color: #007bdc; padding: 10px;">OBSERVACIONES</th></tr>
        <tr><td colspan="'.$colspan_total.'" style="padding: 20px; background-color: #fefce8; border-left: 5px solid #eab308; text-align: justify; color: #78350f;">'.(!empty($observaciones_juntas) ? implode(". <br><br>", $observaciones_juntas) : 'Sin novedades').'</td></tr>
    </table>
</div>';

// ==========================================
// AQUI USAMOS TU NUEVO MAIL SENDER
// ==========================================
$emailSender = new MailSender();
$asunto = 'Reporte de Grasa - ' . $fecha_formateada;

// Pones tus correos de prueba aquí
$correos_destino = [
    'lexalegna559@gmail.com',
    'tu_segundo_correo_prueba@outlook.com'
];

if ($emailSender->sendMail($asunto, $cuerpo_correo, $correos_destino)) {
    // ALERTA DE ÉXITO ANIMADA
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Enviando...</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background-color: #f2f5f8; font-family: 'Segoe UI', Arial, sans-serif; }</style>
    </head>
    <body>
        <script>
            Swal.fire({
                title: '¡Excelente!',
                text: 'El reporte fue enviado exitosamente.',
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                window.location.href = 'reporte_grasa.php?fecha_filtro=".$fecha_reporte."';
            });
        </script>
    </body>
    </html>";
} else {
    // ALERTA DE ERROR ANIMADA
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background-color: #f2f5f8; font-family: 'Segoe UI', Arial, sans-serif; }</style>
    </head>
    <body>
        <script>
            Swal.fire({
                title: '¡Ups!',
                text: 'Hubo un error al intentar enviar el correo.',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Volver al reporte'
            }).then((result) => {
                window.location.href = 'reporte_grasa.php?fecha_filtro=".$fecha_reporte."';
            });
        </script>
    </body>
    </html>";
}
?>