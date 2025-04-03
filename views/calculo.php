<?php
include '../src/config/db.php';

try {
    // Obtener parámetros
    $fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

    function existeCalculoSemanal($pdo, $fechaInicio, $fechaFin) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM resultados_nomina2 
                              WHERE fecha_inicio = ? AND fecha_fin = ?");
        $stmt->execute([$fechaInicio, $fechaFin]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    if (isset($_GET['verificar_calculo'])) {
        header('Content-Type: application/json');
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        $fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fecha)));
        $fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fecha)));
        
        $existe = existeCalculoSemanal($pdo, $fechaInicioSemana, $fechaFinSemana);
        
        echo json_encode([
            'existe' => $existe,
            'periodo' => date('d/m/Y', strtotime($fechaInicioSemana)) . " al " . date('d/m/Y', strtotime($fechaFinSemana))
        ]);
        exit();
    }

    if (isset($_POST['calcular_nomina'])) {
        // Calcular fechas de la semana
        $fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
        $fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));
        
        // Verificación estricta antes de cualquier operación
        if (existeCalculoSemanal($pdo, $fechaInicioSemana, $fechaFinSemana)) {
            $_SESSION['error_calculo'] = [
                'periodo' => date('d/m/Y', strtotime($fechaInicioSemana)) . " al " . date('d/m/Y', strtotime($fechaFinSemana)),
                'mensaje' => "Ya hay deducciones para esta semana."
            ];
            header("Location: calculo.php?fecha=" . urlencode($fechaSeleccionada));
            exit();
        }
        
        // Bloquear posibles accesos concurrentes
        $pdo->beginTransaction();
        
        try {
            // Verificar nuevamente dentro de la transacción (para evitar race conditions)
            if (existeCalculoSemanal($pdo, $fechaInicioSemana, $fechaFinSemana)) {
                throw new Exception("Ya existe un cálculo para esta semana (verificación concurrente)");
            }
            
            // Proceder con el cálculo
            $stmt = $pdo->prepare("CALL calcularNominaSemana2(?, ?)");
            $stmt->execute([$fechaInicioSemana, $fechaFinSemana]);
            $empleadosProcesados = $stmt->rowCount();
            
            $pdo->commit();
            
            // Resto del código de éxito...
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_calculo'] = [
                'periodo' => date('d/m/Y', strtotime($fechaInicioSemana)) . " al " . date('d/m/Y', strtotime($fechaFinSemana)),
                'mensaje' => "Error: " . $e->getMessage()
            ];
            header("Location: calculo.php?fecha=" . urlencode($fechaSeleccionada));
            exit();
        }
    }
    
    // Configuración de paginación
    $porPagina = 15;
    $offset = ($pagina - 1) * $porPagina;

    // Calcular el inicio y fin de la semana (lunes a domingo)
    $fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
    $fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));

    // Preparar consulta con filtros y paginación
    $sql = "CALL ObtenerNominaSemanalPorRango(:fechaInicio, :fechaFin)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fechaInicio', $fechaInicioSemana, PDO::PARAM_STR);
    $stmt->bindParam(':fechaFin', $fechaFinSemana, PDO::PARAM_STR);
    $stmt->execute();
    
    // Obtener todos los resultados
    $todosEmpleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    
    // Aplicar filtro de búsqueda si existe
    if (!empty($busqueda)) {
        $busquedaLower = strtolower($busqueda);
        $todosEmpleados = array_filter($todosEmpleados, function($empleado) use ($busquedaLower) {
            return strpos(strtolower($empleado['nombre']), $busquedaLower) !== false ||
                   strpos(strtolower($empleado['numero_empleado']), $busquedaLower) !== false ||
                   strpos(strtolower($empleado['departamento']), $busquedaLower) !== false;
        });
    }
    
    // Calcular total de empleados después de filtrar
    $totalEmpleados = count($todosEmpleados);
    $totalPaginas = ceil($totalEmpleados / $porPagina);
    
    // Obtener solo los empleados para la página actual
    $empleados = array_slice($todosEmpleados, $offset, $porPagina);
    
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cálculo de Nómina | Sistema de Gestión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --light-bg: #f8fafc;
            --sidebar-bg: #1e293b;
            --sidebar-active: #0f172a;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-bg);
            color: #334155;
            line-height: 1.5;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border: 1px solid #e0e0e0;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-title {
            color: #2c3e50;
            font-size: 1.4rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        #loadingOverlay {
            transition: opacity 0.3s ease;
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        .btn-calculate {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            margin-right: 10px;
        }

        .btn-calculate:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-calculate:active {
            transform: translateY(0);
        }

        .btn-calculate i {
            margin-right: 8px;
        }

        .error-container {
            animation: fadeIn 0.5s ease-out;
        }

        .elegant-error-container {
            max-width: 500px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 30px rgba(0,0,0,0.08);
            font-family: 'Inter', sans-serif;
            border: 1px solid #f0f0f0;
        }

        .error-image-container {
            background: linear-gradient(135deg, #fff9f9 0%, #ffefef 100%);
            padding: 2.5rem 0;
            text-align: center;
            border-bottom: 1px solid #f9e0e0;
        }

        .error-icon {
            height: 80px;
            width: auto;
            filter: drop-shadow(0 3px 6px rgba(233, 30, 99, 0.1));
        }

        .error-content {
            padding: 1.75rem;
        }

        .error-title {
            background: #fff5f5;
            color: #e74c3c;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-title i {
            margin-right: 0.5rem;
            font-size: 1.2em;
        }

        .error-period {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 3px solid #e74c3c;
            text-align: center;
        }

        .error-period p {
            margin: 0;
            color: #555;
            font-size: 0.95rem;
        }

        .period-dates {
            margin-top: 0.5rem !important;
            font-size: 1.1rem !important;
            font-weight: 600;
            color: #333 !important;
        }

        .error-instructions {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
            line-height: 1.6;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .error-instructions p {
            margin: 0 0 0.75rem;
        }

        .company-info {
            display: inline-flex;
            align-items: center;
            background: #fff;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            color: #444;
            border: 1px solid #eee;
        }

        .company-info i {
            margin-right: 0.5rem;
            color: #e74c3c;
        }

        .dismiss-button {
            display: block;
            width: 100%;
            padding: 0.75rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .dismiss-button:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .dismiss-button:active {
            transform: translateY(0);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background-color: var(--sidebar-bg);
            color: #e2e8f0;
            padding: 1.5rem 0;
            position: relative;
            z-index: 10;
            transition: width 0.3s ease;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .sidebar h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }
        
        .sidebar-section {
            margin-bottom: 1.75rem;
        }
        
        .sidebar-section h3 {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: rgba(226, 232, 240, 0.6);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            padding: 0 1.5rem;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar ul li a {
            color: rgba(226, 232, 240, 0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1.5rem;
            border-radius: 0;
            transition: var(--transition);
            font-weight: 500;
            font-size: 0.9375rem;
            position: relative;
        }
        
        .sidebar ul li a:hover {
            background: rgba(226, 232, 240, 0.05);
            color: white;
        }
        
        .sidebar ul li a.active {
            background: var(--sidebar-active);
            color: white;
            font-weight: 600;
        }
        
        .sidebar ul li a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary-color);
        }
        
        .sidebar ul li a i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            padding: 2.5rem;
            overflow-y: auto;
        }
        
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        h1 i {
            color: var(--primary-color);
        }
        
        .filter-form {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .form-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #475569;
        }
        
        .filter-form input[type="date"],
        .filter-form input[type="text"],
        .filter-form input[type="search"] {
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
        }
        
        .filter-form input[type="date"]:focus,
        .filter-form input[type="text"]:focus,
        .filter-form input[type="search"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .filter-form button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-form button:hover {
            background-color: var(--primary-dark);
        }
        
        /* Tabla mejorada */
        .nomina-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-top: 1.5rem;
        }
        
        .nomina-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .nomina-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }
        
        .nomina-table tr:last-child td {
            border-bottom: none;
        }
        
        .nomina-table tr:hover td {
            background-color: #f8fafc;
        }
        
        /* Estilos para valores monetarios */
        .text-money {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }
        
        .text-positive {
            color: var(--success-color);
        }
        
        .text-negative {
            color: var(--danger-color);
        }
        
        .text-total {
            font-weight: 700;
            color: #1e293b;
        }
        
        .message {
            padding: 1.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .message p {
            color: #64748b;
        }
        
        .week-summary {
            background-color: #f1f5f9;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }
        
        .pagination a, 
        .pagination span {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .pagination a {
            color: var(--primary-color);
            border: 1px solid #e2e8f0;
            background: white;
        }
        
        .pagination a:hover {
            background-color: #f1f5f9;
        }
        
        .pagination .current {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .pagination .disabled {
            color: #94a3b8;
            pointer-events: none;
        }
        
        .results-count {
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 1rem;
            text-align: right;
        }
        .btn-export {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-left: auto;
        }
        
        .btn-export:hover {
            background-color: #c0392b;
        }
        
        .btn-export i {
            margin-right: 8px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            
            .main-content {
                padding: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 72px;
                overflow: hidden;
            }
            
            .sidebar-header h2, 
            .sidebar-section h3,
            .sidebar ul li a span {
                display: none;
            }
            
            .sidebar ul li a {
                justify-content: center;
                padding: 0.75rem;
            }
            
            .sidebar ul li a i {
                font-size: 1.25rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            .pagination {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><span>Nóminas</span></h2>
            </div>

            <!-- Sección: Informes -->
            <div class="sidebar-section">
                <h3><span>Informes</span></h3>
                <ul>
                    <li><a href="../public/index.php"><i class="fas fa-chart-bar"></i> <span>Resumen</span></a></li>
                </ul>
            </div>

            <!-- Sección: Gestionar -->
            <div class="sidebar-section">
                <h3><span>Gestionar</span></h3>
                <ul>
                    <li><a href="../views/checadas.php"><i class="fas fa-calendar-alt"></i> <span>Asistencia</span></a></li>
                    <li><a href="../views/empleados.php"><i class="fas fa-users"></i> <span>Empleados</span></a></li>
                    <li><a href="../views/calculo.php" class="active"><i class="fas fa-calculator"></i> <span>Deducciones</span></a></li>
                    <li><a href="../views/bonos.php"><i class="fas fa-gift"></i> <span>Bonos</span></a></li>
                    <li><a href="../views/roles.php"><i class="fas fa-briefcase"></i> <span>Cargos</span></a></li>
                    <li><a href="../views/importar.php"><i class="fas fa-file-import"></i> <span>Importar datos</span></a></li>
                </ul>
            </div>

            <!-- Sección: Imprimibles -->
            <div class="sidebar-section">
                <h3><span>Imprimibles</span></h3>
                <ul>
                    <li><a href="../views/reportes.php"><i class="fas fa-file-alt"></i> <span>Reportes PDF</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="main-content">
            <div class="content-container">
                <div class="action-buttons">
                    <h1><i class="fas fa-calculator"></i> Cálculo de Nómina</h1>
                    <div>
                        <!-- Botón para calcular nómina con confirmación -->
                        <button type="button" onclick="confirmarCalculo('<?= date('d/m/Y', strtotime($fechaInicioSemana)) ?>', '<?= date('d/m/Y', strtotime($fechaFinSemana)) ?>')" 
                                class="btn-calculate">
                            <i class="fas fa-calculator"></i> Hacer Cálculo Semanal
                        </button>
                        
                        <a href="javascript:void(0);" onclick="generarPDF('<?= urlencode($fechaSeleccionada) ?>')" 
                            class="btn-export">
                            <i class="fas fa-file-pdf"></i> Exportar a PDF
                        </a>
                    </div>
                </div>

                <!-- Modal de confirmación -->
                <div id="confirmModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                    <div class="modal-content" style="background:white; padding:2rem; border-radius:8px; max-width:500px; width:90%;">
                        <h3 style="margin-bottom:1rem;" id="modalTitle">Confirmar Cálculo</h3>
                        <div id="modalMessage">
                            ¿Está seguro que desea calcular la nómina para la semana del <span id="modalDates"></span>?
                            <p style="color:#666; font-size:0.9rem; margin-top:0.5rem;">
                                Esta acción puede tomar varios minutos.
                            </p>
                        </div>
                        
                        <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2rem;">
                            <button onclick="document.getElementById('confirmModal').style.display='none'" 
                                    style="padding:0.5rem 1rem; background:#f1f5f9; border:1px solid #ddd; border-radius:4px;">
                                Cancelar
                            </button>
                            <button onclick="ejecutarCalculo()" id="confirmButton"
                                    style="padding:0.5rem 1rem; background:#28a745; color:white; border:none; border-radius:4px;">
                                Confirmar
                            </button>
                        </div>
                    </div>
                </div>

                <div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.9); z-index:1001; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="text-align:center; max-width:400px; padding:2rem; background:white; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                        <div class="spinner" style="border:5px solid #f3f3f3; border-top:5px solid #3498db; border-radius:50%; width:50px; height:50px; animation:spin 1s linear infinite; margin:0 auto 1.5rem;"></div>
                        <h3 style="margin-bottom:1rem; color:#333;">Calculando Nómina Semanal</h3>
                        <p id="loadingText" style="color:#555; margin-bottom:0.5rem;">Procesando datos, por favor espere...</p>
                        
                        <!-- Barra de progreso simulada -->
                        <div style="height:6px; background:#f1f1f1; border-radius:3px; margin:1rem 0; overflow:hidden;">
                            <div id="progressBar" style="height:100%; background:#3498db; width:0%; transition:width 10s linear;"></div>
                        </div>
                        
                        <p style="color:#888; font-size:0.9rem;">
                            <span id="countdown">10</span> segundos restantes...
                        </p>
                    </div>
                </div>
                <?php if (isset($_SESSION['mensaje_exito'])): ?>
                    <div class="alert alert-success" style="margin-bottom:20px; padding:15px; background-color:#d4edda; color:#155724; border-radius:4px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong><?= $_SESSION['mensaje_exito']['titulo'] ?></strong>
                            <p style="margin:5px 0 0;"><?= $_SESSION['mensaje_exito']['mensaje'] ?></p>
                            <?php if ($_SESSION['mensaje_exito']['empleados'] > 0): ?>
                                <p style="margin:5px 0 0; font-size:0.9em;">Empleados procesados: <?= $_SESSION['mensaje_exito']['empleados'] ?></p>
                            <?php endif; ?>
                        </div>
                        <button onclick="this.parentElement.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.2em;">×</button>
                    </div>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                <?php endif; ?>
                
                <!-- Formulario para seleccionar semana y buscar empleados -->
                <form class="filter-form" method="GET" action="calculo.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha">Selecciona una semana:</label>
                            <input type="date" id="fecha" name="fecha" value="<?php echo $fechaSeleccionada; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="busqueda">Buscar empleado:</label>
                            <input type="search" id="busqueda" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" 
                                   placeholder="Nombre, N° empleado o departamento">
                        </div>
                    </div>
                    
                    <button type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </form>
                
                <!-- Resumen de la semana seleccionada -->
                <div class="week-summary">
                    Mostrando nóminas de la semana del <?php echo date('d/m/Y', strtotime($fechaInicioSemana)); ?> al <?php echo date('d/m/Y', strtotime($fechaFinSemana)); ?>
                </div>
                
                <!-- Contador de resultados -->
                <?php if ($totalEmpleados > 0): ?>
                    <div class="results-count">
                        Mostrando <?php echo min($porPagina, count($empleados)); ?> de <?php echo $totalEmpleados; ?> empleados
                    </div>
                <?php endif; ?>
                
                <!-- Contenedor de resultados -->
                <div id="resultados">
                    <?php if (!empty($empleados)): ?>
                        <table class="nomina-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>No. Emp.</th>
                                    <th>Dep.</th>
                                    <th>Faltas</th>
                                    <th>Días Faltados</th>
                                    <th>Bonos</th>
                                    <th>Descuentos</th>
                                    <th>Deduccion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empleados as $empleado): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['numero_empleado']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['departamento']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['faltas']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['dias_faltas']); ?></td>
                                        <td class="text-money text-positive">$<?php echo number_format($empleado['total_bonos'], 2); ?></td>
                                        <td class="text-money text-negative">-$<?php echo number_format($empleado['descuento'], 2); ?></td>
                                        <td class="text-money text-total">$<?php echo number_format($empleado['resultado_total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <!-- Paginación -->
                        <?php if ($totalPaginas > 1): ?>
                            <div class="pagination">
                                <?php if ($pagina > 1): ?>
                                    <a href="?fecha=<?php echo $fechaSeleccionada; ?>&busqueda=<?php echo urlencode($busqueda); ?>&pagina=1">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                    <a href="?fecha=<?php echo $fechaSeleccionada; ?>&busqueda=<?php echo urlencode($busqueda); ?>&pagina=<?php echo $pagina - 1; ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                                    <span class="disabled"><i class="fas fa-angle-left"></i></span>
                                <?php endif; ?>
                                
                                <?php 
                                // Mostrar hasta 5 páginas alrededor de la actual
                                $inicio = max(1, $pagina - 2);
                                $fin = min($totalPaginas, $pagina + 2);
                                
                                if ($inicio > 1) {
                                    echo '<a href="?fecha='.$fechaSeleccionada.'&busqueda='.urlencode($busqueda).'&pagina=1">1</a>';
                                    if ($inicio > 2) echo '<span>...</span>';
                                }
                                
                                for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <?php if ($i == $pagina): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?fecha=<?php echo $fechaSeleccionada; ?>&busqueda=<?php echo urlencode($busqueda); ?>&pagina=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor;
                                
                                if ($fin < $totalPaginas) {
                                    if ($fin < $totalPaginas - 1) echo '<span>...</span>';
                                    echo '<a href="?fecha='.$fechaSeleccionada.'&busqueda='.urlencode($busqueda).'&pagina='.$totalPaginas.'">'.$totalPaginas.'</a>';
                                }
                                ?>
                                
                                <?php if ($pagina < $totalPaginas): ?>
                                    <a href="?fecha=<?php echo $fechaSeleccionada; ?>&busqueda=<?php echo urlencode($busqueda); ?>&pagina=<?php echo $pagina + 1; ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    <a href="?fecha=<?php echo $fechaSeleccionada; ?>&busqueda=<?php echo urlencode($busqueda); ?>&pagina=<?php echo $totalPaginas; ?>">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="disabled"><i class="fas fa-angle-right"></i></span>
                                    <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="message">
                            <p>No se encontraron resultados para la búsqueda.</p>
                            <?php if(isset($_GET['fecha']) || isset($_GET['busqueda'])): ?>
                                <p class="small">Verifique que existan datos para la semana del <?php echo date('d/m/Y', strtotime($fechaInicioSemana)); ?> al <?php echo date('d/m/Y', strtotime($fechaFinSemana)); ?></p>
                                <?php if(!empty($busqueda)): ?>
                                    <p class="small">con el término de búsqueda: "<?php echo htmlspecialchars($busqueda); ?>"</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        // Función para confirmar y verificar el cálculo
        function confirmarCalculo(inicio, fin) {

            document.getElementById('modalDates').textContent = inicio + ' al ' + fin;
            document.getElementById('modalTitle').textContent = "Verificando...";
            document.getElementById('modalMessage').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando si ya existe cálculo para esta semana';
            document.getElementById('confirmModal').style.display = 'flex';
            document.getElementById('confirmButton').style.display = 'none';
            
            fetch(`calculo.php?verificar_calculo=1&fecha=${document.getElementById('fecha').value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.existe) {
                        document.getElementById('errorTitle').textContent = "Operación no disponible";
                        document.getElementById('errorMessage').innerHTML = `
                            <div style="text-align: center; margin-bottom: 20px;">
                                <img src="../src/config/components/assets/error_chicken_icon.png" 
                                    alt="Error" 
                                    style="height: 80px; width: auto; margin-bottom: 15px;">
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #e74c3c;">
                                    <h4 style="color: #e74c3c; margin: 0 0 10px 0;">Cálculo existente detectado</h4>
                                    <p style="margin: 0; font-size: 1.1rem; font-weight: 500;">
                                        ${data.periodo}
                                    </p>
                                </div>
                            </div>
                            <div style="text-align: center; font-size: 0.9rem; color: #666; line-height: 1.6;">
                                <p>El sistema ha detectado que ya existe un cálculo registrado para este período.</p>
                                <div style="background: #f5f5f5; padding: 12px; border-radius: 6px; margin-top: 15px;">
                                    <i class="fas fa-exclamation-circle" style="color: #e74c3c;"></i>
                                    Para modificar este cálculo, contacte al departamento de Sistemas
                                </div>
                            </div>
                        `;
                        document.getElementById('confirmButton').style.display = 'none';
                    } else {
                        document.getElementById('modalTitle').textContent = "Confirmación requerida";
                        document.getElementById('modalMessage').innerHTML = `
                            <div style="text-align: center; margin-bottom: 20px;">
                                <i class="fas fa-calculator" style="font-size: 2.5rem; color: #3498db; margin-bottom: 15px;"></i>
                                <h4 style="margin: 10px 0; color: #333;">Procesar nómina semanal</h4>
                                <p style="font-size: 1.1rem; font-weight: 500; color: #2c3e50;">
                                    ${data.periodo}
                                </p>
                            </div>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <p style="margin: 0; color: #555;">
                                    <i class="fas fa-info-circle" style="color: #3498db;"></i> 
                                    Esta operación generará los cálculos definitivos para la nómina.
                                </p>
                            </div>
                            <div style="font-size: 0.9rem; color: #666; text-align: center;">
                                <p>Tiempo estimado: 10 segundos o mas</p>
                            </div>
                        `;
                        document.getElementById('confirmButton').style.display = 'block';
                        document.getElementById('confirmButton').innerHTML = '<i class="fas fa-play-circle"></i> Iniciar cálculo';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalTitle').textContent = "Error";
                    document.getElementById('modalMessage').textContent = "Ocurrió un error al verificar. Por favor intente nuevamente.";
                });
        }

        // Función para ejecutar el cálculo
        function ejecutarCalculo() {

            document.getElementById('confirmModal').style.display = 'none';
            

            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.display = 'flex';
            

            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = '100%';
            

            let seconds = 10;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                if (seconds <= 0) clearInterval(countdownInterval);
            }, 1000);
            
            const messages = [
                "Preparando datos iniciales...",
                "Calculando salarios base...",
                "Procesando bonos y beneficios...",
                "Aplicando descuentos y deducciones...",
                "Verificando consistencia de datos...",
                "Generando resultados finales...",
                "Realizando últimos ajustes...",
                "¡Proceso casi completo!"
            ];
            
            let counter = 0;
            const loadingText = document.getElementById('loadingText');
            const messageInterval = setInterval(() => {
                loadingText.textContent = messages[counter % messages.length];
                counter++;
            }, 1500);

            setTimeout(() => {
                clearInterval(messageInterval);
                clearInterval(countdownInterval);
                
                // Crear formulario dinámico
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'calcular_nomina';
                input.value = '1';
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }, 10000); 
        }

        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            locale: "es",
            defaultDate: "<?php echo $fechaSeleccionada; ?>"
        });
        
        <?php if(!empty($busqueda)): ?>
            document.getElementById('busqueda').focus();
        <?php endif; ?>
        
        function generarPDF(fecha) {
            window.open('exportar_nomina.php?fecha=' + fecha, '_blank');
        }
    </script>
</body>
</html>