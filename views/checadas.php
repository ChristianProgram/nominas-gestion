<?php
include '../src/config/db.php';

// Obtener la fecha seleccionada o usar la fecha de hoy
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Inicializar las fechas de inicio y fin
$fechaInicio = '';
$fechaFin = '';

// Determinar si se selecciona un día o una semana
$modoFiltro = isset($_GET['modo_filtro']) ? $_GET['modo_filtro'] : 'semana'; // Cambiado a 'semana' por defecto

if ($modoFiltro === 'dia') {
    // Filtrar por un día específico
    $fechaInicio = $fechaSeleccionada;
    $fechaFin = $fechaSeleccionada;
} elseif ($modoFiltro === 'semana') {
    // Filtrar por una semana (lunes a domingo)
    $fechaInicio = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
    $fechaFin = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));
}

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

// Obtener faltas entre las fechas seleccionadas
$sqlFaltas = "SELECT Numero_Empleado, fecha FROM faltas WHERE fecha BETWEEN :fechaInicio AND :fechaFin";
$stmtFaltas = $pdo->prepare($sqlFaltas);
$stmtFaltas->bindParam(':fechaInicio', $fechaInicio);
$stmtFaltas->bindParam(':fechaFin', $fechaFin);
$stmtFaltas->execute();
$faltas = $stmtFaltas->fetchAll(PDO::FETCH_ASSOC);

// Convertir las faltas a un formato más manejable
$faltasPorEmpleado = [];
foreach ($faltas as $falta) {
    $faltasPorEmpleado[$falta['Numero_Empleado']][] = $falta['fecha'];
}

