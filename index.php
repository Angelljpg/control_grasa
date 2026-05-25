<?php
require 'includes/conexion.php';
session_start();

if(isset($_SESSION['id_usuario'])) {
    if($_SESSION['puesto'] == 'Supervisor') {
        header("Location: reporte_grasa.php");
    } else {
        header("Location: captura_grasa.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Progel Mantenimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <div class="logo-circle">P</div>
                <h3>Progel</h3>
                <p>Sistema de Recuperación</p>
            </div>
            <div class="login-body">
                
                <?php if(isset($_GET['error'])): ?>
                    <?php 
                        $error_msg = isset($_GET['error']) ? $_GET['error'] : 'error_general';
                        $mensajes = [
                            'nomina_no_encontrada' => 'Nómina no encontrada en el sistema.',
                            'nomina_vacia' => 'Por favor ingresa tu número de nómina.',
                            'nomina_invalida' => 'El número de nómina debe contener solo dígitos.',
                            'usuario_inactivo' => 'Tu usuario ha sido desactivado. Contacta a administración.',
                            'error_general' => 'Ocurrió un error. Intenta nuevamente.'
                        ];
                        $mensaje = $mensajes[$error_msg] ?? $mensajes['error_general'];
                    ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <form action="php/validar_login.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="num_nomina" class="form-label">Número de Nómina</label>
                        <input 
                            type="text" 
                            id="num_nomina"
                            name="num_nomina" 
                            class="form-control" 
                            placeholder="Ejemplo: 12345" 
                            required 
                            autofocus
                            maxlength="10"
                            pattern="[0-9]+"
                        >
                        <div id="nomina-error" class="form-error"></div>
                    </div>
                    <button type="submit" class="btn-ingresar">Ingresar al Sistema</button>
                </form>

                <div style="margin-top: 20px; text-align: center; font-size: 0.85rem; color: #64748b;">
                    <p>Ingresa tu número de nómina para continuar</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const nominaInput = document.getElementById('num_nomina');
        const nominaError = document.getElementById('nomina-error');

        form.addEventListener('submit', function(e) {
            nominaError.classList.remove('show');
            nominaError.textContent = '';

            const nomina = nominaInput.value.trim();

            if (nomina === '') {
                e.preventDefault();
                nominaError.textContent = 'El número de nómina es requerido';
                nominaError.classList.add('show');
                nominaInput.focus();
                return false;
            }

            if (!/^\d+$/.test(nomina)) {
                e.preventDefault();
                nominaError.textContent = 'Solo se permiten números';
                nominaError.classList.add('show');
                nominaInput.focus();
                return false;
            }

            if (nomina.length < 3) {
                e.preventDefault();
                nominaError.textContent = 'La nómina debe tener al 4 dígitos';
                nominaError.classList.add('show');
                nominaInput.focus();
                return false;
            }
        });

        // Permitir solo números
        nominaInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>