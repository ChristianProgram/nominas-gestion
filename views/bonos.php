<?php
include '../src/config/db.php';

// Obtener la fecha seleccionada o usar la fecha de hoy
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Calcular el inicio y fin de la semana
$fechaInicio = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
$fechaFin = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));

// Generar un idFecha único para la semana (puede ser el timestamp de la fecha de inicio)
$idFecha = strtotime($fechaInicio);

// Definir el término de búsqueda
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Paginación
$empleadosPorPagina = 10; // Número de empleados por página
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $empleadosPorPagina;

// Obtener lista de empleados paginada
$sqlEmpleados = "SELECT Numero_Empleado, Nombre FROM empleados WHERE Nombre LIKE :search OR Numero_Empleado LIKE :search ORDER BY Nombre ASC LIMIT :limit OFFSET :offset";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->bindValue(':limit', $empleadosPorPagina, PDO::PARAM_INT);
$stmtEmpleados->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtEmpleados->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total de empleados para la paginación (con búsqueda)
$sqlTotalEmpleados = "SELECT COUNT(*) as total FROM empleados WHERE Nombre LIKE :search OR Numero_Empleado LIKE :search";
$stmtTotalEmpleados = $pdo->prepare($sqlTotalEmpleados);
$stmtTotalEmpleados->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmtTotalEmpleados->execute();
$totalEmpleados = $stmtTotalEmpleados->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalEmpleados / $empleadosPorPagina);

// Obtener bonos entre las fechas seleccionadas
$sqlBonos = "SELECT numero_empleado, cantidad, razon, fecha, idFecha FROM bonos WHERE idFecha = :idFecha";
$stmtBonos = $pdo->prepare($sqlBonos);
$stmtBonos->bindParam(':idFecha', $idFecha, PDO::PARAM_INT);
$stmtBonos->execute();
$bonos = $stmtBonos->fetchAll(PDO::FETCH_ASSOC);

// Convertir los bonos a un formato más manejable
$bonosPorEmpleado = [];
foreach ($bonos as $bono) {
    $bonosPorEmpleado[$bono['numero_empleado']][] = $bono;
}

