<?php
include '../src/config/db.php';

// Obtener lista de empleados
$sqlEmpleados = "SELECT ID, Nombre, Numero_Empleado, Departamento FROM empleados ORDER BY Nombre ASC";
$stmtEmpleados = $pdo->prepare($sqlEmpleados);
$stmtEmpleados->execute();
$empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Empleados</title>
    <link rel="stylesheet" href="../public/styles.css">
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
                    <h1 class="section-title">Lista de Empleados</h1>
                </div>

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
            </div>
        </div>
    </div>
</body>
</html>