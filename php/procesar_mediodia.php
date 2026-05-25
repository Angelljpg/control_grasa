<?php
session_start();
require '../includes/conexion.php';

if(!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$fecha_actual = date('Y-m-d');
$hora_con_segundos = date('H:i:s'); 

// DOBLE VALIDACIÓN: Evita que lo manden dos veces
$sql_check = "SELECT COUNT(*) FROM bitacora_recuperacion WHERE fecha_captura = ? AND corte_programado = '12:00 PM'";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->execute([$fecha_actual]);

if($stmt_check->fetchColumn() > 0) {
    echo "<script>
            alert('Error: El drenado de las 12:00 PM ya fue registrado hoy.'); 
            window.location.href = '../menu.php';
          </script>";
    exit();
}

// 1. Recibimos los únicos 2 datos que manda el formulario de mediodía
$drenado_tanque = $_POST['drenado_tanque'] ?? 'No';
$observaciones  = $_POST['observaciones'] ?? '';


$porcentaje_grasa = 0;       
$felpas_operando  = 0;       
$grasa_tanque     = 'No';    
$caldo_cc         = 'No';    


$corte_programado = '12:00 PM';
$estado_tiempo = 'A tiempo'; 

$sql = "INSERT INTO bitacora_recuperacion 
        (fecha_captura, hora_captura, id_usuario, corte_programado, estado_tiempo, 
         porcentaje_grasa, felpas_operando, grasa_tanque_confirmacion, caldo_cocedor, drenado_tanque, observaciones) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
        $grasa_tanque,
        $caldo_cc,
        $drenado_tanque,
        $observaciones
    ]);

    echo "<script>
            alert('¡Drenado de las 12:00 PM registrado exitosamente!');
            window.location.href = '../menu.php';
          </script>";

} catch(PDOException $e) {
    echo "Error al guardar el registro: " . $e->getMessage();
}
?>