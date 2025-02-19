<?php
include '../src/config/db.php'; // Ajusta la ruta según la ubicación de tu archivo de conexión a la base de datos

$id = $_GET['id'];

// Obtener datos del empleado
$sqlEmpleado = "SELECT ID, Nombre, Numero_Empleado, Departamento FROM empleados WHERE ID = :id";
$stmtEmpleado = $pdo->prepare($sqlEmpleado);
$stmtEmpleado->bindParam(':id', $id);
$stmtEmpleado->execute();
$empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);

// Actualizar departamento
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $departamento = $_POST['departamento'];

    $sqlUpdate = "UPDATE empleados SET Departamento = :departamento WHERE ID = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':departamento', $departamento);
    $stmtUpdate->bindParam(':id', $id);
    $stmtUpdate->execute();

    header("Location: empleados.php"); // Ajusta la ruta según la ubicación de tu archivo de lista de empleados
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empleado</title>
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
                <li><a href="importar.php">Importar</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="content-container">
                <div class="section-header">
                    <h1 class="section-title">Editar Empleado</h1>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" value="<?php echo $empleado['Nombre']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="numero_empleado">Número de Empleado:</label>
                        <input type="text" id="numero_empleado" value="<?php echo $empleado['Numero_Empleado']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="departamento">Departamento:</label>
                        <input type="text" id="departamento" name="departamento" value="<?php echo $empleado['Departamento']; ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="../../empleados.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>