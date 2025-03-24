<?php
include '../src/config/db.php';

try {
    // Obtener la fecha seleccionada o usar la fecha de hoy
    $fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

    // Calcular el inicio y fin de la semana (lunes a domingo)
    $fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
    $fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));

    // Consulta SQL para obtener los resultados de la tabla resultados_nomina2
    $sql = "
        SELECT 
            id_empleado, nombre, numero_empleado, departamento, sueldo_semanal, total_bonos, 
            faltas, dias_faltas, descuento, resultado_total, fecha_inicio, fecha_fin
        FROM resultados_nomina2
        WHERE (fecha_inicio BETWEEN :fechaInicio AND :fechaFin)
        OR (fecha_fin BETWEEN :fechaInicio AND :fechaFin)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fechaInicio', $fechaInicioSemana, PDO::PARAM_STR);
    $stmt->bindParam(':fechaFin', $fechaFinSemana, PDO::PARAM_STR);

    $stmt->execute();

    // Obtener los resultados
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cerrar el cursor para futuras ejecuciones del mismo procedimiento
    $stmt->closeCursor();
    
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cálculo de Nómina</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
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
        /* Estilos para la tabla */
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
        /* Estilos para el botón de cálculo */
        .boton-calcular {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .boton-calcular:hover {
            background-color: #45a049;
        }
        /* Estilos para el mensaje de carga */
        .cargando {
            display: none;
            font-style: italic;
            color: #999;
        }
        /* Estilos para el mensaje de error */
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
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
        <div class="main-content">
            <div class="content-container">
                <h1>Cálculo de Nómina</h1>

                <!-- Formulario con botón -->
                <form method="GET" action="calculo.php">
                    <label for="fecha">Selecciona una semana:</label>
                    <input type="date" id="fecha" name="fecha" required>
                    <button type="submit">Mostrar Nómina</button>
                </form>

                <!-- Solo mostrar la tabla si hay datos -->
                <?php if (!empty($empleados)): ?>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Número de Empleado</th>
                                <th>Faltas</th>
                                <th>Días con Faltas</th>
                                <th>Total de Bonos</th>
                                <th>Descuento</th>
                                <th>Total Nómina</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleados as $empleado): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['numero_empleado']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['faltas']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['dias_faltas']); ?></td>
                                    <td>$<?php echo number_format($empleado['total_bonos'], 2); ?></td>
                                    <td>-$<?php echo number_format($empleado['descuento'], 2); ?></td>
                                    <td>$<?php echo number_format($empleado['resultado_total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No se encontraron resultados para la semana seleccionada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Flatpickr para el calendario
        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            locale: "es",
            mode: "range", // Permite seleccionar un rango de fechas
            defaultDate: "<?php echo $fechaSeleccionada; ?>"
        });

        function calcularNomina() {
            // Mostrar mensaje de carga
            document.getElementById("cargando").style.display = "block";
            document.getElementById("error-message").style.display = "none"; // Ocultar mensaje de error

            fetch('../src/config/procesar_calculo.php')
                .then(response => {
                    // Verificar si la respuesta es correcta
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    // Mostrar los resultados en el contenedor
                    document.getElementById("resultados").innerHTML = data;
                    document.getElementById("cargando").style.display = "none";

                    // Animación para la tabla
                    const tabla = document.querySelector('table');
                    if (tabla) {
                        setTimeout(() => {
                            tabla.classList.add('visible');
                        }, 100);
                    }
                })
                .catch(error => {
                    // Mostrar mensaje de error si la solicitud falla
                    console.error("Error al procesar el cálculo:", error);
                    document.getElementById("cargando").style.display = "none";
                    document.getElementById("error-message").style.display = "block";
                });
        }
    </script>
</body>
</html>