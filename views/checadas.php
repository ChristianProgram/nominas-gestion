<?php
include '../src/config/db.php';

// Obtener la fecha seleccionada o usar la fecha de hoy
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Inicializar las fechas de inicio y fin
$fechaInicio = '';
$fechaFin = '';

// Obtener el rango de fechas (si se selecciona un rango de fechas)
if (isset($_GET['fecha_desde']) && isset($_GET['fecha_hasta'])) {
    $fechaInicio = $_GET['fecha_desde'];
    $fechaFin = $_GET['fecha_hasta'];
} else {
    // Si no se selecciona un rango, se usa la semana de la fecha seleccionada
    $fechaInicioSemana = date('Y-m-d', strtotime('monday this week', strtotime($fechaSeleccionada)));
    $fechaFinSemana = date('Y-m-d', strtotime('sunday this week', strtotime($fechaSeleccionada)));
    $fechaInicio = $fechaInicioSemana;
    $fechaFin = $fechaFinSemana;
}

// Obtener lista de empleados
$sqlEmpleados = "SELECT Numero_Empleado, Nombre FROM empleados ORDER BY Nombre ASC";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

// Obtener checadas entre las fechas seleccionadas
$sqlChecadas = "SELECT Numero_Empleado, fecha, asistio FROM checadas_nuevo WHERE fecha BETWEEN :fechaInicio AND :fechaFin";
$stmtChecadas = $pdo->prepare($sqlChecadas);
$stmtChecadas->bindParam(':fechaInicio', $fechaInicio);
$stmtChecadas->bindParam(':fechaFin', $fechaFin);
$stmtChecadas->execute();
$checadas = $stmtChecadas->fetchAll(PDO::FETCH_ASSOC);

// Guardado automático (simulación al final del día)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechaSeleccionada = $_POST['fecha'];

    try {
        $pdo->beginTransaction();

        // Borrar registros previos de esa fecha
        $sqlDelete = "DELETE FROM checadas_nuevo WHERE fecha = :fecha";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->bindParam(':fecha', $fechaSeleccionada);
        $stmtDelete->execute();

        // Insertar nuevas asistencias
        $sqlInsert = "INSERT INTO checadas_nuevo (Numero_Empleado, fecha, asistio) VALUES (:Numero_Empleado, :fecha, :asistio)";
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($empleados as $empleado) {
            $numeroEmpleado = $empleado['Numero_Empleado'];
            $asistio = isset($_POST['asistencia'][$numeroEmpleado]) ? 1 : 0;

            $stmtInsert->execute([
                ':Numero_Empleado' => $numeroEmpleado,
                ':fecha' => $fechaSeleccionada,
                ':asistio' => $asistio
            ]);
        }

        $pdo->commit();
        echo "<div class='alert success'>Asistencias guardadas correctamente.</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div class='alert error'>Error al guardar las asistencias: " . $e->getMessage() . "</div>";
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
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Menú</h2>
            <ul>
                <li><a href="checadas.php">Checadas</a></li>
                <li><a href="empleados.php">Personal</a></li>
                <li><a href="calculo.php">Calculo</a></li>
                <li><a href="importar.php">Importar</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Checadas</h1>
                </div>

                <!-- Filtro por Fecha o Rango -->
                <form method="GET" action="checadas.php" class="filter-form">
                    <label for="fechaDesde">Selecciona un rango de fechas:</label><br>
                    <input type="text" id="fechaDesde" name="fecha_desde" value="<?php echo $fechaInicio; ?>" placeholder="Desde" onchange="this.form.submit()">
                    <input type="text" id="fechaHasta" name="fecha_hasta" value="<?php echo $fechaFin; ?>" placeholder="Hasta" onchange="this.form.submit()">
                </form>

                <!-- Tabla de Asistencias Semanal -->
                <table class="asistencias-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <?php
                            // Mostrar los días en el rango
                            $dias = [];
                            $inicio = new DateTime($fechaInicio);
                            $fin = new DateTime($fechaFin);
                            $interval = DateInterval::createFromDateString('1 day');
                            $periodo = new DatePeriod($inicio, $interval, $fin->modify('+1 day'));

                            // Días en español
                            $diasSemana = [
                                'Monday' => 'Lunes', 
                                'Tuesday' => 'Martes', 
                                'Wednesday' => 'Miércoles', 
                                'Thursday' => 'Jueves', 
                                'Friday' => 'Viernes', 
                                'Saturday' => 'Sábado', 
                                'Sunday' => 'Domingo'
                            ];

                            foreach ($periodo as $dia) {
                                $diaNombre = $dia->format('l'); // Obtiene el nombre del día en inglés
                                $diaNombreEspañol = $diasSemana[$diaNombre]; // Traduce al español
                                echo "<th>" . $diaNombreEspañol . " (" . $dia->format('Y-m-d') . ")</th>";
                                $dias[] = $dia->format('Y-m-d');
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($empleados as $empleado) {
                            $numeroEmpleado = $empleado['Numero_Empleado'];
                            echo "<tr>";
                            echo "<td>" . $empleado['Nombre'] . "</td>";

                            // Mostrar la asistencia de cada día en el rango
                            foreach ($dias as $dia) {
                                $asistio = 0;
                                // Verificar si existe la checada para el empleado y la fecha
                                foreach ($checadas as $checada) {
                                    if ($checada['Numero_Empleado'] == $numeroEmpleado && $checada['fecha'] == $dia) {
                                        $asistio = $checada['asistio'];
                                        break;
                                    }
                                }
                                $clase = $asistio ? 'present' : 'absent';
                                echo "<td class='$clase'>" . ($asistio ? '✔' : '✖') . "</td>";
                            }

                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Flatpickr para los calendarios
        flatpickr("#fechaDesde", {
            dateFormat: "Y-m-d",
            locale: "es"
        });

        flatpickr("#fechaHasta", {
            dateFormat: "Y-m-d",
            locale: "es"
        });
    </script>
</body>
</html>
