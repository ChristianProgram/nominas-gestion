<?php
include '../src/config/db.php';

// Obtener fecha seleccionada o usar la fecha de hoy por defecto
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener lista de empleados
$sqlEmpleados = "SELECT Numero_Empleado, Nombre FROM empleados ORDER BY Nombre ASC";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

// Si se envió el formulario para guardar asistencias
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
        $sqlInsert = "INSERT INTO checadas (idEmpleado, fecha, asistio) VALUES (:idEmpleado, :fecha, :asistio)";
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($empleados as $empleado) {
            $idEmpleado = $empleado['Numero_Empleado'];
            $asistio = isset($_POST['asistencia'][$idEmpleado]) ? 1 : 0;

            $stmtInsert->execute([
                ':idEmpleado' => $idEmpleado,
                ':fecha' => $fechaSeleccionada,
                ':asistio' => $asistio
            ]);
        }

        $pdo->commit();
        echo "<p style='color:green;'>Asistencias guardadas correctamente.</p>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>Error al guardar las asistencias: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checadas</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Menú</h2>
            <ul>
                <li><a href="importar.php">Importar</a></li>
                <li><a href="checadas.php">Checadas</a></li>
                <li><a href="calculo.php">Calculo</a></li>
                <li><a href="empleados.php">Personal</a></li>
            </ul>
        </div>
        <div class="content">
            <h1>Checadas</h1>

            <!-- Filtro por Fecha -->
            <form method="GET" action="checadas.php">
                <label for="fecha">Selecciona una Fecha:</label>
                <input type="date" name="fecha" id="fecha" value="<?php echo $fechaSeleccionada; ?>" onchange="this.form.submit()">
            </form>

            <!-- Formulario de Asistencias -->
            <form method="POST" action="checadas.php">
                <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">

                <table border="1">
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
                            echo "<tr>";
                            echo "<td>" . $empleado['Numero_Empleado'] . "</td>";
                            echo "<td>" . $empleado['Nombre'] . "</td>";
                            echo "<td><input type='checkbox' name='asistencia[" . $empleado['Numero_Empleado'] . "]'></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <br>
                <button type="submit">Guardar Asistencias</button>
            </form>
        </div>
    </div>
</body>
</html>
