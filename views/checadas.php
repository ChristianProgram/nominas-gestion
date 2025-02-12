<?php
include '../src/config/db.php';

// Obtener el valor del filtro si existe
$filtroFecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
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
            </ul>
        </div>
        <div class="content">
            <h1>Checadas</h1>
            <p>Aquí se mostrarán las checadas registradas en el sistema.</p>

            <!-- Filtro por Fecha con Calendario -->
            <form method="GET" action="checadas.php">
                <label for="fecha">Selecciona una Fecha:</label>
                <input type="date" name="fecha" id="fecha" value="<?php echo $filtroFecha; ?>" onchange="this.form.submit()">
            </form>

            <table border="1">
                <thead>
                    <tr>
                        <th>ID Checada</th>
                        <th>Fecha</th>
                        <th>ID Empleado</th>
                        <th>Nombre</th>
                        <th>Asistió</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta para obtener checadas con filtro de fecha
                    $sql = "SELECT ce.idChecadaEmpleado, c.fecha, ce.idEmpleado, ce.nombre, ce.asistio 
                            FROM checadaempleado ce
                            INNER JOIN checadas c ON ce.idFecha = c.idFecha";

                    // Aplicar filtro si hay una fecha seleccionada
                    if (!empty($filtroFecha)) {
                        $sql .= " WHERE c.fecha = :fecha";
                    }

                    $sql .= " ORDER BY c.fecha DESC";
                    $stmt = $conn->prepare($sql);

                    // Bind del parámetro si hay filtro
                    if (!empty($filtroFecha)) {
                        $stmt->bindParam(':fecha', $filtroFecha, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    $checadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($checadas) > 0) {
                        foreach ($checadas as $row) {
                            echo "<tr>";
                            echo "<td>" . $row['idChecadaEmpleado'] . "</td>";
                            echo "<td>" . $row['fecha'] . "</td>";
                            echo "<td>" . $row['idEmpleado'] . "</td>";
                            echo "<td>" . $row['nombre'] . "</td>";
                            echo "<td>" . ($row['asistio'] == 1 ? "Sí" : "No") . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No hay registros de checadas para esta fecha.</td></tr>";
                    }

                    // Cerrar conexión
                    $conn = null;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
