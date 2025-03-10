<?php
include '../src/config/db.php'; // Asegúrate de incluir la conexión a la base de datos

// Obtener la fecha seleccionada o usar la fecha de hoy
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Calcular el inicio y fin de la semana (lunes a domingo)
$fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
$fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));

// Consulta para obtener los datos de los empleados y sus faltas en la semana
$sql = "
    SELECT 
        e.Numero_Empleado, 
        e.Nombre,
        COUNT(f.fecha) AS Faltas_Semana,
        SUM(CASE WHEN DAYOFWEEK(f.fecha) = 1 THEN 1 ELSE 0 END) AS Domingos_Trabajados
    FROM empleados e
    LEFT JOIN faltas f ON e.Numero_Empleado = f.Numero_Empleado 
        AND f.fecha BETWEEN :fechaInicio AND :fechaFin
    GROUP BY e.Numero_Empleado
";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':fechaInicio', $fechaInicioSemana);
$stmt->bindParam(':fechaFin', $fechaFinSemana);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cálculo de Nómina</title>
    <link rel="stylesheet" href="../public/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
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
            <h2>Menú</h2>
            <ul>
                <li><a href="checadas.php">Checadas</a></li>
                <li><a href="empleados.php">Personal</a></li>
                <li><a href="calculo.php">Cálculo</a></li>
                <li><a href="roles.php">Cargos</a></li>
                <li><a href="importar.php">Importar</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Cálculo de Nómina</h1>
                </div>

                <!-- Filtro por semana -->
                <form method="GET" action="calculo.php" class="filter-form">
                    <label for="fecha">Selecciona una semana:</label>
                    <input type="text" id="fecha" name="fecha" value="<?php echo $fechaSeleccionada; ?>" onchange="this.form.submit()">
                </form>

                <!-- Botón para calcular la nómina -->
                <button class="boton-calcular" onclick="calcularNomina()">Calcular Percepciones</button>
                <p class="cargando" id="cargando">Calculando... Por favor, espera.</p>
                <p id="error-message" class="error" style="display: none;">Hubo un error al procesar la solicitud. Por favor, intenta nuevamente.</p>

                <!-- Tabla de Empleados -->
                <table class="asistencias-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Número de Empleado</th>
                            <th>Faltas</th>
                            <th>Prima Dominical</th>
                            <th>Asistencias</th>
                            <th>Percepción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleados as $empleado): ?>
                            <?php
                            // Calcular la prima dominical (20% extra por domingo trabajado)
                            $primaDominical = $empleado['Domingos_Trabajados'] * 100 * 0.2; // Ejemplo: $100 por día + 20%

                            // Calcular el bono de asistencia (3 días extras si no hay faltas)
                            $bonoAsistencia = ($empleado['Faltas_Semana'] == 0) ? 3 * 100 : 0; // Ejemplo: $100 por día

                            // Calcular la percepción total
                            $percepcionTotal = (5 * 100) + $primaDominical + $bonoAsistencia; // 5 días laborales + prima + bono
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($empleado['Nombre']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Numero_Empleado']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['Faltas_Semana']); ?></td>
                                <td>$<?php echo number_format($primaDominical, 2); ?></td>
                                <td><?php echo 5 - $empleado['Faltas_Semana']; ?></td>
                                <td>$<?php echo number_format($percepcionTotal, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Contenedor para la tabla de resultados -->
                <div id="resultados"></div>
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