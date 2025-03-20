<?php
error_reporting(E_ALL); // Mostrar todos los errores
ini_set('display_errors', 1); // Mostrar errores en pantalla

include '../src/config/db.php'; // Asegúrate de que la ruta sea correcta

try {
    if (!isset($_GET['id'])) {
        die("ID de empleado no proporcionado.");
    }

    $id = $_GET['id'];

    // Obtener datos del empleado usando el procedimiento almacenado
    $sqlEmpleado = "CALL ObtenerEmpleadoPorID(:id)";
    $stmtEmpleado = $pdo->prepare($sqlEmpleado);
    $stmtEmpleado->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtEmpleado->execute();
    $empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        die("Empleado no encontrado.");
    }

    // Cerrar el cursor del primer conjunto de resultados
    $stmtEmpleado->closeCursor();

    // Obtener la lista de cargos (roles) usando el procedimiento almacenado
    $sqlRoles = "CALL ObtenerRoles()";
    $stmtRoles = $pdo->query($sqlRoles);
    $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

    // Cerrar el cursor del segundo conjunto de resultados
    $stmtRoles->closeCursor();

    // Actualizar cargo (rol) usando el procedimiento almacenado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_POST['departamento'])) {
            die("Cargo no proporcionado.");
        }

        $cargo = (int)$_POST['departamento']; // Convertir a entero

        $sqlUpdate = "CALL ActualizarDepartamentoEmpleado(:id, :cargo)";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':cargo', $cargo, PDO::PARAM_INT);
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
            <div class="sidebar-header">
                <h2>📊 Menú</h2>
            </div>
            <ul>
                <li><a href="checadas.php" class="active">🕒 Checadas</a></li>
                <li><a href="bonos.php" class="active">💰 Bonos</a></li>
                <li><a href="empleados.php">👨‍💼 Personal</a></li>
                <li><a href="calculo.php">📉 Cálculo</a></li>
                <li><a href="roles.php">🏆 Cargos</a></li>
                <li><a href="importar.php">📂 Importar</a></li>
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