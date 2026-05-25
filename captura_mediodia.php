<?php
session_start();
require 'includes/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

require 'includes/conexion.php';

$nombre_usuario = $_SESSION['nombre_completo'];
$fecha_hoy = date('Y-m-d');

// Validamos si ya se drenó hoy a las 12 PM
$sql_check = "SELECT COUNT(*) FROM bitacora_recuperacion WHERE fecha_captura = ? AND corte_programado = '12:00 PM'";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->execute([$fecha_hoy]);
$ya_capturado = $stmt_check->fetchColumn() > 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Mediodía - Progel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="bg-light">

    <header class="header-progel shadow mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <h4 class="m-0 fw-bold"><i class="fas fa-sun me-2 text-warning"></i> Registro 12:00 PM</h4>
            <div class="text-end">
                <span class="me-3 text-light d-none d-md-inline"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                <a href="menu.php" class="btn btn-outline-light btn-sm rounded-pill me-2">Volver al Menú</a>
            </div>
        </div>
    </header>

    <div class="container mb-5">
        
        <?php if($ya_capturado): ?>
            <div class="row justify-content-center mt-5">
                <div class="col-md-6 text-center">
                    <div class="card shadow border-0 rounded-4 p-5">
                        <i class="fas fa-check-circle text-success mb-3" style="font-size: 5rem;"></i>
                        <h3 class="fw-bold text-dark">Drenado Registrado</h3>
                        <p class="text-muted mt-2">El drenado del tanque de las 12:00 PM ya fue confirmado el día de hoy.</p>
                        <a href="menu.php" class="btn btn-primary mt-3 px-4 py-2 rounded-pill shadow-sm">Regresar al Menú</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card main-card shadow-lg mx-auto" style="max-width: 600px;">
                <div class="card-header bg-warning text-dark text-center p-3 border-0">
                    <h4 class="mb-0 fw-bold">Confirmación de Drenado</h4>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="php/procesar_mediodia.php" method="POST">
                        
                        <div class="row mb-4 text-center justify-content-center">
                            <div class="col-12">
                                <h5 class="mb-4 text-secondary">¿Se realizó el drenado del tanque de 20 mil?</h5>
                                
                                <input type="radio" class="btn-check" name="drenado_tanque" id="drenado_si" value="Si" required>
                                <label class="btn btn-outline-success btn-lg rounded-pill mx-2 px-5 fw-bold" for="drenado_si">SÍ</label>

                                <input type="radio" class="btn-check" name="drenado_tanque" id="drenado_no" value="No">
                                <label class="btn btn-outline-danger btn-lg rounded-pill mx-2 px-5 fw-bold" for="drenado_no">NO</label>
                            </div>
                        </div>

                        <div class="mb-4 mt-4">
                            <label class="form-label fw-bold text-secondary">Observaciones (Opcional)</label>
                            <textarea class="form-control bg-light" name="observaciones" rows="3" placeholder="Anota aquí si hubo algún problema con el drenado..."></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning btn-guardar py-3 shadow text-dark fw-bold">
                                Guardar Drenado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>