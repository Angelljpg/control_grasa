<?php
session_start();
date_default_timezone_set('America/Mexico_City');

require '../includes/conexion.php';

if(!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario']; 

$porcentaje_grasa = $_POST['porcentaje_grasa'] ?? 0;
$felpas_operando  = $_POST['felpas_operando'] ?? 9;
$grasa_tanque     = $_POST['grasa_tanque'] ?? 'No';
$caldo_cc         = $_POST['caldo_cc'] ?? 'No';
$drenado_tanque   = $_POST['drenado_tanque'] ?? 'No';
$observaciones    = $_POST['observaciones'] ?? '';

// ==========================================================
// NUEVO: DETECTAR CUÁLES FELPAS ESTÁN F.O.
// ==========================================================
$felpas_fo = [];
for($i = 1; $i <= 9; $i++) {
    if(isset($_POST["estado_felpa_$i"]) && $_POST["estado_felpa_$i"] === 'F.O.') {
        $felpas_fo[] = "F" . $i; // Guarda "F1", "F2", etc.
    }
}
// Unimos las felpas fallidas con una coma, o lo dejamos vacío si todas sirven
$felpas_fuera_servicio = !empty($felpas_fo) ? implode(', ', $felpas_fo) : '';
// ==========================================================

$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i'); 
$hora_con_segundos = date('H:i:s'); 

$corte_programado = '';
$estado_tiempo = 'Con retardo'; 

if ($hora_actual >= '04:00' && $hora_actual < '11:00') {
    $corte_programado = '4:00 AM';
    if ($hora_actual <= '04:20') { $estado_tiempo = 'A tiempo'; }
} elseif ($hora_actual >= '11:00' && $hora_actual < '16:00') {
    $corte_programado = '11:00 AM';
    if ($hora_actual <= '11:20') { $estado_tiempo = 'A tiempo'; }
} elseif ($hora_actual >= '16:00' && $hora_actual < '23:30') {
    $corte_programado = '4:00 PM';
    if ($hora_actual <= '16:20') { $estado_tiempo = 'A tiempo'; }
} else {
    $corte_programado = '11:30 PM';
    if ($hora_actual >= '23:30' && $hora_actual <= '23:50') { 
        $estado_tiempo = 'A tiempo'; 
    }
}

$sql_check = "SELECT COUNT(*) FROM bitacora_recuperacion WHERE fecha_captura = ? AND corte_programado = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->execute([$fecha_actual, $corte_programado]);

if($stmt_check->fetchColumn() > 0) {
    echo "<script>
            alert('Error: La bitácora de las $corte_programado ya fue registrada el día de hoy.'); 
            window.location.href = '../menu.php';
          </script>";
    exit();
}

// Agregamos la nueva columna a la inserción SQL
$sql = "INSERT INTO bitacora_recuperacion 
        (fecha_captura, hora_captura, id_usuario, corte_programado, estado_tiempo, 
         porcentaje_grasa, felpas_operando, felpas_fuera_servicio, grasa_tanque_confirmacion, caldo_cocedor, drenado_tanque, observaciones) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conexion->prepare($sql);
    
    $stmt->execute([
        $fecha_actual,
        $hora_con_segundos,
        $id_usuario,
        $corte_programado,
        $estado_tiempo,
        $porcentaje_grasa,
        $felpas_operando,
        $felpas_fuera_servicio, // Guardamos el texto de las F.O.
        $grasa_tanque,
        $caldo_cc,
        $drenado_tanque,
        $observaciones
    ]);

    echo "<script>
            alert('¡Registro guardado exitosamente para el corte de las $corte_programado ($estado_tiempo)!');
            window.location.href = '../menu.php';
          </script>";

} catch(PDOException $e) {
    echo "Error al guardar el registro: " . $e->getMessage();
}
?>