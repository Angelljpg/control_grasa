<?php
session_start();
require 'includes/conexion.php';

// Validamos que el usuario haya iniciado sesión
if(!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$nombre_usuario = $_SESSION['nombre_completo']; 
$nomina_usuario = $_SESSION['num_nomina'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Captura Programada - Progel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <header class="header-progel shadow mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="bg-white text-primary rounded-circle text-center fw-bold me-3 shadow-sm" style="width: 50px; height: 50px; line-height: 50px; font-size: 1.2rem;">
                    P
                </div>
                <div>
                    <h4 class="m-0 fw-bold">Progel</h4>
                    <small class="text-light opacity-75">Sistema de Mantenimiento</small>
                </div>
            </div>
            <div class="text-end">
                <span class="me-3 d-none d-md-inline text-light"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                <a href="menu.php" class="btn btn-outline-light btn-sm rounded-pill">Regresar</a>
            </div>
        </div>
    </header>

    <div class="container mb-5">
        <div class="card main-card shadow-lg mx-auto" style="max-width: 800px;">
            <div class="card-header main-card-header text-white text-center">
                <h3 class="mb-0 fw-bold">Captura Programada</h3>
                <p class="mb-0 mt-1 opacity-75">Registro de Recuperación de Caldo y Grasa</p>
            </div>
            <div class="card-body p-4 p-md-5 bg-white">
                
                <form action="php/Procesar_captura.php" method="POST">
                    
                    <div class="row mb-5 justify-content-center">
                        <div class="col-md-8">
                            <label for="porcentaje_grasa" class="form-label fw-bold d-block text-center text-secondary mb-3">
                                % Parlot (Grasa)
                            </label>
                            <div class="input-group input-group-lg shadow-sm rounded-3">
                                <input type="number" class="form-control text-center bg-light" id="porcentaje_grasa" name="porcentaje_grasa" min="0" max="100" placeholder="0" required>
                                <span class="input-group-text bg-white text-primary fw-bold">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5 justify-content-center bg-light p-4 rounded-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold d-block text-center mb-4 text-secondary">
                                Panel de Felpas en Operación
                            </label>
                            <div class="row g-3">
                                <?php for($i=1; $i<=9; $i++): ?>
                                <div class="col-4">
                                    <button type="button" class="btn btn-success w-100 py-3 btn-felpa shadow-sm" data-id="<?php echo $i; ?>">
                                        F<?php echo $i; ?><br>
                                        <small class="estado-texto" style="font-size: 0.75em;">Funcionando</small>
                                    </button>
                                    <input type="hidden" name="estado_felpa_<?php echo $i; ?>" id="input_felpa_<?php echo $i; ?>" value="Funcionando">
                                </div>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" id="felpas_operando" name="felpas_operando" value="9">
                        </div>
                    </div>

                    <div class="row mb-5 text-center justify-content-center">
                        
                        <div class="col-md-4 mb-4 mb-md-0">
                            <label class="form-label fw-bold d-block text-secondary mb-3">Grasa a tanque</label>
                            <input type="radio" class="btn-check" name="grasa_tanque" id="grasa_si" value="Si" required>
                            <label class="btn btn-outline-success btn-pill mx-1" for="grasa_si">SÍ</label>

                            <input type="radio" class="btn-check" name="grasa_tanque" id="grasa_no" value="No">
                            <label class="btn btn-outline-danger btn-pill mx-1" for="grasa_no">NO</label>
                        </div>

                        <div class="col-md-4 mb-4 mb-md-0">
                            <label class="form-label fw-bold d-block text-secondary mb-3">Caldo cc</label>
                            <input type="radio" class="btn-check" name="caldo_cc" id="caldo_si" value="Si" required>
                            <label class="btn btn-outline-success btn-pill mx-1" for="caldo_si">SÍ</label>

                            <input type="radio" class="btn-check" name="caldo_cc" id="caldo_no" value="No">
                            <label class="btn btn-outline-danger btn-pill mx-1" for="caldo_no">NO</label>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold d-block text-secondary mb-3">Drenado de tanque</label>
                            <input type="radio" class="btn-check" name="drenado_tanque" id="drenado_si" value="Si" required>
                            <label class="btn btn-outline-success btn-pill mx-1" for="drenado_si">SÍ</label>

                            <input type="radio" class="btn-check" name="drenado_tanque" id="drenado_no" value="No">
                            <label class="btn btn-outline-danger btn-pill mx-1" for="drenado_no">NO</label>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="observaciones" class="form-label fw-bold text-secondary">
                            Observaciones Adicionales
                        </label>
                        <textarea class="form-control bg-light" id="observaciones" name="observaciones" rows="3" placeholder="Escribe cualquier detalle, fuga o falla reportada aquí..."></textarea>
                    </div>

                    <div class="d-grid mt-2">
                        <button type="submit" class="btn btn-primary btn-guardar py-3 shadow">
                            Guardar Captura
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const botonesFelpa = document.querySelectorAll('.btn-felpa');
            const inputTotalFelpas = document.getElementById('felpas_operando');

            botonesFelpa.forEach(boton => {
                boton.addEventListener('click', function() {
                    let idFelpa = this.getAttribute('data-id');
                    let textoEstado = this.querySelector('.estado-texto');
                    let inputOculto = document.getElementById('input_felpa_' + idFelpa);

                    // De Verde a Rojo
                    if (this.classList.contains('btn-success')) {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-danger');
                        textoEstado.textContent = 'F.O.';
                        inputOculto.value = 'F.O.';
                    } 
                    // De Rojo a Verde
                    else {
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-success');
                        textoEstado.textContent = 'Funcionando';
                        inputOculto.value = 'Funcionando';
                    }

                    // Actualizamos el número total
                    actualizarConteoFelpas();
                });
            });

            function actualizarConteoFelpas() {
                let felpasActivas = document.querySelectorAll('.btn-felpa.btn-success').length;
                inputTotalFelpas.value = felpasActivas;
            }
        });
    </script>
</body>
</html>