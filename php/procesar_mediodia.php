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
                text: 'Error: El drenado de las 12:00 PM ya fue registrado hoy.',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Volver'
            }).then((result) => {
                window.location.href = '../menu.php';
            });
        </script>
    </body>
    </html>";
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

    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Procesando...</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background-color: #f2f5f8; font-family: 'Segoe UI', Arial, sans-serif; }</style>
    </head>
    <body>
        <script>
            Swal.fire({
                title: '¡Excelente!',
                text: '¡Drenado de las 12:00 PM registrado exitosamente!',
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                window.location.href = '../menu.php';
            });
        </script>
    </body>
    </html>";

} catch(PDOException $e) {
    echo "Error al guardar el registro: " . $e->getMessage();
}
?>