<?php
include '../src/config/db.php'; // Asegúrate de que la ruta sea correcta

// Obtener el término de búsqueda si se proporciona
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Modificar la consulta para incluir la búsqueda y el nombre del cargo
$sqlEmpleados = "
    SELECT e.ID, e.Nombre, e.Numero_Empleado, r.nombre_cargo AS Departamento 
    FROM empleados e
    LEFT JOIN roles r ON e.Departamento = r.id
    WHERE e.Nombre LIKE :search OR e.Numero_Empleado LIKE :search
    ORDER BY e.Nombre ASC
";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Empleados</title>
    <link rel="stylesheet" href="../public/styles.css"> <!-- Ajusta la ruta del CSS -->
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Menú</h2>
            <ul>
                <li><a href="checadas.php">Checadas</a></li>
                <li><a href="empleados.php">Personal</a></li>
                <li><a href="calculo.php">Calculo</a></li>
                <li><a href="roles.php">Cargos</a></li>
                <li><a href="importar.php">Importar</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Lista de Empleados</h1>
                </div>

                <!-- Formulario de búsqueda -->
                <form method="GET" action="empleados.php">
                    <input type="text" name="search" placeholder="Buscar por nombre o número" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit">Buscar</button>
                </form>

                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Número de Empleado</th>
                            <th>Departamento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleados as $empleado): ?>
                            <tr>
                                <td><?php echo $empleado['ID']; ?></td>
                                <td><?php echo $empleado['Nombre']; ?></td>
                                <td><?php echo $empleado['Numero_Empleado']; ?></td>
                                <td><?php echo $empleado['Departamento']; ?></td>
                                <td><a href="editar_empleado.php?id=<?php echo $empleado['ID']; ?>">Editar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (empty($empleados)): ?>
                    <p>No se encontraron empleados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>