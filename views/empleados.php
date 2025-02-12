<?php
include '../src/config/db.php';

// Obtener el valor del filtro si existe
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Personal</title>
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
            <h1>Apartado de Personal</h1>
            <p>Podrás verificar todo el personal del departamento.</p>

            <!-- Formulario de Búsqueda -->
            <form method="GET" action="empleados.php">
                <input type="text" name="busqueda" placeholder="Buscar por nombre o número" value="<?php echo $busqueda; ?>">
                <button type="submit">Buscar</button>
            </form>

            <table border="1">
                <thead>
                    <tr>
                        <th>Número Empleado</th>
                        <th>Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Construcción de la consulta con límite y filtro de búsqueda
                    $sql = "SELECT numero, nombre FROM nominasueldo";
                    
                    // Agregar filtro si hay búsqueda
                    if (!empty($busqueda)) {
                        $sql .= " WHERE numero LIKE :busqueda OR nombre LIKE :busqueda";
                    }
                    
                    // Limitar a 100 registros
                    $sql .= " ORDER BY numero ASC LIMIT 10";
                    
                    $stmt = $conn->prepare($sql);

                    // Asignar parámetros si hay búsqueda
                    if (!empty($busqueda)) {
                        $busquedaParam = "%" . $busqueda . "%";
                        $stmt->bindParam(':busqueda', $busquedaParam, PDO::PARAM_STR);
                    }

                    $stmt->execute();
                    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Mostrar los registros
                    if (count($empleados) > 0) {
                        foreach ($empleados as $row) {
                            echo "<tr>";
                            echo "<td>" . $row['numero'] . "</td>";
                            echo "<td>" . $row['nombre'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No se encontró personal.</td></tr>";
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
