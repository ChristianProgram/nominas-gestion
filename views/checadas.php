<?php
include '../src/config/db.php';

// Obtener la fecha seleccionada o usar la fecha de hoy
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener lista de empleados
$sqlEmpleados = "SELECT Numero_Empleado, Nombre FROM empleados ORDER BY Nombre ASC";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

// Obtener checadas de la fecha seleccionada
$sqlChecadas = "SELECT Numero_Empleado, asistio FROM checadas_nuevo WHERE fecha = :fecha";
$stmtChecadas = $pdo->prepare($sqlChecadas);
$stmtChecadas->bindParam(':fecha', $fechaSeleccionada);
$stmtChecadas->execute();
$checadas = $stmtChecadas->fetchAll(PDO::FETCH_KEY_PAIR);

// Guardado automático (simulación al final del día)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fechaSeleccionada = $_POST['fecha'];

    try {
        $pdo->beginTransaction();

        // Borrar registros previos de esa fecha
        $sqlDelete = "DELETE FROM checadas WHERE fecha = :fecha";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->bindParam(':fecha', $fechaSeleccionada);
        $stmtDelete->execute();

        // Insertar nuevas asistencias
        $sqlInsert = "INSERT INTO checadas (Numero_Empleado, fecha, asistio) VALUES (:Numero_Empleado, :fecha, :asistio)";
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

                <!-- Filtro por Fecha -->
                <form method="GET" action="checadas.php" class="filter-form">
                    <label for="fecha">Selecciona una Fecha:</label>
                    <input type="text" id="fecha" name="fecha" value="<?php echo $fechaSeleccionada; ?>" onchange="this.form.submit()">
                </form>

                <!-- Formulario de Asistencias -->
                <form method="POST" action="checadas.php">
                    <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">

                    <table class="payroll-table">
                        <thead>
                            <tr>
                                <th>ID Empleado</th>
                                <th>Nombre</th>
                                <th>Asistió</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($empleados as $empleado) {
                                $numeroEmpleado = $empleado['Numero_Empleado'];
                                $nombre = $empleado['Nombre'];
                                $asistio = isset($checadas[$numeroEmpleado]) ? $checadas[$numeroEmpleado] : 0;
                                $checked = $asistio ? 'checked' : '';

                                echo "<tr>";
                                echo "<td>" . $numeroEmpleado . "</td>";
                                echo "<td>" . $nombre . "</td>";
                                echo "<td><label class='checkbox-container'><input type='checkbox' name='asistencia[" . $numeroEmpleado . "]' $checked><span class='checkmark'></span></label></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar Asistencias</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Flatpickr para el calendario
        flatpickr("#fecha", {
            dateFormat: "Y-m-d",
            locale: "es"
        });
    </script>
</body>
</html>