<?php
// reportes.php
include '../src/config/db.php'; // Asegúrate de que este archivo esté correctamente configurado

// Verifica si la conexión a la base de datos está establecida
if (!$pdo) {
    die("Error de conexión a la base de datos.");
}

if (isset($_POST['buscar_faltas'])) {
    // Obtener la fecha del formulario y validarla
    $fecha = $_POST['fecha-reporte'] ?? '';
    
    // Validar que la fecha no esté vacía
    if (empty($fecha)) {
        die("Por favor, selecciona una fecha válida.");
    }

    try {
        // Preparar la consulta SQL
        $sql = "CALL ObtenerReportesFaltasSemanales2(:fecha)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
        
        // Obtener los resultados
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        }
        .sidebar-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-section {
            margin-bottom: 1.5rem;
        }
        .sidebar-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            background-color: #00263F;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 0.5rem 0;
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
        }
        .sidebar ul li a:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .sidebar ul li a.active {
            background:rgb(5, 56, 90);
            color: white;
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
                    <li><a href="#" class="active"><i class="fas fa-chart-bar"></i> Resumen</a></li>
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
                    <li><a href="../views/reportes.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                </ul>
            </div>

            <!-- Sección: Imprimibles -->
            <div class="sidebar-section">
                <h3>Imprimibles</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-print"></i> Reportes PDF</a></li>
                    <li><a href="#"><i class="fas fa-file-excel"></i> Exportar Excel</a></li>
                </ul>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="content">
            <h1>Reportes</h1>
            <div class="filter-section">
                <label for="tipo-reporte">Tipo de Reporte:</label>
                <select id="tipo-reporte">
                    <option value="todos">Todos</option>
                    <option value="asistencia">Asistencia</option>
                    <option value="nomina">Nómina</option>
                    <option value="empleados">Empleados</option>
                    <option value="faltas">Faltas</option>
                    <option value="bonos">Bonos</option>
                </select>
                
                <label for="fecha-reporte">Fecha:</label>
                <input type="date" id="fecha-reporte" name="fecha-reporte" required>
                
                <form method="POST">
                    <button type="submit" name="buscar_faltas">Buscar Reportes de Faltas</button>
                </form>
            </div>
            
            <table id="tabla-reportes">
                <thead>
                    <tr>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Departamento</th>
                        <th>Total Faltas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resultados)): ?>
                        <?php foreach ($resultados as $fila): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['Fecha Inicio']); ?></td>
                                <td><?php echo htmlspecialchars($fila['Fecha Fin']); ?></td>
                                <td><?php echo htmlspecialchars($fila['Departamento']); ?></td>
                                <td><?php echo htmlspecialchars($fila['Total Faltas']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No hay resultados disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>