// Guardado automático (simulación al final del día)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechaSeleccionada = $_POST['fecha'];

    try {
        $pdo->beginTransaction();

        // Obtener todos los empleados (sin paginación) para asegurar que se guarden todos
        $sqlTodosEmpleados = "SELECT Numero_Empleado FROM empleados WHERE Nombre LIKE :search OR Numero_Empleado LIKE :search";
        $stmtTodosEmpleados = $pdo->prepare($sqlTodosEmpleados);
        $stmtTodosEmpleados->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
        $stmtTodosEmpleados->execute();
        $todosEmpleados = $stmtTodosEmpleados->fetchAll(PDO::FETCH_ASSOC);

        // Insertar nuevas faltas
        $sqlInsert = "INSERT INTO faltas (Numero_Empleado, fecha) VALUES (:Numero_Empleado, :fecha)
                      ON DUPLICATE KEY UPDATE fecha = :fecha"; // Evitar duplicados
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($todosEmpleados as $empleado) {
            $numeroEmpleado = $empleado['Numero_Empleado'];

            // Verificar si el empleado tiene falta marcada para cada día
            foreach ($_POST['faltas'][$numeroEmpleado] as $dia => $falta) {
                if ($falta == 1) {
                    $stmtInsert->execute([
                        ':Numero_Empleado' => $numeroEmpleado,
                        ':fecha' => $dia
                    ]);
                }
            }
        }

        $pdo->commit();
        echo "<script>
                alert('Faltas guardadas correctamente.');
                window.location.href = 'checadas.php?fecha=$fechaSeleccionada&modo_filtro=$modoFiltro&search=" . urlencode($searchTerm) . "';
              </script>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('Error al guardar las faltas: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checadas</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .asistencias-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .asistencias-table th, .asistencias-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .asistencias-table th {
            background-color: #f2f2f2;
        }
        .present {
            background-color: green;
            color: white;
        }
        .absent {
            background-color: red;
            color: white;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a, .pagination span {
            margin: 0 5px;
            text-decoration: none;
            color: #333;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .pagination a.active {
            font-weight: bold;
            color: #000;
            background-color: #f2f2f2;
        }
        .pagination a:hover:not(.disabled) {
            background-color: #ddd;
        }
        .pagination .disabled {
            color: #aaa;
            cursor: not-allowed;
            background-color: #f9f9f9;
        }
        .pagination-button {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s ease;
        }
        .pagination-button:hover:not(.disabled) {
            background-color: #ddd;
        }
        .pagination-button.disabled {
            color: #aaa;
            cursor: not-allowed;
            background-color: #f9f9f9;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form label {
            margin-right: 10px;
        }
        .filter-form select, .filter-form input {
            padding: 5px;
            margin-right: 10px;
        }
        .falta-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .falta-btn.marcada {
            background-color: red;
            color: white;
        }

        .falta-btn.no-marcada {
            background-color: #f2f2f2;
            color: #333;
        }

        /* Estilos para el fondo de las celdas */
        .celda-asistencia {
            padding: 8px;
            text-align: center;
        }

        .celda-asistencia.asistio {
            background-color: green;
            color: white;
        }

        .celda-asistencia.falto {
            background-color: red;
            color: white;
        }

        /* Sidebar */
        :root {
            --primary-color: #00263F;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
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
        }
        
        /* Tabla de asistencia */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .attendance-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 500;
            position: sticky;
            top: 0;
        }
        
        .attendance-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
            vertical-align: middle;
        }
        
        .attendance-table tr:nth-child(even) {
            background-color: var(--light-bg);
        }
        
        .attendance-table tr:hover {
            background-color: #f1f5f9;
        }
        
        /* Estilos para celdas de asistencia */
        .attendance-cell {
            padding: 0.5rem;
            text-align: center;
            border-radius: 4px;
        }
        
        .present {
            background-color: var(--success-color);
            color: white;
        }
        
        .absent {
            background-color: var(--danger-color);
            color: white;
        }
        
        /* Botones de asistencia */
        .attendance-btn {
            padding: 0.5rem 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .attendance-btn.present {
            background-color: var(--success-color);
            color: white;
        }
        
        .attendance-btn.absent {
            background-color: var(--danger-color);
            color: white;
        }
        
        .attendance-btn:hover {
            opacity: 0.9;
        }
        
        /* Botón de guardar */
        .save-button {
            background-color: var(--success-color);
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
            margin-top: 1.5rem;
        }
        
        .save-button:hover {
            background-color: #0f9e6e;
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
        }

    </style>
</head>
<body>
    <div class="container">
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
                    <li><a href="../views/checadas.php" class="active"><i class="fas fa-calendar-alt"></i> Asistencia</a></li>
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
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Checadas</h1>
                </div>

                <!-- Filtro por Día o Semana -->
                <form method="GET" action="checadas.php" class="filter-form">
                    <label for="modo_filtro">Filtrar por:</label>
                    <select id="modo_filtro" name="modo_filtro" onchange="this.form.submit()">
                        <option value="dia" <?php echo ($modoFiltro === 'dia') ? 'selected' : ''; ?>>Día</option>
                        <option value="semana" <?php echo ($modoFiltro === 'semana') ? 'selected' : ''; ?>>Semana</option>
                    </select>

                    <?php if ($modoFiltro === 'dia'): ?>
                        <label for="fecha">Selecciona un día:</label>
                        <input type="text" id="fecha" name="fecha" value="<?php echo $fechaSeleccionada; ?>" onchange="this.form.submit()">
                    <?php elseif ($modoFiltro === 'semana'): ?>
                        <label for="fecha">Selecciona una semana:</label>
                        <input type="text" id="fecha" name="fecha" value="<?php echo $fechaSeleccionada; ?>" onchange="this.form.submit()">
                    <?php endif; ?>

                    <!-- Campo oculto para conservar el término de búsqueda -->
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                </form>

                <!-- Formulario de búsqueda -->
                <form method="GET" action="checadas.php">
                    <input type="text" name="search" placeholder="Buscar por nombre o número" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <!-- Campos ocultos para conservar la fecha y el modo de filtrado -->
                    <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                    <input type="hidden" name="modo_filtro" value="<?php echo $modoFiltro; ?>">
                    <button type="submit">Buscar</button>
                </form>

                <!-- Tabla de Asistencias -->
                <form method="POST" action="checadas.php">
                    <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                    <input type="hidden" name="modo_filtro" value="<?php echo $modoFiltro; ?>">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit" style="margin-top: 20px;">Guardar Faltas</button>
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th>N° Empleado</th>
                                <th>Nombre</th>
                                <?php
                                // Mostrar los días en el rango
                                $dias = [];
                                $inicio = new DateTime($fechaInicio);
                                $fin = new DateTime($fechaFin);
                                $interval = DateInterval::createFromDateString('1 day');
                                $periodo = new DatePeriod($inicio, $interval, $fin->modify('+1 day'));

                                // Días en español abreviados
                                $diasSemana = [
                                    'Monday' => 'Lun', 
                                    'Tuesday' => 'Mar', 
                                    'Wednesday' => 'Mié', 
                                    'Thursday' => 'Jue', 
                                    'Friday' => 'Vie', 
                                    'Saturday' => 'Sáb', 
                                    'Sunday' => 'Dom'
                                ];

                                foreach ($periodo as $dia) {
                                    $diaNombre = $dia->format('l');
                                    $diaNombreEspañol = $diasSemana[$diaNombre];
                                    $fechaFormateada = $dia->format('d/m');
                                    echo "<th title='$diaNombreEspañol'>$diaNombreEspañol<br><small>$fechaFormateada</small></th>";
                                    $dias[] = $dia->format('Y-m-d');
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($empleados)): ?>
                                <?php foreach ($empleados as $empleado): ?>
                                    <?php
                                    $numeroEmpleado = $empleado['Numero_Empleado'];
                                    ?>
                                    <tr>
                                        <td><?php echo $empleado['Numero_Empleado']; ?></td>
                                        <td style="text-align: left;"><?php echo $empleado['Nombre']; ?></td>
                                        
                                        <?php foreach ($dias as $dia): ?>
                                            <?php
                                            $tieneFalta = in_array($dia, $faltasPorEmpleado[$numeroEmpleado] ?? []);
                                            $claseCelda = $tieneFalta ? 'absent' : 'present';
                                            $textoBoton = $tieneFalta ? 'Falta' : 'Presente';
                                            ?>
                                            <td class="attendance-cell <?php echo $claseCelda; ?>">
                                                <input type="hidden" name="faltas[<?php echo $numeroEmpleado; ?>][<?php echo $dia; ?>]" value="<?php echo $tieneFalta ? '1' : '0'; ?>">
                                                <button type="button" class="attendance-btn <?php echo $claseCelda; ?>" onclick="toggleAttendance(this)">
                                                    <?php echo $textoBoton; ?>
                                                </button>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo count($dias) + 2; ?>" style="text-align: center; padding: 2rem;">
                                        <div style="color: var(--text-light);">
                                            <i class="fas fa-user-slash" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                            <p>No se encontraron empleados</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>

                <!-- Paginación -->
                <div class="pagination">
                    <!-- Botón "Anterior" -->
                    <?php if ($paginaActual > 1): ?>
                        <a href="checadas.php?pagina=<?php echo $paginaActual - 1; ?>&modo_filtro=<?php echo $modoFiltro; ?>&fecha=<?php echo $fechaSeleccionada; ?>&search=<?php echo urlencode($searchTerm); ?>" class="pagination-button">Anterior</a>
                    <?php else: ?>
                        <span class="pagination-button disabled">Anterior</span>
                    <?php endif; ?>

                    <!-- Números de página -->
                    <?php
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        $clase = ($i == $paginaActual) ? 'active' : '';
                        echo "<a href='checadas.php?pagina=$i&modo_filtro=$modoFiltro&fecha=$fechaSeleccionada&search=" . urlencode($searchTerm) . "' class='$clase'>$i</a>";
                    }
                    ?>

                    <!-- Botón "Siguiente" -->
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="checadas.php?pagina=<?php echo $paginaActual + 1; ?>&modo_filtro=<?php echo $modoFiltro; ?>&fecha=<?php echo $fechaSeleccionada; ?>&search=<?php echo urlencode($searchTerm); ?>" class="pagination-button">Siguiente</a>
                    <?php else: ?>
                        <span class="pagination-button disabled">Siguiente</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Flatpickr para el calendario
        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            locale: "es"
        });
        
        // Función para cambiar el estado de asistencia
        function toggleAttendance(btn) {
            const cell = btn.parentElement;
            const hiddenInput = cell.querySelector('input[type="hidden"]');
            
            if (btn.classList.contains('present')) {
                // Cambiar a falta
                btn.classList.remove('present');
                btn.classList.add('absent');
                btn.textContent = 'Falta';
                hiddenInput.value = '1';
                cell.classList.remove('present');
                cell.classList.add('absent');
            } else {
                // Cambiar a presente
                btn.classList.remove('absent');
                btn.classList.add('present');
                btn.textContent = 'Presente';
                hiddenInput.value = '0';
                cell.classList.remove('absent');
                cell.classList.add('present');
            }
        }
    </script>
</body>
</html>