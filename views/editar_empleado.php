<?php
error_reporting(E_ALL); // Mostrar todos los errores
ini_set('display_errors', 1); // Mostrar errores en pantalla

include '../src/config/db.php'; // Asegúrate de que la ruta sea correcta

try {
    if (!isset($_GET['id'])) {
        die("ID de empleado no proporcionado.");
    }

    $id = $_GET['id'];

    // Obtener datos del empleado
    $sqlEmpleado = "SELECT ID, Nombre, Numero_Empleado, Departamento FROM empleados WHERE ID = :id";
    $stmtEmpleado = $pdo->prepare($sqlEmpleado);
    $stmtEmpleado->bindParam(':id', $id);
    $stmtEmpleado->execute();
    $empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        die("Empleado no encontrado.");
    }

    // Obtener la lista de cargos (roles)
    $sqlRoles = "SELECT id, nombre_cargo FROM roles";
    $stmtRoles = $pdo->query($sqlRoles);
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // Actualizar cargo (rol)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['departamento'])) {
            die("Cargo no proporcionado.");
        }

        $cargo = (int)$_POST['departamento']; // Convertir a entero

        $sqlUpdate = "UPDATE empleados SET Departamento = :cargo WHERE ID = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':cargo', $cargo, PDO::PARAM_INT); // Asegurar que sea un entero
        $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtUpdate->execute();

        header("Location: empleados.php"); // Redirigir a la lista de empleados
        exit();
    }
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
} catch (Exception $e) {
    die("Error general: " . $e->getMessage());
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

                <form method="POST" action="editar_empleado.php?id=<?php echo $id; ?>">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" value="<?php echo htmlspecialchars($empleado['Nombre']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="numero_empleado">Número de Empleado:</label>
                        <input type="text" id="numero_empleado" value="<?php echo htmlspecialchars($empleado['Numero_Empleado']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="departamento">Cargo:</label>
                        <select id="departamento" name="departamento" required>
                            <option value="">Seleccione un cargo</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id']; ?>" <?php echo ($rol['id'] == $empleado['Departamento']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol['nombre_cargo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="empleados.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>