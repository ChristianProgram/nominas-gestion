<?php
// reportes.php
include '../src/config/db.php';

if (!$pdo) {
    die("Error de conexión a la base de datos.");
}

$resultados = [];
$departamentos = [];
$fecha_seleccionada = '';

// Obtener lista de departamentos para el filtro
try {
    $stmt = $pdo->query("SELECT DISTINCT departamento FROM reportes_faltas_semanales ORDER BY departamento");
    $departamentos = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error al obtener departamentos: " . $e->getMessage());
}

if (isset($_POST['buscar_faltas'])) {
    $fecha_seleccionada = $_POST['fecha-reporte'] ?? '';
    $departamento_filtro = $_POST['departamento-filtro'] ?? '';
    
    if (empty($fecha_seleccionada)) {
        die("Por favor, selecciona una fecha válida.");
    }

    try {
        // Llamar al procedimiento almacenado con parámetro de departamento
        $sql = "CALL FiltroReporteSemanal(:fecha)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':fecha', $fecha_seleccionada, PDO::PARAM_STR);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar por departamento si se seleccionó uno
        if (!empty($departamento_filtro)) {
            $resultados = array_filter($resultados, function($item) use ($departamento_filtro) {
                return $item['Departamento'] === $departamento_filtro;
            });
        }
        
        $stmt->closeCursor();
    } catch (PDOException $e) {
        die("Error al ejecutar la consulta: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- FontAwesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: #ffffff;
            padding: 1rem;
            flex-shrink: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .sidebar h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
        }
        
        .sidebar-section {
            margin-bottom: 1.5rem;
        }
        
        .sidebar-section h3 {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            padding: 0.5rem 1rem;
            background-color: rgba(0, 38, 63, 0.5);
            border-radius: 4px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar ul li {
            margin: 0.25rem 0;
        }
        
        .sidebar ul li a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.75rem 1rem;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        .sidebar ul li a.active {
            background: rgba(5, 56, 90, 0.8);
            color: white;
            font-weight: 500;
        }
        
        .sidebar ul li a i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        /* Contenido principal */
        .content {
            flex: 1;
            padding: 2rem;
            background-color: #ffffff;
        }

        /* Tarjetas del dashboard */
        .dashboard {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .card {
            flex: 1;
            padding: 1.5rem;
            border-radius: 8px;
            color: #fff;
            text-align: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card.gray { background: #64748b; }
        .card.green { background: #16a34a; }
        .card.orange { background: #f97316; }
        .card.red { background: #dc2626; }

        /* Sección de Reportes */
        .reportes-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .reporte-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .reporte-card:hover {
            transform: translateY(-5px);
        }
        .reporte-card h3 {
            margin-top: 0;
            font-size: 1.25rem;
            color: #1e293b;
        }
        .reporte-card p {
            color: #64748b;
        }
        .reporte-card a {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        .reporte-card a:hover {
            background-color: #15803d;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .filter-section {
            margin-bottom: 20px;
        }
        .filter-section select,
        .filter-section input,
        .filter-section button {
            padding: 10px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background: #3498db;
            color: white;
        }
        .content {
            flex: 1;
            padding: 2rem;
            background-color: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin: 1rem;
        }
        
        .filter-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        select, input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        button:hover {
            background-color: var(--secondary-color);
        }
        
        .results-info {
            background-color: #e8f4fd;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }
        
        /* Estilos para la tabla */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .data-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .data-table tr:nth-child(even) {
            background-color: var(--light-gray);
        }
        
        .data-table tr:hover {
            background-color: #e3f2fd;
        }
        
        .badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
        }
        
        .badge-danger {
            color: white;
            background-color: var(--accent-color);
        }
        
        .no-results {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
        }
        
        .export-buttons {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
        }
        
        /* Estilo base del botón */
        .btn-export {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: #2ecc71; /* Verde para acciones de exportar */
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-right: 10px;
        }

        /* Estilo hover */
        .btn-export:hover {
            background-color: #27ae60; /* Verde más oscuro */
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        /* Estilo active (al hacer clic) */
        .btn-export:active {
            transform: translateY(0);
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }

        /* Icono dentro del botón */
        .btn-export i {
            margin-right: 8px;
            font-size: 16px;
        }

        /* Versión alternativa para imprimir */
        .btn-print {
            background-color: #3498db; /* Azul para imprimir */
        }

        .btn-print:hover {
            background-color: #2980b9;
        }

        /* Versión para PDF si la agregas después */
        .btn-pdf {
            background-color: #e74c3c; /* Rojo para PDF */
        }

        .btn-pdf:hover {
            background-color: #c0392b;
        }

        /* Efecto para cuando está deshabilitado */
        .btn-export:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Responsive para pantallas pequeñas */
        @media (max-width: 768px) {
            .btn-export {
                padding: 8px 15px;
                font-size: 13px;
            }
            
            .btn-export i {
                margin-right: 5px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Nominas</h2>
            </div>

            <!-- Sección: Informes -->
            <div class="sidebar-section">
                <h3>Informes</h3>
                <ul>
                    <li><a href="../public/index.php" class="active"><i class="fas fa-chart-bar"></i> Resumen</a></li>
                </ul>
            </div>

            <!-- Sección: Gestionar -->
            <div class="sidebar-section">
                <h3>Gestionar</h3>
                <ul>
                    <li><a href="../views/checadas.php"><i class="fas fa-calendar-alt"></i> Asistencia</a></li>
                    <li><a href="../views/empleados.php"><i class="fas fa-users"></i> Empleados</a></li>
                    <li><a href="../views/calculo.php"><i class="fas fa-calculator"></i> Deducciones</a></li>
                    <li><a href="../views/bonos.php"><i class="fas fa-gift"></i> Bonos</a></li>
                    <li><a href="../views/roles.php"><i class="fas fa-briefcase"></i> Cargos</a></li>
                    <li><a href="../views/importar.php"><i class="fas fa-file-import"></i> Importar datos</a></li>
                </ul>
            </div>

            <!-- Sección: Imprimibles -->
            <div class="sidebar-section">
                <h3>Imprimibles</h3>
                <ul>
                    <li><a href="../views/reportes.php"><i class="fas fa-file-alt"></i> Reportes PDF</a></li>
                </ul>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <h1><i class="fas fa-file-alt"></i> Reportes de Faltas Semanales</h1>
            
            <div class="filter-section">
                <form method="POST" class="filter-form">
                    <div class="form-group">
                        <label for="fecha-reporte"><i class="fas fa-calendar-alt"></i> Selecciona una fecha</label>
                        <input type="date" id="fecha-reporte" name="fecha-reporte" required 
                               value="<?php echo htmlspecialchars($fecha_seleccionada); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="departamento-filtro"><i class="fas fa-building"></i> Filtrar por departamento</label>
                        <select id="departamento-filtro" name="departamento-filtro">
                            <option value="">Todos los departamentos</option>
                            <?php foreach ($departamentos as $depto): ?>
                                <option value="<?php echo htmlspecialchars($depto); ?>" 
                                    <?php echo (isset($_POST['departamento-filtro']) && $_POST['departamento-filtro'] === $depto) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($depto); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="buscar_faltas">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </form>
            </div>
            
            <?php if (isset($_POST['buscar_faltas'])): ?>
                <div class="results-info">
                    <h3><i class="fas fa-info-circle"></i> Resultados para la semana del 
                        <?php 
                            $fecha_inicio = new DateTime($fecha_seleccionada);
                            $fecha_inicio->modify('monday this week');
                            echo $fecha_inicio->format('d/m/Y'); 
                        ?> al 
                        <?php 
                            $fecha_fin = clone $fecha_inicio;
                            $fecha_fin->modify('sunday this week');
                            echo $fecha_fin->format('d/m/Y'); 
                        ?>
                    </h3>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar-day"></i> Fecha Inicio</th>
                                <th><i class="fas fa-calendar-day"></i> Fecha Fin</th>
                                <th><i class="fas fa-building"></i> Departamento</th>
                                <th><i class="fas fa-user-times"></i> Total Faltas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($resultados)): ?>
                                <?php foreach ($resultados as $fila): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($fila['Fecha Inicio'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($fila['Fecha Fin'])); ?></td>
                                        <td><?php echo htmlspecialchars($fila['Departamento']); ?></td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <?php echo htmlspecialchars($fila['Total Faltas']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="no-results">
                                        <i class="fas fa-exclamation-circle"></i> No se encontraron reportes para los filtros seleccionados
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="export-buttons">
                    <a href="falta_semanal.php?fecha=<?php echo urlencode($fecha_seleccionada); ?>&departamento=<?php echo urlencode($departamento_seleccionado); ?>" 
                    class="btn-export">
                        <i class="fas fa-file-pdf"></i> Exportar a PDF
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>