// Guardado automático (simulación al final de la semana)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechaSeleccionada = $_POST['fecha'];

    try {
        $pdo->beginTransaction();

        // Insertar nuevos bonos solo para empleados con datos asignados
        foreach ($_POST['bonos'] as $numeroEmpleado => $bono) {
            // Verificar si el empleado tiene un bono asignado
            if (!empty($bono['cantidad']) && !empty($bono['razon'])) {
                // Llamar al procedimiento almacenado
                $sql = "CALL InsertarOActualizarBono(:numero_empleado, :cantidad, :razon, :fecha, :idFecha)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':numero_empleado' => $numeroEmpleado,
                    ':cantidad' => $bono['cantidad'],
                    ':razon' => $bono['razon'],
                    ':fecha' => $fechaSeleccionada,
                    ':idFecha' => $idFecha
                ]);
            }
        }

        $pdo->commit();
        echo "<script>
                alert('Bonos guardados correctamente.');
                window.location.href = 'bonos.php?fecha=$fechaSeleccionada&search=" . urlencode($searchTerm) . "';
              </script>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('Error al guardar los bonos: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonos | Sistema de Nómina</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary-color: #00263F;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-color: #334155;
            --text-light: #64748b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
            background-color: var(--light-bg);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
            background-color: var(--light-bg);
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
        .main-content {
            flex: 1;
            padding: 2rem;
            background-color: #fff;
            overflow-x: auto;
        }
        
        .section-header {
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* Filtros */
        .filter-container {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .filter-select, .filter-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .filter-select:focus, .filter-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .search-input {
            flex: 1;
            min-width: 300px;
        }
        
        .filter-button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-button:hover {
            background-color: #2563eb;
        }
        
        /* Tabla de bonos */
        .bonos-table-container {
            overflow-x: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .bonos-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .bonos-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
            position: sticky;
            top: 0;
        }
        
        .bonos-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .bonos-table tr:nth-child(even) {
            background-color: var(--light-bg);
        }
        
        .bonos-table tr:hover {
            background-color: #f1f5f9;
        }
        
        /* Estilos para inputs */
        .input-cantidad {
            width: 100px;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-align: right;
        }
        
        .input-razon {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        /* Botones */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #0f9e6e;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 1.5rem;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }
        
        .close {
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .close:hover {
            color: var(--text-color);
        }
        
        /* Navegación semanal */
        .week-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            background-color: var(--light-bg);
            border-radius: 6px;
        }
        
        .week-title {
            font-weight: 500;
            color: var(--primary-color);
        }
        
        /* Paginación */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
        }
        
        .page-item {
            list-style: none;
        }
        
        .page-link {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .page-link:hover {
            background-color: var(--light-bg);
        }
        
        .page-item.active .page-link {
            background-color: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }
        
        .page-item.disabled .page-link {
            color: var(--text-light);
            pointer-events: none;
            background-color: #f8fafc;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 1rem;
            }
            
            .main-content {
                padding: 1.5rem;
            }
            
            .filter-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .search-input {
                min-width: 100%;
            }
            
            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
        <div class="sidebar-header">
            <h2>Nóminas</h2>
        </div>

        <!-- Sección: Informes -->
        <div class="sidebar-section">
            <h3>Informes</h3>
            <ul>
                <li><a href="../public/index.php"><i class="fas fa-chart-bar"></i> Resumen</a></li>
            </ul>
        </div>

        <!-- Sección: Gestionar -->
        <div class="sidebar-section">
            <h3>Gestionar</h3>
            <ul>
                <li><a href="../views/checadas.php"><i class="fas fa-calendar-alt"></i> Asistencia</a></li>
                <li><a href="../views/empleados.php" class="active"><i class="fas fa-users"></i> Empleados</a></li>
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
    
    <div class="main-content">
        <div class="section-header">
            <h1 class="section-title"><i class="fas fa-gift"></i> Bonos Semanales</h1>
        </div>

        <!-- Filtros -->
        <form method="GET" action="bonos.php" class="filter-container">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="fecha" class="filter-label">Semana:</label>
                    <input type="text" id="fecha" name="fecha" class="filter-input" value="<?php echo $fechaSeleccionada; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="search" class="filter-label">Buscar empleado:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="search" name="search" class="filter-input search-input" placeholder="Nombre o número" value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                        <button type="submit" class="filter-button">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabla de Bonos -->
        <form method="POST" action="bonos.php">
            <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
            
            <div class="bonos-table-container">
                <table class="bonos-table">
                    <thead>
                        <tr>
                            <th>N° Empleado</th>
                            <th>Nombre</th>
                            <th>Cantidad</th>
                            <th>Razón</th>
                            <th>Historial</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($empleados)): ?>
                            <?php foreach ($empleados as $empleado): ?>
                                <?php
                                $numeroEmpleado = $empleado['Numero_Empleado'];
                                $bonoEmpleado = $bonosPorEmpleado[$numeroEmpleado][0] ?? null;
                                ?>
                                <tr>
                                    <td><?php echo $empleado['Numero_Empleado']; ?></td>
                                    <td><?php echo $empleado['Nombre']; ?></td>
                                    <td>
                                        <input type="number" 
                                               name="bonos[<?php echo $numeroEmpleado; ?>][cantidad]" 
                                               class="input-cantidad" 
                                               value="<?php echo $bonoEmpleado ? $bonoEmpleado['cantidad'] : '0'; ?>" 
                                               step="0.01"
                                               min="0">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="bonos[<?php echo $numeroEmpleado; ?>][razon]" 
                                               class="input-razon" 
                                               value="<?php echo $bonoEmpleado ? htmlspecialchars($bonoEmpleado['razon']) : ''; ?>"
                                               placeholder="Motivo del bono">
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-warning ver-bonos-btn" 
                                                data-numero-empleado="<?php echo $numeroEmpleado; ?>">
                                            <i class="fas fa-history"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem;">
                                    <div style="color: var(--text-light);">
                                        <i class="fas fa-user-slash" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                        <p>No se encontraron empleados</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </form>

        <!-- Modal para mostrar bonos semanales -->
        <div id="modalBonos" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Historial de Bonos</h2>
                    <span class="close">&times;</span>
                </div>
                
                <div class="week-navigation">
                    <button id="semanaAnterior" class="btn btn-primary">
                        <i class="fas fa-chevron-left"></i> Semana Anterior
                    </button>
                    <span id="rangoSemanal" class="week-title"></span>
                    <button id="semanaSiguiente" class="btn btn-primary">
                        Semana Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div id="lista-bonos"></div>
            </div>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
            <div class="pagination-container">
                <ul class="pagination">
                    <!-- Botón Anterior -->
                    <li class="page-item <?php echo ($paginaActual == 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" 
                           href="bonos.php?pagina=<?php echo $paginaActual - 1; ?>&fecha=<?php echo $fechaSeleccionada; ?>&search=<?php echo urlencode($searchTerm); ?>">
                            &laquo;
                        </a>
                    </li>
                    
                    <!-- Números de página -->
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?php echo ($i == $paginaActual) ? 'active' : ''; ?>">
                            <a class="page-link" 
                               href="bonos.php?pagina=<?php echo $i; ?>&fecha=<?php echo $fechaSeleccionada; ?>&search=<?php echo urlencode($searchTerm); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Botón Siguiente -->
                    <li class="page-item <?php echo ($paginaActual == $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link" 
                           href="bonos.php?pagina=<?php echo $paginaActual + 1; ?>&fecha=<?php echo $fechaSeleccionada; ?>&search=<?php echo urlencode($searchTerm); ?>">
                            &raquo;
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        let fechaActual = new Date();
        let numeroEmpleadoActual = null;

        // Flatpickr para el calendario
        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            locale: "es",
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    instance.input.form.submit();
                }
            }
        });

        // Función para formatear la fecha
        function formatearFecha(date) {
            return date.toISOString().split('T')[0];
        }

        // Función para obtener el rango semanal
        function obtenerRangoSemanal(date) {
            const inicioSemana = new Date(date);
            inicioSemana.setDate(date.getDate() - date.getDay() + (date.getDay() === 0 ? -6 : 1));
            const finSemana = new Date(inicioSemana);
            finSemana.setDate(inicioSemana.getDate() + 6);
            
            return {
                inicio: formatearFecha(inicioSemana),
                fin: formatearFecha(finSemana)
            };
        }

        // Función para cargar los bonos
        function cargarBonosSemana() {
            const rango = obtenerRangoSemanal(fechaActual);
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            
            document.getElementById('rangoSemanal').textContent = 
                `${new Date(rango.inicio).toLocaleDateString('es-ES', options)} - ${new Date(rango.fin).toLocaleDateString('es-ES', options)}`;

            fetch(`obtener_bonos.php?numero_empleado=${numeroEmpleadoActual}&fecha_inicio=${rango.inicio}&fecha_fin=${rango.fin}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('lista-bonos').innerHTML = data;
                });
        }

        // Abrir modal y cargar bonos
        document.querySelectorAll('.ver-bonos-btn').forEach(button => {
            button.addEventListener('click', function() {
                numeroEmpleadoActual = this.getAttribute('data-numero-empleado');
                fechaActual = new Date();
                cargarBonosSemana();
                document.getElementById('modalBonos').style.display = 'block';
            });
        });

        // Navegación semanal
        document.getElementById('semanaAnterior').addEventListener('click', function() {
            fechaActual.setDate(fechaActual.getDate() - 7);
            cargarBonosSemana();
        });

        document.getElementById('semanaSiguiente').addEventListener('click', function() {
            fechaActual.setDate(fechaActual.getDate() + 7);
            cargarBonosSemana();
        });

        // Cerrar modal
        document.querySelector('.close').addEventListener('click', function() {
            document.getElementById('modalBonos').style.display = 'none';
        });

        // Cerrar al hacer clic fuera
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('modalBonos')) {
                document.getElementById('modalBonos').style.display = 'none';
            }
        });
    </script>
</body>
</html>