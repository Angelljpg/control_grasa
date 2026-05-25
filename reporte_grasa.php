<?php
session_start();
// Validamos que el usuario haya iniciado sesión
if(!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

require 'includes/conexion.php';

// 1. Recibimos la fecha del buscador, si no hay, buscamos la más reciente
if(isset($_GET['fecha_filtro']) && !empty($_GET['fecha_filtro'])) {
    $fecha_reporte = $_GET['fecha_filtro'];
} else {
    $stmtDate = $conexion->query("SELECT MAX(fecha_captura) as ultima_fecha FROM bitacora_recuperacion");
    $fecha_reporte = $stmtDate->fetchColumn();
    if(!$fecha_reporte) {
        $fecha_reporte = date('Y-m-d'); 
    }
}

// 2. Traemos los registros de 11:00 AM, 12:00 PM y 4:00 PM de ese día (Incluimos 4:00 AM para pruebas)
$sql = "SELECT * FROM bitacora_recuperacion 
        WHERE fecha_captura = ? 
        AND corte_programado IN ('4:00 AM', '11:00 AM', '12:00 PM', '4:00 PM') 
        ORDER BY FIELD(corte_programado, '4:00 AM', '11:00 AM', '12:00 PM', '4:00 PM')";
$stmt = $conexion->prepare($sql);
$stmt->execute([$fecha_reporte]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Formateamos la fecha
$meses = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
list($anio, $mes, $dia) = explode('-', $fecha_reporte);
$fecha_formateada = $dia . '/' . $meses[$mes] . '/' . $anio;

// 4. Preparamos las columnas
$recorridos = [];
$parlot = [];
$felpas = [];
$grasa = [];
$caldo = [];
$drenado = [];
$observaciones_juntas = [];

foreach($registros as $row) {
    // Hora exacta de captura
    $hora_real = date("h:i A", strtotime($row['hora_captura']));
    
    // Estatus de retardo o a tiempo
    if ($row['estado_tiempo'] == 'Con retardo') {
        $recorridos[] = "<strong>" . $row['corte_programado'] . "</strong><br><span style='color: #dc3545; font-size: 0.75rem; font-weight: bold; text-transform: none;'>RETARDO (" . $hora_real . ")</span>";
    } else {
        $recorridos[] = "<strong>" . $row['corte_programado'] . "</strong><br><span style='color: #10b981; font-size: 0.75rem; font-weight: bold; text-transform: none;'>A TIEMPO (" . $hora_real . ")</span>";
    }

    $parlot[] = $row['porcentaje_grasa'] . ' %';

    // =========================================================
    // LÓGICA DE FELPAS: LISTA DE OPERANDO VS F.O.
    // =========================================================
    $todas_las_felpas = ['F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8', 'F9'];
    $felpas_fo = [];
    
    // Convertimos el texto "F2, F7" de la BD en un arreglo
    if(!empty($row['felpas_fuera_servicio'])) {
        $felpas_fo = array_map('trim', explode(',', $row['felpas_fuera_servicio']));
    }
    
    // Obtenemos las que SÍ funcionan
    $felpas_funcionando = array_diff($todas_las_felpas, $felpas_fo);
    
    if(empty($felpas_fo)) {
        // Si no hay ninguna fallando
        $texto_felpas = "<span style='color: #10b981; font-size: 0.85rem; font-weight: bold;'>TODAS OPERANDO (9/9)</span>";
    } else {
        // Imprimimos la lista verde (Operando) y la roja (F.O.)
        $texto_felpas = "<span style='color: #10b981; font-size: 0.75rem; font-weight: bold; line-height: 1.5;'>OP: " . implode(', ', $felpas_funcionando) . "</span><br>";
        $texto_felpas .= "<span style='color: #dc3545; font-size: 0.75rem; font-weight: bold; line-height: 1.5;'>F.O.: " . implode(', ', $felpas_fo) . "</span>";
    }
    
    $felpas[] = $texto_felpas;
    // =========================================================

    // Insignias para SI y NO
    $grasa[] = strtoupper($row['grasa_tanque_confirmacion']);
    $caldo[] = strtoupper($row['caldo_cocedor']);
    $drenado[] = strtoupper($row['drenado_tanque']);
    
    if(!empty($row['observaciones'])) {
        $hora_obs = date("H:i", strtotime($row['hora_captura']));
        $observaciones_juntas[] = $hora_obs . ' ' . strtoupper($row['observaciones']);
    }
}

// Columnas para el HTML
$cantidad_cortes = count($registros) > 0 ? count($registros) : 1;
$colspan_total = $cantidad_cortes + 1; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Supervisión - Progel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f2f5f8; font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        .header-progel { background: linear-gradient(135deg, #007bdc, #1a8ae1); color: white; padding: 15px 0; box-shadow: 0 4px 15px rgba(0, 123, 220, 0.25); }
        .header-progel h4 { font-weight: 700; letter-spacing: 0.5px; }
        .contenedor-reporte { padding: 30px 20px; display: flex; justify-content: center; }
        .formato-progel { width: 100%; max-width: 1100px; border-collapse: collapse; background: white; box-shadow: 0 15px 35px rgba(0, 123, 220, 0.12); font-family: 'Segoe UI', Arial, sans-serif; text-transform: uppercase; border-radius: 20px; overflow: hidden; }
        .formato-progel th, .formato-progel td { border: 1px solid #e2e8f0 !important; padding: 14px 12px; vertical-align: middle; }
        .formato-progel td { text-align: center; font-weight: 500; color: #1e293b; }
        .formato-progel .titulo-izquierdo { text-align: right; width: 32%; font-weight: 700; background-color: #f8fafc; color: #0f172a; font-size: 0.9rem; letter-spacing: 0.3px; }
        .bg-azul-progel { background: linear-gradient(135deg, #007bdc, #1a8ae1) !important; color: white !important; font-weight: 700; }
        .bg-azul-claro { background-color: #e8f4fd !important; color: #007bdc !important; font-weight: 700; border-bottom: 2px solid #007bdc !important; }
        .titulo-principal { font-size: 1.7rem; letter-spacing: 2px; padding: 18px !important; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .fecha-row { font-size: 1.1rem; text-align: left !important; padding: 12px 20px !important; }
        .caja-observaciones { font-weight: normal; text-align: justify !important; padding: 22px 25px !important; line-height: 1.8; font-size: 0.95rem; background-color: #fefce8; border-left: 5px solid #eab308 !important; color: #78350f; text-transform: none !important; }
        .formato-progel td:not(.titulo-izquierdo) { font-weight: 600; background-color: #ffffff; font-size: 0.95rem; }
        .formato-progel tr:hover td:not(.titulo-izquierdo) { background-color: #f1f5f9; transition: background 0.2s ease; }
        .btn-outline-light { border-radius: 30px; padding: 5px 18px; font-weight: 500; transition: all 0.2s; text-decoration: none; }
        .btn-outline-light:hover { background-color: white; color: #007bdc; transform: translateY(-1px); }
        .btn-success { background: linear-gradient(135deg, #10b981, #059669); border: none; border-radius: 30px; padding: 5px 18px; font-weight: 500; text-decoration: none; color: white; display: inline-block; }
        .btn-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); color: white; }
        
        @media (max-width: 768px) {
            .contenedor-reporte { padding: 15px; overflow-x: auto; }
            .formato-progel th, .formato-progel td { padding: 8px 6px; font-size: 0.75rem; }
            .titulo-principal { font-size: 1.2rem; }
            .caja-observaciones { font-size: 0.8rem; padding: 12px !important; }
        }
        @media print {
            .header-progel, .btn-success, .btn-outline-light { display: none; }
            .formato-progel { box-shadow: none; margin: 0; border-radius: 0; }
            body { background: white; padding: 0; margin: 0; }
            .contenedor-reporte { padding: 0; }
            .caja-observaciones { background-color: #fefce8; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-azul-progel, .bg-azul-claro { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

   <header class="header-progel shadow mb-4">
        <div class="container d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="m-0 fw-bold">Panel de Supervisión</h4>
            
            <form action="" method="GET" class="d-flex align-items-center mx-auto my-2 my-md-0 bg-white p-1 rounded-pill shadow-sm" style="max-width: 300px;">
                <input type="date" name="fecha_filtro" class="form-control border-0 bg-transparent rounded-pill" value="<?php echo htmlspecialchars($fecha_reporte); ?>" required>
                <button type="submit" class="btn btn-primary rounded-pill px-3">Ver</button>
            </form>

            <div class="text-end mt-2 mt-sm-0">
                <span class="me-3 text-light d-none d-md-inline">
                    <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?>
                </span>
                <a href="enviar_reporte.php?fecha_filtro=<?php echo htmlspecialchars($fecha_reporte); ?>" class="btn btn-success btn-sm rounded-pill me-2 shadow-sm">
    <i class="fas fa-paper-plane me-1"></i> Enviar Correo
</a>
                <a href="menu.php" class="btn btn-outline-light btn-sm rounded-pill">Volver</a>
            </div>
        </div>
    </header>

    <div class="contenedor-reporte">
        <table class="formato-progel">
            <tr>
                <th colspan="<?php echo $colspan_total; ?>" class="bg-azul-progel text-center titulo-principal">REPORTE DE GRASA</th>
            </tr>
            <tr>
                <th colspan="<?php echo $colspan_total; ?>" class="bg-azul-progel fecha-row">FECHA : <?php echo $fecha_formateada; ?></th>
            </tr>
            
            <tr>
                <td class="titulo-izquierdo">RECORRIDOS</td>
                <?php 
                if(empty($recorridos)) echo "<td>-</td>";
                foreach($recorridos as $r) echo "<td>$r</td>"; 
                ?>
            </tr>
            
            <tr>
                <td class="titulo-izquierdo">PORCENTAJE DE PARLOT</td>
                <?php 
                if(empty($parlot)) echo "<td>-</td>";
                foreach($parlot as $p) echo "<td>$p</td>"; 
                ?>
            </tr>
            
            <tr>
                <td class="titulo-izquierdo">FELPAS EN OPERACIÓN</td>
                <?php 
                // Aquí el código imprime directamente la variable sin el "/ 9" manual
                if(empty($felpas)) echo "<td>-</td>";
                foreach($felpas as $f) echo "<td>$f</td>"; 
                ?>
            </tr>
            
            <tr>
                <td class="titulo-izquierdo">GRASA A TANQUE 20 MIL</td>
                <?php 
                if(empty($grasa)) echo "<td>-</td>";
                foreach($grasa as $g) {
                    $badgeClass = $g == 'SI' ? 'style="background:#10b981; color:white; padding:4px 12px; border-radius:20px; display:inline-block;"' : 'style="background:#ef4444; color:white; padding:4px 12px; border-radius:20px; display:inline-block;"';
                    echo "<td><span $badgeClass>$g</span></td>";
                }
                ?>
            </tr>
            
            <tr>
                <td class="titulo-izquierdo">CALDO CC 7</td>
                <?php 
                if(empty($caldo)) echo "<td>-</td>";
                foreach($caldo as $c) {
                    $badgeClass = $c == 'SI' ? 'style="background:#10b981; color:white; padding:4px 12px; border-radius:20px; display:inline-block;"' : 'style="background:#ef4444; color:white; padding:4px 12px; border-radius:20px; display:inline-block;"';
                    echo "<td><span $badgeClass>$c</span></td>";
                }
                ?>
            </tr>
            
            <tr>
                <td class="titulo-izquierdo">DRENADO DEL TANQUE 20 MIL</td>
                <?php 
                if(empty($drenado)) echo "<td>-</td>";
                foreach($drenado as $d) {
                    $badgeClass = $d == 'SI' ? 'style="background:#10b981; color:white; padding:4px 12px; border-radius:20px; display:inline-block;"' : 'style="background:#ef4444; color:white; padding:4px 12px; border-radius:20px; display:inline-block;"';
                    echo "<td><span $badgeClass>$d</span></td>";
                }
                ?>
            </tr>

            <tr>
                <th colspan="<?php echo $colspan_total; ?>" class="bg-azul-claro text-center" style="font-size: 1.1rem;">OBSERVACIONES</th>
            </tr>
            <tr>
                <td colspan="<?php echo $colspan_total; ?>" class="caja-observaciones">
                    <?php 
                        if(!empty($observaciones_juntas)) {
                            echo implode(". <br><br>", $observaciones_juntas) . '.';
                        } else {
                            echo 'SIN NOVEDADES REPORTADAS EN ESTE DÍA.';
                        }
                    ?>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>