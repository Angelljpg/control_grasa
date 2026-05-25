<?php
session_start();
require 'includes/conexion.php';

// Si no hay sesión, lo regresamos al login
if(!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$nombre = $_SESSION['nombre_completo'];
// Ya no es estrictamente necesario validar el puesto para mostrar cosas
$puesto = $_SESSION['puesto']; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Progel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Contenedor principal con disposición VERTICAL */
        .menu-vertical {
            display: flex;
            flex-direction: column;
            gap: 25px;
            max-width: 550px;
            margin: 0 auto;
        }
        
        .menu-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: row;
            align-items: center;
            color: inherit;
            background: white;
            padding: 20px 25px;
            width: 100%;
        }
        
        .menu-card:hover {
            transform: translateX(8px);
            box-shadow: 0 15px 30px rgba(0, 123, 220, 0.15) !important;
            color: inherit;
        }
        
        .icon-box {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .menu-content {
            flex: 1;
            text-align: left;
        }
        
        .menu-content h4 {
            margin: 0 0 5px 0;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .menu-content p {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
        }
        
        /* Badge de puesto */
        .badge-puesto {
            background: linear-gradient(135deg, #007bdc, #1a8ae1);
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Header con tu paleta de colores */
        .header-progel { 
            background: linear-gradient(135deg, #007bdc, #1a8ae1); 
            color: white; 
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(0, 123, 220, 0.25);
        }
        
        .btn-outline-light {
            border-radius: 30px;
            padding: 5px 18px;
            transition: all 0.2s;
        }
        
        .btn-outline-light:hover {
            background-color: white;
            color: #007bdc;
            transform: translateY(-2px);
        }
        
        /* Colores de los iconos */
        .icon-box.bg-success {
            background: linear-gradient(135deg, #10b981, #059669) !important;
        }
        
        .icon-box.bg-primary {
            background: linear-gradient(135deg, #007bdc, #1a8ae1) !important;
        }
        
        .icon-box.bg-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .menu-card {
                padding: 15px;
            }
            .icon-box {
                width: 55px;
                height: 55px;
                font-size: 1.5rem;
                margin-right: 15px;
            }
            .menu-content h4 {
                font-size: 1.1rem;
            }
            .menu-content p {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

    <header class="header-progel shadow mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="bg-white text-primary rounded-circle text-center fw-bold me-3 shadow-sm" style="width: 45px; height: 45px; line-height: 45px; font-size: 1.2rem;">
                    <i class="fas fa-industry"></i>
                </div>
                <div>
                    <h4 class="m-0 fw-bold">Progel</h4>
                    <small class="text-light opacity-75">Sistema de Control de Grasa</small>
                </div>
            </div>
            <div class="text-end">
                <a href="php/logout.php" class="btn btn-outline-light btn-sm rounded-pill">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <div class="container mb-5">
        
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h2 class="fw-bold" style="color: #1e293b;">¡Bienvenido!</h2>
                <p class="mt-2 mb-3" style="color: #475569;">
                    <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($nombre); ?>
                </p>
                
            </div>
        </div>

        <!-- Menú VERTICAL - Una tarjeta debajo de la otra -->
        <div class="menu-vertical">
            
            <!-- Opción 1: Bitácora de Grasa -->
            <a href="captura_grasa.php" class="menu-card shadow-sm">
                <div class="icon-box bg-success text-white shadow-sm">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="menu-content">
                    <h4><i class="fas fa-tint me-2" style="font-size: 0.9rem;"></i> Bitácora de Grasa</h4>
                    <p>Registro de recuperación de caldo y grasa - Cortes programados</p>
                </div>
                <div class="ms-auto text-muted">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <!-- Opción 2: Registro Diario -->
            <a href="reporte_grasa.php" class="menu-card shadow-sm">
                <div class="icon-box bg-primary text-white shadow-sm">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="menu-content">
                    <h4><i class="fas fa-calendar-alt me-2" style="font-size: 0.9rem;"></i> Registro Diario</h4>
                    <p>Consulta de registros de producción</p>
                </div>
                <div class="ms-auto text-muted">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>

            <!-- Opción 3: Registro Mediodía -->
            <a href="captura_mediodia.php" class="menu-card shadow-sm">
                <div class="icon-box bg-warning text-white shadow-sm">
                    <i class="fas fa-sun"></i>
                </div>
                <div class="menu-content">
                    <h4><i class="fas fa-clock me-2" style="font-size: 0.9rem;"></i> Registro Mediodía</h4>
                    <p>Control exclusivo del drenado del tanque a las 12:00 PM</p>
                </div>
                <div class="ms-auto text-muted">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>

        </div>
        
        <!-- Pequeña nota decorativa -->
        <div class="text-center mt-5 pt-3">
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i> Sistema de captura de Grasa
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>