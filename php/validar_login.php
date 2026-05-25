<?php
session_start();
require '../includes/conexion.php';

$error_type = 'error_general';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num_nomina = isset($_POST['num_nomina']) ? trim($_POST['num_nomina']) : '';

    // Validaciones del lado del servidor
    if (empty($num_nomina)) {
        $error_type = 'nomina_vacia';
    } elseif (!is_numeric($num_nomina)) {
        $error_type = 'nomina_invalida';
    } else {
        try {
            // Buscar usuario en la base de datos
            $sql = "SELECT * FROM usuarios WHERE num_nomina = ? AND activo = 1 LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$num_nomina]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Guardamos los datos en la sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
                $_SESSION['puesto'] = $usuario['puesto'];
                $_SESSION['num_nomina'] = $usuario['num_nomina'];

                header("location: ../menu.php");
                exit();
            

                // Redirigimos dependiendo del puesto
                if ($usuario['puesto'] == 'Supervisor') {
                    header("Location: ../reporte_grasa.php");
                } else {
                    header("Location: ../captura_grasa.php");
                }
                exit();
            } else {
                // Verificar si existe pero está inactivo
                $sql_inactivo = "SELECT * FROM usuarios WHERE num_nomina = ? LIMIT 1";
                $stmt_inactivo = $conexion->prepare($sql_inactivo);
                $stmt_inactivo->execute([$num_nomina]);
                $usuario_inactivo = $stmt_inactivo->fetch(PDO::FETCH_ASSOC);

                if ($usuario_inactivo && !$usuario_inactivo['activo']) {
                    $error_type = 'usuario_inactivo';
                } else {
                    $error_type = 'nomina_no_encontrada';
                }
            }
        } catch(PDOException $e) {
            error_log("Error en validar_login: " . $e->getMessage());
            $error_type = 'error_general';
        }
    }
}

// Redirigir al index con el error
header("Location: ../index.php?error=" . urlencode($error_type));
exit();
?>
