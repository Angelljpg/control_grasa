<?php
date_default_timezone_set('America/Mazatlan');

$host = 'localhost';
$dbname = 'control_grasa'; 
$username = 'root'; 
$password = ''; 

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
